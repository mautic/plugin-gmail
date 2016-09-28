<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

//$request = $app->getRequest();
//if (!$request->isXmlHttpRequest() && $view['slots']->get('contentOnly', false) === false):
//    //load base template
//    $template = ($request->get('contentOnly')) ? 'slim' : 'base';
//    $view->extend("MauticCoreBundle:Default:$template.html.php");
//endif;

if (isset($tmpl) && $tmpl == 'index') {
    $view->extend('MauticGmailBundle:Timeline:index.html.php');
}

$baseUrl = isset($lead) ? $view['router']->path(
    'mautic_gmail_timeline_view',['leadId' => $lead->getId()]
) :
    $view['router']->path('mautic_gmail_timeline_index');
?>
<style>
    .col-xs-6 {
        padding-left: 2px;
        padding-right: 2px;
    }

    span.timeline-icon {
        float: right;
        margin-top: -5px;
    }

    span.timeline-lead {
        font-weight: bold;
    }

    .timeline-row {
        padding: 15px;
        border: 1px solid lightblue;
        margin: 10px;
    }

    .timeline-row span {
        display: inline-block;
        vertical-align: middle;
    }

    span.timeline-name {
        display: block;
    }

    div.timeline-details {
        border-top: 1px solid lightgray;
        margin-top: 10px;
        padding-top: 10px;
    }

    div.tl-header {
        color: #333;
        background: #eee;
        padding: 5px 10px;
    }

    div.tl-header .tl-new {
        font-weight: bold;
        color #300;
    }

    .timeline-row-highlighted {
        background-color: #fafafa;
    }

    .timeline-row.timeline-featured {
        background: #eee;
    }

    .timeline-row.tr-new {
        background: #FFF2D4;
    }

    span.timeline-icon {
        width: 25px;
    }

</style>
<div class="tl-header">
    Displaying <?= $events['total'] ?> events (<span class="tl-new"><?= $newCount ?></span> new)
</div>
<!-- timeline -->
<div class="event-list" id="timeline-container">

    <?php foreach ($events['events'] as $counter => $event): ?>
        <?php
        $counter += 1; // prevent 0
        $icon = (isset($event['icon'])) ? $event['icon'] : 'fa-history';
        $eventLabel = (isset($event['eventLabel'])) ? $event['eventLabel'] : $event['eventType'];
        if (is_array($eventLabel)):
            $linkType = empty($eventLabel['isExternal']) ? 'data-toggle="ajax"' : 'target="_new"';
            $eventLabel = "<a href=\"{$eventLabel['href']}\" $linkType>{$eventLabel['label']}</a>";
        endif;

        $details = '';
        if (isset($event['contentTemplate']) && $view->exists($event['contentTemplate'])):
            $details = trim($view->render($event['contentTemplate'], ['event' => $event]));
        endif;

        $rowStripe = ($counter % 2 === 0) ? ' timeline-row-highlighted' : '';
        ?>
        <div class="timeline-row<?php echo $rowStripe; ?><?php if (!empty($event['featured'])) {
            echo ' timeline-featured';
        }
        if ($newCount-- > 0) {
            echo " tr-new";
        }
        ?>">
            <span class="timeline-row-id hide"><?= $event['timestamp']->format('U') ?></span>
            <span class="timeline-row-lead-id hide"><?= $event['leadId'] ?></span>
                <span class="timeline-icon">
                    <a href="javascript:void(0);"
                       onclick="jQuery('#timeline-details-<?php echo $counter; ?>').toggleClass('hide')"
                       data-activate-details="<?php echo $counter; ?>"
                       class="btn btn-xs btn-nospin btn-default<?php if (empty($details)) {
                           echo " disabled";
                       } ?>" data-toggle="tooltip"
                       title="<?php echo $view['translator']->trans('mautic.lead.timeline.toggle_details'); ?>">
                        <span class="fa fa-fw <?php echo $icon ?>"></span>
                    </a>
                </span>

                <span class="timeline-icon">
                    <a href="javascript:void(0);" class="btn btn-xs btn-nospin" data-toggle="tooltip"
                       onclick="jQuery(this).toggleClass('btn-warning')"
                       title="Mute notifications">
                        <span class="fa fa-fw fa-bell-slash-o"></span>
                    </a>
                </span>

                <span class="timeline-type"><?php if (isset($event['eventType'])) {
                        echo $event['eventType'];
                    } ?>: </span>

            <span class="timeline-lead ellipsis"><a href="mailto:<?php echo $event['leadEmail']; ?>"
                                                    title="<?php echo $event['leadEmail']; ?>"
                                                    target="_new"><?php echo $event['leadName']; ?></a></span>

            <span class="timeline-timestamp"> on <?php echo $view['date']->toText($event['timestamp']); ?></span>

            <?php if ($eventLabel !== $event['eventType']): ?>
                <span class="timeline-name ellipsis"><?php echo $eventLabel; ?></span>
            <?php endif; ?>

            <?php if (!empty($details)): ?>
                <div class="timeline-details hide" id="timeline-details-<?php echo $counter; ?>">
                    <?php echo $details ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

</div>

<?php echo $view->render(
    'MauticCoreBundle:Helper:pagination.html.php',
    [
        'page' => $events['page'],
        'fixedPages' => $events['maxPages'],
        'fixedLimit' => true,
        'baseUrl' => $baseUrl,
        'target' => '#timeline-table',
        'totalItems'=>$events['total']
    ]
); ?>

<!--/ timeline -->

