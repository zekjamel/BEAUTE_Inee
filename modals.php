<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- MODAL-Diag -->
<div id="modal-diagnostic" class="modal fade" tabindex="-1" aria-labelledby="modalDiagnosticLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content position-relative">

            <!-- BOUTON FERMER -->
            <button type="button" class="modal-close color--black ico-20" data-bs-dismiss="modal" aria-label="Close">
                <span class="flaticon-246"></span>
            </button>

            <!-- CONTENU MODAL -->
            <div class="modal-body">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-10 py-5 text-center">

                            <!-- ÉTAPE 1 -->
                            <div id="step-1">
                                <h3 id="modalDiagnosticLabel" class="mb-3">
                                    Avez-vous déjà réalisé un <span class="fw-bold">diagnostic Beauté</span> ?
                                </h3>
                                <p class="mb-4">Votre parcours commence ici. Sélectionnez simplement :</p>
                                <div class="d-flex justify-content-center flex-wrap gap-3">
                                    <button type="button" class="btn btn--tra-black hover--gold" onclick="showStep('non')">
                                        Non, pas encore
                                    </button>
                                    <button type="button" class="btn btn--tra-gold hover--gold" onclick="showStep('oui')">
                                        Oui, j’ai déjà
                                    </button>
                                </div>
                            </div>

                            <!-- ÉTAPE 2A : pas de diagnostic -->
                            <div id="step-2-non" class="d-none">
                                <h4 class="mt-4 mb-3">Pas encore de diagnostic ?</h4>
                                <p class="mb-4">Pour accéder à nos services, vous devez disposer d’un compte Beautéinee.</p>
                                <div class="d-flex justify-content-center flex-wrap gap-3 mb-3">
                                    <?php /*<a href="<?= base_url('authentication/login') ?>" class="btn btn--tra-gold hover--gold">
                                        Je me connecte
                                    </a>*/ ?>
                                    <a href="/login" class="btn btn--tra-black hover--gold">
                                        Je crée un compte
                                    </a>
                                </div>
                                <p class="text-muted"><small>Une fois connecté·e, réalisez votre diagnostic en quelques clics.</small></p>
                            </div>

                            <!-- ÉTAPE 2B : déjà fait -->
                            <div id="step-2-oui" class="d-none">
                                <h4 class="mt-4 mb-3">Bravo pour votre diagnostic !</h4>
                                <p class="mb-4">Découvrez dès maintenant notre sélection de produits :</p>
                                <a href="https://beaute-inee.myshopify.com" class="btn btn--tra-gold hover--gold mb-3">
                                    Accéder à la boutique
                                </a>
                                <p class="text-muted"><small>Profitez de nos offres exclusives et de la livraison rapide.</small></p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!-- /modal-body -->

        </div>
    </div>
</div>

