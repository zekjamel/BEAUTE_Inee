<?php
/**
 * Configuration autonome — Beauté INÉE
 * Fichier de configuration autonome — données, traductions, assets.
 * Ce fichier est inclus en haut de chaque page.
 */

/* ──────────────────────────────────────────────
   1. DÉTECTION DE LA LANGUE
   ────────────────────────────────────────────── */

// Déterminer la langue : paramètre URL > cookie > Accept-Language header
$current_language = 'fr'; // Le site et la maquette de référence sont en français par défaut

// Vérifier le paramètre URL
if (isset($_GET['lang'])) {
    $current_language = ($_GET['lang'] === 'fr') ? 'fr' : 'en';
    setcookie('language', $current_language, time() + (30 * 24 * 60 * 60), '/'); // 30 jours
} 
// Vérifier le cookie
elseif (isset($_COOKIE['language'])) {
    $current_language = ($_COOKIE['language'] === 'fr') ? 'fr' : 'en';
}
// Analyser l'en-tête Accept-Language du navigateur
elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    // Extraire la première langue préférée
    if (preg_match('/^(fr|en)/i', $accept_language, $matches)) {
        $current_language = (strtolower($matches[1]) === 'fr') ? 'fr' : 'en';
    } elseif (preg_match('/(fr|en)/i', $accept_language, $matches)) {
        $current_language = (strtolower($matches[1]) === 'fr') ? 'fr' : 'en';
    }
    setcookie('language', $current_language, time() + (30 * 24 * 60 * 60), '/'); // 30 jours
}

/* ──────────────────────────────────────────────
   2. CHEMINS & URLS
   ────────────────────────────────────────────── */

$theme_assets_url = './';
$base_url         = '';   // racine relative

function base_url($path = '') {
    // En mode autonome, on pointe vers la racine du dossier
    return './' . ltrim($path, '/');
}

function site_url($path = '') {
    return base_url($path);
}


/* ──────────────────────────────────────────────
   3. TRADUCTIONS  (_l)
   ────────────────────────────────────────────── */

