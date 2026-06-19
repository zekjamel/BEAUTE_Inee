<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<section
        id="coming-soon"
        class="d-flex flex-column justify-content-center align-items-center text-center"
        style="
                position: relative;
                min-height: 100vh;
                background: url('<?= $theme_assets_url; ?>images/coming-soon-bg.avif') center/cover no-repeat;
                "
>
    <!-- Overlay noir -->
    <div
            class="overlay"
            style="
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 1;
        "
    ></div>

    <div
            class="container position-relative px-4"
            style="z-index: 2; max-width: 600px;"
    >
        <!-- Titre -->
        <h1 class="h1-title text-white mb-4">
            Bientôt de retour,<br>encore plus beau.
        </h1>

        <!-- Texte -->
        <p class="text-white mb-5 fs-lg">
            Nous peaufinons notre site pour vous offrir une expérience plus fluide et personnalisée.
            Restez connecté.e.
        </p>

        <!-- Boutons -->
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a
                    href="https://site.booxi.eu/beauteinee?lang=fre_fr&book=265739"
                    class="btn btn--gold hover--tra-gold btn-lg"
            >
                Nos services restent disponibles
            </a>
            <a
                    href="https://beauteinee.agence-xr.io/lp/register"
                    class="btn btn--tra-white hover--white btn-lg"
            >
                Pré-inscription
            </a>
        </div>
    </div>
</section>

<!-- Vos JS dynamiques + fin de page -->
<?php if (isset($js_files)) : ?>
    <?php foreach ($js_files as $js) : ?>
        <script src="<?= $js; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</div><!-- /#page -->
</body>
</html>
