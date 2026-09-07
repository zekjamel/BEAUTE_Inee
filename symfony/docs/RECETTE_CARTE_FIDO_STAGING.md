# Recette staging — enrôlement et connexion par carte

Cette recette complète les contrôles automatisés pour les interactions qui exigent une carte CardLab, un lecteur et une empreinte réelle. Elle doit être exécutée sur staging avec des comptes et des cartes de test.

## Préconditions

- `CARD_LOGIN_MOBILE_NFC_ENABLED=false` sur staging ;
- carte de test associée à une cliente active ;
- miniLector compatible et navigateur à jour ;
- accès aux journaux applicatifs et aux outils réseau du navigateur ;
- aucune donnée réelle de PIN ne doit être notée dans cette recette.

## Enrôlement en boutique

1. Ouvrir la fiche de la carte et vérifier l’association à la bonne cliente.
2. Ouvrir l’écran d’enrôlement.
3. Remettre le clavier ou le terminal à la testeuse.
4. Laisser la testeuse choisir seule son PIN et enregistrer son empreinte.
5. Vérifier que le bouton FIDO2 reste désactivé tant que les trois confirmations ne sont pas cochées.
6. Cocher les trois confirmations, lancer l’enrôlement FIDO2 et sélectionner la carte externe, jamais Touch ID.
7. Vérifier que Quardlock confirme l’identité FIDO découvrable.
8. Ouvrir la page de connexion Beauté INÉE proposée par l’écran, puis effectuer le test final avec la carte.

Résultat attendu : la carte est enregistrée dans Quardlock et Beauté INÉE sans qu’aucune valeur de PIN ou donnée biométrique soit saisie dans l’application.

## Matrice de connexion

| Scénario | Résultat attendu |
| --- | --- |
| Bonne empreinte | Connexion autorisée et redirection vers l’espace client |
| Une empreinte incorrecte | Accès refusé par la carte, aucun message affirmant la méthode utilisée |
| Deux empreintes incorrectes | Accès refusé par la carte, aucun message affirmant la méthode utilisée |
| Troisième empreinte incorrecte | Le terminal ou le navigateur propose le PIN personnel de la carte |
| Bon PIN personnel | Connexion autorisée sans transmission du PIN à Symfony |
| Mauvais PIN | Connexion refusée |
| Email et mot de passe valides | Connexion autorisée sans carte |
| Carte inactive | Connexion refusée |
| Carte liée à un autre compte | Seul le compte réellement associé à la carte peut être ouvert |
| Ré-enrôlement d’une carte existante | Nouvelle identité FIDO validée, cycle de vie existant conservé |
| Mobile avec NFC désactivé | Aucun lancement WebAuthn ; message de validation en cours et orientation vers email/mot de passe |

## Contrôle de non-conservation du PIN

Après la recette :

1. Rechercher les termes `pin` et `code pin` dans les journaux staging. Ne jamais saisir la valeur réelle du PIN dans une commande, un ticket ou un compte rendu.
2. Vérifier les requêtes émises par la page d’enrôlement et la page de connexion : aucune propriété ou valeur de PIN ne doit apparaître.
3. Vérifier le schéma et les données des tables relatives aux utilisateurs, clientes, cartes et journaux Quardlock : aucun champ ni contenu de PIN ne doit exister.
4. Vérifier que les appels WebAuthn conservent `userVerification: 'required'`.

## Pilote

Pendant le pilote, relever séparément : réussite biométrique au premier essai, apparition déclarée du PIN, durée d’enrôlement, oublis de PIN, demandes de support et préférence entre PIN de secours et mot de passe. Quardlock ne précisant pas la méthode de vérification, l’usage réel du PIN doit être recueilli auprès des testeuses sans demander leur PIN.
