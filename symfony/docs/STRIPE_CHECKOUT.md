# Paiement de la carte Beauté INÉE

## État local

- `/commande/carte` : achat de la carte connectée Beauté INÉE, 70 € + 7 € de livraison, total 77 € EUR.
- Stripe Checkout hébergé, via le SDK officiel `stripe/stripe-php`.
- `/stripe/webhook` : confirmation signée. La page de retour ne valide jamais le paiement.
- Le webhook vérifie le mode test/réel, la session, la référence, le montant et la devise avant de créer la carte. Verrou transactionnel sur la commande pour les confirmations concurrentes ; un paiement déjà confirmé ne réinitialise pas le cycle carte.
- Référence en réel : `BI-CARTE-…`. En test : `TEST-BI-CARTE-…`. Fournisseur : CardLab. L’identifiant physique CardLab reste renseigné séparément par l’équipe.
- Les références historiques `DEV-CARD-BI-…` et le fournisseur `fake-cardlab` sont affichés comme des tests. Les valeurs historiques en base ne sont pas réécrites.
- Le simulateur est réservé aux administrateurs et interdit lorsque `APP_ENV=prod` (y compris le staging utilisant cet environnement).
- L’achat crée une fiche cliente, une commande et un paiement ; après paiement, une carte apparaît pour l’équipe. Aucun accès client ni invitation de compte client n’est créé automatiquement par ce nouveau parcours.
- Les programmes à 120/150/240 € n’utilisent pas le paiement de la carte : leur achat reste indisponible.

## Configuration privée

Renseigner dans `.env.local` (ignoré par Git) ou dans les secrets serveur :

```dotenv
STRIPE_MODE=test
STRIPE_SECRET_KEY=
STRIPE_WEBHOOK_SECRET=
STRIPE_RETURN_BASE_URL=https://dev.beauteinee.fr
```

Le mode Stripe est indépendant de `APP_ENV` : staging utilise `STRIPE_MODE=test` même avec Symfony en `prod`, et la production utilise `STRIPE_MODE=live`. Le paiement est bloqué si le préfixe de la clé ne correspond pas au mode ou si le domaine de retour connu ne correspond pas. Les webhooks test/réels sont également vérifiés.

Utiliser la clé secrète du mode test pour la recette et le secret du webhook correspondant. Une clé publique n’est pas nécessaire pour ce Checkout hébergé. Ne jamais mettre les secrets dans les templates, les sources, les commandes shell ou Git.

Enregistrer une destination webhook Stripe de type événements snapshot vers `https://<domaine>/stripe/webhook`, avec :

- `checkout.session.completed`
- `checkout.session.async_payment_succeeded`
- `checkout.session.async_payment_failed`
- `checkout.session.expired`

Le secret de signature de cette destination commence par `whsec_`. Il est distinct de la clé API. Pour un test via Stripe CLI, utiliser le secret fourni par le listener.

La vente reste contrôlée par `FEATURE_CARD_SALES_ENABLED`. Un webhook valide reste traité même si la vente est ensuite désactivée, pour terminer les paiements en cours.

## Déploiement

Le workflow de production attend les secrets GitHub `PRODUCTION_STRIPE_SECRET_KEY` et `PRODUCTION_STRIPE_WEBHOOK_SECRET` et configure le retour sur `https://beauteinee.fr`. Il maintient les ventes désactivées ; l’ouverture doit faire l’objet d’une décision de mise en production. Le staging conserve sa configuration Stripe privée de test dans son `.env.local` serveur et le workflow impose `STRIPE_MODE=test`. Le workflow de production impose `STRIPE_MODE=live`. Chaque environnement utilise le secret `whsec_` de sa propre destination webhook ; ce préfixe seul ne distingue pas test et réel.

Aucun compte Stripe n’a encore été connecté et aucun paiement réel n’a été effectué pendant cette implémentation. Aucun déploiement ni migration de données existantes n’a été exécuté.

## Recette avant ouverture

Tester en mode Stripe test : paiement accepté, paiement refusé/annulé, retour navigateur avant webhook, répétition du webhook, mauvais montant/devise/mode, puis commande visible par une opératrice et parcours physique CardLab. Vérifier le pays/adresse de livraison dans la commande. Les tests automatisés utilisent des clients simulés et une base SQLite temporaire ; ils ne remplacent pas cette recette.

Le reçu Stripe doit être configuré dans le compte Stripe. Une confirmation Beauté INÉE (référence, articles, total payé, livraison) est enregistrée dans `email_logs` lors de la transaction de paiement, puis envoyée après son commit. Un doublon de webhook ne renvoie pas une confirmation déjà envoyée. Une erreur SMTP conserve le paiement et la carte, journalise un échec sans détail SMTP sensible, et renvoie HTTP 503 pour permettre une nouvelle tentative Stripe. Un ancien paiement confirmé sans journal de confirmation peut être rattrapé en renvoyant son événement de paiement réussi depuis Stripe après déploiement.

Un envoi interrompu brutalement reste au statut `sending` : vérifier sa remise effective avant toute reprise manuelle. SMTP ne fournit pas de garantie absolue d’envoi exactement une fois (notamment si une connexion coupe après acceptation du message). Il n’y a pas encore de synchronisation des remboursements/litiges.

L’audit Composer du 29 septembre 2026 signale aussi une vulnérabilité préexistante d’EasyAdmin (`CVE-2026-81892`, corrigée à partir de 5.5.1 dans la branche 5). À corriger avant la production ; Stripe n’est pas la dépendance signalée.

Sources : https://docs.stripe.com/api/checkout/sessions/create et https://docs.stripe.com/webhooks