// Traductions EN ANGLAIS
$_translations_en = [

    // ── META ──
    'beaute_inee_landing_page_title'           => 'Beauté INÉE – Teleconsultations & in-cabin diagnostics',
    'beaute_inee_landing_page_meta_author'      => 'Beauté INÉE',
    'beaute_inee_landing_page_meta_description'  => 'Beauté INÉE – Diagnostics connectés et soins sur mesure pour votre peau. Teleconsultations et diagnostics en cabine.',
    'beaute_inee_landing_page_meta_keywords'     => 'beauté, diagnostic peau, soins personnalisés, K-beauty, consultation peau, Beauté INÉE',

    // ── MENU ──
    'menu_book_appointment'          => 'Book an Appointment',
    'menu_book_skinconsultation'     => 'Skin Consultation',
    'menu_book_followup'             => 'Follow-Up Appointment',
    'menu_book_diagnostic_treatment' => 'Diagnostic + Facial Treatment',
    'menu_shop'                      => 'Shop',
    'menu_shop_selection'            => 'Our Selection',
    'menu_shop_k_beauty'             => 'K-Beauty',
    'menu_prediagnostic'             => 'Pre-Diagnostic',
    'menu_prediagnostic_free'        => 'Free Pre-Diagnostic',
    'menu_prediagnostic_mysmartdiag' => 'MySmartDiag',
    'menu_brand'                     => 'Brand',
    'menu_brand_story'               => 'Our Story',
    'menu_partner'                   => 'Partner',
    'menu_partner_experts'           => 'Skin Care Experts',
    'menu_partner_brands'            => 'Brands',

    // ── URLS ──
    'book_skinconsultation_url'      => 'https://site.booxi.eu/beauteinee?lang=eng_en&book=265739',
    'book_followup_url'              => 'https://site.booxi.eu/beauteinee?lang=eng_en&book=266430',
    'book_diagnostic_treatment_url'  => 'https://site.booxi.eu/beauteinee?lang=eng_en&book=255114',
    'prediagnostic_free_url'         => 'https://2qrq081oo0r.typeform.com/to/NW9Gn9Kp',
    'prediagnostic_mysmartdiag_url'  => 'https://2qrq081oo0r.typeform.com/to/JRUNhjbH',
    'partner_experts_url'            => 'https://forms.gle/crqVU3D32bNzQBBF6',
    'partner_brands_url'             => '#',
    'shop_cart_url'                  => 'https://beaute-inee.myshopify.com',

    // ── HERO ──
    'beaute_inee_index_hero_title'              => 'HERE BEGINS YOUR SKIN\'S STORY',
    'beaute_inee_index_hero_subtitle'           => 'Your skin deserves more than a classic treatment. Give it a tailor-made expertise.',
    'beaute_inee_index_hero_cta_services'        => 'Discover our services',
    'beaute_inee_index_hero_video_fallback_alt'  => 'Beauté INÉE – Hero',

    // ── HOW IT WORKS ──
    'beaute_inee_index_how_it_works_section_id' => 'The Beauté INÉE Experience',
    'beaute_inee_index_how_it_works_title'      => 'How It Works',
    'beaute_inee_index_how_it_works_text'        => 'Follow our simple and effective process to enjoy the best care and advice for your skin.',

    // ── STEPS ──
    'beaute_inee_index_step1_title' => 'Booking',
    'beaute_inee_index_step1_text'  => 'Book online or in-cabin to start your personalized skincare journey.',
    'beaute_inee_index_step2_title' => 'Diagnosis',
    'beaute_inee_index_step2_text'  => 'Receive a precise diagnosis to better understand your skin\'s needs.',
    'beaute_inee_index_step3_title' => 'Custom Routine',
    'beaute_inee_index_step3_text'  => 'Adopt a tailor-made skincare routine based on our experts\' advice.',
    'beaute_inee_index_step4_title' => 'Follow-Up',
    'beaute_inee_index_step4_text'  => 'Enjoy continuous follow-up for 3 months for lasting results.',

    // ── BANNER ──
    'beaute_inee_index_banner2_title'    => 'Discover our in-salon experience. Our beauty cabins are located in Paris 8th at Feel Good.',
    'beaute_inee_index_banner2_subtitle' => 'Book online or contact us with any questions.',
    'beaute_inee_index_banner2_cta'      => 'Book Now',

    // ── SERVICES ──
    'beaute_inee_index_services_title'    => 'Our Beauty Tech Services',
    'beaute_inee_index_services_subtitle' => 'Because every skin tells a unique story, we combine the rigor of technological analysis with our skin therapists\' keen observation for care that evolves with you.',

    'beaute_inee_index_service1_title'      => 'Skin Consultation',
    'beaute_inee_index_service1_subtitle'   => 'Diagnosis + Beauty Coaching — Paris 8th Cabin',
    'beaute_inee_index_service1_btn1_url'   => 'https://site.booxi.eu/beauteinee?lang=eng_en&book=265739',
    'beaute_inee_index_service1_btn2_label' => 'Paris 8th Cabin',
    'beaute_inee_index_service1_btn2_url'   => 'https://site.booxi.eu/beauteinee?lang=eng_en&book=265739',

    'beaute_inee_index_service2_title'      => 'Follow-Up Appointment',
    'beaute_inee_index_service2_subtitle'   => 'Only after the first consultation — Paris 8th Cabin',
    'beaute_inee_index_service2_btn1_url'   => 'https://site.booxi.eu/beauteinee?lang=eng_en&book=266430',
    'beaute_inee_index_service2_btn2_label' => 'Paris 8th Cabin',
    'beaute_inee_index_service2_btn2_url'   => 'https://site.booxi.eu/beauteinee?lang=eng_en&book=266430',

    'beaute_inee_index_service3_title'     => 'Diagnostic + Facial Treatment',
    'beaute_inee_index_service3_subtitle'  => 'In-Salon — Paris 8th Cabin',
    'beaute_inee_index_service3_btn_label' => 'Treatments that complement your diagnosis',
    'beaute_inee_index_service3_btn_url'   => 'https://site.booxi.eu/beauteinee?lang=eng_en&book=255114',

    // ── PRODUCTS ──
    'beaute_inee_index_products_section_id' => 'Our Selection',
    'beaute_inee_index_products_title'      => 'Our Product Selection',
    'beaute_inee_index_products_text'        => 'We collaborate with committed, innovative and demanding brands 🌱 to offer you products perfectly suited to your skin based on your consultation results. At Beauté INÉE, we reduce nearly 500€ waste from ill-suited products through an economical, sustainable and responsible approach that respects both your skin and the planet. Our skin therapists guide you through this custom routine, designed to meet your skin\'s real needs while minimizing environmental impact.',
    'beaute_inee_index_products_cta'         => 'Discover',

    // ── EXPERTS ──
    'beaute_inee_index_experts_section_id' => 'Our Expertise',
    'beaute_inee_index_experts_title'      => 'La Skin Transformation',
    'beaute_inee_index_experts_intro'       => 'Beauté INÉE combine l\'expertise humaine et la technologie pour transformer votre routine beauté. Notre approche unique repose sur un diagnostic connecté et un accompagnement personnalisé.',
    'beaute_inee_index_experts_image_alt'   => 'La Skin Transformation – Beauté INÉE',
    'beaute_inee_index_experts_closing'     => 'Avec Beauté INÉE, reprenez le pouvoir sur votre peau, en toute sérénité.',

    // ── METHODOLOGY ──
    'beaute_inee_index_methodology_title'       => 'Notre méthodologie',
    'beaute_inee_index_methodology_item1_label' => 'Diagnostic connecté',
    'beaute_inee_index_methodology_item1_text'  => 'Analyse précise de votre peau grâce à notre technologie propriétaire.',
    'beaute_inee_index_methodology_item2_label' => 'Expertise humaine',
    'beaute_inee_index_methodology_item2_text'  => 'Nos skin therapists certifiés interprètent les résultats et vous accompagnent.',
    'beaute_inee_index_methodology_item3_label' => 'Routine sur mesure',
    'beaute_inee_index_methodology_item3_text'  => 'Des recommandations personnalisées pour des résultats durables.',

    // ── ACCORDION (EXPERTS) ──
    'beaute_inee_index_accordion1_title' => 'Peaux sensibles & réactives',
    'beaute_inee_index_accordion1_text'  => 'Notre diagnostic détecte les signes d\'inflammation et les zones sensibilisées pour proposer un protocole apaisant adapté.',
    'beaute_inee_index_accordion2_title' => 'Peaux mixtes à grasses',
    'beaute_inee_index_accordion2_text'  => 'Nous identifions les déséquilibres de sébum et proposons des soins régulateurs sans agression.',
    'beaute_inee_index_accordion3_title' => 'Peaux sèches & déshydratées',
    'beaute_inee_index_accordion3_text'  => 'Un protocole réparateur ciblé pour relancer la régénération cellulaire et restaurer la barrière cutanée.',
    'beaute_inee_index_accordion4_title' => 'Peaux à imperfections',
    'beaute_inee_index_accordion4_text'  => 'Des solutions purifiantes douces et des actifs ciblés pour un teint plus net et uniforme.',
    'beaute_inee_index_accordion5_title' => 'Peaux matures',
    'beaute_inee_index_accordion5_text'  => 'Des soins anti-âge personnalisés pour préserver l\'éclat et la fermeté de votre peau.',

    // ── BENEFITS ──
    'beaute_inee_index_benefits_title'    => 'Les bienfaits de nos soins',
    'beaute_inee_index_benefits_subtitle' => 'Découvrez comment nos diagnostics connectés et nos soins personnalisés transforment votre peau.',
    'beaute_inee_index_benefits_cta'      => 'En savoir plus',

    'beaute_inee_index_benefit1_alt'           => 'Effet apaisant',
    'beaute_inee_index_benefit1_overlay_title'  => 'Apaisant',
    'beaute_inee_index_benefit1_title'          => 'Effet Apaisant',
    'beaute_inee_index_benefit1_text'           => 'Calme les rougeurs, les irritations et les sensations de tiraillement.',

    'beaute_inee_index_benefit2_alt'           => 'Effet réparateur',
    'beaute_inee_index_benefit2_overlay_title'  => 'Réparateur',
    'beaute_inee_index_benefit2_title'          => 'Effet Réparateur',
    'beaute_inee_index_benefit2_text'           => 'Relance la régénération cellulaire pour une peau renforcée.',

    'beaute_inee_index_benefit3_alt'           => 'Effet équilibrant',
    'beaute_inee_index_benefit3_overlay_title'  => 'Équilibrant',
    'beaute_inee_index_benefit3_title'          => 'Effet Équilibrant',
    'beaute_inee_index_benefit3_text'           => 'Régule le sébum et réduit les imperfections durablement.',

    'beaute_inee_index_benefit4_alt'           => 'Effet unifiant',
    'beaute_inee_index_benefit4_overlay_title'  => 'Unifiant',
    'beaute_inee_index_benefit4_title'          => 'Effet Unifiant',
    'beaute_inee_index_benefit4_text'           => 'Révèle l\'éclat naturel de votre teint et atténue les taches.',

    // ── ABOUT ──
    'beaute_inee_index_about_section_id' => 'Notre histoire',
    'beaute_inee_index_about_title'       => 'Beauté INÉE',
    'beaute_inee_index_about_text1'       => 'Beauté INÉE est née de la vision partagée de trois sœurs aux parcours complémentaires et au même engagement : réinventer la beauté pour qu\'elle reflète la diversité des peaux, des besoins et des usages.',
    'beaute_inee_index_about_text2'       => 'Quand on écoute la peau, toute histoire commence à se révéler...',
    'beaute_inee_index_about_image_alt'   => 'Notre histoire – Beauté INÉE',
    'beaute_inee_index_about_cta'         => 'En savoir plus',

    // ── SNEAK PEEK ──
    'beaute_inee_index_sneakpeek_image_alt'  => 'Beauté INÉE – Produits',
    'beaute_inee_index_sneakpeek_text1'      => 'Beauté INÉE est née de la vision partagée de trois sœurs aux parcours complémentaires.',
    'beaute_inee_index_sneakpeek_text2'      => 'Découvrez leurs parcours :',
    'beaute_inee_index_sneakpeek_btn_khadi'  => 'Khadidiatou',
    'beaute_inee_index_sneakpeek_btn_fatou'  => 'Fatoumata',
    'beaute_inee_index_sneakpeek_btn_awa'    => 'Awa',

    // ── FAQ ──
    'beaute_inee_index_faq_section_id' => 'Frequently Asked Questions',
    'beaute_inee_index_faq_title'       => 'FAQ',

    'beaute_inee_index_faq_q1'  => 'How long is a Skin Consultation?',
    'beaute_inee_index_faq_a1'  => 'Each Skin Consultation lasts approximately 30 minutes, including diagnosis, advice and product recommendations.',
    'beaute_inee_index_faq_q2'  => 'Who are the Skin Therapists?',
    'beaute_inee_index_faq_a2'  => 'Our Skin Therapists are certified skincare experts trained in the latest scientific and technological advances. They perform precise diagnoses and advise you on personalized routines.',
    'beaute_inee_index_faq_q3'  => 'What skin types can you diagnose?',
    'beaute_inee_index_faq_a3'  => 'At Beauté INÉE, we diagnose all skin types—sensitive, combination, oily, dry, atopic, and normal. Our technology is designed to be inclusive and respectful of every complexion.',
    'beaute_inee_index_faq_q4'  => 'Are Skin Consultations suitable for teenagers?',
    'beaute_inee_index_faq_a4'  => 'Yes, our Skin Consultations are perfectly suited to teenagers, taking into account their specific needs. Parental consent is simply required.',
    'beaute_inee_index_faq_q5'  => 'What are the benefits of a tailor-made routine?',
    'beaute_inee_index_faq_a5'  => 'A bespoke skincare routine ensures products perfectly matched to your skin type and needs, minimizing waste and maximizing efficacy.',
    'beaute_inee_index_faq_q6'  => 'How does an in-cabin Skin Consultation work?',
    'beaute_inee_index_faq_a6'  => 'Our in-cabin consultations start with a personalized discussion followed by a full diagnostic using our advanced tools.',
    'beaute_inee_index_faq_list6' => 'Digitalized skin assessment|Personalized advice|Custom skincare routine|Option to store recommendations on your Beauté INÉE smart card',
    'beaute_inee_index_faq_q7'  => 'How does a remote (video) Skin Consultation work?',
    'beaute_inee_index_faq_a7'  => 'After the session, you receive by email: digital skin report, targeted product recommendations, and a bespoke treatment plan.',
    'beaute_inee_index_faq_list7' => 'Online booking at beauteinee.fr|Pre-appointment expert questionnaire|Intelligent photo analysis before or during the call|Live video consultation with a certified skin expert',
    'beaute_inee_index_faq_q8'  => 'How does a Skin Consultation + facial treatment work in-cabin?',
    'beaute_inee_index_faq_a8'  => 'This service combines technological diagnosis and a custom facial treatment (cleansing, hydration, calming) in a single session.',
    'beaute_inee_index_faq_q9'  => 'Should I come without makeup to my Skin Consultation?',
    'beaute_inee_index_faq_a9'  => 'Yes, arriving makeup-free is recommended. A gentle makeup remover is available on site if needed.',
    'beaute_inee_index_faq_list9' => 'Observe your natural skin texture|Identify redness and imperfections|Detect dry areas',
    'beaute_inee_index_faq_q10' => 'What skin issues do you treat?',
    'beaute_inee_index_faq_a10' => 'We address common concerns: dryness, acne, pigmentation spots, fine lines, etc., for every phototype. For medical issues we refer you to a healthcare professional.',
    'beaute_inee_index_faq_q11' => 'What are the advantages of a personalized skin diagnosis?',
    'beaute_inee_index_faq_a11' => 'Precise identification of your skin\'s needs, targeted recommendations and ongoing follow-up for lasting results.',
    'beaute_inee_index_faq_list11' => 'Precise identification of skin needs|Targeted product recommendations|Ongoing follow-up for lasting results',
    'beaute_inee_index_faq_q12' => 'Can I get a routine adapted to my lifestyle?',
    'beaute_inee_index_faq_a12' => 'Yes, we tailor routines to your pace—minimalist, express, or comprehensive.',
    'beaute_inee_index_faq_q13' => 'From what age can one consult at Beauté INÉE?',
    'beaute_inee_index_faq_a13' => 'Our services are open to all, including minors (parental consent required).',
    'beaute_inee_index_faq_q14' => 'Should I change my routine with the seasons?',
    'beaute_inee_index_faq_a14' => 'We recommend quarterly diagnostics to adapt your routine to weather changes.',
    'beaute_inee_index_faq_q15' => 'Are recommended products suitable for sensitive/reactive skin?',
    'beaute_inee_index_faq_a15' => 'Yes, we select soothing, gentle formulas that respect sensitive skin.',
    'beaute_inee_index_faq_q16' => 'How long until I see results?',
    'beaute_inee_index_faq_a16' => 'Initial improvements are typically visible after 28 days of consistent use.',
    'beaute_inee_index_faq_q17' => 'Where is the Beauté INÉE cabin located?',
    'beaute_inee_index_faq_a17' => 'Our cabin is located at 135 Boulevard Haussmann, 75008 Paris, inside the Feel Good community space.',

    // ── TESTIMONIALS ──
    'beaute_inee_index_testimonials_section_id' => 'Testimonials',
    'beaute_inee_index_testimonials_title'       => 'Our Clients Trust Us',

    'beaute_inee_index_testimonial1_text'   => 'Our teams feel more relaxed and motivated. It truly changed our office dynamic.',
    'beaute_inee_index_testimonial1_author' => 'Sophie R.',
    'beaute_inee_index_testimonial1_role'   => 'HR Director, Tech Start-up',
    'beaute_inee_index_testimonial2_text'   => 'These sessions helped our team collaborate better and increase productivity.',
    'beaute_inee_index_testimonial2_author' => 'Julien D.',
    'beaute_inee_index_testimonial2_role'   => 'QWL Manager, Coworking Space',
    'beaute_inee_index_testimonial3_text'   => 'Our teams enjoy the sessions—a relaxed atmosphere and renewed motivation.',
    'beaute_inee_index_testimonial3_author' => 'Leslie A.',
    'beaute_inee_index_testimonial3_role'   => 'Happiness Manager, Luxury Group',
    'beaute_inee_index_testimonial4_text'   => 'Thanks to Beauté INÉE events, our pharmacy saw a significant client increase—skin diagnostics were a hit.',
    'beaute_inee_index_testimonial4_author' => 'Jeanne M.',
    'beaute_inee_index_testimonial4_role'   => 'Pharmacy Manager',
    'beaute_inee_index_testimonial5_text'   => 'We loved combining AI technology with influencers to promote our products—immediate sales impact.',
    'beaute_inee_index_testimonial5_author' => 'Paul L.',
    'beaute_inee_index_testimonial5_role'   => 'Shop Owner',

    // ── PRO ──
    'beaute_inee_index_pro_title'           => 'PROFESSIONALS, ELEVATE YOUR CARE WITH BEAUTÉ INÉE',
    'beaute_inee_index_pro_bullet1'         => 'Join our ambassador network sharing our vision of connected, personalized beauty.',
    'beaute_inee_index_pro_bullet2'         => 'Our Beauty Tech solution merges advanced tech and human expertise to enhance your client experience.',
    'beaute_inee_index_pro_bullet3'         => 'Easy integration for precise diagnostics and bespoke care, ensuring visible results and loyalty.',
    'beaute_inee_index_pro_bullet4'         => 'Access our Pro Portal, featuring the Beauté INÉE smart card—designed to transform your business.',
    'beaute_inee_index_pro_partners_title'  => 'Professionals We Support',
    'beaute_inee_index_pro_partners_intro'  => 'Beauté INÉE partners with passionate professionals seeking high-value, innovative services.',
    'beaute_inee_index_pro_partners_call'   => 'We cater to experts driven by innovation:',
    'beaute_inee_index_pro_partner_esth'    => 'Estheticians',
    'beaute_inee_index_pro_partner_derm'    => 'Dermatologists',
    'beaute_inee_index_pro_partner_cosm'    => 'Dermo-cosmetics Advisors',
    'beaute_inee_index_pro_partner_brand'   => 'Cosmetic Brands',
    'beaute_inee_index_pro_partners_footer' => 'Professionals… dare to enrich your client experience and showcase your expertise.',
    'beaute_inee_index_pro_cta'             => 'Contact Us',
    'beaute_inee_index_pro_email1'          => 'mailto:hello@beauteinee.fr',
    'beaute_inee_index_pro_email2'          => 'contactpro@beauteinee.fr',
    'beaute_inee_index_pro_image_alt'       => 'Beauté INÉE – Professionals',
    'beaute_inee_index_pro_logo_alt'        => 'Partner logo',

    // ── CONTACT ──
    'beaute_inee_index_contact_title'       => 'Contact Us',
    'beaute_inee_index_contact_text'        => 'Have questions? Don\'t hesitate to reach out!',
    'beaute_inee_index_contacts_name'       => 'Beauté INÉE Feelgood Community',
    'beaute_inee_index_contacts_address'    => '135 Boulevard Haussmann, 75008 Paris',
    'beaute_inee_index_contacts_phone'      => '+33 1 00 00 00 00',
    'beaute_inee_index_contacts_email'      => 'hello@beauteinee.fr',
    'beaute_inee_index_contacts_image_alt'  => 'Institut Beauté INÉE',
    'beaute_inee_index_map_src'             => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2624.0!2d2.3!3d48.87!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2s135+Boulevard+Haussmann%2C+75008+Paris!5e0!3m2!1sfr!2sfr!4v1',

    // ── BRANDS ──
    'beaute_inee_index_brands_section_id' => 'Our Partners',
    'beaute_inee_index_brands_logo_alt'    => 'Partner brand',

    // ── FOOTER ──
    'footer_logo_alt'             => 'Logo Beauté INÉE',
    'footer_slogan_line1'         => 'Offrez à votre peau ce dont elle a besoin.',
    'footer_slogan_line2'         => 'Vous sentir bien dans votre peau avec Beauté INÉE, c\'est possible.',
    'footer_pages'                => 'Pages',
    'footer_shop'                 => 'La boutique',
    'footer_our_story'            => 'Notre histoire',
    'footer_blog'                 => 'Blog',
    'footer_information'          => 'Informations',
    'footer_cgu'                  => 'C.G.U.',
    'footer_return_policy'        => 'Politique de retours',
    'footer_legal_notice'         => 'Mentions légales',
    'footer_contact_us'           => 'Nous contacter',
    'footer_contact_prompt'       => 'Vous avez des questions ou des suggestions ?',
    'footer_need_advice'          => 'Besoin de conseils ?',
    'footer_write_us'             => 'Écrivez-nous à :',
    'footer_all_rights_reserved'  => 'Tous droits réservés.',
    'footer_designed_by'          => 'Conception',

    'social_facebook'  => 'Facebook',
    'social_tiktok'    => 'TikTok',
    'social_instagram' => 'Instagram',
    'social_linkedin'  => 'LinkedIn',

    // ── COOKIE ──
    'cookie_banner_header'       => 'CE SITE UTILISE DES COOKIES',
    'cookie_banner_description'  => 'En poursuivant votre navigation sur notre site, vous acceptez que nous collections des données telles que les pages visitées et les interactions pour améliorer votre expérience.',
    'cookie_accept'              => 'OK',
    'cookie_decline'             => 'Refuser',

    // ── MISC ──
    'load_more'                        => 'Load More',
    'landing_page_title_leads_form'    => 'Inscription',
    'welcome_title_login_client'       => 'Bienvenue chez Beauté INÉE',
    'description_title_login_client'   => 'Votre espace beauté personnalisé',
];