<!-- Modal-Carte -->
<div id="modal-carte-identite" class="modal fade" tabindex="-1" aria-labelledby="modalCarteIdentiteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content position-relative rounded-4 shadow-lg overflow-hidden">

            <!-- BOUTON FERMER -->
            <button type="button" class="btn-close position-absolute top-0 end-0 m-4" data-bs-dismiss="modal" aria-label="Close"></button>

            <!-- CONTENU MODAL -->
            <div class="modal-body p-5">
                <section class="content-section">
                    <div>
                        <div class="row align-items-center g-5">

                            <!-- IMAGE : Carte Beauté -->
                            <div class="col-lg-6 text-center text-lg-end">
                                <img class="img-fluid rounded-3 shadow" style="max-width: 90%;" src="<?= $theme_assets_url; ?>images/carte_bi.png" alt="Carte Beauté INÉE">
                            </div>

                            <!-- TEXTE : Avant-première -->
                            <div class="col-lg-6">
                                <div class="bg-light p-4 p-lg-5 rounded-3 border">
                                  <a class="btn btn--gold hover--tra-gold text-white px-5 py-3 mb-3 rounded-pill fw-bold" href="/dev/card-checkout">
                                    J’achète ma carte connectée
                                  </a>
                                    <h3 class="h4 mb-3">Et si votre peau avait enfin sa carte d'identité ?</h3>
                                    <p class="mb-3">
                                        Plus qu’un accessoire, la carte connectée <strong>Beauté INÉE</strong> est la carte vitale de votre peau. Un passeport beauté intelligent qui vous guide au quotidien et vous oriente vers les soins, produits et diagnostics adaptés à vos besoins.
                                    </p>
                                    <ul class="list-unstyled mt-3">
                                        <li class="d-flex align-items-start mb-3">
                                            <img src="<?= $theme_assets_url; ?>images/2.svg" class="me-2" alt="" width="50" height="50">
                                            <span>Plus qu’un accessoire, la carte connectée <strong>Beauté INÉE</strong> est la carte vitale de votre peau</span>
                                        </li>
                                        <li class="d-flex align-items-start mb-3">
                                            <img src="<?= $theme_assets_url; ?>images/3.svg" class="me-2" alt="" width="50" height="50">
                                            <span>Un passeport beauté intelligent qui vous guide au quotidien et vous oriente vers les soins, produits et diagnostics adaptés à vos besoins.</span>
                                        </li>
                                        <li class="d-flex align-items-start mb-3">
                                            <img src="<?= $theme_assets_url; ?>images/1.svg" class="me-2" alt="" width="50" height="50">
                                            <span>Ne perdez plus de temps à chercher les bons produits</span>
                                        </li>
                                        <li class="d-flex align-items-start mb-3">
                                            <img src="<?= $theme_assets_url; ?>images/6.svg" class="me-2" alt="" width="50" height="50">
                                            <span>N’achetez plus à l’aveugle ce qui ne convient pas à votre peau</span>
                                        </li>
                                        <li class="d-flex align-items-start mb-3">
                                            <img src="<?= $theme_assets_url; ?>images/4.svg" class="me-2" alt="" width="50" height="50">
                                            <span>Votre peau change, votre routine aussi : la carte s’adapte à chaque étape.</span>
                                        </li>
                                        <li class="d-flex align-items-start">
                                            <img src="<?= $theme_assets_url; ?>images/5.svg" class="me-2" alt="" width="50" height="50">
                                            <span>Ne recommencez plus à zéro à chaque rendez-vous : vos données vous suivent, en toute sécurité</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>
                </section>
            </div>
            <!-- /modal-body -->

        </div>
    </div>
</div>


