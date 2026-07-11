<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<header id="header" class="tra-menu navbar-light white-scroll">
    <div class="header-wrapper">
        <!-- MOBILE HEADER -->
        <div class="wsmobileheader clearfix">
            <span class="smllogo">
                <div id="logo-black-m">
                    <a href="<?= base_url('lp'); ?>" class="logo logo-black">
                        <img src="<?= $theme_assets_url; ?>images/BI-logoNoir.png" alt="Beauté INÉE">
                    </a>
                </div>
                <div id="logo-white-m">
                    <a href="<?= base_url('lp'); ?>" class="logo logo-white">
                        <img src="<?= $theme_assets_url; ?>images/BI-logoBlanc.png" alt="Beauté INÉE">
                    </a>
                </div>
            </span>
            <a id="wsnavtoggle" class="wsanimated-arrow"><span></span></a>
        </div>
        <!-- NAVIGATION MENU -->
        <div class="wsmainfull menu clearfix">
            <div class="wsmainwp clearfix">
                <!-- HEADER BLACK LOGO (menu sticky / fond blanc) -->
                <div class="desktoplogo" id="logo-black">
                    <a href="<?= base_url('lp'); ?>" class="logo logo-black">
                        <img src="<?= $theme_assets_url; ?>images/BI-logoNoir.png" alt="Beauté INÉE" style="max-width:130px;">
                    </a>
                </div>
                <!-- HEADER WHITE LOGO (menu transparent en haut de page) -->
                <div class="desktoplogo" id="logo-white">
                    <a href="<?= base_url('lp'); ?>" class="logo logo-white">
                        <img src="<?= $theme_assets_url; ?>images/BI-logoBlanc.png" alt="Beauté INÉE" style="max-width:130px;">
                    </a>
                </div>
                <!-- MAIN MENU -->
                <nav class="wsmenu clearfix"><div class="overlapblackbg"></div>
                    <ul class="wsmenu-list">

                        <!-- Prendre rendez-vous -->
                        <li aria-haspopup="true">
                            <a href="#" class="h-link"><?= _l('menu_book_appointment'); ?> <span class="wsarrow"></span></a>
                            <ul class="sub-menu">
                                <li aria-haspopup="true">
                                    <a href="<?= _l('book_skinconsultation_url'); ?>" target="_blank">
                                        <?= _l('menu_book_skinconsultation'); ?>
                                    </a>
                                </li>
                                <li aria-haspopup="true">
                                    <a href="<?= _l('book_followup_url'); ?>" target="_blank">
                                        <?= _l('menu_book_followup'); ?>
                                    </a>
                                </li>
                                <li aria-haspopup="true">
                                    <a href="<?= _l('book_diagnostic_treatment_url'); ?>" target="_blank">
                                        <?= _l('menu_book_diagnostic_treatment'); ?>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Boutique -->
                        <li aria-haspopup="true">
                            <a href="#" class="h-link"><?= _l('menu_shop'); ?> <span class="wsarrow"></span></a>
                            <ul class="sub-menu">
                                <li aria-haspopup="true">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#modal-diagnostic"><?= _l('menu_shop_selection'); ?></a>
                                </li>
                                <li aria-haspopup="true">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#modal-diagnostic"><?= _l('menu_shop_k_beauty'); ?></a>
                                </li>
                            </ul>
                        </li>

                        <!-- Prédiagnostic -->
                        <li aria-haspopup="true">
                            <a href="#" class="h-link"><?= _l('menu_prediagnostic'); ?> <span class="wsarrow"></span></a>
                            <ul class="sub-menu">
                                <li aria-haspopup="true">
                                    <a href="<?= _l('prediagnostic_free_url'); ?>" target="_blank">
                                        <?= _l('menu_prediagnostic_free'); ?>
                                    </a>
                                </li>
                                <li aria-haspopup="true">
                                    <a href="<?= _l('prediagnostic_mysmartdiag_url'); ?>" target="_blank">
                                        <?= _l('menu_prediagnostic_mysmartdiag'); ?>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- La Marque -->
                        <li aria-haspopup="true">
                            <a href="#" class="h-link"><?= _l('menu_brand'); ?> <span class="wsarrow"></span></a>
                            <ul class="sub-menu">
                                <li aria-haspopup="true">
                                    <a href="#histoire">
                                        <?= _l('menu_brand_story'); ?>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Partenaire -->
                        <li aria-haspopup="true">
                            <a href="#" class="h-link"><?= _l('menu_partner'); ?> <span class="wsarrow"></span></a>
                            <ul class="sub-menu">
                                <li aria-haspopup="true">
                                    <a href="<?= _l('partner_experts_url'); ?>" target="_blank">
                                        <?= _l('menu_partner_experts'); ?>
                                    </a>
                                </li>
                                <?php /*<li aria-haspopup="true">
                                    <a href="<?= _l('partner_brands_url'); ?>" target="_blank">
                                        <?= _l('menu_partner_brands'); ?>
                                    </a>
                                </li> */ ?>
                            </ul>
                        </li>

                        <!-- Compte -->
                        <li class="nl-simple btn-action" aria-haspopup="true">
                            <a href="/login" class="btn btn--tra-gold hover--gold last-link" aria-label="Connexion">
                                <i class="fa fa-user"></i>
                            </a>
                        </li>

                        <!-- Panier -->
                        <?php /*<li class="nl-simple btn-action" aria-haspopup="true">
                            <a href="<?= _l('shop_cart_url'); ?>" target="_blank" class="btn btn--tra-white hover--white last-link">
                                <i class="fa fa-shopping-basket"></i>
                            </a>
                        </li>*/ ?>

                    </ul>
                </nav><!-- END MAIN MENU -->
            </div>
        </div><!-- END NAVIGATION MENU -->
    </div><!-- End header-wrapper -->
</header>

<div id="stlChanger">
    <div class="blockChanger bgChanger">
        <a href="#" class="chBut ico-35" data-bs-toggle="modal" data-bs-target="#modal-carte-identite">
            <p class="switch">
                <span class="drk-mode">
                    <i class="fa-regular fa-credit-card"></i>
                </span>
            </p>
        </a>
    </div>
</div>