// Traductions EN FRANÇAIS
$_translations_fr = [

    // ── META ──
    'beaute_inee_landing_page_title'           => 'Beauté INÉE – Téléconsultations & diagnostics en cabine',
    'beaute_inee_landing_page_meta_author'      => 'Beauté INÉE',
    'beaute_inee_landing_page_meta_description'  => 'Beauté INÉE – Diagnostics connectés et soins sur mesure pour votre peau. Téléconsultations et diagnostics en cabine.',
    'beaute_inee_landing_page_meta_keywords'     => 'beauté, diagnostic peau, soins personnalisés, K-beauty, consultation peau, Beauté INÉE',

    // ── MENU ──
    'menu_book_appointment'          => 'Prendre un rendez-vous',
    'menu_book_skinconsultation'     => 'Consultation peau',
    'menu_book_followup'             => 'Rendez-vous de suivi',
    'menu_book_diagnostic_treatment' => 'Diagnostic + Soin du visage',
    'menu_shop'                      => 'Boutique',
    'menu_shop_selection'            => 'Notre sélection',
    'menu_shop_k_beauty'             => 'K-Beauty',
    'menu_prediagnostic'             => 'Pré-diagnostic',
    'menu_prediagnostic_free'        => 'Pré-diagnostic gratuit',
    'menu_prediagnostic_mysmartdiag' => 'MySmartDiag',
    'menu_brand'                     => 'Marque',
    'menu_brand_story'               => 'Notre histoire',
    'menu_partner'                   => 'Partenaires',
    'menu_partner_experts'           => 'Experts en soins',
    'menu_partner_brands'            => 'Marques',

    // ── URLS ──
    'book_skinconsultation_url'      => 'https://site.booxi.eu/beauteinee?lang=fre_fr&book=265739',
    'book_followup_url'              => 'https://site.booxi.eu/beauteinee?lang=fre_fr&book=266430',
    'book_diagnostic_treatment_url'  => 'https://site.booxi.eu/beauteinee?lang=fre_fr&book=255114',
    'prediagnostic_free_url'         => 'https://2qrq081oo0r.typeform.com/to/NW9Gn9Kp',
    'prediagnostic_mysmartdiag_url'  => 'https://2qrq081oo0r.typeform.com/to/JRUNhjbH',
    'partner_experts_url'            => 'https://forms.gle/crqVU3D32bNzQBBF6',
    'partner_brands_url'             => '#',
    'shop_cart_url'                  => 'https://beaute-inee.myshopify.com',

    // ── HERO ──
    'beaute_inee_index_hero_title'              => 'ICI COMMENCE L\'HISTOIRE DE VOTRE PEAU',
    'beaute_inee_index_hero_subtitle'           => 'Votre peau mérite plus qu\'un soin classique. Offrez-lui une expertise sur mesure.',
    'beaute_inee_index_hero_cta_services'        => 'Découvrez nos services',
    'beaute_inee_index_hero_video_fallback_alt'  => 'Beauté INÉE – Hero',

    // ── HOW IT WORKS ──
    'beaute_inee_index_how_it_works_section_id' => 'L\'expérience Beauté INÉE',
    'beaute_inee_index_how_it_works_title'      => 'Comment ça marche',
    'beaute_inee_index_how_it_works_text'        => 'Suivez notre processus simple et efficace pour profiter des meilleurs soins et conseils pour votre peau.',

    // ── STEPS ──
    'beaute_inee_index_step1_title' => 'Réservation',
    'beaute_inee_index_step1_text'  => 'Réservez en ligne ou en cabine pour commencer votre parcours beauté personnalisé.',
    'beaute_inee_index_step2_title' => 'Diagnostic',
    'beaute_inee_index_step2_text'  => 'Recevez un diagnostic précis pour mieux comprendre les besoins de votre peau.',
    'beaute_inee_index_step3_title' => 'Routine sur mesure',
    'beaute_inee_index_step3_text'  => 'Adoptez une routine beauté adaptée selon les conseils de nos experts.',
    'beaute_inee_index_step4_title' => 'Suivi',
    'beaute_inee_index_step4_text'  => 'Profitez d\'un suivi continu pendant 3 mois pour des résultats durables.',

    // ── BANNER ──
    'beaute_inee_index_banner2_title'    => 'Découvrez notre expérience en institut',
    'beaute_inee_index_banner2_subtitle' => 'Nos cabines esthétiques sont situées à Paris 8ᵉ chez Feel Good. Réservez une consultation en ligne ou contactez-nous pour toute question.',
    'beaute_inee_index_banner2_cta'      => 'Prendre rendez-vous',

    // ── SERVICES ──
    'beaute_inee_index_services_title'    => 'Nos services Beauty Tech',
    'beaute_inee_index_services_subtitle' => 'Parce que chaque peau raconte une histoire unique, nous conjuguons la rigueur de l’analyse technologique avec la finesse d’observation de nos skinthérapeutes. Pour des soins qui évoluent avec vous.',

    'beaute_inee_index_service1_title'      => 'Skinconsultation',
    'beaute_inee_index_service1_subtitle'   => 'Diagnostic + coaching beauté',
    'beaute_inee_index_service1_btn1_url'   => 'https://site.booxi.eu/beauteinee?lang=fre_fr&book=265739',
    'beaute_inee_index_service1_btn2_label' => 'Cabine Paris 8ᵉ',
    'beaute_inee_index_service1_btn2_url'   => 'https://site.booxi.eu/beauteinee?lang=fre_fr&book=265739',

    'beaute_inee_index_service2_title'      => 'Rendez-vous de Suivi',
    'beaute_inee_index_service2_subtitle'   => 'Uniquement après la première consultation',
    'beaute_inee_index_service2_btn1_url'   => 'https://site.booxi.eu/beauteinee?lang=fre_fr&book=266430',
    'beaute_inee_index_service2_btn2_label' => 'Cabine Paris 8ᵉ',
    'beaute_inee_index_service2_btn2_url'   => 'https://site.booxi.eu/beauteinee?lang=fre_fr&book=266430',

    'beaute_inee_index_service3_title'     => 'Diagnostic + Soin visage',
    'beaute_inee_index_service3_subtitle'  => 'En Institut',
    'beaute_inee_index_service3_btn_label' => 'Cabine Paris 8ᵉ',
    'beaute_inee_index_service3_btn_url'   => 'https://site.booxi.eu/beauteinee?lang=fre_fr&book=255114',

    // ── PRODUCTS ──
    'beaute_inee_index_products_section_id' => 'Les soins qui complètent votre diagnostic',
    'beaute_inee_index_products_title'      => 'Notre sélection produits',
    'beaute_inee_index_products_text'        => 'Nous collaborons avec des marques engagées 🌱, innovantes et exigeantes pour vous proposer des produits parfaitement adaptés à votre peau, selon les résultats de votre consultation. Chez Beauté INÉE, nous réduisons de près de 50% le gaspillage lié à l’utilisation de produits non adaptés à votre peau, grâce à une approche économique, durable et responsable qui respecte à la fois votre peau et la planète. Nos skinthérapeutes vous guident dans cette routine personnalisée, conçue pour répondre aux besoins réels de votre peau tout en ayant un impact environnemental maîtrisé.',
    'beaute_inee_index_products_cta'         => 'Acheter',

    // ── EXPERTS ──
    'beaute_inee_index_experts_section_id' => 'Notre expertise',
    'beaute_inee_index_experts_title'      => 'La Skin transformation : Notre expertise au service de toutes les peaux',
    'beaute_inee_index_experts_intro'       => 'Chez Beauté INÉE, nos experts sont sélectionnés pour leur formation diplômée et leur spécialisation. Chaque expert possède des compétences spécifiques pour traiter différents types de peaux et de problématiques.<br><strong>Notre mission :</strong> identifier avec précision les besoins uniques de votre peau, à l’aide d’outils de diagnostic avancés, et vous proposer des soins sur-mesure et évolutifs, adaptés à votre réalité cutanée.<br><strong>Notre force ?</strong> Une expertise fondée sur l’adaptation, la précision des diagnostics, des conseils personnalisés et des soins ciblés, pour des résultats visibles, durables et responsables.',
    'beaute_inee_index_experts_image_alt'   => 'La Skin Transformation – Beauté INÉE',
    'beaute_inee_index_experts_closing'     => 'Chez Beauté INÉE, nos spécialistes sont formés pour prendre soin de toutes les peaux, quelles que soient leur typologie ou leur carnation : peau normale, sèche, grasse, mixte, sensible, atopique, ou à tendance acnéique.',

    // ── METHODOLOGY ──
    'beaute_inee_index_methodology_title'       => 'En 3 temps : observer, comprendre, agir',
    'beaute_inee_index_methodology_item1_label' => 'Observer',
    'beaute_inee_index_methodology_item1_text'  => 'Grâce à notre technologie avancée, nous dévoilons ce que votre peau cache vraiment.',
    'beaute_inee_index_methodology_item2_label' => 'Comprendre',
    'beaute_inee_index_methodology_item2_text'  => 'Nos skinthérapeutes interprètent ces données pour cerner vos besoins réels.',
    'beaute_inee_index_methodology_item3_label' => 'Agir',
    'beaute_inee_index_methodology_item3_text'  => 'Ensemble, nous mettons en place un soin personnalisé qui évolue avec vous pour une peau plus saine et rayonnante.',

    // ── ACCORDION (EXPERTS) ──
    'beaute_inee_index_accordion1_title' => 'Peaux noires et métissées',
    'beaute_inee_index_accordion1_text'  => 'Spécialistes des phototypes élevés pour une routine adaptée, nos experts accompagnent chaque type de carnation avec des soins ciblés, évitant les erreurs courantes des protocoles génériques.',
    'beaute_inee_index_accordion2_title' => 'Peaux mixtes, à grasses',
    'beaute_inee_index_accordion2_text'  => 'Peaux mixtes, à grasses',
    'beaute_inee_index_accordion3_title' => 'Peaux sensibles, réactives et déshydratées',
    'beaute_inee_index_accordion3_text'  => 'Nous établissons un protocole de soin protecteur et apaisant, adapté aux épidermes fragilisés, en réduisant rougeurs, inconfort et tiraillements.',
    'beaute_inee_index_accordion4_title' => 'Acné, imperfections et cicatrices',
    'beaute_inee_index_accordion4_text'  => 'Des solutions spécifiques pour purifier, cicatriser et améliorer visiblement la texture de la peau, en réduisant marques et boutons avec des soins correctifs non agressifs.',
    'beaute_inee_index_accordion5_title' => 'Anti-âge et rides',
    'beaute_inee_index_accordion5_text'  => 'Nos programmes sur-mesure boostent la production de collagène et d’élastine pour raffermir, lisser et revitaliser la peau tout en respectant sa sensibilité.',

    // ── BENEFITS ──
    'beaute_inee_index_benefits_title'    => 'Chez BEAUTÉ INÉE, chaque préoccupation de peau a sa solution sur-mesure',
    'beaute_inee_index_benefits_subtitle' => 'Identifiez vos besoins, découvrez des routines adaptées et des conseils experts pour une peau transformée.',
    'beaute_inee_index_benefits_cta'      => 'En savoir plus',

    'beaute_inee_index_benefit1_alt'           => 'Effet apaisant',
    'beaute_inee_index_benefit1_overlay_title'  => 'Soulager l’inflammation et les rougeurs<br>Nos conseils ciblés',
    'beaute_inee_index_benefit1_title'          => 'L’effet Apaisant',
    'beaute_inee_index_benefit1_text'           => 'Retrouvez la sérénité d’une peau confortable grâce à des routines sur-mesure',

    'beaute_inee_index_benefit2_alt'           => 'Effet réparateur',
    'beaute_inee_index_benefit2_overlay_title'  => 'Réparer et renforcer la barrière cutanée<br>Notre feuille de route personnalisée',
    'beaute_inee_index_benefit2_title'          => 'L’effet Réparateur',
    'beaute_inee_index_benefit2_text'           => 'Nos solutions pour renforcer les peaux fragilisées, abîmées ou marquées.',

    'beaute_inee_index_benefit3_alt'           => 'Effet équilibrant',
    'beaute_inee_index_benefit3_overlay_title'  => 'Réguler le sébum, réduire les imperfections<br>Solutions sur-mesure',
    'beaute_inee_index_benefit3_title'          => 'Effet équilibrant',
    'beaute_inee_index_benefit3_text'           => 'Dites adieu aux brillances et imperfections récurrentes grâce à nos routines ciblées.',

    'beaute_inee_index_benefit4_alt'           => 'Effet unifiant',
    'beaute_inee_index_benefit4_overlay_title'  => 'Unifier le teint, atténuer les tâches<br>Notre stratégie beauté personnalisée',
    'beaute_inee_index_benefit4_title'          => 'Effet unifiant',
    'beaute_inee_index_benefit4_text'           => 'Réduisez les tâches et révélez l’éclat de votre peau avec un diagnostic personnalisé.',

    // ── ABOUT ──
    'beaute_inee_index_about_section_id' => 'À propos de nous',
    'beaute_inee_index_about_title'       => 'Histoire de la marque',
    'beaute_inee_index_about_text1'       => 'Nous sommes une entreprise française de beauty-tech, créée pour transformer l’expérience skincare grâce à des solutions personnalisées, connectées et accessibles. Fondée par trois sœurs passionnées, Khadidiatou, Fatoumata et Awa Kébé.',
    'beaute_inee_index_about_text2'       => 'Grâce à la technologie, à l’intelligence artificielle et au savoir-faire de nos professionnels, nous permettons à chaque utilisateur de mieux comprendre, suivre et valoriser sa peau.',
    'beaute_inee_index_about_image_alt'   => 'Notre histoire – Beauté INÉE',
    'beaute_inee_index_about_cta'         => 'En savoir plus',

    // ── SNEAK PEEK ──
    'beaute_inee_index_sneakpeek_image_alt'  => 'Beauté INÉE – Produits',
    'beaute_inee_index_sneakpeek_text1'      => 'Beauté INÉE développe des services innovants autour du diagnostic cutané, de la data beauté et de l’intelligence artificielle, afin d’offrir à chacun·e une routine de soin sur mesure — en boutique, en ligne, ou lors d’événements spécialisés.',
    'beaute_inee_index_sneakpeek_text2'      => 'Nous croyons en une beauté inclusive, responsable, sans dogmes — où la technologie vient renforcer l’expertise, sans jamais la remplacer.',
    'beaute_inee_index_sneakpeek_btn_khadi'  => 'Khadi',
    'beaute_inee_index_sneakpeek_btn_fatou'  => 'Fatou',
    'beaute_inee_index_sneakpeek_btn_awa'    => 'Awa',

    // ── FAQ ──
    'beaute_inee_index_faq_section_id' => 'Questions fréquemment posées',
    'beaute_inee_index_faq_title'       => 'FAQ',

    'beaute_inee_index_faq_q1'  => 'Combien de temps dure une consultation peau ?',
    'beaute_inee_index_faq_a1'  => 'Chaque consultation peau dure environ 30 minutes, incluant le diagnostic, les conseils et les recommandations de produits.',
    'beaute_inee_index_faq_q2'  => 'Qui sont les Skin Therapists ?',
    'beaute_inee_index_faq_a2'  => 'Nos Skin Therapists sont des experts en soins certifiés, formés aux dernières avancées scientifiques et technologiques. Ils effectuent des diagnostics précis et vous conseillent sur des routines personnalisées.',
    'beaute_inee_index_faq_q3'  => 'Quels types de peau pouvez-vous diagnostiquer ?',
    'beaute_inee_index_faq_a3'  => 'Chez Beauté INÉE, nous diagnostiquons tous les types de peau — sensible, mixte, grasse, sèche, atopique et normale. Notre technologie est conçue pour être inclusive et respectueuse de chaque teint.',
    'beaute_inee_index_faq_q4'  => 'Les consultations peau conviennent-elles aux adolescents ?',
    'beaute_inee_index_faq_a4'  => 'Oui, nos consultations peau sont parfaitement adaptées aux adolescents, en tenant compte de leurs besoins spécifiques. Le consentement parental est simplement requis.',
    'beaute_inee_index_faq_q5'  => 'Quels sont les avantages d\'une routine sur mesure ?',
    'beaute_inee_index_faq_a5'  => 'Une routine beauté sur mesure garantit des produits parfaitement adaptés à votre type de peau et à vos besoins, minimisant les gaspillages et maximisant l\'efficacité.',
    'beaute_inee_index_faq_q6'  => 'Comment fonctionne une consultation peau en cabine ?',
    'beaute_inee_index_faq_a6'  => 'Nos consultations en cabine commencent par une discussion personnalisée suivie d\'un diagnostic complet utilisant nos outils avancés.',
    'beaute_inee_index_faq_list6' => 'Évaluation de la peau numérisée|Conseils personnalisés|Routine beauté sur mesure|Option pour stocker les recommandations sur votre carte Beauté INÉE',
    'beaute_inee_index_faq_q7'  => 'Comment fonctionne une consultation peau à distance (vidéo) ?',
    'beaute_inee_index_faq_a7'  => 'Après la session, vous recevez par email : rapport numérique de la peau, recommandations de produits ciblées et un plan de traitement personnalisé.',
    'beaute_inee_index_faq_list7' => 'Réservation en ligne sur beauteinee.fr|Questionnaire expert avant la consultation|Analyse photo intelligente avant ou pendant l\'appel|Consultation vidéo en direct avec un expert peau certifié',
    'beaute_inee_index_faq_q8'  => 'Comment fonctionne une consultation peau + soin du visage en cabine ?',
    'beaute_inee_index_faq_a8'  => 'Ce service combine le diagnostic technologique et un soin du visage personnalisé (nettoyage, hydratation, apaisement) en une seule séance.',
    'beaute_inee_index_faq_q9'  => 'Dois-je venir sans maquillage à ma consultation peau ?',
    'beaute_inee_index_faq_a9'  => 'Oui, arriver sans maquillage est recommandé. Un démaquillant doux est disponible sur place si nécessaire.',
    'beaute_inee_index_faq_list9' => 'Observer la texture naturelle de votre peau|Identifier les rougeurs et les imperfections|Détecter les zones sèches',
    'beaute_inee_index_faq_q10' => 'Quels problèmes de peau traitez-vous ?',
    'beaute_inee_index_faq_a10' => 'Nous abordons les problèmes courants : sécheresse, acné, taches de pigmentation, ridules, etc., pour chaque phototype. Pour les problèmes médicaux, nous vous orientons vers un professionnel de santé.',
    'beaute_inee_index_faq_q11' => 'Quels sont les avantages d\'un diagnostic peau personnalisé ?',
    'beaute_inee_index_faq_a11' => 'Identification précise des besoins de votre peau, recommandations ciblées et suivi continu pour des résultats durables.',
    'beaute_inee_index_faq_list11' => 'Identification précise des besoins de la peau|Recommandations de produits ciblées|Suivi continu pour des résultats durables',
    'beaute_inee_index_faq_q12' => 'Puis-je obtenir une routine adaptée à mon mode de vie ?',
    'beaute_inee_index_faq_a12' => 'Oui, nous adaptons les routines à votre rythme — minimaliste, express ou complet.',
    'beaute_inee_index_faq_q13' => 'À partir de quel âge peut-on consulter chez Beauté INÉE ?',
    'beaute_inee_index_faq_a13' => 'Nos services sont ouverts à tous, y compris les mineurs (consentement parental requis).',
    'beaute_inee_index_faq_q14' => 'Dois-je changer ma routine avec les saisons ?',
    'beaute_inee_index_faq_a14' => 'Nous recommandons des diagnostics trimestriels pour adapter votre routine aux changements climatiques.',
    'beaute_inee_index_faq_q15' => 'Les produits recommandés conviennent-ils aux peaux sensibles/réactives ?',
    'beaute_inee_index_faq_a15' => 'Oui, nous sélectionnons des formules apaisantes et douces qui respectent la peau sensible.',
    'beaute_inee_index_faq_q16' => 'Combien de temps avant de voir des résultats ?',
    'beaute_inee_index_faq_a16' => 'Les améliorations initiales sont généralement visibles après 28 jours d\'utilisation régulière.',
    'beaute_inee_index_faq_q17' => 'Où se trouve la cabine Beauté INÉE ?',
    'beaute_inee_index_faq_a17' => 'Notre cabine est situé à 135 Boulevard Haussmann, 75008 Paris, à l\'intérieur de l\'espace communautaire Feel Good.',

    // ── TESTIMONIALS ──
    'beaute_inee_index_testimonials_section_id' => 'Témoignages',
    'beaute_inee_index_testimonials_title'       => 'Nos clients nous font confiance',

    'beaute_inee_index_testimonial1_text'   => 'Nos équipes se sentent plus détendues et motivées. Cela a vraiment changé la dynamique de notre bureau.',
    'beaute_inee_index_testimonial1_author' => 'Sophie R.',
    'beaute_inee_index_testimonial1_role'   => 'Directrice RH, Start-up Tech',
    'beaute_inee_index_testimonial2_text'   => 'Ces sessions nous ont aidés à mieux collaborer et augmenter notre productivité.',
    'beaute_inee_index_testimonial2_author' => 'Julien D.',
    'beaute_inee_index_testimonial2_role'   => 'Manager QVT, Espace de coworking',
    'beaute_inee_index_testimonial3_text'   => 'Nos équipes aiment les sessions — une atmosphère détendue et une motivation renouvelée.',
    'beaute_inee_index_testimonial3_author' => 'Leslie A.',
    'beaute_inee_index_testimonial3_role'   => 'Happiness Manager, Groupe de luxe',
    'beaute_inee_index_testimonial4_text'   => 'Grâce aux événements Beauté INÉE, notre pharmacie a vu une augmentation significative de clients — les diagnostics peau ont eu un grand succès.',
    'beaute_inee_index_testimonial4_author' => 'Jeanne M.',
    'beaute_inee_index_testimonial4_role'   => 'Gérante de pharmacie',
    'beaute_inee_index_testimonial5_text'   => 'Nous avons adoré combiner la technologie IA avec des influenceurs pour promouvoir nos produits — impact immédiat sur les ventes.',
    'beaute_inee_index_testimonial5_author' => 'Paul L.',
    'beaute_inee_index_testimonial5_role'   => 'Propriétaire de boutique',

    // ── PRO ──
    'beaute_inee_index_pro_title'           => 'PROFESSIONNELS, ÉLEVEZ VOS SOINS AVEC BEAUTÉ INÉE',
    'beaute_inee_index_pro_bullet1'         => 'Rejoignez notre réseau d\'ambassadeurs partageant notre vision de la beauté connectée et personnalisée.',
    'beaute_inee_index_pro_bullet2'         => 'Notre solution Beauty Tech fusionne la technologie avancée et l\'expertise humaine pour améliorer l\'expérience client.',
    'beaute_inee_index_pro_bullet3'         => 'Intégration facile pour des diagnostics précis et des soins adaptés, garantissant des résultats visibles et la fidélité.',
    'beaute_inee_index_pro_bullet4'         => 'Accédez à notre Portail Pro, avec la carte intelligente Beauté INÉE — conçue pour transformer votre activité.',
    'beaute_inee_index_pro_partners_title'  => 'Professionnels que nous soutenons',
    'beaute_inee_index_pro_partners_intro'  => 'Beauté INÉE s\'associe à des professionnels passionnés en quête de services innovants et à haute valeur ajoutée.',
    'beaute_inee_index_pro_partners_call'   => 'Nous répondons aux experts motivés par l\'innovation :',
    'beaute_inee_index_pro_partner_esth'    => 'Esthéticiens',
    'beaute_inee_index_pro_partner_derm'    => 'Dermatologues',
    'beaute_inee_index_pro_partner_cosm'    => 'Conseillers Dermo-cosmétiques',
    'beaute_inee_index_pro_partner_brand'   => 'Marques cosmétiques',
    'beaute_inee_index_pro_partners_footer' => 'Professionnels… osez enrichir l\'expérience de vos clients et mettez en avant votre expertise.',
    'beaute_inee_index_pro_cta'             => 'Nous contacter',
    'beaute_inee_index_pro_email1'          => 'mailto:hello@beauteinee.fr',
    'beaute_inee_index_pro_email2'          => 'contactpro@beauteinee.fr',
    'beaute_inee_index_pro_image_alt'       => 'Beauté INÉE – Professionnels',
    'beaute_inee_index_pro_logo_alt'        => 'Logo partenaire',

    // ── CONTACT ──
    'beaute_inee_index_contact_title'       => 'Nous contacter',
    'beaute_inee_index_contact_text'        => 'Vous avez des questions ? N\'hésitez pas à nous joindre !',
    'beaute_inee_index_contacts_name'       => 'Beauté INÉE Feelgood Community',
    'beaute_inee_index_contacts_address'    => '135 Boulevard Haussmann, 75008 Paris',
    'beaute_inee_index_contacts_phone'      => '+33 1 00 00 00 00',
    'beaute_inee_index_contacts_email'      => 'hello@beauteinee.fr',
    'beaute_inee_index_contacts_image_alt'  => 'Institut Beauté INÉE',
    'beaute_inee_index_map_src'             => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2624.0!2d2.3!3d48.87!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2s135+Boulevard+Haussmann%2C+75008+Paris!5e0!3m2!1sfr!2sfr!4v1',

    // ── BRANDS ──
    'beaute_inee_index_brands_section_id' => 'Nos partenaires',
    'beaute_inee_index_brands_logo_alt'    => 'Marque partenaire',

    // ── FOOTER ──
    'footer_logo_alt'             => 'Logo Beauté INÉE',
    'footer_slogan_line1'         => 'Offrez à votre peau ce dont elle a besoin.',
    'footer_slogan_line2'         => 'Vous sentir bien dans votre peau avec Beauté INÉE, c\'est possible.',
    'footer_pages'                => 'Pages',
    'footer_shop'                 => 'La boutique',
    'footer_our_story'            => 'Notre histoire',
    'footer_blog'                 => 'Blog',
    'footer_information'          => 'Informations',
    'footer_cgu'                  => 'C.G.U.',
    'footer_return_policy'        => 'Politique de retours',
    'footer_legal_notice'         => 'Mentions légales',
    'footer_contact_us'           => 'Nous contacter',
    'footer_contact_prompt'       => 'Vous avez des questions ou des suggestions ?',
    'footer_need_advice'          => 'Besoin de conseils ?',
    'footer_write_us'             => 'Écrivez-nous à :',
    'footer_all_rights_reserved'  => 'Tous droits réservés.',
    'footer_designed_by'          => 'Conception',

    'social_facebook'  => 'Facebook',
    'social_tiktok'    => 'TikTok',
    'social_instagram' => 'Instagram',
    'social_linkedin'  => 'LinkedIn',

    // ── COOKIE ──
    'cookie_banner_header'       => 'CE SITE UTILISE DES COOKIES',
    'cookie_banner_description'  => 'En poursuivant votre navigation sur notre site, vous acceptez que nous collections des données telles que les pages visitées et les interactions pour améliorer votre expérience.',
    'cookie_accept'              => 'OK',
    'cookie_decline'             => 'Refuser',

    // ── MISC ──
    'load_more'                        => 'Voir plus',
    'landing_page_title_leads_form'    => 'Inscription',
    'welcome_title_login_client'       => 'Bienvenue chez Beauté INÉE',
    'description_title_login_client'   => 'Votre espace beauté personnalisé',
];