<!-- /////////////////////////////// -->
<!-- MODAL L'EFFET APAISANT         -->
<!-- /////////////////////////////// -->
<div id="modal-apaisant" class="modal fade" tabindex="-1"
     aria-labelledby="modalApaisantLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg overflow-hidden">

            <!-- CLOSE BUTTON -->
            <button type="button"
                    class="btn-close position-absolute top-0 end-0 m-4"
                    data-bs-dismiss="modal" aria-label="Close"></button>

            <!-- BODY -->
            <div class="modal-body d-flex align-items-center justify-content">
                <div class="container p-7">

                    <!-- TITLE -->
                    <h3 id="modalApaisantLabel"
                        class="h4 mb-4 fw-bold color--primary">
                        L’effet <span class="color--gold">Apaisant</span>
                    </h3>

                    <!-- SUBTITLE -->
                    <h5 class="h5-lg mb-4 text-muted">
                        <u>Effet Apaisant</u> — <em>Partie Éducation</em>
                    </h5>

                    <!-- SECTION HEADER -->
                    <p class="mb-3">
                        <strong>Qu’est‑ce que l’inflammation cutanée ?</strong>
                    </p>

                    <!-- PARAGRAPHS -->
                    <p class="mb-4">
                        L’inflammation est une réaction <span class="fw-bold">naturelle</span> de défense de la peau.
                        Elle se manifeste souvent par des <span class="text--gold">rougeurs</span>, des sensations de
                        <em>chaleur</em>, de picotement ou de tiraillement. Cela peut être ponctuel (réaction à un
                        produit, au froid, au stress) ou chronique (peau réactive ou sujette à des dermatoses
                        comme la <span class="fw-bold color--secondary">rosacée</span>).
                    </p>

                    <p class="mb-4">
                        Les peaux <u>sensibles</u> ont généralement une barrière cutanée fragilisée : elles laissent
                        passer trop facilement les agents irritants (pollution, parfums, calcaire) et perdent plus
                        rapidement leur <span class="fw-bold">hydratation</span>. Rougeurs, échauffements, irritations…
                        ce n’est pas « dans la tête ».
                    </p>

                    <p class="mb-4">
                        Grâce à notre <span class="fw-bold">diagnostic connecté</span>, nous détectons les
                        <span class="color--gold">signes d’inflammation</span> (visibles ou non), les zones
                        sensibilisées et les facteurs aggravants : pollution, stress, hormones, routines trop agressives…
                    </p>

                    <!-- LIST -->
                    <p class="mb-2 fw-semibold">Nos recommandations vous guideront vers :</p>
                    <ul class="list-unstyled ms-4 mb-5 text-start">
                        <li>– soins hydratants <span class="fw-bold">haute tolérance</span></li>
                        <li>– textures <span class="fst-italic">légères</span> et protectrices</li>
                        <li>– actifs calmants (<span class="color--secondary">niacinamide</span>,
                            <span class="color--secondary">allantoïne</span>, <span class="color--secondary">panthénol</span>)</li>
                        <li>– gestes et fréquences adaptés à la fragilité de votre peau</li>
                    </ul>

                    <!-- FOOTER -->
                    <p class="mb-1">
                        Avec <strong>Beauté INÉE</strong>, reprenez le pouvoir sur votre peau, en toute sérénité.
                    </p>
                    <p class="fw-bold mb-0">Laura, Experte Beauté INÉE</p>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- /////////////////////////////// -->
<!-- MODAL L'EFFET RÉPARATEUR       -->
<!-- /////////////////////////////// -->
<div id="modal-reparateur" class="modal fade" tabindex="-1"
     aria-labelledby="modalReparateurLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg overflow-hidden">

            <button type="button" class="btn-close position-absolute top-0 end-0 m-4"
                    data-bs-dismiss="modal" aria-label="Close"></button>

            <div class="modal-body d-flex align-items-center justify-content">
                <div class="container p-7">

                    <h3 id="modalReparateurLabel"
                        class="h4 mb-4 fw-bold color--primary">
                        L’effet <span class="color--gold">Réparateur</span>
                    </h3>
                    <h5 class="h5-lg mb-4 text-muted">
                        <u>Effet Réparateur</u> — <em>Partie Éducation</em>
                    </h5>

                    <p class="mb-4"><strong>Pourquoi la peau s’abîme-t-elle ?</strong></p>
                    <p class="mb-4">
                        Notre peau se répare naturellement, mais certains soins agressifs, conditions extrêmes ou
                        stress peuvent ralentir ce processus. Une barrière altérée devient perméable, causant
                        <span class="text--gold">sécheresses</span>, tiraillements et micro‑inflammations.
                    </p>

                    <p class="mb-4">
                        Pour les peaux marquées, fragilisées ou abîmées : cicatrices, rugosités, démangeaisons…
                        notre diagnostic cible les zones en souffrance et propose un protocole doux pour relancer
                        la <span class="fw-bold">régénération</span> cellulaire.
                    </p>

                    <p class="mb-2 fw-semibold">Axes de recommandation :</p>
                    <ul class="list-unstyled ms-4 mb-5 text-start">
                        <li>– soins riches en <u>céramides</u>, acides gras, madecassoside</li>
                        <li>– textures enveloppantes (baumes, crèmes)</li>
                        <li>– protocole ciblé : nuit, post‑soin, changement de saison</li>
                        <li>– suivi personnalisé pour mesurer l’amélioration</li>
                    </ul>

                    <p class="mb-1">
                        Réparer, ce n’est pas camoufler : c’est offrir à votre peau les conditions pour se reconstruire.
                    </p>
                    <p class="fw-bold mb-0">Laura, Experte Beauté INÉE</p>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- /////////////////////////////// -->
