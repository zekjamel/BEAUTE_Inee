<?php
require_once __DIR__ . '/config.php';

if (!defined('BASEPATH')) {
    define('BASEPATH', __DIR__);
}
?>
<!DOCTYPE html>
<html lang="<?= $current_language; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="<?= htmlspecialchars(_l('beaute_inee_landing_page_meta_author')); ?>">
    <meta name="description" content="<?= htmlspecialchars(_l('beaute_inee_landing_page_meta_description')); ?>">
    <meta name="keywords" content="<?= htmlspecialchars(_l('beaute_inee_landing_page_meta_keywords')); ?>">
    <title><?= htmlspecialchars(_l('beaute_inee_landing_page_title')); ?></title>
    <link rel="icon" href="<?= $theme_assets_url; ?>images/favicon.ico">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Lato:wght@300;400;500;600;700&family=Alatsi&display=swap" rel="stylesheet">
    <link href="<?= $theme_assets_url; ?>css/dropdown-effects/fade-down.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web@latest"></script>
    <?php foreach ($css_files as $css): ?>
        <link href="<?= htmlspecialchars($css); ?>" rel="stylesheet">
    <?php endforeach; ?>
    <link href="<?= $theme_assets_url; ?>css/cookie-banner.css" rel="stylesheet">
</head>
<body class="customers bg--white">
<div id="page" class="page">
<?php require __DIR__ . '/header.php'; ?>