// Sélectionner les bonnes traductions selon la langue
$_translations = ($current_language === 'fr') ? $_translations_fr : $_translations_en;

function _l($key, $default = '') {
    global $_translations;
    if (isset($_translations[$key])) {
        return $_translations[$key];
    }
    return $default !== '' ? $default : $key;
}


/* ──────────────────────────────────────────────
   3. DONNÉES DYNAMIQUES (tableaux)
   ────────────────────────────────────────────── */

// Produits
$products = [
    ['brand' => 'TORRIDEN', 'name' => 'Crème apaisante à l\'acide hyaluronique', 'image' => 'torriden-creme-apaisante-acide-hyaluronique.jpg', 'alt' => 'TORRIDEN Crème apaisante à l\'acide hyaluronique', 'modal' => 'modal-diagnostic'],
    ['brand' => 'MEDICUBE', 'name' => 'Gelée de collagène', 'image' => 'gelee-collagene.webp', 'alt' => 'MEDICUBE Gelée de collagène', 'modal' => 'modal-diagnostic'],
    ['brand' => 'MIXSOON', 'name' => 'Essence aux haricots fermentés', 'image' => 'haricots-fermentes.webp', 'alt' => 'MIXSOON Essence aux haricots fermentés', 'modal' => 'modal-diagnostic'],
    ['brand' => 'MIXSOON', 'name' => 'Mousse nettoyante fermentée', 'image' => 'mixsoon-mousse.webp', 'alt' => 'MIXSOON Mousse nettoyante fermentée', 'modal' => 'modal-diagnostic'],
    ['brand' => 'I\'M FROM', 'name' => 'Toner au riz fermenté', 'image' => 'toner-au-riz.webp', 'alt' => 'I\'M FROM Toner au riz fermenté', 'modal' => 'modal-diagnostic'],
];