<!-- MODAL L'EFFET ÉQUILIBRANT      -->
<!-- /////////////////////////////// -->
<div id="modal-equilibrant" class="modal fade" tabindex="-1"
     aria-labelledby="modalEquilibrantLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg overflow-hidden">

            <button type="button" class="btn-close position-absolute top-0 end-0 m-4"
                    data-bs-dismiss="modal" aria-label="Close"></button>

            <div class="modal-body d-flex align-items-center justify-content">
                <div class="container p-7">

                    <h3 id="modalEquilibrantLabel"
                        class="h4 mb-4 fw-bold color--primary">
                        L’effet <span class="color--gold">Équilibrant</span>
                    </h3>
                    <h5 class="h5-lg mb-4 text-muted">
                        <u>Effet Équilibrant</u> — <em>Partie Éducation</em>
                    </h5>

                    <p class="mb-4"><strong>C’est quoi une peau déséquilibrée ?</strong></p>
                    <p class="mb-4">
                        Une production de sébum trop forte ou trop faible, des imperfections récurrentes…
                        ce sont des signes de déséquilibre, pas une punition. Le diagnostic Beauté INÉE identifie
                        précisément les zones concernées et les causes pour **rééquilibrer** votre épiderme.
                    </p>

                    <p class="mb-2 fw-semibold">Notre approche :</p>
                    <ul class="list-unstyled ms-4 mb-5 text-start">
                        <li>– soins purifiants <span class="fst-italic">doux</span>, sans agression</li>
                        <li>– actifs régulateurs (zinc, acide azélaïque, AHA/BHA doux)</li>
                        <li>– routine progressive pour stabiliser la peau</li>
                        <li>– sélection non comédogène, testée & adaptée</li>
                    </ul>

                    <p class="mb-1">
                        L’<span class="fw-bold">équilibre</span> est un chemin, pas une norme. Avec Beauté INÉE, il commence par la connaissance de votre peau.
                    </p>
                    <p class="fw-bold mb-0">Laura, Experte Beauté INÉE</p>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- /////////////////////////////// -->
<!-- MODAL L'EFFET UNIFIANT         -->
<!-- /////////////////////////////// -->
<div id="modal-unifiant" class="modal fade" tabindex="-1"
     aria-labelledby="modalUnifiantLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg overflow-hidden">

            <button type="button" class="btn-close position-absolute top-0 end-0 m-4"
                    data-bs-dismiss="modal" aria-label="Close"></button>

            <div class="modal-body d-flex align-items-center justify-content">
                <div class="container p-7">

                    <h3 id="modalUnifiantLabel"
                        class="h4 mb-4 fw-bold color--primary">
                        L’effet <span class="color--gold">Unifiant</span>
                    </h3>
                    <h5 class="h5-lg mb-4 text-muted">
                        <u>Effet Unifiant</u> — <em>Partie Éducation</em>
                    </h5>

                    <p class="mb-4"><strong>Pourquoi le teint devient-il irrégulier ?</strong></p>
                    <p class="mb-4">
                        Taches brunes, cicatrices d’acné, cernes pigmentés ou perte d’éclat…
                        souvent liées à un déséquilibre de la mélanine ou des micro‑inflammations répétées.
                    </p>

                    <p class="mb-2 fw-semibold">Votre programme peut inclure :</p>
                    <ul class="list-unstyled ms-4 mb-5 text-start">
                        <li>– actifs ciblés : vitamine C, acide tranexamique, niacinamide</li>
                        <li>– soins anti‑UV à large spectre</li>
                        <li>– routine exfoliante douce & régulière</li>
                        <li>– suivi pigmentaire dans le temps</li>
                    </ul>

                    <p class="mb-1">
                        Unifier, ce n’est pas effacer votre histoire, c’est révéler votre éclat naturel.
                    </p>
                    <p class="fw-bold mb-0">Laura, Experte Beauté INÉE</p>

                </div>
            </div>
        </div>
    </div>