<?php // ---------------------------- ?>
<?php // Hero Section: full-screen slideshow with video background ?>
<section id="hero-1" class="hero-section">
    <div class="slideshow">
        <div class="slideshow-inner">
            <div class="slides">
                <div class="slide is-active wow fadeIn" data-wow-delay="0.3s">

                    <!-- Slide Content: centered text over video -->
                    <div class="slide-content">
                        <div class="container">
                            <div class="row justify-content-md-center">
                                <div class="col col-lg-10">
                                    <div class="caption color--white">
                                        <!-- Hero Title -->
                                        <div class="title">
                                            <h2><?php echo _l('beaute_inee_index_hero_title'); ?></h2>
                                        </div>
                                        <!-- Hero Subtitle + CTA -->
                                        <div class="text">
                                            <h5 class="h5-lg">
                                                <?php echo _l('beaute_inee_index_hero_subtitle'); ?>
                                            </h5>
                                            <a href="#services" class="btn btn--gold hover--black">
                                                <?php echo _l('beaute_inee_index_hero_cta_services'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Video Container: autoplay, muted, loop -->
                    <div class="video-container">
                        <video
                                class="bg-video"
                                autoplay muted loop playsinline preload="auto"
                        >
                            <source src="<?= $theme_assets_url; ?>video/entete.mp4" type="video/mp4" />
                            <!-- Fallback image if video fails -->
                            <img
                                    src="<?= $theme_assets_url; ?>images/entete.jpg"
                                    alt="<?php echo _l('beaute_inee_index_hero_video_fallback_alt'); ?>"
                            />
                        </video>
                        <div class="video-overlay"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<?php // ---------------------------- ?>
<?php // How It Works Section: three-column intro ?>
<section id="how-it-works" class="pt-8 about-1 about-section">
    <div class="container">
        <div class="row justify-content-center">
            <!-- Text Block: centered heading + description -->
            <div class="col-lg-10 col-xl-9">
                <div class="txt-block text-center">
                    <!-- Section Label -->
                    <span class="section-id">
                        <?php echo _l('beaute_inee_index_how_it_works_section_id'); ?>
                    </span>
                    <!-- Section Title -->
                    <h2 class="h2-title">
                        <?php echo _l('beaute_inee_index_how_it_works_title'); ?>
                    </h2>
                    <!-- Section Description -->
                    <p class="mb-0">
                        <?php echo _l('beaute_inee_index_how_it_works_text'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php // ---------------------------- ?>
<?php // Process Steps Section: four-step overview of the user journey ?>
<section id="process-steps" class="py-6 services-section division">
    <div class="container">
        <!-- SERVICES WRAPPER -->
        <div class="sbox-2-wrapper text-center">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4">

                <!-- STEP #1 -->
                <div class="col pb-5">
                    <div class="sbox-2 sb-1 wow fadeInUp" data-wow-delay="0.3s">
                        <!-- Icon for step 1 -->
                        <i class="ph ph-calendar-check ph-light ph-4x color--secondary"></i>
                        <!-- Step title & description -->
                        <div class="sbox-txt">
                            <h5 class="h5-lg">
                                <?php echo _l('beaute_inee_index_step1_title'); ?>
                            </h5>
                            <p>
                                <?php echo _l('beaute_inee_index_step1_text'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- STEP #2 -->
                <div class="col pb-5">
                    <div class="sbox-2 sb-2 wow fadeInUp" data-wow-delay="0.4s">
                        <!-- Icon for step 2 -->
                        <i class="ph ph-magnifying-glass ph-light ph-4x color--primary"></i>
                        <!-- Step title & description -->
                        <div class="sbox-txt">
                            <h5 class="h5-lg">
                                <?php echo _l('beaute_inee_index_step2_title'); ?>
                            </h5>
                            <p>
                                <?php echo _l('beaute_inee_index_step2_text'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- STEP #3 -->
                <div class="col pb-5">
                    <div class="sbox-2 sb-3 wow fadeInUp" data-wow-delay="0.5s">
                        <!-- Icon for step 3 -->
                        <i class="ph ph-list-checks ph-light ph-4x color--saumon"></i>
                        <!-- Step title & description -->
                        <div class="sbox-txt">
                            <h5 class="h5-lg">
                                <?php echo _l('beaute_inee_index_step3_title'); ?>
                            </h5>
                            <p>
                                <?php echo _l('beaute_inee_index_step3_text'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- STEP #4 -->
                <div class="col pb-5">
                    <div class="sbox-2 sb-4 wow fadeInUp" data-wow-delay="0.6s">
                        <!-- Icon for step 4 -->
                        <i class="ph ph-recycle ph-light ph-4x color--peche"></i>
                        <!-- Step title & description -->
                        <div class="sbox-txt">
                            <h5 class="h5-lg">
                                <?php echo _l('beaute_inee_index_step4_title'); ?>
                            </h5>
                            <p>
                                <?php echo _l('beaute_inee_index_step4_text'); ?>
                            </p>
                        </div>
                    </div>
                </div>

            </div> <!-- End row -->
        </div>   <!-- END SERVICES WRAPPER -->
    </div>       <!-- End container -->
</section>

<?php // ---------------------------- ?>
<?php // Banner-2 Section: in-store invitation banner ?>
<section class="bg--01 bg--scroll py-8 banner-2 banner-section">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="banner-2-txt text-center color--white">

                    <!-- Banner Title -->
                    <h3>
                        <?php echo _l('beaute_inee_index_banner2_title'); ?>
                    </h3>

                    <!-- Banner Subtitle -->
                    <span class="section-id">
                        <?php echo _l('beaute_inee_index_banner2_subtitle'); ?>
                    </span>

                    <!-- Banner CTA Button -->
                    <a href="#booking" class="btn btn--tra-gold hover--gold">
                        <?php echo _l('beaute_inee_index_banner2_cta'); ?>
                    </a>

                </div>
            </div>
        </div> <!-- End row -->
    </div>     <!-- End container -->
</section>

<?php // ---------------------------- ?>
<?php // Services Section: highlight three core Beauty Tech services ?>
<section id="services" class="py-8 cards-section division">
    <div class="container">
        <!-- SECTION TITLE -->
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="section-title text-center mb-6">
                    <!-- Services Title -->
                    <h2 class="h2-title">
                        <?php echo _l('beaute_inee_index_services_title'); ?>
                    </h2>
                    <!-- Services Subtitle -->
                    <p>
                        <?php echo _l('beaute_inee_index_services_subtitle'); ?>
                    </p>
                </div>
            </div>
        </div>
        <!-- GIFT CARDS WRAPPER -->
        <div class="cards-row">
            <div class="row row-cols-1 row-cols-lg-3">

                <!-- Service Card #1 -->
                <div class="col">
                    <div id="gcard-1-1" class="gift-card wow fadeInUp">
                        <!-- Icon -->
                        <i class="fa-solid fa-user-check fa-4x color--primary"></i>
                        <!-- Title -->
                        <h4 class="h4-md mt-3">
                            <?php echo _l('beaute_inee_index_service1_title'); ?>
                        </h4>
                        <!-- Subtitle -->
                        <p class="service-subtitle">
                            <?php echo _l('beaute_inee_index_service1_subtitle'); ?>
                        </p>
                        <!-- Buttons -->
                        <div class="service-buttons">
                            <a href="<?php echo _l('beaute_inee_index_service1_btn1_url'); ?>"
                               class="btn btn--tra-gold hover--gold">
                                <i class="fa fa-video-camera"></i>
                            </a>
                            <a href="<?php echo _l('beaute_inee_index_service1_btn2_url'); ?>"
                               class="btn btn--tra-black hover--black">
                                <?php echo _l('beaute_inee_index_service1_btn2_label'); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Service Card #2 -->
                <div class="col">
                    <div id="gcard-1-2" class="gift-card wow fadeInUp">
                        <!-- Icon -->
                        <i class="fas fa-calendar-check fa-4x color--primary"></i>
                        <!-- Title -->
                        <h4 class="h4-md mt-3">
                            <?php echo _l('beaute_inee_index_service2_title'); ?>
                        </h4>
                        <!-- Subtitle -->
                        <p class="service-subtitle">
                            <?php echo _l('beaute_inee_index_service2_subtitle'); ?>
                        </p>
                        <!-- Buttons -->
                        <a href="<?php echo _l('beaute_inee_index_service2_btn1_url'); ?>"
                           class="btn btn--tra-gold hover--gold">
                            <i class="fa fa-video-camera"></i>
                        </a>
                        <a href="<?php echo _l('beaute_inee_index_service2_btn2_url'); ?>"
                           class="btn btn--tra-black hover--black">
                            <?php echo _l('beaute_inee_index_service2_btn2_label'); ?>
                        </a>
                    </div>
                </div>

                <!-- Service Card #3 -->
                <div class="col">
                    <div id="gcard-1-3" class="gift-card wow fadeInUp">
                        <!-- Icon -->
                        <i class="fas fa-face-smile fa-4x color--primary"></i>
                        <!-- Title -->
                        <h4 class="h4-md mt-3">
                            <?php echo _l('beaute_inee_index_service3_title'); ?>
                        </h4>
                        <!-- Subtitle -->
                        <p class="service-subtitle">
                            <?php echo _l('beaute_inee_index_service3_subtitle'); ?>
                        </p>
                        <!-- Button -->
                        <a href="<?php echo _l('beaute_inee_index_service3_btn_url'); ?>"
                           class="btn btn--tra-black hover--black">
                            <?php echo _l('beaute_inee_index_service3_btn_label'); ?>
                        </a>
                    </div>
                </div>

            </div> <!-- End row -->
        </div>     <!-- End cards-row -->
    </div>        <!-- End container -->
</section>


    <hr>

<?php // ---------------------------- ?>
<?php // Products Section: dynamic carousel via loop // ARRAY in header ?>
<section id="products" class="pt-8 cards-section division">
    <div class="container">
        <!-- CATEGORY TITLE -->
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="section-title text-center mb-6">
                    <span class="section-id">
                        <?php echo _l('beaute_inee_index_products_section_id'); ?>
                    </span>
                    <h2 class="h2-title">
                        <?php echo _l('beaute_inee_index_products_title'); ?>
                    </h2>
                    <p>
                        <?php echo _l('beaute_inee_index_products_text'); ?>
                    </p>
                </div>
            </div>
        </div>

        <div id="brands-1" class="pt-3 brands-section">
            <div class="container">
                <div class="row">
                    <div class="col text-center">
                        <div class="owl-carousel brands-carousel-5">

                            <?php foreach($products as $prod): ?>
                                <div class="col px-2">
                                    <div class="team-member wow fadeInUp">
                                        <div class="team-member-photo">
                                            <div class="hover-overlay">
                                                <img class="img-fluid"
                                                     src="<?= $theme_assets_url; ?>images/product/<?= $prod['image']; ?>"
                                                     alt="<?= htmlspecialchars($prod['alt']); ?>">
                                                <div class="item-overlay"></div>
                                            </div>
                                        </div>
                                        <div class="team-member-data">
                                            <span class="section-id"><?= htmlspecialchars($prod['brand']); ?></span>
                                            <h5 class="h5-lg"><?= htmlspecialchars($prod['name']); ?></h5>
                                            <p class="tra-link">
                                                <a href="#"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#<?= $prod['modal']; ?>">
                                                    <?php echo _l('beaute_inee_index_products_cta'); ?>
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        </div> <!-- owl-carousel -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- PRICING-5
			============================================= -->
    <?php /*
    <div class="pt-8 pricing-5 pricing-section division">
        <div class="container">
            <!-- PRICING-5 WRAPPER -->
            <div class="pricing-5-wrapper">
                <div class="row">
                    <!-- PRICING-5 TABLE -->
                    <div class="pricing-5-wrapper">
                        <div class="row">
                            <!-- PRICING-5 TABLE -->
                            <div class="col-lg-5">
                                <div class="pricing-5-table left-column wow fadeInUp">
                                    <!-- PRICING LIST CATEGORY -->
                                    <div class="pricing-5-category mb-4">
                                        <h3>Prestations</h3>
                                    </div>
                                    <!-- CUTTING & STYLING -->
                                    <ul class="pricing-list">
                                        <!-- PRICING ITEM #1 -->
                                        <li class="pricing-5-item">
                                            <div class="detail-price">
                                                <div class="price-name"><p>Skin consultation en ligne</p></div>
                                                <div class="price-dots"></div>
                                                <div class="price-number"><p>45€</p></div>
                                            </div>
                                            <!-- Description -->
                                            <div class="price-txt">
                                                <p>30min - Dear klairs</p>
                                            </div>
                                        </li>
                                        <!-- PRICING ITEM #2 -->
                                        <li class="pricing-5-item">
                                            <div class="detail-price">
                                                <div class="price-name"><p>Skin consultation en cabine</p></div>
                                                <div class="price-dots"></div>
                                                <div class="price-number"><p>60€</p></div>
                                            </div>
                                            <!-- Description -->
                                            <div class="price-txt">
                                                <p>30min - Beauté INEE</p>
                                            </div>
                                        </li>
                                    </ul> <!-- END CUTTING & STYLING -->
                                </div>
                            </div> <!-- END PRICING-1 TABLE -->

                            <!-- PRICING-5 TABLE -->
                            <div class="col-lg-7">
                                <div class="pricing-5-table right-column wow fadeInUp">
                                    <!-- PRICING LIST CATEGORY -->
                                    <div class="pricing-5-category mb-4">
                                        <h3>Produits</h3>
                                    </div>

                                    <!-- HAIR COLORING -->
                                    <ul class="pricing-list">
                                        <!-- PRICING ITEM #1 -->
                                        <li class="pricing-5-item">
                                            <div class="detail-price">
                                                <div class="price-name"><p>Box Dear klairs Mini : 30ml & 20ml</p></div>
                                                <div class="price-dots"></div>
                                                <div class="price-number"><p>25€</p></div>
                                            </div>
                                            <!-- Description -->
                                            <div class="price-txt">
                                                <p>Dear klairs</p>
                                            </div>
                                        </li>
                                        <!-- PRICING ITEM #2 -->
                                        <li class="pricing-5-item">
                                            <div class="detail-price">
                                                <div class="price-name"><p>Crème sur-mesure 30 mL</p></div>
                                                <div class="price-dots"></div>
                                                <div class="price-number"><p>99€</p></div>
                                            </div>
                                            <!-- Description -->
                                            <div class="price-txt">
                                                <p>ABBI</p>
                                            </div>
                                        </li>
                                        <!-- PRICING ITEM #3 -->
                                        <li class="pricing-5-item">
                                            <div class="detail-price">
                                                <div class="price-name"><p>Coffret sur-mesure 30 mL</p></div>
                                                <div class="price-dots"></div>
                                                <div class="price-number"><p>119€</p></div>
                                            </div>
                                            <!-- Description -->
                                            <div class="price-txt">
                                                <p>ABBI</p>
                                            </div>
                                        </li>
                                        <!-- PRICING ITEM #4 -->
                                        <li class="pricing-5-item">
                                            <div class="detail-price">
                                                <div class="price-name"><p>Skin consultation en ligne + Box premium soin visage ABBI sur mesure</p></div>
                                                <div class="price-dots"></div>
                                                <div class="price-number"><p>164€</p></div>
                                            </div>
                                            <!-- Description -->
                                            <div class="price-txt">
                                                <p>30min - Beauté INEE</p>
                                            </div>
                                        </li>
                                        <!-- PRICING ITEM #5 -->
                                        <li class="pricing-5-item">
                                            <div class="detail-price">
                                                <div class="price-name"><p>Skin consultation en ligne + Box Découverte soin visage Dear klairs Mini</p></div>
                                                <div class="price-dots"></div>
                                                <div class="price-number"><p>70€</p></div>
                                            </div>
                                            <!-- Description -->
                                            <div class="price-txt">
                                                <p>30min - Beauté INEE</p>
                                            </div>
                                        </li>
                                    </ul> <!-- END HAIR COLORING -->
                                </div>
                            </div> <!-- END PRICING-1 TABLE -->
                        </div>
                    </div> <!--END  PRICING-1 WRAPPER -->


                </div>
            </div> <!--END  PRICING-1 WRAPPER -->


            <!-- BUTTON -->
            <div class="row">
                <div class="col">
                    <div class="more-btn mt-6">
                        <a href="#booking" class="btn btn--tra-black hover--black">Réservez en ligne</a>
                    </div>
                </div>
            </div>	<!-- END BUTTON -->


        </div>  <!-- End container -->
    </div>	<!-- PRICING-5  -->
    */ ?>

    <!-- GALLERY-4
			============================================= -->
    <?php /*<div id="gallery-4" class="py-8 gallery-section division">
        <div class="container-fluid">


            <!-- GALLERY-4 WRAPPER -->
            <div class="gallery-4-wrapper">
                <div class="row">

                    <!-- IMAGE #1 -->
                    <div class="col-md-6 col-lg-2">
                        <div id="img-4-1" class="gallery-image">
                            <div class="hover-overlay">
                                <!-- Image -->
                                <img class="img-fluid" src="<?= $theme_assets_url; ?>images/gallery/woman_04.jpg" alt="gallery-image">
                                <div class="item-overlay"></div>
                            </div>
                        </div>
                    </div>

                    <!-- IMAGE #2 -->
                    <div class="col-md-6 col-lg-5">
                        <div id="img-4-2" class="gallery-image">
                            <div class="hover-overlay">
                                <!-- Image -->
                                <img class="img-fluid" src="<?= $theme_assets_url; ?>images/gallery/woman_01.jpg" alt="gallery-image">
                                <div class="item-overlay"></div>
                            </div>
                        </div>
                    </div>

                    <!-- IMAGE #1 -->
                    <div class="col-lg-3">

                        <!-- IMAGE #3 -->
                        <div id="img-4-3" class="gallery-image">
                            <div class="hover-overlay">
                                <!-- Image -->
                                <img class="img-fluid" src="<?= $theme_assets_url; ?>images/gallery/woman_03.jpg" alt="gallery-image">
                                <div class="item-overlay"></div>
                            </div>
                        </div>

                        <!-- IMAGE #4 -->
                        <div id="img-4-4" class="gallery-image">
                            <div class="hover-overlay">
                                <!-- Image -->
                                <img class="img-fluid" src="<?= $theme_assets_url; ?>images/gallery/woman_02.jpg" alt="gallery-image">
                                <div class="item-overlay"></div>
                            </div>
                        </div>

                    </div>


                    <div class="col-lg-2">

                        <!-- IMAGE #5 -->
                        <div id="img-4-5" class="gallery-image">
                            <div class="hover-overlay">

                                <!-- Image -->
                                <img class="img-fluid" src="<?= $theme_assets_url; ?>images/gallery/woman_05.jpg" alt="gallery-image">
                                <div class="item-overlay"></div>

                            </div>
                        </div>

                        <!-- IMAGE #5 -->
                        <div id="img-4-6" class="gallery-image">
                            <div class="hover-overlay">
                                <!-- Image -->
                                <img class="img-fluid" src="<?= $theme_assets_url; ?>images/gallery/woman_06.jpg" alt="gallery-image">
                                <div class="item-overlay"></div>
                            </div>
                        </div>
                    </div>
                </div>  <!-- End row -->
            </div>	<!-- END GALLERY-4 WRAPPER -->

        </div>	   <!-- End container -->
    </div>*/ ?>	<!-- END GALLERY-4 -->


<?php // ---------------------------- ?>
<?php // Experts Section: Skin transformation expertise overview ?>
<section id="experts" class="shape--01 smoke--shape my-8 py-7 ct-01 content-section division">
    <div class="container">
        <div class="row d-flex align-items-center">

            <!-- TEXT BLOCK -->
            <div class="col-lg-6 order-last order-lg-2">
                <div class="txt-block left-column wow fadeInRight">

                    <!-- Section Label (optional) -->
                    <?php /*<span class="section-id">
                        <?php echo _l('beaute_inee_index_experts_section_id'); ?>
                    </span>*/ ?>

                    <!-- Section Title -->
                    <h2 class="h2-md">
                        <?php echo _l('beaute_inee_index_experts_title'); ?>
                    </h2>

                    <!-- Intro Paragraph with mission & strength -->
                    <p>
                        <?php echo _l('beaute_inee_index_experts_intro'); ?>
                    </p>

                    <!-- Methodology Block -->
                    <div class="methodology mt-4">
                        <h5 class="mb-3">
                            <?php echo _l('beaute_inee_index_methodology_title'); ?>
                        </h5>
                        <ul class="list-unstyled">
                            <li>
                                <strong><?php echo _l('beaute_inee_index_methodology_item1_label'); ?></strong> :
                                <?php echo _l('beaute_inee_index_methodology_item1_text'); ?>
                            </li>
                            <li>
                                <strong><?php echo _l('beaute_inee_index_methodology_item2_label'); ?></strong> :
                                <?php echo _l('beaute_inee_index_methodology_item2_text'); ?>
                            </li>
                            <li>
                                <strong><?php echo _l('beaute_inee_index_methodology_item3_label'); ?></strong> :
                                <?php echo _l('beaute_inee_index_methodology_item3_text'); ?>
                            </li>
                        </ul>
                    </div>

                    <!-- Accordion Wrapper: different skin types -->
                    <div class="accordion accordion-wrapper mt-5">
                        <ul class="accordion">

                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <li class="accordion-item">
                                    <div class="accordion-thumb">
                                        <p><?php echo _l("beaute_inee_index_accordion{$i}_title"); ?></p>
                                    </div>
                                    <div class="accordion-panel">
                                        <p class="mb-0">
                                            <?php echo _l("beaute_inee_index_accordion{$i}_text"); ?>
                                        </p>
                                    </div>
                                </li>
                            <?php endfor; ?>

                        </ul>
                    </div>

                    <!-- Closing paragraph -->
                    <p>
                        <?php echo _l('beaute_inee_index_experts_closing'); ?>
                    </p>

                </div>
            </div>

            <!-- IMAGE BLOCK -->
            <div class="col-lg-6 order-first order-lg-2">
                <div class="img-block right-column wow fadeInLeft">
                    <img class="img-fluid lazyload"
                         src="<?= $theme_assets_url; ?>images/Image_la_Skin_transformation.webp"
                         alt="<?php echo _l('beaute_inee_index_experts_image_alt'); ?>">
                </div>
            </div>

        </div>
    </div>
</section>


<?php // ---------------------------- ?>
<?php // Benefits Section: skin concerns & tailored solutions ?>
<section id="benefices" class="pt-6 pb-5 team-section division bg--smoke">
    <div class="container text-center">

        <!-- Section Header -->
        <div class="team-members-category">
            <div class="row">
                <div class="col">
                    <div class="category-title mb-6">
                        <h2 class="h2-lg">
                            <?php echo _l('beaute_inee_index_benefits_title'); ?>
                        </h2>
                        <p>
                            <?php echo _l('beaute_inee_index_benefits_subtitle'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Benefits Cards Loop // Array in Header -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4">
                <?php foreach ($benefits as $i => $b):
                    $benefitNumber = $i + 1;
                    ?>
                    <div class="col">
                        <div class="team-member wow fadeInUp">
                            <!-- Image & Overlay -->
                            <div class="team-member-photo">
                                <div class="hover-overlay">
                                    <img class="img-fluid lazyload"
                                         src="<?= $theme_assets_url; ?>images/<?= $b['image_path']; ?>"
                                         alt="<?php echo htmlspecialchars(_l("beaute_inee_index_benefit{$benefitNumber}_alt")); ?>">
                                    <div class="item-overlay">
                                        <div class="overlay-content">
                                            <h4 class="overlay-title">
                                                <?php echo _l("beaute_inee_index_benefit{$benefitNumber}_overlay_title"); ?>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Title, Text & CTA -->
                            <div class="team-member-data">
                                <h5 class="h5-lg">
                                    <?php echo _l("beaute_inee_index_benefit{$benefitNumber}_title"); ?>
                                </h5>
                                <p class="tra-link">
                                    <?php echo _l("beaute_inee_index_benefit{$benefitNumber}_text"); ?>
                                </p>
                                <div class="txt-block-btn pb-5">
                                    <a href="#"
                                       data-bs-toggle="modal"
                                       data-bs-target="#<?php echo $b['modal']; ?>"
                                       class="btn btn--gold hover--tra-gold">
                                        <?php echo _l('beaute_inee_index_benefits_cta'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</section>


<?php // ---------------------------- ?>
<?php // About Section: brand story & sneak peek ?>
<section id="histoire" class="pt-8 ct-03 content-section division">
    <div class="container">
        <div class="row">

            <!-- Brand Story Text Block -->
            <div class="col-lg-6">
                <div class="txt-block left-column wow fadeInRight">

                    <!-- Section Label -->
                    <span class="section-id">
                        <?php echo _l('beaute_inee_index_about_section_id'); ?>
                    </span>

                    <!-- Section Title -->
                    <h2 class="h2-md">
                        <?php echo _l('beaute_inee_index_about_title'); ?>
                    </h2>

                    <!-- Intro Paragraphs -->
                    <p class="mb-2">
                        <?php echo _l('beaute_inee_index_about_text1'); ?>
                    </p>
                    <p>
                        <?php echo _l('beaute_inee_index_about_text2'); ?>
                    </p>

                    <!-- Brand Image -->
                    <div class="ct-03-img">
                        <img class="img-fluid" style="width:95%"
                             src="<?= $theme_assets_url; ?>images/histoire.webp"
                             alt="<?php echo _l('beaute_inee_index_about_image_alt'); ?>">
                    </div>

                    <!-- Learn More Button -->
                    <div class="txt-block-btn pb-5">
                        <a href="#"
                           data-bs-toggle="modal"
                           data-bs-target="#modal-histoire"
                           class="btn btn--tra-gold hover--gold">
                            <?php echo _l('beaute_inee_index_about_cta'); ?>
                        </a>
                    </div>

                </div>
            </div>

            <!-- Sneak Peek Text Block -->
            <div class="col-lg-6">
                <div class="txt-block right-column wow fadeInLeft">

                    <!-- Preview Image -->
                    <div class="ct-03-img mb-4 hidden-xs">
                        <img class="img-fluid" style="width:95%"
                             src="<?= $theme_assets_url; ?>images/beauty_07.png"
                             alt="<?php echo _l('beaute_inee_index_sneakpeek_image_alt'); ?>">
                    </div>

                    <!-- Sneak Peek Text -->
                    <div class="ct-03-txt avant-premiere p-3 bg-light rounded">
                        <p class="mb-2">
                            <?php echo _l('beaute_inee_index_sneakpeek_text1'); ?>
                        </p>
                        <p class="mb-1">
                            <?php echo _l('beaute_inee_index_sneakpeek_text2'); ?>
                        </p>

                        <!-- Founder Buttons -->
                        <div class="txt-block-btn pb-5">
                            <?php foreach (['khadi','fatou','awa'] as $founder): ?>
                                <a href="#"
                                   data-bs-toggle="modal"
                                   data-bs-target="#modal-<?php echo $founder; ?>"
                                   class="btn btn--black hover--tra-black">
                                    <?php echo _l("beaute_inee_index_sneakpeek_btn_" . $founder); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>

        </div> <!-- End row -->
    </div>   <!-- End container -->
</section>


<section id="bi-program" class="shape--02 smoke--shape my-8 py-7 ct-01 content-section division">
    <div class="container">

        <!-- Onglets -->
        <ul class="nav nav-pills bi-tabs justify-content-center gap-2 mb-5" id="biTabs" role="tablist" data-animate>
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4 py-2"
                        id="step1-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#step1"
                        type="button" role="tab"
                        aria-controls="step1" aria-selected="true">
                    Étape 1&nbsp;: Carte connectée
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 py-2"
                        id="step2-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#step2"
                        type="button" role="tab"
                        aria-controls="step2" aria-selected="false">
                    Étape 2&nbsp;: Programme personnalisé
                </button>
            </li>
        </ul>

        <div class="tab-content" id="biTabsContent">

            <!-- ============ Étape 1 : Carte connectée ============ -->
            <div class="tab-pane fade show active" id="step1" role="tabpanel" aria-labelledby="step1-tab">
                <div class="row align-items-center g-5">

                    <!-- Texte -->
                    <div class="col-lg-6 order-2 order-lg-1" data-animate data-animate-delay="0.05">
                        <span class="badge bg-dark text-white rounded-pill px-3 py-2 mb-3">Une première mondiale</span>
                        <h2 class="h2-md mb-4">Et si votre peau avait enfin sa carte d’identité&nbsp;?</h2>

                        <ul class="list-unstyled lh-lg mb-4">
                            <li class="d-flex align-items-center mb-1">
                                <i class="ph ph-eye-slash me-3 fs-4"></i>
                                <span>Fini les achats à l’aveugle</span>
                            </li>
                            <li class="d-flex align-items-center mb-1">
                                <i class="ph ph-clipboard-text me-3 fs-4"></i>
                                <span>Faites-vous guider par des experts</span>
                            </li>
                            <li class="d-flex align-items-center mb-1">
                                <i class="ph ph-calendar-check me-3 fs-4"></i>
                                <span>Ne laissez plus votre peau sans suivi</span>
                            </li>
                            <li class="d-flex align-items-center mb-1">
                                <i class="ph ph-shield-check me-3 fs-4"></i>
                                <span>Vos données beauté sécurisées, toujours à portée de main</span>
                            </li>
                        </ul>

                        <p class="small mb-4"><strong>-15% immédiat</strong> sur tous les soins Beauté&nbsp;INÉE</p>

                        <!-- Ouvre l’onglet Étape 2 -->
                        <a href="https://buy.stripe.com/fZueVdcqAbKn74z5Tn0co03" target="_blank"
                           class="btn btn--gold hover--tra-gold">
                            J’achète ma carte
                        </a>
                    </div>

                    <!-- Visuel -->
                    <div class="col-lg-6 text-center order-1 order-lg-2" data-animate data-animate-delay="0.15">
                        <img src="<?= $theme_assets_url; ?>images/carte-bi_connectee.svg"
                             alt="Carte connectée Beauté INÉE – BI-tech"
                             class="img-fluid bi-hero-svg"
                             style="max-width:560px;filter:drop-shadow(0 22px 45px rgba(0,0,0,.22));">
                    </div>

                </div>
            </div>

            <!-- ============ Étape 2 : Formules ============ -->
            <div class="tab-pane fade" id="step2" role="tabpanel" aria-labelledby="step2-tab">
                <div class="row g-5 align-items-start">

                    <!-- Intro -->
                    <div class="col-lg-4" data-animate>
                        <div class="txt-block">
                            <span class="badge bg-dark text-white rounded-pill mb-3">Programme évolutif</span>
                            <h2 class="h2-md mb-3">Choisissez votre formule</h2>
                            <p class="mb-3">Une carte. Une routine. Une expertise au creux de la main.</p>
                            <ul class="list-unstyled lh-lg">
                                <li class="d-flex align-items-start mb-2">
                                    <i class="ph ph-toggle-right me-2"></i>
                                    Paiement en plusieurs fois disponible
                                </li>
                                <li class="d-flex align-items-start">
                                    <i class="ph ph-leaf me-2"></i>
                                    Jusqu’à <strong>-50&nbsp;%</strong> chez nos marques partenaires
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="col-lg-8">
                        <div class="row g-4">

                            <!-- Essentielle -->
                            <div class="col-md-6 col-xl-4" data-animate data-animate-delay="0.05">
                                <div class="card bi-pricing bi-pricing--dark h-100 rounded-4 shadow-lg overflow-hidden">
                                    <div class="card-body d-flex flex-column p-4">
                                        <h5 class="card-title mb-1">Formule Essentielle</h5>
                                        <div class="text-muted mb-3">Durée : 3 mois</div>
                                        <div class="display-6 fw-bold mb-3">€120</div>
                                        <ul class="list-unstyled small mb-4 flex-grow-1">
                                            <li class="mb-2">1 <em>skinconsultation</em> (diagnostic profond inclus)</li>
                                            <li class="mb-2">1 rendez-vous de suivi</li>
                                            <li>10&nbsp;€ offerts sur vos produits</li>
                                        </ul>
                                        <a href="https://buy.stripe.com/3cIdR9fCM4hV4Wr2Hb0co00" target="_blank" class="btn btn--gold hover--tra-gold w-100 mt-auto">Commander</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Équilibrée -->
                            <div class="col-md-6 col-xl-4" data-animate data-animate-delay="0.10">
                                <div class="card bi-pricing bi-pricing--amber h-100 rounded-4 shadow-lg overflow-hidden">
                                    <div class="card-body d-flex flex-column p-4">
                                        <h5 class="card-title mb-1">Formule Équilibrée</h5>
                                        <div class="text-muted mb-3">Durée : 6 mois</div>
                                        <div class="display-6 fw-bold mb-3">€150</div>
                                        <ul class="list-unstyled small mb-4 flex-grow-1">
                                            <li class="mb-2">1 <em>skinconsultation</em> (diagnostic profond inclus)</li>
                                            <li class="mb-2">2 rendez-vous de suivi</li>
                                            <li>10&nbsp;€ offerts sur vos produits</li>
                                        </ul>
                                        <a href="https://buy.stripe.com/9B67sLcqA3dR3SnchL0co01" target="_blank" class="btn btn--gold hover--tra-gold w-100 mt-auto">Commander</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Signature -->
                            <div class="col-md-12 col-xl-4" data-animate data-animate-delay="0.15">
                                <div class="card bi-pricing bi-pricing--deep h-100 rounded-4 shadow-lg overflow-hidden">
                                    <div class="card-body d-flex flex-column p-4">
                                        <h5 class="card-title mb-1">Formule Signature</h5>
                                        <div class="text-muted mb-3">Durée : 12 mois</div>
                                        <div class="display-6 fw-bold mb-3">€240</div>
                                        <ul class="list-unstyled small mb-4 flex-grow-1">
                                            <li class="mb-2">1 <em>skinconsultation</em> (diagnostic profond inclus)</li>
                                            <li class="mb-2">4 rendez-vous de suivi</li>
                                            <li>10&nbsp;€ offerts sur vos produits</li>
                                        </ul>
                                        <a href="https://buy.stripe.com/6oU6oH62c9Cf60v3Lf0co02" target="_blank" class="btn btn--gold hover--tra-gold w-100 mt-auto">Commander</a>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /row -->
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


<?php // ---------------------------- ?>
<?php // FAQ Section: common questions accordion // ARRAY in Controller ?>
<section id="experts" class="shape--01 smoke--shape my-8 py-5 ct-01 content-section division">
    <div class="container">
        <div class="row d-flex align-items-center">
            <!-- FAQ Text Block -->
            <div class="col-lg-7 order-first order-lg-1">
                <div class="txt-block left-column wow fadeInRight">

                    <!-- Section Label -->
                    <span class="section-id">
                        <?= _l('beaute_inee_index_faq_section_id'); ?>
                    </span>

                    <!-- FAQ Title -->
                    <h2 class="h2-md">
                        <?= _l('beaute_inee_index_faq_title'); ?>
                    </h2>

                    <!-- Accordion Wrapper -->
                    <div class="accordion accordion-wrapper mt-5">
                        <ul class="accordion" id="faq-list">
                            <?php foreach ($faqs as $i => $faq):
                                $question = isset($faq['question_key']) ? _l($faq['question_key']) : ($faq['question'] ?? '');
                                $answer = isset($faq['answer_key']) ? _l($faq['answer_key']) : ($faq['answer'] ?? '');
                                $answerList = isset($faq['answer_list_key'])
                                    ? _l($faq['answer_list_key'])
                                    : ($faq['answer_list'] ?? '');
                                ?>
                                <li class="accordion-item <?= $i === 0 ? 'is-active' : ''; ?>"
                                <?php if ($i >= 5) echo 'style="display:none;"'; ?>>
                                <div class="accordion-thumb">
                                    <p><?= htmlspecialchars($question); ?></p>
                                </div>
                                <!-- Answer -->
                                <div class="accordion-panel" <?= $i === 0 ? 'style="display:block;"' : ''; ?>>
                                    <p class="mb-0"><?= htmlspecialchars($answer); ?></p>
                                    <?php if ($answerList !== ''): ?>
                                        <?php $items = explode('|', $answerList); ?>
                                        <ul>
                                            <?php foreach ($items as $item): ?>
                                                <li><?= htmlspecialchars($item); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php
                        if (count($faqs) > 5):
                            ?>
                            <div class="text-center mt-4">
                                <button id="load-more-faq" class="btn btn--tra-gold hover--tra-gold">
                                    <?= _l('load_more'); /* à ajouter dans vos lang files */ ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <!-- IMAGE À CÔTÉ DE LA FAQ -->
            <div class="col-lg-5 order-last order-lg-2 mb-5 mb-lg-0">
                <div class="img-block right-column wow fadeInLeft">
                    <picture>
                        <source type="image/webp" srcset="<?= $theme_assets_url; ?>images/faq-side.webp">
                        <img
                                class="img-fluid shadow"
                                src="<?= $theme_assets_url; ?>images/faq-side.webp"
                                alt="FAQ — Illustration Beauté INÉE">
                    </picture>
                </div>
            </div>

        </div>
    </div>
</section>


<?php // ---------------------------- ?>
<?php // Testimonials Section: client reviews carousel ?>
<div id="reviews-2" class="py-8 reviews-section">
    <div class="container">

        <!-- Section Header -->
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="section-title text-center mb-6">
                    <!-- Section Label -->
                    <span class="section-id">
                        <?php echo _l('beaute_inee_index_testimonials_section_id'); ?>
                    </span>
                    <!-- Section Title -->
                    <h2 class="h2-title">
                        <?php echo _l('beaute_inee_index_testimonials_title'); ?>
                    </h2>
                </div>
            </div>
        </div>

        <!-- Testimonials Carousel -->
        <div class="row">
            <div class="col">
                <div class="owl-carousel owl-theme reviews-2-wrapper">
                    <?php foreach ($testimonials as $t):
                        $reviewText = isset($t['text_key']) ? _l($t['text_key']) : ($t['text'] ?? '');
                        $reviewAuthor = isset($t['author_key']) ? _l($t['author_key']) : ($t['author'] ?? '');
                        $reviewRole = isset($t['role_key']) ? _l($t['role_key']) : ($t['role'] ?? '');
                        ?>
                        <div class="review-2">
                            <div class="review-txt">
                                <!-- Rating Stars -->
                                <div class="star-rating ico-20 color--yellow clearfix">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <span class="flaticon-star-<?php echo $i <= $t['rating'] ? '1' : ($t['rating'] < $i ? '-empty' : ''); ?>"></span>
                                    <?php endfor; ?>
                                </div>
                                <!-- Review Text -->
                                <p>
                                    <?= htmlspecialchars($reviewText); ?>
                                </p>
                                <!-- Author & Role -->
                                <div class="review-author">
                                    <p><?= htmlspecialchars($reviewAuthor); ?></p>
                                    <span><?= htmlspecialchars($reviewRole); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div> <!-- End container -->
</div> <!-- END TESTIMONIALS -->


<?php // ---------------------------- ?>
<?php // PRO Section: B2B offering overview ?>
<section id="benefits" class="py-8 content-section bg--smoke">
    <div class="container smoke--shape">
        <div class="row d-flex align-items-center">

            <!-- TEXT BLOCK -->
            <div class="col-lg-6 order-last order-lg-2 wow fadeInRight" data-wow-delay="0.5s">
                <div class="txt-block left-column">

                    <!-- Section Title -->
                    <h2 class="h2-md">
                        <?php echo _l('beaute_inee_index_pro_title'); ?>
                    </h2>

                    <!-- Bullet List -->
                    <ul class="simple-list">
                        <?php foreach ($pro_bullets as $bullet): ?>
                            <li class="list-item">
                                <p><?php echo _l($bullet); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Partner Categories -->
                    <div class="text-start">
                        <h3 class="h4 fw-bold mb-3">
                            <?php echo _l('beaute_inee_index_pro_partners_title'); ?>
                        </h3>
                        <p class="mb-4">
                            <?php echo _l('beaute_inee_index_pro_partners_intro'); ?>
                        </p>
                        <p class="mb-3">
                            <?php echo _l('beaute_inee_index_pro_partners_call'); ?>
                        </p>
                        <ul class="simple-list mb-4">
                            <?php foreach ($pro_partners as $partner): ?>
                                <li class="list-item"><?php echo _l($partner); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="fst-italic">
                            <?php echo _l('beaute_inee_index_pro_partners_footer'); ?>
                        </p>
                    </div>

                    <!-- Contact Buttons -->
                    <div class="txt-block-btn pb-5">
                        <a href="<?php echo _l('beaute_inee_index_pro_email1'); ?>"
                           class="btn btn--tra-gold hover--gold" target="_blank">
                            <?php echo _l('beaute_inee_index_pro_cta'); ?>
                        </a>
                        <a href="mailto:<?php echo _l('beaute_inee_index_pro_email2'); ?>"
                           class="btn btn--tra-black hover--black">
                            <?php echo _l('beaute_inee_index_pro_email2'); ?>
                        </a>
                    </div>

                    <!-- Brands Carousel -->
                    <div class="row">
                        <div class="col text-center">
                            <div class="owl-carousel brands-carousel-5">
                                <?php foreach ($pro_logos as $logo): ?>
                                    <div class="brand-logo">
                                        <a href="<?php echo $logo['url']; ?>">
                                            <img class="img-fluid"
                                                 src="<?= $theme_assets_url; ?>images/partnair/<?php echo $logo['file']; ?>"
                                                 alt="<?php echo htmlspecialchars(_l('beaute_inee_index_pro_logo_alt')); ?>">
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- IMAGE BLOCK -->
            <div class="mt-5 col-lg-6 order-first order-lg-2">
                <div class="ct-05-img right-column wow fadeInLeft">
                    <img class="img-fluid lazyload"
                         src="<?= $theme_assets_url; ?>images/bi_pro.webp"
                         alt="<?php echo _l('beaute_inee_index_pro_image_alt'); ?>">
                </div>
            </div>

        </div> <!-- End row -->
    </div>   <!-- End container -->
</section>

<?php // ---------------------------- ?>
<?php // Contact Section: Inquiry prompt ?>
<section id="booking" class="pt-8 division">
    <div class="container text-center">
        <div class="row">
            <div class="col">
                <div class="category-title mb-6">
                    <h2><?php echo _l('beaute_inee_index_contact_title'); ?></h2>
                    <p><?php echo _l('beaute_inee_index_contact_text'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php // ---------------------------- ?>
<?php // Contacts Section: map + details ?>
<div id="contacts-1" class="contacts-section division">
    <div class="container">
        <div class="row equal-height">

            <!-- Google Map -->
            <div class="col-md-6">
                <div class="google-map">
                    <iframe
                            src="<?php echo _l('beaute_inee_index_map_src'); ?>"
                            width="100%" height="100%" style="border:0;"
                            allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

            <!-- Contact Details -->
            <div class="col-md-6">
                <div class="ct-09-img">
                    <img class="img-fluid"
                         src="<?= $theme_assets_url; ?>images/Institut.webp"
                         alt="<?php echo _l('beaute_inee_index_contacts_image_alt'); ?>">
                </div>
                <div class="ct-09-txt">
                    <h3><?php echo _l('beaute_inee_index_contacts_name'); ?></h3>
                    <p class="ct-09-address"><?php echo _l('beaute_inee_index_contacts_address'); ?></p>
                    <ul class="advantages ico-30 clearfix">
                        <?php /*<li>
                            <p><a href="tel:<?php echo _l('beaute_inee_index_contacts_phone'); ?>">
                                    <?php echo _l('beaute_inee_index_contacts_phone'); ?>
                                </a></p>
                        </li>
                        <li class="advantages-links-divider"><span class="flaticon-vertical-line"></span></li>*/ ?>
                        <li>
                            <p><a href="mailto:<?php echo _l('beaute_inee_index_contacts_email'); ?>">
                                    <?php echo _l('beaute_inee_index_contacts_email'); ?>
                                </a></p>
                        </li>
                    </ul>
                </div>
            </div>

        </div> <!-- End row -->
    </div>     <!-- End container -->
</div> <!-- End Contacts Section -->


<?php // ---------------------------- ?>
<?php // Brands Section: trusted partners carousel ?>
<section id="brands-1" class="pt-8 brands-section">
    <div class="container">

        <!-- Section Header -->
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="section-title text-center mb-6">
                    <span class="section-id">
                        <?php echo _l('beaute_inee_index_brands_section_id'); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Brands Carousel -->
        <div class="row">
            <div class="col text-center">
                <div class="owl-carousel brands-carousel-5">
                    <?php foreach ($brands as $brand): ?>
                        <div class="brand-logo">
                            <a href="<?php echo htmlspecialchars($brand['url']); ?>">
                                <img class="img-fluid"
                                     src="<?= $theme_assets_url; ?>images/partnair/<?php echo $brand['file']; ?>"
                                     alt="<?php echo htmlspecialchars(_l('beaute_inee_index_brands_logo_alt')); ?>">
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div> <!-- End container -->
</section>

<?php require __DIR__ . '/footer.php'; ?>
