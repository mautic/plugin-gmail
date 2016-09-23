<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title>Mautic</title>
    <meta name="robots" content="noindex, nofollow"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="icon" type="image/x-icon" href="<?php echo $view['assets']->getUrl('media/images/favicon.ico') ?>"/>
    <link rel="apple-touch-icon" href="<?php echo $view['assets']->getUrl('media/images/apple-touch-icon.png') ?>"/>
    <?php $view['assets']->outputSystemStylesheets(); ?>
    <?php echo $view->render('MauticCoreBundle:Default:script.html.php'); ?>
    <?php $view['assets']->outputHeadDeclarations(); ?>
</head>
<body>
<section id="main" role="main">
    <div class="container" style="margin:20px 0;">
        <div class="row">
            <div class="col-lg-4 col-lg-offset-4">
                <div class="panel" name="form-login">
                    <div class="panel-body">
                        <div class="mautic-logo img-circle mb-md text-center">
                            <svg version="1.1" class="mautic-logo-figure" xmlns="http://www.w3.org/2000/svg"
                                 xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 128 128"
                                 enable-background="new 0 0 128 128" xml:space="preserve">
                            <path class="circle" d="M64,119.843c-30.937,0-56.108-25.17-56.108-56.108C7.893,32.799,33.063,7.629,64,7.629
                            c7.474,0,14.734,1.446,21.578,4.301c1.936,0.807,2.85,3.03,2.041,4.964c-0.805,1.937-3.029,2.849-4.963,2.043
                            C76.742,16.472,70.465,15.221,64,15.221c-26.751,0-48.514,21.763-48.514,48.514c0,26.752,21.763,48.516,48.514,48.516
                            c26.751,0,48.513-21.764,48.513-48.516c0-5.735-0.988-11.345-2.939-16.677c-0.723-1.968,0.289-4.149,2.258-4.869
                            c1.971-0.721,4.15,0.291,4.871,2.259c2.258,6.171,3.404,12.661,3.404,19.287C120.107,94.673,94.938,119.843,64,119.843"/>
                                <polygon class="m" points="88.545,57.987 79.807,67.055 84.664,87.332 95.621,87.332 "/>
                                <polygon class="m-arrow" points="84.982,31.157 88.004,34.176 63.933,59.605 43.446,38.475 31.664,87.332 42.621,87.332
                            49.122,60.198 63.933,76.399 95.699,41.815 98.738,44.875 101.75,27.784 "/>
                            </svg>
                        </div>
                        <div id="main-panel-flash-msgs">
                            <?php echo $view->render('MauticCoreBundle:Notification:flashes.html.php'); ?>
                        </div>

                        <form class="form-group login-form" name="login" data-toggle="ajax" role="form"
                              action="<?php echo $view['router']->path('mautic_gmail_timeline_logincheck') ?>"
                              method="post">
                            <div class="input-group mb-md">

                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <label for="username" class="sr-only"><?php echo $view['translator']->trans(
                                        'mautic.user.auth.form.loginusername'
                                    ); ?></label>
                                <input type="text" id="username" name="_username"
                                       class="form-control input-lg" value="<?php echo $last_username ?>" required
                                       autofocus
                                       placeholder="<?php echo $view['translator']->trans(
                                           'mautic.user.auth.form.loginusername'
                                       ); ?>"/>
                            </div>
                            <div class="input-group mb-md">
                                <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                <label for="password" class="sr-only"><?php echo $view['translator']->trans(
                                        'mautic.core.password'
                                    ); ?>:</label>
                                <input type="password" id="password" name="_password"
                                       class="form-control input-lg" required
                                       placeholder="<?php echo $view['translator']->trans('mautic.core.password'); ?>"/>
                            </div>

                            <div class="checkbox-inline custom-primary pull-left mb-md">
                                <label for="remember_me">
                                    <input type="checkbox" id="remember_me" name="_remember_me"/>
                                    <span></span>
                                    <?php echo $view['translator']->trans('mautic.user.auth.form.rememberme'); ?>
                                </label>
                            </div>

                            <input type="hidden" name="_csrf_token"
                                   value="<?php echo $view['form']->csrfToken('authenticate') ?>"/>
                            <button class="btn btn-lg btn-primary btn-block"
                                    type="submit"><?php echo $view['translator']->trans(
                                    'mautic.user.auth.form.loginbtn'
                                ); ?></button>

                            <div class="mt-sm text-right">
                                <a href="<?php echo $view['router']->path('mautic_user_passwordreset'); ?>"
                                   target="_new"><?php echo $view['translator']->trans(
                                        'mautic.user.user.passwordreset.link'
                                    ); ?></a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-lg-offset-4 text-center text-muted">
                <?php echo $view['translator']->trans('mautic.core.copyright', array('%date%' => date('Y'))); ?>
            </div>
        </div>
    </div>
</section>
<?php echo $view['security']->getAuthenticationContent(); ?>
</body>
</html>