</div>


<!-- MODAL LES SŒURS KÉBÉ -->
<div id="modal-histoire" class="modal fade" tabindex="-1" aria-labelledby="modalSoeursKebeLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg overflow-hidden">

            <!-- BOUTON FERMETURE -->
            <button type="button" class="btn-close position-absolute top-3 end-3" data-bs-dismiss="modal" aria-label="Fermer"></button>

            <!-- CORPS -->
            <div class="modal-body py-5 px-4">
                <div class="row align-items-center gx-5">

                    <!-- IMAGE GRANDE -->
                    <div class="col-lg-6">
                        <img src="<?= $theme_assets_url; ?>images/team/soeurs.jpg"
                             alt="Les sœurs Kébé"
                             class="img-fluid rounded-3 shadow-sm">
                    </div>

                    <!-- TEXTE -->
                    <div class="col-lg-6 py-5 px-4">
                        <h2 id="modalSoeursKebeLabel" class="h3 fw-bold mb-4 text-uppercase">
                            Les sœurs Kébé
                        </h2>
                        <p class="mb-4">
                            Beauté INÉE est née de la vision partagée de <span class="fw-bold">trois sœurs</span> aux parcours complémentaires et au même engagement :
                            <span class="fst-italic">réinventer la beauté</span> pour qu’elle reflète la
                            <span class="color--gold">diversité</span> des peaux, des besoins et des usages.
                        </p>
                        <p class="fst-italic color--secondary">
                            “Quand on écoute la peau, toute histoire commence à se révéler...”
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL KHADIDIATOU KÉBÉ -->
<div id="modal-khadi" class="modal fade" tabindex="-1" aria-labelledby="modalKhadidiatouLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg overflow-hidden">

            <!-- CLOSE BUTTON -->
            <button type="button" class="btn-close position-absolute top-3 end-3" data-bs-dismiss="modal" aria-label="Fermer"></button>

            <!-- BODY -->
            <div class="modal-body py-5 px-4">
                <div class="row align-items-center gx-5">

                    <!-- IMAGE -->
                    <div class="col-lg-5">
                        <img src="<?= $theme_assets_url; ?>images/team/khadidiatou-kebe.jpg"
                             alt="Khadidiatou Kébé"
                             class="img-fluid rounded-3 shadow-sm">
                    </div>

                    <!-- TEXT -->
                    <div class="col-lg-7 pe-7">
                        <h2 id="modalKhadidiatouLabel" class="h3 fw-bold mb-3 text-uppercase">
                            Khadidiatou Kébé
                        </h2>
                        <p class="h5 fst-italic mb-4 color--gold">
                            “J’ai longtemps cherché à comprendre ma peau. En grandissant, j’ai vécu avec des problématiques cutanées récurrentes, sans jamais trouver de solution adaptée.”
                        </p>
                        <p class="mb-3">
                            Cette quête m’a menée à vouloir <u>aller plus loin</u>, à ne plus simplement observer, mais à <strong>décoder</strong> la peau.<br>
                            En tant que <span class="fw-semibold">data analyst</span>, mon métier consiste à lire entre les lignes, à donner du sens à l’invisible. J’ai naturellement voulu appliquer cette approche à la peau, pour qu’elle soit enfin entendue.
                        </p>
                        <p>
                            Grâce à la <span class="fw-bold">technologie</span>, aux données et à l’écoute, j’ai voulu créer un outil capable de parler la langue de chaque peau — pour qu’elle cesse d’être un mystère, et devienne une source de confiance.
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL FATOUMATA KÉBÉ -->
<div id="modal-fatou" class="modal fade" tabindex="-1" aria-labelledby="modalFatoumataLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg overflow-hidden">

            <button type="button" class="btn-close position-absolute top-3 end-3" data-bs-dismiss="modal" aria-label="Fermer"></button>

            <div class="modal-body py-5 px-4">
                <div class="row align-items-center gx-5">

                    <div class="col-lg-5">
                        <img src="<?= $theme_assets_url; ?>images/team/fatoumata-kebe.jpg"
                             alt="Fatoumata Kébé"
                             class="img-fluid rounded-3 shadow-sm">
                    </div>

                    <div class="col-lg-7 pe-7">
                        <h2 id="modalFatoumataLabel" class="h3 fw-bold mb-3 text-uppercase">
                            Fatoumata Kébé
                        </h2>
                        <p class="h5 fst-italic mb-4 color--gold">
                            “Ma vocation, c’est le soin. J’ai commencé comme esthéticienne, puis j’ai évolué dans le retail jusqu’à devenir manager pour des marques du groupe LVMH.”
                        </p>
                        <p class="mb-3">
                            Ce métier, je l’ai appris sur le terrain, au contact des clientes et des équipes, confrontée à des problématiques bien réelles. J’ai moi‑même subi du harcèlement scolaire à cause de ma peau, alors je sais à quel point un regard ou un conseil mal formulé peut marquer.
                        </p>
                        <p>
                            Avec Beauté INÉE, je voulais réconcilier l’humain et le soin. Mettre le retail, la technologie et l’expertise au service d’une beauté plus juste, plus inclusive, plus rassurante. Où l’on prend enfin le temps d’écouter, de diagnostiquer et de proposer ce qui est réellement adapté.
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL AWA KÉBÉ -->
<div id="modal-awa" class="modal fade" tabindex="-1" aria-labelledby="modalAwaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg overflow-hidden">

            <button type="button" class="btn-close position-absolute top-3 end-3" data-bs-dismiss="modal" aria-label="Fermer"></button>

            <div class="modal-body py-5 px-4">
                <div class="row align-items-center gx-5">

                    <div class="col-lg-5">
                        <img src="<?= $theme_assets_url; ?>images/team/awa-kebe.jpg"
                             alt="Awa Kébé"
                             class="img-fluid rounded-3 shadow-sm">
                    </div>

                    <div class="col-lg-7 pe-7">
                        <h2 id="modalAwaLabel" class="h3 fw-bold mb-3 text-uppercase">
                            Awa Kébé
                        </h2>
                        <p class="h5 fst-italic mb-4 color--gold">
                            “J’ai toujours été attentive à la peau, pour moi et pour les autres.”
                        </p>
                        <p class="mb-3">
                            Quand je suis devenue maman, j’ai été confrontée à toutes sortes de problématiques de peau. Et dans mes fonctions auprès de la jeunesse, j’ai vu défiler des adolescents en plein désarroi face à leur peau, à leur image, à leur estime de soi.
                        </p>
                        <p>
                            Pour moi, Beauté INÉE est née de ce besoin d’accompagner les jeunes, les familles, les peaux oubliées, sans jugement. Je voulais un environnement qui respecte la complexité de chacun, avec douceur, intelligence et écoute. Avec Beauté INÉE, je me suis engagée à porter cette voix‑là : celle des peaux qu’on ignore, des jeunes qu’on oublie, des parents qui tâtonnent. Parce que la peau, elle parle. Et parce qu’en l’écoutant mieux, on peut réconcilier des générations entières avec leur reflet.
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