// Accordion (skin types)
$accordion = [
    ['title' => 'Peaux sensibles & réactives',    'text' => 'Notre diagnostic détecte les signes d\'inflammation et les zones sensibilisées pour proposer un protocole apaisant adapté.'],
    ['title' => 'Peaux mixtes à grasses',          'text' => 'Nous identifions les déséquilibres de sébum et proposons des soins régulateurs sans agression.'],
    ['title' => 'Peaux sèches & déshydratées',     'text' => 'Un protocole réparateur ciblé pour relancer la régénération cellulaire et restaurer la barrière cutanée.'],
    ['title' => 'Peaux à imperfections',           'text' => 'Des solutions purifiantes douces et des actifs ciblés pour un teint plus net et uniforme.'],
    ['title' => 'Peaux matures',                   'text' => 'Des soins anti-âge personnalisés pour préserver l\'éclat et la fermeté de votre peau.'],
];

// Benefits
$benefits = [
    ['image_path' => 'benefice-apaise.jpg',      'modal' => 'modal-apaisant',    'alt' => 'Effet apaisant',    'overlay_title' => 'Apaisant',    'title' => 'Effet Apaisant',    'text' => 'Calme les rougeurs, les irritations et les sensations de tiraillement.'],
    ['image_path' => 'benefice-reparateur.jpg',  'modal' => 'modal-reparateur',  'alt' => 'Effet réparateur',  'overlay_title' => 'Réparateur',  'title' => 'Effet Réparateur',  'text' => 'Relance la régénération cellulaire pour une peau renforcée.'],
    ['image_path' => 'benefice-equilibrant.jpg', 'modal' => 'modal-equilibrant', 'alt' => 'Effet équilibrant', 'overlay_title' => 'Équilibrant', 'title' => 'Effet Équilibrant', 'text' => 'Régule le sébum et réduit les imperfections durablement.'],
    ['image_path' => 'benefice-unifiant.jpg',    'modal' => 'modal-unifiant',    'alt' => 'Effet unifiant',    'overlay_title' => 'Unifiant',    'title' => 'Effet Unifiant',    'text' => 'Révèle l\'éclat naturel de votre teint et atténue les taches.'],
];

