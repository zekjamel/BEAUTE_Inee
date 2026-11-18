# Contexte persistant — Beauté Inée

Dernière vérification : 18 novembre 2026, environnement local et staging.

## Architecture et composants importants

- Le dépôt contient le site PHP historique à la racine et sa migration progressive vers Symfony dans `symfony/`.
- Le backend utilise Symfony 7.4, Twig, Doctrine ORM/Migrations, EasyAdmin, Security, Mailer et HttpClient.
- Les principaux domaines déjà présents sont les comptes clients, diagnostics, commandes, paiements, cartes connectées, administration et suivi des e-mails.
- Le site public Symfony conserve encore un pont avec le site historique et une copie des ressources publiques sous `symfony/public/`.
- L'intégration CardLab/Quardlock est portée par les entités `ConnectedCard` et `QuardlockAuditLog`, les services Quardlock, `AdminController` et les vues d'administration des cartes.

## État actuel du projet

Au 18 novembre 2026 :

- `origin/staging` (`4e93a25`) est déployée sur https://dev.beauteinee.fr. Un push sur `staging` déclenche le déploiement (composer install, migrations, cache:clear).
- `main` a reçu une fusion de `origin/staging` (`a9b4a27`) : le code applicatif de `main` et de `staging` est identique. `main` contient en plus le workflow de production et `symfony/public/.htaccess` (`62cb338` est le dernier commit).
- La production (beauteinee.fr) se déploie uniquement à la main (`workflow_dispatch`, saisir `DEPLOY`). Elle n'a pas été redéployée avec ces changements.
- Base locale (MySQL sur le port 8889) : toutes les migrations sont appliquées et le schéma est synchronisé avec le mapping.
- 61 tests PHPUnit passent (bases SQLite temporaires, faux transport mail et Stripe).
- Les exports SQL et les copies d'environnement (`exports/`, `symfony/conf.env.local`, `symfony/env copie`) ont été retirés du projet et mis à la corbeille de macOS. Ne jamais les recréer dans le dépôt.

## Intégration CardLab / Quardlock

Le parcours implémenté est le suivant :

1. associer l'identifiant physique CardLab à une carte Beauté Inée ;
2. préparer la carte pour l'initialisation en boutique ;
3. définir le PIN puis enregistrer l'empreinte directement sur la carte ;
4. démarrer une session d'enrôlement Symfony de 15 minutes ;
5. créer une session Client API Quardlock côté serveur ;
6. exécuter le parcours FIDO2/WebAuthn via un relais same-origin Symfony ;
7. enregistrer l'identité dans Quardlock ;
8. vérifier le numéro de jeton avec `IsTokenLocked`, puis passer la carte à `initialized` ;
9. tester l'authentification avant l'activation et la remise au client.

Le PIN et le gabarit biométrique restent sur la carte. Symfony ne stocke pas de donnée biométrique.

## Travail récemment effectué

- `4bd8428` (11 juillet 2026) : intégration initiale CardLab/Quardlock, modèle de données, migrations, bibliothèque serveur officielle, audit et interface d'administration.
- `6246b64` (13 juillet 2026) : parcours FIDO2/WebAuthn et relais Symfony pour l'API Client Quardlock.
- `7495095` (16 juillet 2026) : finalisation du parcours d'enrôlement et clarification de l'ordre PIN, empreinte, FIDO, test et activation.
- `b53feeb` (23 août 2026) : client HTTP Quardlock dédié et configuration d'une autorité de certification avec validation TLS active.
- `26ea20c` à `3c44679` (29–30 septembre 2026, branche `staging`) : réenrôlement des cartes, identification client par carte en boutique, accès équipe (admin/opératrice), paiement Stripe de la carte, catalogue produits administrable, e-mails de confirmation de commande.
- `44fc660` et `4e93a25` (18 novembre 2026) : mot de passe oublié (`/mot-de-passe-oublie`) et dépendance `symfony/rate-limiter`.
- `62cb338` (18 novembre 2026) : secrets Stripe et flags désactivés dans le workflow de production, avec vérifications après déploiement.

## Décisions techniques prises

- La clé `QUARDLOCK_API_KEY` reste exclusivement côté serveur.
- Le navigateur reçoit un handle opaque lié à la session Symfony, pas le véritable jeton Client API.
- Le nonce d'enrôlement est aléatoire, stocké haché en base et expire après 15 minutes.
- Les appels navigateur vers l'API Client transitent par un relais Symfony same-origin pour éviter CORS et protéger le jeton.
- L'inscription FIDO ne suffit pas à activer une carte : le jeton est d'abord vérifié, la carte passe à `initialized`, puis l'activation reste une action distincte.
- Les événements d'intégration sont audités sans clé API, jeton de session ni donnée biométrique.
- Le transport serveur utilise un client Symfony dédié `quardlock.client`, avec `verify_peer` et `verify_host` activés. Le chemin CA dépend de `QUARDLOCK_CA_FILE`.
- Les sessions de diagnostic doivent être révoquées immédiatement après le test.
- `FeatureFlags` (`FEATURE_CUSTOMER_LOGIN_ENABLED`, `FEATURE_CARD_SALES_ENABLED`) : désactivés par défaut et en production, activés sur staging. Le personnel peut toujours se connecter.
- Mot de passe oublié : jeton aléatoire stocké haché (`password_reset_tokens`), valable 1 heure et à usage unique. La réponse est identique que le compte existe ou non. Les demandes sont limitées à 10 par IP par quart d'heure et 3 par e-mail par heure. Un compte désactivé reste bloqué, un compte jamais activé reçoit l'e-mail d'activation, et les clientes suivent le flag de connexion client.
- Stripe : `StripeGateway::isConfigured()` exige que le préfixe de la clé corresponde à `STRIPE_MODE`, avec `test` sur dev.beauteinee.fr et `live` sur beauteinee.fr.

