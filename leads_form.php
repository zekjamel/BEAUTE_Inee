<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<head>
    <link rel="stylesheet" href="<?= module_dir_url(LANDING_PAGE_MODULE_NAME, 'assets/css/landing_page.css'); ?>">
</head>

<div class="clients-login-wrapper">

    <!-- === Left side: visuel / animation === -->
    <div class="login-sidebar bg--noir-pur"> <!-- BG value in BO custom CSS -->

        <div class="sidebar-content text-center">
            <!-- LOGOS DYNAMIQUES -->
            <div class="clients-login-logo" id="logo-white">
                <?php if (get_company_logo()) {
                    get_company_logo(base_url(), 'logo-white');
                } ?>
            </div>

            <h2 class="tw-font-semibold mbot10">
                <?= _l('welcome_title_login_client'); ?>
                <?php  ?>
            </h2>
            <p class="text-muted mbot30">
                <?= _l('description_title_login_client'); ?>
            </p>

            <!-- ANIMATION SVG “PATH” -->
            <div class="login-animation">
                <svg viewBox="0 0 200 200" class="login-path-svg">
                    <path id="motionPath" fill="none" stroke="rgba(255,255,255,0.5)" stroke-width="4"
                          d="M10,100 C40,10 160,10 190,100 S160,190 10,100"/>
                </svg>
                <div class="dot"></div>
            </div>
        </div>

    </div>

    <!-- === Right side: le formulaire === -->
    <div class="login-main">

        <div class="login-form-container">

            <h1 class="tw-font-semibold text-center mbot20 login-heading">
                <?php echo _l('landing_page_title_leads_form'); ?>
            </h1>

            <div class="panel_s">
                <div class="panel-body">
                    <iframe width="400" height="600" src="https://beauteinee.agence-xr.io/forms/wtl/d711f8bd1834ad50cfa2462a829f2d3f" frameborder="0" sandbox="allow-top-navigation allow-forms allow-scripts allow-same-origin allow-popups" allowfullscreen></iframe>
                </div>
            </div>

        </div>

    </div>

</div>