<?php
/**
 * @package     Mautic
 * @copyright   2016 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticGmailBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\LeadBundle\Controller\LeadAccessTrait;
use Mautic\LeadBundle\Controller\LeadDetailsTrait;
use Mautic\UserBundle\Security\Authenticator\FormAuthenticator;
use Mautic\UserBundle\Security\Provider\UserProvider;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SimpleFormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception as Exception;

class TimelineController extends CommonController
{

    use LeadAccessTrait;
    use LeadDetailsTrait;
    use DetailsTrait;
    use AccessTrait;

    public function indexAction(Request $request, $page = 1)
    {
        if ($this->factory->getSecurity()->isAnonymous()) {
            return new RedirectResponse($this->generateUrl('mautic_gmail_timeline_login',
                [ 'returnUrl'=>$this->generateUrl('mautic_gmail_timeline_index')]));
        }

        $leads = $this->checkAllAccess('view');

        if ($leads instanceof Response) {

            return $leads;
        }
        
        $this->setListFilters();

        $session = $this->get('session');
        if ($request->getMethod() === 'POST' && $request->request->has('search')) {
            $filters = [
                'search'        => InputHelper::clean($request->request->get('search')),
                'includeEvents' => InputHelper::clean($request->request->get('includeEvents', [])),
                'excludeEvents' => InputHelper::clean($request->request->get('excludeEvents', []))
            ];
            $session->set('mautic.gmail.timeline.filters', $filters);
        } else {
            $filters = null;
        }

        $order  = [
            $session->get('mautic.gmail.timeline.orderby'),
            $session->get('mautic.gmail.timeline.orderbydir'),
        ];

        // get all events grouped by lead
        $events = $this->getAllEngagements($leads, $filters, $order, $page, 25);

        $str = $this->request->server->get('QUERY_STRING');
        $str = substr($str, strpos($str, '?')+1);
        parse_str($str, $query);

        return $this->delegateView(
            [
                'viewParameters'  => [
                    'leads'        => $leads,
                    'page'        => $page,
                    'events'      => $events,
                    'tmpl'   => (!$this->request->isXmlHttpRequest())?'index':'',
                    'newCount' => (array_key_exists('count', $query) && $query['count'])?$query['count']:0
                ],
                'passthroughVars' => [
                    'route'         => false,
                    'mauticContent' => 'gmailTimeline',
                    'timelineCount' => $events['total']
                ],
                'contentTemplate' => 'MauticGmailBundle:Timeline:list.html.php'
            ]
        );
    }

    public function viewAction(Request $request, $leadId, $page = 1)
    {
        if (empty($leadId)) {

            return $this->notFound();
        }

        if ($this->factory->getSecurity()->isAnonymous()) {
            return new RedirectResponse($this->generateUrl('mautic_gmail_timeline_login',
                [ 'returnUrl'=>$this->generateUrl('mautic_gmail_timeline_index')]));
        }

        $lead = $this->checkAccess($leadId, 'view');
        if ($lead instanceof Response) {

            return $lead;
        }

        $this->setListFilters();

        $session = $this->get('session');
        if ($request->getMethod() === 'POST' && $request->request->has('search')) {
            $filters = [
                'search'        => InputHelper::clean($request->request->get('search')),
                'includeEvents' => InputHelper::clean($request->request->get('includeEvents', [])),
                'excludeEvents' => InputHelper::clean($request->request->get('excludeEvents', []))
            ];
            $session->set('mautic.gmail.timeline.'.$leadId.'.filters', $filters);
        } else {
            $filters = null;
        }

        $order  = [
            $session->get('mautic.gmail.timeline.'.$leadId.'.orderby'),
            $session->get('mautic.gmail.timeline.'.$leadId.'.orderbydir'),
        ];

        $events = $this->getEngagements($lead, $filters, $order, $page);

        return $this->delegateView(
            [
                'viewParameters'  => [
                    'lead'        => $lead,
                    'page'        => $page,
                    'events'      => $events
                ],
                'passthroughVars' => [
                    'route'         => false,
                    'mauticContent' => 'gmailTimeline',
                    'timelineCount' => $events['total']
                ],
                'contentTemplate' => 'MauticGmailBundle:Timeline:index.html.php'
            ]
        );
    }

    /**
     * Generates login form and processes login
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function loginAction (Request $request)
    {
        $session = $this->request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        }

        if (!empty($error)) {
            if (($error instanceof Exception\BadCredentialsException)) {
                $msg = 'mautic.user.auth.error.invalidlogin';
            } elseif ($error instanceof Exception\DisabledException) {
                $msg = 'mautic.user.auth.error.disabledaccount';
            } else {
                $msg = $error->getMessage();
            }

            $this->addFlash($msg, array(), 'error', null, false);
        }
        $request->query->set('tmpl', 'login');
        
        return $this->delegateView(array(
            'viewParameters'  => array(
                'last_username' => $session->get(Security::LAST_USERNAME)
            ),
            'contentTemplate' => 'MauticGmailBundle:Timeline:login.html.php',
            'passthroughVars' => array(
                'route'          => $this->generateUrl('login'),
                'mauticContent'  => 'user',
                'sessionExpired' => true
            )
        ));
    }

    /**
     * Authenticate user using a subrequest to the auth mechanism
     */
    public function loginCheckAction(Request $request)
    {
        $subRequest = Request::create($this->generateUrl('mautic_user_logincheck'), 'POST',
            array('_username' => $request->get('_username'),
                '_password' => $request->get('_password'),
                '_csrf_token' => $request->get('_csrf_token'),
                '_remember_me' => $request->get('_remember_me')
                ),
            $request->cookies->all(), array(), $request->server->all());

        $httpKernel = $this->container->get('http_kernel');
        $httpKernel->handle($subRequest, HttpKernelInterface::MASTER_REQUEST, false);

        return $this->redirect($this->generateUrl('mautic_gmail_timeline_index'));
    }

}