## Ce qui fonctionne ou a été validé

- Syntaxe PHP valide pour les principaux contrôleurs et services Quardlock.
- Les 20 fichiers YAML de configuration sont valides.
- Le mapping Doctrine est valide hors comparaison avec la base.
- Les routes d'administration CardLab/Quardlock sont enregistrées.
- La commande `app:quardlock:check-connection` a réussi le 26 août 2026 et a révoqué sa session de test.
- Le code couvre l'association CardLab, l'audit, l'émission d'une session courte, le relais de `GetPrecomputedTokenSerialNumber`, `GetChallenge` et `RegisterToken`, puis la validation finale du jeton.

## Blocages et points à confirmer

- Les secrets GitHub `PRODUCTION_STRIPE_SECRET_KEY` et `PRODUCTION_STRIPE_WEBHOOK_SECRET` sont à créer avant d'ouvrir la vente de cartes en production. Sans eux, le déploiement passe, mais Stripe reste non configuré.
- Le résultat du dernier déploiement staging n'a pas été consulté dans GitHub Actions (`gh` non authentifié). Le site répond avec les nouvelles pages.
- Mot de passe oublié validé en local (e-mail réel reçu, mot de passe changé), pas encore sur staging.
- La commande `app:quardlock:check-enrollment-preparation` a échoué à plusieurs reprises sur `InitializeApiClientSession`, sans statut HTTP, alors que le test de connexion simple réussissait. La cause reste à diagnostiquer ; ne pas conclure à un problème TLS sans preuve.
- Le parcours complet avec navigateur, carte CardLab réelle et miniLector n'a pas été revalidé.
- `QUARDLOCK_CA_FILE` est déclaré dans `.env`, mais la clé n'était pas présente dans le `.env.local` inspecté par nom de variables. Le test serveur réussit actuellement avec la chaîne de confiance disponible sur la machine.
- `QuardlockClientApiRelay` utilise encore le client HTTP générique, alors que les appels serveur utilisent `quardlock.client`. Vérifier si le relais doit partager la configuration CA dédiée avant toute modification.
- L'état de staging et de production, leurs variables d'environnement, leurs migrations et leur document root n'ont pas été vérifiés.

## Prochaines étapes

1. Tester le mot de passe oublié sur dev.beauteinee.fr (migration et envoi d'e-mail réel).
2. Fusionner `main` dans `staging` pour que staging récupère le workflow de production et `.htaccess`.
3. Reproduire séparément l'échec de préparation Quardlock et identifier l'exception de transport réelle sans exposer de secret.
4. Vérifier la configuration CA attendue pour l'API Server et le relais Client API dans chaque environnement.
5. Tester le parcours complet en boutique avec une carte CardLab et le miniLector : PIN, empreinte, FIDO2, validation Quardlock, test d'authentification, activation et remise.
6. Vérifier les journaux d'audit et les transitions de la carte après un enrôlement réel.
7. Créer les secrets Stripe de production avant d'activer `FEATURE_CARD_SALES_ENABLED` en production.
8. Vérifier explicitement staging et production avant tout déploiement ; ne pas supposer qu'un push déclenche le déploiement.

## Fichiers importants du chantier

- `symfony/config/packages/framework.yaml` : client HTTP Quardlock et paramètres TLS.
- `symfony/.env` : noms et valeurs par défaut non sensibles des variables Quardlock.
- `symfony/src/Integration/Quardlock/OfficialQuardlockServerApi.php` : adaptation de la bibliothèque serveur officielle au transport Symfony.
- `symfony/src/Service/QuardlockServerApiClient.php` : opérations serveur, sessions Client API et validation du jeton.
- `symfony/src/Service/QuardlockClientApiRelay.php` : relais same-origin de l'API Client.
- `symfony/src/Service/QuardlockEnrollmentService.php` : règles et état de l'enrôlement.
- `symfony/src/Service/QuardlockAuditService.php` : audit non sensible.
- `symfony/src/Service/CardLifecycleService.php` : transitions du cycle de vie des cartes.
- `symfony/src/Controller/AdminController.php` : routes et orchestration du parcours administratif.
- `symfony/src/Entity/ConnectedCard.php` et `symfony/src/Entity/QuardlockAuditLog.php` : modèle persistant.
- `symfony/templates/admin/cards/enrollment.html.twig` : procédure boutique et orchestration WebAuthn côté navigateur.
- `symfony/templates/admin/cards/show.html.twig` et `symfony/templates/admin/quardlock/index.html.twig` : suivi opérationnel et audit.
- `symfony/src/Command/CheckQuardlockConnectionCommand.php` et `CheckQuardlockEnrollmentPreparationCommand.php` : diagnostics séparés.
- `symfony/migrations/Version20260711150000.php` à `Version20260711155000.php` : schéma Quardlock/CardLab et alignement des index.
- `symfony/lib/Quardlock/ServerApi/` et `symfony/public/vendor/quardlock/` : bibliothèques officielles intégrées.
