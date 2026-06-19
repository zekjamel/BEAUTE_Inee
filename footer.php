<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<footer class="pt-8 bg-light">
    <div class="container">

        <!-- 1ère ligne : logo & slogan | pages & mentions | nous contacter -->
        <div class="row text-center text-md-start gx-5 gy-4">

            <!-- colonne 1 : logo + slogan -->
            <div class="col-md-4">
                <img
                        src="<?= $theme_assets_url; ?>images/BI-logoNoir.png"
                        alt="<?= _l('footer_logo_alt', 'Logo Beauté INÉE'); ?>"
                        class="img-fluid mb-3"
                        style="max-width:180px; opacity:.7;"
                >
                <p class="mb-0 fw-semibold">
                    <?= _l('footer_slogan_line1', 'Offrez à votre peau ce dont elle a besoin.'); ?><br>
                    <?= _l('footer_slogan_line2', 'Vous sentir bien dans votre peau avec Beauté INÉE, c\'est possible.'); ?>
                </p>
            </div>

            <!-- colonne 2 : pages -->
            <div class="col-md-2">
                <h5 class="fw-bold mb-3"><?= _l('footer_pages', 'Pages'); ?></h5>
                <ul class="list-unstyled mb-4">
                    <li>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#modal-diagnostic" class="text-decoration-none text-dark">
                            <?= _l('footer_shop', 'La boutique'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="#histoire" class="text-decoration-none text-dark">
                            <?= _l('footer_our_story', 'Notre histoire'); ?>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- colonne 3 : informations légales -->
            <div class="col-md-2">
                <h5 class="fw-bold mb-3"><?= _l('footer_information', 'Informations'); ?></h5>
                <ul class="list-unstyled mb-0">
                    <li>
                        <a href="<?= base_url('lp/cgu'); ?>" class="text-decoration-none text-dark">
                            <?= _l('footer_cgu', 'C.G.U.'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('lp/return_policy'); ?>" class="text-decoration-none text-dark">
                            <?= _l('footer_return_policy', 'Politique de retours'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('lp/legacy'); ?>" class="text-decoration-none text-dark">
                            <?= _l('footer_legal_notice', 'Mentions légales'); ?>
                        </a>
                    </li>
                    <li class="mt-2">
                        <button class="bi-consent-link" data-bi-consent-revoke type="button">
                            Gestion des cookies
                        </button>
                    </li>
                </ul>
            </div>

            <!-- colonne 4 : nous contacter -->
            <div class="col-md-4">
                <h5 class="fw-bold mb-3"><?= _l('footer_contact_us', 'Nous contacter'); ?></h5>
                <p class="mb-2">
                    <?= _l('footer_contact_prompt', 'Vous avez des questions ou des suggestions ?'); ?><br>
                    <?= _l('footer_need_advice', 'Besoin de conseils ?'); ?>
                </p>
                <p class="mb-0">
                    <?= _l('footer_write_us', 'Écrivez-nous à :'); ?><br>
                    <a href="mailto:hello@beauteinee.fr" class="text-dark fw-semibold">
                        hello@beauteinee.fr
                    </a>
                </p>
            </div>

        </div><!-- /row principale -->

        <hr class="my-5">

        <!-- réseaux sociaux -->
        <div class="row justify-content-center mb-4">
            <div class="col-auto">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item mx-2">
                        <a href="https://www.facebook.com/profile.php?id=100066485893115" class="text-dark fs-4" aria-label="<?= _l('social_facebook', 'Facebook'); ?>">
                            <span class="flaticon-facebook"></span>
                        </a>
                    </li>
                    <li class="list-inline-item mx-2">
                        <a href="https://www.tiktok.com/@beaute_inee/video/7489019608637213974" class="text-dark fs-4" aria-label="<?= _l('social_tiktok', 'TikTok'); ?>">
                            <span class="flaticon-tik-tok"></span>
                        </a>
                    </li>
                    <li class="list-inline-item mx-2">
                        <a href="https://www.instagram.com/beaute_inee/" class="text-dark fs-4" aria-label="<?= _l('social_instagram', 'Instagram'); ?>">
                            <span class="flaticon-instagram"></span>
                        </a>
                    </li>
                    <li class="list-inline-item mx-2">
                        <a href="https://www.linkedin.com/company/beaute-inee/?viewAsMember=true" class="text-dark fs-4" aria-label="<?= _l('social_linkedin', 'LinkedIn'); ?>">
                            <span class="flaticon-linkedin"></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- copyright -->
        <div class="row">
            <div class="col text-center">
                <p class="mb-0 text-muted small">
                    &copy; <?= date('Y'); ?> Beauté INÉE. <?= _l('footer_all_rights_reserved', 'Tous droits réservés.'); ?><br>
                    <?= _l('footer_designed_by', 'Conception'); ?>
                    <a href="https://tech.kaviar.app/" target="_blank" class="text-decoration-none">
                        KaviAR [Tech]
                    </a>
                </p>
            </div>
        </div>

    </div><!-- /container -->
</footer>

<?php if (isset($js_files) && is_array($js_files)): ?>
    <?php foreach ($js_files as $script): ?>
        <script src="<?= $script; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<?php $this->load->view('modals'); ?>

<!-- LAZY LOAD -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js" async></script>

<!-- COOKIE BANNER -->
<script src="<?= $theme_assets_url; ?>js/cookie-banner.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var whiteLogo = document.querySelector('.desktoplogo#logo-white a.logo');
        var blackLogo = document.querySelector('.desktoplogo#logo-black a.logo');
        if (whiteLogo) whiteLogo.href = "<?= base_url('lp'); ?>";
        if (blackLogo) blackLogo.href = "<?= base_url('lp'); ?>";
    });

    function showStep(branch) {
        document.getElementById('step-1').classList.add('d-none');
        document.getElementById(`step-2-${branch}`).classList.remove('d-none');
    }
</script>

<script>
    $(document).ready(function() {
        if ($.fn.slider) $('.slider').slider({});
    });

    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('load-more-faq');
        if (!btn) return;
        btn.addEventListener('click', function() {
            document.querySelectorAll('#faq-list li[style*="display:none"]').forEach(function(li) {
                li.style.display = '';
            });
            btn.style.display = 'none';
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const showFromHash = (hash) => {
            const trigger = document.querySelector(`[data-bs-target="${hash}"], a[href="${hash}"][data-bs-toggle="tab"]`);
            if (trigger && window.bootstrap?.Tab) new bootstrap.Tab(trigger).show();
        };
        if (location.hash) showFromHash(location.hash);

        document.querySelectorAll('a[data-bs-toggle="tab"],button[data-bs-toggle="tab"]')
            .forEach(el => el.addEventListener('shown.bs.tab', (e) => {
                const target = e.target.getAttribute('data-bs-target');
                if (target) history.replaceState(null, '', target);
            }));

        const items = document.querySelectorAll('[data-animate]');
        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    const el = entry.target;
                    const delay = el.getAttribute('data-animate-delay') || 0;
                    if (entry.isIntersecting) {
                        el.style.animationDelay = `${delay}s`;
                        el.classList.remove('fadeOutDown');
                        el.classList.add('animated','fadeInUp');
                    } else {
                        el.classList.remove('fadeInUp');
                        el.classList.add('animated','fadeOutDown');
                    }
                });
            }, { threshold: 0.15 });
            items.forEach(el => io.observe(el));
        } else {
            items.forEach(el => el.style.opacity = 1);
        }
    });

    (function () {
        if (!window.bootstrap || !bootstrap.Tab) return;
        const cta = document.querySelector('#bi-program .js-go-step2');
        if (!cta) return;
        cta.addEventListener('click', function (e) {
            e.preventDefault();
            const triggerEl = document.querySelector('#step2-tab');
            if (triggerEl) bootstrap.Tab.getOrCreateInstance(triggerEl).show();
            const pane = document.querySelector('#step2');
            if (pane) setTimeout(() => pane.scrollIntoView({ behavior: 'smooth', block: 'start' }), 50);
        });
    })();
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const isMobile = matchMedia('(max-width: 992px)').matches;
        const prefersReduce = matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (isMobile || prefersReduce) {
            document.documentElement.classList.add('no-anim');
            document.querySelectorAll('.wow').forEach(el => {
                el.style.visibility = 'visible';
                el.classList.remove('wow', 'animated', 'fadeIn', 'fadeInUp', 'fadeInLeft', 'fadeInRight');
                el.removeAttribute('data-wow-delay');
                el.removeAttribute('data-wow-duration');
            });
            document.querySelectorAll('[data-animate]').forEach(el => {
                el.removeAttribute('data-animate');
                el.removeAttribute('data-animate-delay');
                el.style.opacity = '';
                el.style.transform = '';
            });
        } else {
            if (window.WOW) new WOW({ mobile: true, live: false }).init();
        }
    });
</script>

</body>
</html>
