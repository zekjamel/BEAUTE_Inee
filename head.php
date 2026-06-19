<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="<?php echo $this->config->item('language'); ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="author" content="<?php echo _l('beaute_inee_landing_page_meta_author'); ?>">
    <meta name="description" content="<?php echo _l('beaute_inee_landing_page_meta_description'); ?>">
    <meta name="keywords" content="<?php echo _l('beaute_inee_landing_page_meta_keywords'); ?>">
    <?php $current_lang = $this->config->item('language'); ?>
    <meta name="x-current-language" content="<?= $current_lang; ?>">

    <title><?php echo isset($title) ? $title : _l('beaute_inee_landing_page_title'); ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- FAVICON AND TOUCH ICONS -->
    <link rel="shortcut icon" type="image/x-icon" href="<?= site_url('uploads/company/' . get_option('favicon')); ?>">
    <link rel="icon" type="image/x-icon" href="<?= site_url('uploads/company/' . get_option('favicon')); ?>">

    <?php $company_logo = get_option('company_logo_dark'); ?>

    <link rel="apple-touch-icon" sizes="152x152" href="<?= 'https://screenshot.agence-xr.io/?url='.base_url('uploads/company/' . $company_logo).'&w=152&h=152'; ?>">
    <link rel="apple-touch-icon" sizes="120x120" href="<?= 'https://screenshot.agence-xr.io/?url='.base_url('uploads/company/' . $company_logo).'&w=120&h=120'; ?>">
    <link rel="apple-touch-icon" sizes="76x76" href="<?= 'https://screenshot.agence-xr.io/?url='.base_url('uploads/company/' . $company_logo).'&w=76&h=76'; ?>">
    <link rel="apple-touch-icon" href="<?= 'https://screenshot.agence-xr.io/?url='.base_url('uploads/company/' . $company_logo).'&w=128&h=128'; ?>">
    <link rel="icon" href="<?= 'https://screenshot.agence-xr.io/?url='.base_url('uploads/company/' . $company_logo).'&w=128&h=128'; ?>" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Alatsi&display=swap" rel="stylesheet">

    <!-- GOOGLE FONTS -->
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Vollkorn:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link id="effect" href="<?= $theme_assets_url; ?>css/dropdown-effects/fade-down.css" media="all" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/phosphor-icons@1.4.0/src/css/icons.css" rel="stylesheet">

    <?php if(isset($css_files)): ?>
        <?php foreach($css_files as $css): ?>
            <link href="<?php echo $css; ?>" rel="stylesheet" type="text/css">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- COOKIE BANNER -->
    <link href="<?= $theme_assets_url; ?>css/cookie-banner.css" rel="stylesheet" type="text/css">

</head>

<body class="customers bg--white <?php echo strtolower($this->agent->browser()); ?><?php if(is_mobile()){echo ' mobile';}?><?php if(isset($bodyclass)){echo ' ' . $bodyclass; } ?>" <?php if($isRTL == 'true'){ echo 'dir="rtl"';} ?>>
<?php hooks()->do_action('customers_after_body_start'); ?>

<div id="page" class="page">
