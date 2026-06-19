/**
 * Cookie Consent – Beauté INÉE
 * Conforme RGPD / Recommandations CNIL (2022)
 *
 * Catégories :
 *   necessary   – Toujours actifs (mémorisation du consentement)
 *   preferences – Préférences d'affichage
 *   analytics   – Mesure d'audience anonyme
 *   marketing   – Intégrations tierces (Booxi, Typeform, Maps)
 */

(function () {
    'use strict';

    /* ── Config ────────────────────────────────────────────── */
    var BI_CONSENT = {
        cookieName : 'bi_cookie_consent',
        version    : '1.0',
        expireDays : 395,          // ~13 mois (recommandation CNIL)
        logEndpoint: './php/consent-log.php',

        categories: {
            necessary: {
                label      : 'Cookies nécessaires',
                description: 'Ces cookies sont indispensables au fonctionnement du site (mémorisation de votre consentement). Ils ne peuvent pas être désactivés.',
                required   : true,
                default    : true
            },
            preferences: {
                label      : 'Cookies de préférences',
                description: 'Permettent de mémoriser vos préférences d\'affichage (langue, mise en page) pour améliorer votre navigation.',
                required   : false,
                default    : false
            },
            analytics: {
                label      : 'Mesure d\'audience',
                description: 'Nous aident à comprendre comment le site est utilisé (pages vues, parcours) afin d\'améliorer nos contenus. Les données sont anonymisées et ne permettent pas de vous identifier.',
                required   : false,
                default    : false
            },
            marketing: {
                label      : 'Cookies fonctionnels tiers',
                description: 'Nécessaires au fonctionnement de services intégrés : prise de rendez-vous (Booxi), questionnaire pré-diagnostic (Typeform), carte interactive (Google Maps). Leur désactivation empêche l\'utilisation de ces fonctionnalités.',
                required   : false,
                default    : false
            }
        }
    };

    /* ── Helpers ────────────────────────────────────────────── */

    function setCookie(name, value, days) {
        var d = new Date();
        d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = name + '=' + encodeURIComponent(value)
            + ';expires=' + d.toUTCString()
            + ';path=/;SameSite=Lax;Secure';
    }

    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    function readConsent() {
        var raw = getCookie(BI_CONSENT.cookieName);
        if (!raw) return null;
        try {
            var data = JSON.parse(raw);
            /* Invalide si version différente */
            if (data.version !== BI_CONSENT.version) return null;
            return data;
        } catch (e) { return null; }
    }

    function saveConsent(accepted, action) {
        var data = {
            version   : BI_CONSENT.version,
            timestamp : new Date().toISOString(),
            action    : action,      // 'accept_all' | 'reject_all' | 'custom'
            categories: accepted
        };
        setCookie(BI_CONSENT.cookieName, JSON.stringify(data), BI_CONSENT.expireDays);
        sendLog(data);
    }

    function sendLog(data) {
        /* Audit côté serveur (feu-et-oublie) */
        try {
            var fd = new FormData();
            fd.append('version',    data.version);
            fd.append('timestamp',  data.timestamp);
            fd.append('action',     data.action);
            fd.append('categories', JSON.stringify(data.categories));
            navigator.sendBeacon(BI_CONSENT.logEndpoint, fd);
        } catch (e) { /* silencieux */ }
    }

    function hasConsent(category) {
        var c = readConsent();
        return c && c.categories && c.categories[category] === true;
    }

    /* ── DOM helpers ────────────────────────────────────────── */

    function $(id) { return document.getElementById(id); }

    function hide(el) { el.classList.add('bi-hidden'); el.setAttribute('aria-hidden', 'true'); }
    function show(el) { el.classList.remove('bi-hidden'); el.removeAttribute('aria-hidden'); }

    /* ── Banner ─────────────────────────────────────────────── */

    function showBanner() {
        var banner = $('bi-cookie-banner');
        if (banner) show(banner);
    }

    function hideBanner() {
        var banner = $('bi-cookie-banner');
        if (banner) hide(banner);
    }

    function acceptAll() {
        var accepted = {};
        Object.keys(BI_CONSENT.categories).forEach(function (k) { accepted[k] = true; });
        saveConsent(accepted, 'accept_all');
        hideBanner();
        hideModal();
        applyConsent(accepted);
    }

    function rejectAll() {
        var accepted = {};
        Object.keys(BI_CONSENT.categories).forEach(function (k) {
            accepted[k] = BI_CONSENT.categories[k].required;
        });
        saveConsent(accepted, 'reject_all');
        hideBanner();
        hideModal();
        applyConsent(accepted);
    }

    /* ── Modal ──────────────────────────────────────────────── */

    function showModal() {
        var modal = $('bi-cookie-modal');
        if (!modal) return;
        /* Pré-remplir les toggles avec l'état actuel */
        var current = readConsent();
        Object.keys(BI_CONSENT.categories).forEach(function (k) {
            var toggle = document.querySelector('#bi-toggle-' + k);
            if (!toggle) return;
            if (current) {
                toggle.checked = current.categories[k] === true;
            } else {
                toggle.checked = BI_CONSENT.categories[k].default;
            }
        });
        show(modal);
        /* Focus trap : premier bouton focusable */
        var firstFocusable = modal.querySelector('button, [tabindex]:not([tabindex="-1"])');
        if (firstFocusable) firstFocusable.focus();
    }

    function hideModal() {
        var modal = $('bi-cookie-modal');
        if (modal) hide(modal);
    }

    function savePreferences() {
        var accepted = {};
        Object.keys(BI_CONSENT.categories).forEach(function (k) {
            var toggle = document.querySelector('#bi-toggle-' + k);
            accepted[k] = toggle ? toggle.checked : BI_CONSENT.categories[k].required;
        });
        saveConsent(accepted, 'custom');
        hideBanner();
        hideModal();
        applyConsent(accepted);
    }

    /* ── Apply consent (activer / désactiver services) ──────── */

    function applyConsent(accepted) {
        /* Déclenche un événement custom que d'autres scripts peuvent écouter */
        var event = new CustomEvent('bi:consentUpdated', { detail: accepted });
        document.dispatchEvent(event);
        /* Exemple : si analytics accepté, on pourrait charger GA ici */
    }

    /* ── Build HTML ─────────────────────────────────────────── */

    function buildBanner() {
        var div = document.createElement('div');
        div.id = 'bi-cookie-banner';
        div.setAttribute('role', 'dialog');
        div.setAttribute('aria-label', 'Gestion des cookies');
        div.setAttribute('aria-live', 'polite');
        div.classList.add('bi-hidden'); /* caché par défaut, showBanner() le rend visible */
        div.innerHTML = '<div class="bi-cb-inner">'
            + '<div class="bi-cb-text">'
            +   '<h4>Ce site utilise des cookies</h4>'
            +   '<p>Certains cookies sont nécessaires au fonctionnement du site ; d\'autres nous aident à l\'améliorer. '
            +   'Vous pouvez accepter ou refuser leur dépôt à tout moment. '
            +   '<a href="./legacy.php#cookies">En savoir plus</a></p>'
            + '</div>'
            + '<div class="bi-cb-actions">'
            +   '<button class="bi-cb-btn bi-cb-btn-reject"  id="bi-cb-reject" type="button">Tout refuser</button>'
            +   '<button class="bi-cb-btn bi-cb-btn-prefs"   id="bi-cb-prefs"  type="button">Personnaliser</button>'
            +   '<button class="bi-cb-btn bi-cb-btn-accept"  id="bi-cb-accept" type="button">Tout accepter</button>'
            + '</div>'
            + '</div>';
        document.body.appendChild(div);
    }

    function buildModal() {
        var div = document.createElement('div');
        div.id = 'bi-cookie-modal';
        div.setAttribute('role', 'dialog');
        div.setAttribute('aria-modal', 'true');
        div.setAttribute('aria-label', 'Préférences de cookies');
        div.classList.add('bi-hidden'); /* caché par défaut */

        var rows = '';
        Object.keys(BI_CONSENT.categories).forEach(function (k) {
            var cat = BI_CONSENT.categories[k];
            rows += '<div class="bi-category" id="bi-cat-' + k + '">'
                + '<div class="bi-category-header">'
                +   '<div class="bi-category-info">'
                +     '<span class="bi-category-name">' + cat.label + '</span>'
                +     '<button type="button" class="bi-category-toggle-btn" data-cat="' + k + '" aria-expanded="false">Détails ▾</button>'
                +   '</div>'
                +   '<label class="bi-toggle" aria-label="' + cat.label + '">'
                +     '<input type="checkbox" id="bi-toggle-' + k + '"'
                +       (cat.required ? ' checked disabled' : '') + '>'
                +     '<span class="bi-toggle-slider"></span>'
                +   '</label>'
                + '</div>'
                + '<p class="bi-category-desc">' + cat.description + '</p>'
                + '</div>';
        });

        div.innerHTML = '<div class="bi-modal-overlay" id="bi-modal-overlay"></div>'
            + '<div class="bi-modal-box">'
            +   '<button class="bi-modal-close" id="bi-modal-close" type="button" aria-label="Fermer">&#x2715;</button>'
            +   '<h2>Préférences de cookies</h2>'
            +   '<p class="bi-modal-sub">Beauté INÉE s\'engage pour votre vie privée. '
            +   'Gérez vos préférences ci-dessous. '
            +   '<a href="./legacy.php#cookies">Politique cookies</a></p>'
            +   rows
            +   '<div class="bi-modal-footer">'
            +     '<button class="bi-cb-btn bi-cb-btn-reject"  id="bi-modal-reject"  type="button">Tout refuser</button>'
            +     '<button class="bi-cb-btn bi-cb-btn-accept"  id="bi-modal-accept"  type="button">Tout accepter</button>'
            +     '<button class="bi-cb-btn bi-cb-btn-prefs"   id="bi-modal-save"    type="button">Enregistrer</button>'
            +   '</div>'
            + '</div>';
        document.body.appendChild(div);
    }

    /* ── Event listeners ────────────────────────────────────── */

    function bindEvents() {
        /* Banner */
        var btnAccept = $('bi-cb-accept');
        var btnReject = $('bi-cb-reject');
        var btnPrefs  = $('bi-cb-prefs');
        if (btnAccept) btnAccept.addEventListener('click', acceptAll);
        if (btnReject) btnReject.addEventListener('click', rejectAll);
        if (btnPrefs)  btnPrefs.addEventListener('click',  function () { showModal(); });

        /* Modal */
        var modalClose  = $('bi-modal-close');
        var modalAccept = $('bi-modal-accept');
        var modalReject = $('bi-modal-reject');
        var modalSave   = $('bi-modal-save');
        if (modalClose)  modalClose.addEventListener('click',  hideModal);
        if (modalAccept) modalAccept.addEventListener('click', acceptAll);
        if (modalReject) modalReject.addEventListener('click', rejectAll);
        if (modalSave)   modalSave.addEventListener('click',   savePreferences);
        /* Fermer en cliquant en dehors de la modal-box (l'overlay est pointer-events:none) */
        var modalWrap = $('bi-cookie-modal');
        if (modalWrap) {
            modalWrap.addEventListener('click', function (e) {
                if (e.target === modalWrap) hideModal();
            });
        }

        /* Expand/collapse category details */
        document.querySelectorAll('.bi-category-toggle-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var catEl = $('bi-cat-' + btn.dataset.cat);
                if (!catEl) return;
                var isOpen = catEl.classList.toggle('bi-open');
                btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                btn.textContent = isOpen ? 'Masquer ▴' : 'Détails ▾';
            });
        });

        /* Keyboard: Échap ferme la modal */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') hideModal();
        });

        /* Focus trap dans la modal */
        var modal = $('bi-cookie-modal');
        if (modal) {
            modal.addEventListener('keydown', function (e) {
                if (e.key !== 'Tab') return;
                var focusable = modal.querySelectorAll('button:not([disabled]), input:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])');
                var first = focusable[0];
                var last  = focusable[focusable.length - 1];
                if (e.shiftKey) {
                    if (document.activeElement === first) { e.preventDefault(); last.focus(); }
                } else {
                    if (document.activeElement === last)  { e.preventDefault(); first.focus(); }
                }
            });
        }
    }

    /* ── Lien de révocation (footer) ────────────────────────── */

    function bindRevokeLink() {
        document.querySelectorAll('[data-bi-consent-revoke]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                showModal();
            });
        });
    }

    /* ── Init ───────────────────────────────────────────────── */

    function init() {
        /* Construire les éléments UI */
        buildBanner();
        buildModal();
        bindEvents();
        bindRevokeLink();

        var consent = readConsent();
        if (!consent) {
            /* Première visite : afficher la bannière */
            showBanner();
        } else {
            /* Consentement déjà donné : appliquer silencieusement */
            applyConsent(consent.categories);
        }
    }

    /* Lancement après chargement du DOM */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    /* Exposer publiquement pour permettre l'ouverture depuis n'importe où */
    window.BiCookieConsent = {
        open      : showModal,
        acceptAll : acceptAll,
        rejectAll : rejectAll,
        has       : hasConsent
    };

})();