// FAQs
$faqs = [
    ['question_key' => 'beaute_inee_index_faq_q1',  'answer_key' => 'beaute_inee_index_faq_a1',  'answer_list_key' => ''],
    ['question_key' => 'beaute_inee_index_faq_q2',  'answer_key' => 'beaute_inee_index_faq_a2',  'answer_list_key' => ''],
    ['question_key' => 'beaute_inee_index_faq_q3',  'answer_key' => 'beaute_inee_index_faq_a3',  'answer_list_key' => ''],
    ['question_key' => 'beaute_inee_index_faq_q4',  'answer_key' => 'beaute_inee_index_faq_a4',  'answer_list_key' => ''],
    ['question_key' => 'beaute_inee_index_faq_q5',  'answer_key' => 'beaute_inee_index_faq_a5',  'answer_list_key' => ''],
    ['question_key' => 'beaute_inee_index_faq_q6',  'answer_key' => 'beaute_inee_index_faq_a6',  'answer_list_key' => 'beaute_inee_index_faq_list6'],
    ['question_key' => 'beaute_inee_index_faq_q7',  'answer_key' => 'beaute_inee_index_faq_a7',  'answer_list_key' => 'beaute_inee_index_faq_list7'],
    ['question_key' => 'beaute_inee_index_faq_q8',  'answer_key' => 'beaute_inee_index_faq_a8',  'answer_list_key' => ''],
    ['question_key' => 'beaute_inee_index_faq_q9',  'answer_key' => 'beaute_inee_index_faq_a9',  'answer_list_key' => 'beaute_inee_index_faq_list9'],
    ['question_key' => 'beaute_inee_index_faq_q10', 'answer_key' => 'beaute_inee_index_faq_a10', 'answer_list_key' => ''],
    ['question_key' => 'beaute_inee_index_faq_q11', 'answer_key' => 'beaute_inee_index_faq_a11', 'answer_list_key' => 'beaute_inee_index_faq_list11'],
    ['question_key' => 'beaute_inee_index_faq_q12', 'answer_key' => 'beaute_inee_index_faq_a12', 'answer_list_key' => ''],
    ['question_key' => 'beaute_inee_index_faq_q13', 'answer_key' => 'beaute_inee_index_faq_a13', 'answer_list_key' => ''],
    ['question_key' => 'beaute_inee_index_faq_q14', 'answer_key' => 'beaute_inee_index_faq_a14', 'answer_list_key' => ''],
    ['question_key' => 'beaute_inee_index_faq_q15', 'answer_key' => 'beaute_inee_index_faq_a15', 'answer_list_key' => ''],
    ['question_key' => 'beaute_inee_index_faq_q16', 'answer_key' => 'beaute_inee_index_faq_a16', 'answer_list_key' => ''],
    ['question_key' => 'beaute_inee_index_faq_q17', 'answer_key' => 'beaute_inee_index_faq_a17', 'answer_list_key' => ''],
];

// Testimonials
$testimonials = [
    ['text' => 'Our teams feel more relaxed and motivated. It truly changed our office dynamic.',                             'author' => 'Sophie R.',  'role' => 'HR Director, Tech Start-up',        'rating' => 5],
    ['text' => 'These sessions helped our team collaborate better and increase productivity.',                                'author' => 'Julien D.',  'role' => 'QWL Manager, Coworking Space',       'rating' => 5],
    ['text' => 'Our teams enjoy the sessions—a relaxed atmosphere and renewed motivation.',                                  'author' => 'Leslie A.',  'role' => 'Happiness Manager, Luxury Group',   'rating' => 5],
    ['text' => 'Thanks to Beauté INÉE events, our pharmacy saw a significant client increase—skin diagnostics were a hit.',  'author' => 'Jeanne M.', 'role' => 'Pharmacy Manager',                  'rating' => 5],
    ['text' => 'We loved combining AI technology with influencers to promote our products—immediate sales impact.',          'author' => 'Paul L.',    'role' => 'Shop Owner',                        'rating' => 4],
];

// Pro section
$pro_bullets = [
    'Join our ambassador network sharing our vision of connected, personalized beauty.',
    'Our Beauty Tech solution merges advanced tech and human expertise to enhance your client experience.',
    'Easy integration for precise diagnostics and bespoke care, ensuring visible results and loyalty.',
    'Access our Pro Portal, featuring the Beauté INÉE smart card—designed to transform your business.',
];

$pro_partners = [
    'Estheticians',
    'Dermatologists',
    'Dermo-cosmetics Advisors',
    'Cosmetic Brands',
];

$pro_logos = [
    ['file' => 'stationf.png',   'url' => '#'],
    ['file' => 'theplace.png',   'url' => '#'],
    ['file' => 'escalator.png',  'url' => '#'],
    ['file' => 'hec.jpg',        'url' => '#'],
    ['file' => 'europia.png',    'url' => '#'],
    ['file' => 'mia.png',        'url' => '#'],
    ['file' => 'courbevoie.png', 'url' => '#'],
    ['file' => 'humania.jpg',    'url' => '#'],
    ['file' => 'bhv.jpg',        'url' => '#'],
    ['file' => 'nha.jpg',        'url' => '#'],
    ['file' => 'qwartz.jpg',     'url' => '#'],
    ['file' => 'viva.jpg',       'url' => '#'],
    ['file' => 'agaskin.png',    'url' => '#'],
    ['file' => 'wure.png',       'url' => '#'],
    ['file' => 'cotton.png',     'url' => '#'],
    ['file' => 'elikya.png',     'url' => '#'],
];

$brands = $pro_logos; // Same logos used in both sections


/* ──────────────────────────────────────────────
   4. CSS & JS STATIQUES
   ────────────────────────────────────────────── */

$css_files = [
    './css/bootstrap.min.css',
    './css/owl.carousel.min.css',
    './css/owl.theme.default.min.css',
    './css/animate.css',
    './css/magnific-popup.css',
    './css/datetimepicker.min.css',
    './css/flaticon.css',
    './css/theme.css',
    './css/menu.css',
    './css/style.css',
    './css/zefix-anim.css',
    './css/zresponsive.css',
];

$js_files = [
    './js/jquery-3.7.0.min.js',
    './js/popper.min.js',
    './js/bootstrap.min.js',
    './js/modernizr.custom.js',
    './js/jquery.easing.js',
    './js/jquery.validate.min.js',
    './js/jquery.magnific-popup.min.js',
    './js/jquery.ajaxchimp.min.js',
    './js/owl.carousel.min.js',
    './js/datetimepicker.js',
    './js/wow.js',
    './js/materialize.js',
    './js/tweenmax.min.js',
    './js/menu.js',
    './js/theme.js',
    './js/slideshow.js',
    './js/custom.js',
    './js/booking-form.js',
    './js/contact-form.js',
    './js/comment-form.js',
    './js/request-form.js',
];
