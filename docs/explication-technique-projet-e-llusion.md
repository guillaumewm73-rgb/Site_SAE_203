# Explication technique du projet e-llusion

Ce document sert de support pour comprendre et expliquer le site e-llusion a l'oral. Il explique comment la base de donnees a ete creee et peuplee, comment les requetes SQL permettent le CRUD, comment l'interface PHP/HTML/CSS fonctionne, comment JavaScript ameliore l'experience utilisateur, et comment les tests qualite ont ete prevus.

Important : les identifiants, mots de passe de base de donnees et mots de passe mail ne doivent pas etre recopies dans un rapport partage. On explique le principe avec les noms des constantes, mais on ne publie pas les secrets.

## 1. Objectif du site

Le site permet de reserver une visite pour l'exposition e-llusion.

Les visiteurs peuvent :

- consulter la page d'accueil ;
- consulter les pages des salles ;
- remplir un formulaire de reservation ;
- creer un compte avec email ou telephone + mot de passe ;
- retrouver leur reservation ;
- modifier ou supprimer leur reservation.

L'administrateur peut :

- se connecter avec un compte admin ;
- consulter toutes les reservations ;
- filtrer les reservations ;
- modifier une reservation ;
- supprimer une reservation ;
- visualiser les places restantes par salle et par creneau ;
- exporter les inscriptions en CSV.

## 2. Architecture du projet

Le projet est organise simplement :

```text
site-e-llusion-sae203/
├── base.sql
├── config.php
├── connexion.php
├── fonctions.php
├── public/
│   ├── index.php
│   ├── reservation.php
│   ├── confirmation.php
│   ├── page_connexion.php
│   ├── admin.php
│   ├── admin_disponibilites.php
│   ├── contact.php
│   ├── salle-001.php
│   ├── salle-002.php
│   ├── salle-005.php
│   ├── salle-021.php
│   ├── includes/
│   │   ├── header.php
│   │   ├── footer.php
│   │   ├── donnee_salles.php
│   │   └── modele_salles.php
│   └── assets/
│       ├── css/style.css
│       ├── js/home.js
│       ├── js/reservation.js
│       ├── js/admin.js
│       └── js/rooms.js
```

Role des fichiers principaux :

- `base.sql` : script SQL pour creer et remplir la base.
- `config.php` : constantes de connexion a la base.
- `connexion.php` : creation de l'objet PDO.
- `fonctions.php` : toutes les fonctions PHP de lecture, creation, modification, suppression, session et mails.
- `public/index.php` : page d'accueil.
- `public/reservation.php` : formulaire de reservation.
- `public/page_connexion.php` : connexion visiteur ou admin.
- `public/admin.php` : interface administratrice.
- `public/admin_disponibilites.php` : endpoint JSON utilise pour mettre a jour les places disponibles.
- `public/assets/css/style.css` : toute la charte graphique du site.
- `public/assets/js/*.js` : scripts d'interaction cote navigateur.

## 3. Creation et peuplement de la base de donnees

La base de donnees est creee depuis phpMyAdmin avec le fichier `base.sql`.

Le principe est :

1. On ouvre phpMyAdmin.
2. On selectionne la base de donnees du projet.
3. On importe `base.sql`.
4. phpMyAdmin execute les `CREATE TABLE`.
5. Il execute ensuite les `INSERT INTO`.
6. Il ajoute les index, les auto-increments et les cles etrangeres.

### 3.1 Tables de la base

La base contient 8 tables principales :

| Table | Role |
|---|---|
| `admin` | contient les comptes administrateurs |
| `categories_visiteur` | liste les categories de visiteurs |
| `jour` | contient les deux jours de visite |
| `creneaux` | contient les horaires disponibles |
| `salles` | contient les salles de l'exposition |
| `salle_creneaux` | relie les salles et les creneaux, avec une capacite de 12 |
| `visiteurs` | contient les informations des visiteurs |
| `reservation` | contient les reservations faites par les visiteurs |

### 3.2 Lecture ligne par ligne du SQL

Exemple de creation de la table `admin` :

```sql
CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `login` varchar(45) NOT NULL,
  `password_hash` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Explication :

- `CREATE TABLE admin` : cree une table appelee `admin`.
- `id int(11) NOT NULL` : cree une colonne numerique obligatoire.
- `login varchar(45) NOT NULL` : cree une colonne texte de 45 caracteres maximum, obligatoire.
- `password_hash varchar(45) NOT NULL` : stocke le mot de passe admin ou son hash.
- `ENGINE=InnoDB` : utilise le moteur MySQL qui gere les cles etrangeres.
- `DEFAULT CHARSET=utf8mb4` : permet de stocker les accents et caracteres speciaux.

Insertion d'un administrateur :

```sql
INSERT INTO `admin` (`id`, `login`, `password_hash`) VALUES
(0, 'toto_des_fraises', '...');
```

Explication :

- `INSERT INTO admin` : ajoute une ligne dans la table `admin`.
- Les colonnes indiquees sont `id`, `login`, `password_hash`.
- `VALUES` donne les valeurs a inserer.
- Dans le rapport, on ne doit pas afficher le vrai mot de passe.

Creation des categories :

```sql
CREATE TABLE `categories_visiteur` (
  `id` int(11) NOT NULL,
  `libelle` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Explication :

- `id` identifie la categorie.
- `libelle` contient le nom visible : Enseignant, Personnel USMB, Visiteur exterieur, Professionnel / Partenaire.

Peuplement :

```sql
INSERT INTO `categories_visiteur` (`id`, `libelle`) VALUES
(1, 'Enseignant'),
(2, 'Personnel USMB'),
(3, 'Visiteur extérieur'),
(4, 'Professionnel / Partenaire');
```

Chaque ligne correspond a une option du menu deroulant du formulaire.

Creation des creneaux :

```sql
CREATE TABLE `creneaux` (
  `id` int(11) NOT NULL,
  `heure_debut` time NOT NULL,
  `jour_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Explication :

- `id` identifie le creneau.
- `heure_debut time` stocke une heure, par exemple `15:00:00`.
- `jour_id` relie le creneau a la table `jour`.

Pourquoi utiliser le type `time` ?

- Il est fait pour stocker une heure.
- Il evite de stocker les heures comme simple texte.
- Il permet de trier proprement les horaires.

Creation des jours :

```sql
CREATE TABLE `jour` (
  `id` int(11) NOT NULL,
  `date_jour` date NOT NULL,
  `nom_jour` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Explication :

- `date_jour date` stocke la vraie date, par exemple `2026-06-18`.
- `nom_jour` stocke le libelle visible : Jeudi ou Vendredi.

Creation des salles :

```sql
CREATE TABLE `salles` (
  `id` int(11) NOT NULL,
  `numero_salle` varchar(45) DEFAULT NULL,
  `nom` varchar(45) NOT NULL,
  `description` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Explication :

- `id` est l'identifiant technique.
- `numero_salle` est le numero affiche : 001, 002, 005, 021.
- `nom` est le nom de la salle.
- `description` contient ici le TP ou l'emplacement.

Creation de `salle_creneaux` :

```sql
CREATE TABLE `salle_creneaux` (
  `id` int(11) NOT NULL,
  `capacite_max` int(11) NOT NULL,
  `salles_id` int(11) NOT NULL,
  `creneaux_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Explication :

- Cette table est une table de liaison.
- Elle dit : telle salle est disponible sur tel creneau.
- `capacite_max` vaut 12, car chaque salle est limitee a 12 places par creneau.
- `salles_id` pointe vers `salles.id`.
- `creneaux_id` pointe vers `creneaux.id`.

Exemple :

```sql
(1, 12, 1, 1)
```

Cela veut dire :

- ligne id 1 ;
- capacite 12 ;
- salle id 1 ;
- creneau id 1.

Creation des visiteurs :

```sql
CREATE TABLE `visiteurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(45) NOT NULL,
  `prenom` varchar(45) DEFAULT NULL,
  `moyen_comm` varchar(45) NOT NULL,
  `categories_visiteur_id` int(11) NOT NULL,
  `participe_buffet` binary(2) DEFAULT NULL,
  `commanditaire` varchar(45) DEFAULT NULL,
  `flag` binary(2) DEFAULT NULL,
  `mdp` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Explication :

- `nom` et `prenom` identifient le visiteur.
- `moyen_comm` sert d'identifiant de connexion : email ou telephone.
- `categories_visiteur_id` indique la categorie choisie.
- `participe_buffet` indique si le visiteur vient au buffet.
- `mdp` stocke le mot de passe de connexion visiteur.
- `commanditaire` et `flag` sont presents dans la base mais ne sont pas essentiels au fonctionnement principal actuel.

Creation des reservations :

```sql
CREATE TABLE `reservation` (
  `id` int(11) NOT NULL,
  `visiteurs_id` int(11) NOT NULL,
  `salle_creneaux_id` int(11) NOT NULL,
  `nombre_personnes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Explication :

- `id` identifie la reservation.
- `visiteurs_id` relie la reservation a un visiteur.
- `salle_creneaux_id` indique la salle + le jour + l'heure.
- `nombre_personnes` indique combien de personnes sont reservees.

### 3.3 Index, auto-increment et cles etrangeres

Exemple :

```sql
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`id`,`visiteurs_id`,`salle_creneaux_id`),
  ADD KEY `fk_reservation_visiteurs1_idx` (`visiteurs_id`),
  ADD KEY `fk_reservation_salle_creneaux1_idx` (`salle_creneaux_id`);
```

Explication :

- `PRIMARY KEY` definit l'identifiant principal.
- `ADD KEY` ajoute des index pour accelerer les recherches et preparer les relations.

Exemple de cle etrangere :

```sql
ALTER TABLE `reservation`
  ADD CONSTRAINT `fk_reservation_salle_creneaux1`
  FOREIGN KEY (`salle_creneaux_id`) REFERENCES `salle_creneaux` (`id`);
```

Explication :

- `FOREIGN KEY` impose que `reservation.salle_creneaux_id` corresponde a une ligne existante dans `salle_creneaux`.
- Cela evite de creer une reservation sur un creneau qui n'existe pas.

## 4. Requetes SQL pour les fonctionnalites CRUD

CRUD signifie :

- Create : creer ;
- Read : lire ;
- Update : modifier ;
- Delete : supprimer.

### 4.1 Create : creer un visiteur

Dans `createVisiteur()` :

```sql
INSERT INTO visiteurs (nom, prenom, moyen_comm, categories_visiteur_id, participe_buffet, mdp)
VALUES (:nom, :prenom, :moyen_comm, :categorie_id, :participe_buffet, :mdp)
```

Explication :

- `INSERT INTO visiteurs` ajoute un visiteur.
- Les `:nom`, `:prenom`, etc. sont des parametres PDO.
- Les parametres evitent d'inserer directement du texte utilisateur dans la requete.
- C'est plus propre et plus securise contre les injections SQL.

### 4.2 Create : creer une reservation

Dans `createReservation()` :

```sql
INSERT INTO reservation (visiteurs_id, salle_creneaux_id, nombre_personnes)
VALUES (:visiteurs_id, :salle_creneaux_id, :nombre_personnes)
```

Explication :

- On cree une reservation apres avoir cree le visiteur.
- `visiteurs_id` indique a qui appartient la reservation.
- `salle_creneaux_id` indique le creneau choisi.
- `nombre_personnes` occupe un nombre de places sur les 12 disponibles.

### 4.3 Read : lire les salles

Dans `getSalles()` :

```sql
SELECT * FROM salles ORDER BY numero_salle ASC
```

Explication :

- `SELECT *` recupere toutes les colonnes.
- `FROM salles` lit la table des salles.
- `ORDER BY numero_salle ASC` trie dans l'ordre 001, 002, 005, 021.

### 4.4 Read : lire les creneaux

Dans `getCreneaux()` :

```sql
SELECT c.id, c.heure_debut, j.date_jour, j.nom_jour
FROM creneaux c
JOIN jour j ON c.jour_id = j.id
ORDER BY j.date_jour ASC, c.heure_debut ASC
```

Explication :

- `creneaux c` donne un alias `c` a la table `creneaux`.
- `jour j` donne un alias `j` a la table `jour`.
- `JOIN` relie un creneau a son jour.
- On trie d'abord par date puis par heure.

### 4.5 Read : calculer les places restantes

Dans `getPlacesRestantes()` :

```sql
SELECT capacite_max FROM salle_creneaux WHERE id = :id
```

Cette requete recupere la capacite maximale du creneau.

Puis :

```sql
SELECT COALESCE(SUM(nombre_personnes), 0) AS nb
FROM reservation
WHERE salle_creneaux_id = :id
```

Explication :

- `SUM(nombre_personnes)` additionne toutes les places deja reservees.
- `COALESCE(..., 0)` retourne 0 s'il n'y a aucune reservation.
- PHP calcule ensuite `capacite_max - nb`.

### 4.6 Read : afficher les reservations admin

Dans `getAdminReservations()` :

```sql
SELECT
    r.id AS reservation_id,
    r.visiteurs_id,
    r.salle_creneaux_id,
    r.nombre_personnes,
    v.nom,
    v.prenom,
    v.moyen_comm,
    cv.libelle AS categorie,
    s.numero_salle,
    s.nom AS nom_salle,
    c.heure_debut,
    j.nom_jour,
    j.date_jour
FROM reservation r
JOIN visiteurs v ON r.visiteurs_id = v.id
JOIN categories_visiteur cv ON v.categories_visiteur_id = cv.id
JOIN salle_creneaux sc ON r.salle_creneaux_id = sc.id
JOIN salles s ON sc.salles_id = s.id
JOIN creneaux c ON sc.creneaux_id = c.id
JOIN jour j ON c.jour_id = j.id
```

Explication :

- On part de `reservation`.
- On joint `visiteurs` pour connaitre le nom et le contact.
- On joint `categories_visiteur` pour afficher la categorie.
- On joint `salle_creneaux` pour connaitre le couple salle/creneau.
- On joint `salles`, `creneaux`, `jour` pour obtenir le libelle complet.

### 4.7 Read : filtrer les reservations

Dans `getAdminReservations()`, si l'admin saisit une recherche :

```sql
WHERE
    v.nom LIKE :q_nom
    OR v.prenom LIKE :q_prenom
    OR v.moyen_comm LIKE :q_contact
    OR s.numero_salle LIKE :q_salle
    OR cv.libelle LIKE :q_categorie
```

Explication :

- `LIKE` cherche une partie de texte.
- Exemple : si on tape `005`, la recherche peut trouver la salle 005.
- Les `%` ajoutes en PHP permettent de chercher le texte n'importe ou dans la valeur.

### 4.8 Update : modifier une reservation

Dans `updateAdminReservation()`, on modifie d'abord le visiteur :

```sql
UPDATE visiteurs
SET
    nom = :nom,
    prenom = :prenom,
    moyen_comm = :moyen_comm,
    categories_visiteur_id = :categorie_id,
    participe_buffet = :participe_buffet
WHERE id = :visiteur_id
```

Puis on modifie la reservation :

```sql
UPDATE reservation
SET
    salle_creneaux_id = :salle_creneaux_id,
    nombre_personnes = :nombre_personnes
WHERE id = :reservation_id AND visiteurs_id = :visiteur_id
```

Explication :

- La premiere requete met a jour les infos du visiteur.
- La deuxieme change le creneau, la salle ou le nombre de places.
- `AND visiteurs_id = :visiteur_id` evite de modifier une reservation qui n'appartient pas au bon visiteur.
- Le tout est fait dans une transaction.

### 4.9 Delete : supprimer une reservation

Dans `deleteAdminReservation()` :

```sql
DELETE FROM reservation WHERE id = :id
```

Explication :

- Supprime la reservation choisie.

Ensuite :

```sql
SELECT COUNT(*) AS total FROM reservation WHERE visiteurs_id = :visiteur_id
```

Explication :

- Verifie si le visiteur a encore d'autres reservations.

Si le visiteur n'a plus aucune reservation :

```sql
DELETE FROM visiteurs WHERE id = :id
```

Explication :

- On supprime aussi le compte visiteur pour ne pas garder un compte vide.

## 5. Connexion PHP/PDO

### 5.1 `config.php`

Le fichier contient des constantes :

```php
define('NOM_BD', '...');
define('SERVEUR_BD', '...');
define('LOGIN_BD', '...');
define('PASSE_BD', '...');
```

Explication :

- `define()` cree une constante.
- `NOM_BD` contient le nom de la base.
- `SERVEUR_BD` contient le serveur MySQL.
- `LOGIN_BD` contient l'utilisateur MySQL.
- `PASSE_BD` contient le mot de passe MySQL.

Ce fichier permet de changer d'environnement facilement : local, MAMP, XAMPP, OVH ou MMI Agence.

### 5.2 `connexion.php`

Structure :

```php
require('config.php');
```

Explication :

- Charge les constantes de connexion.

```php
$conn = new PDO('mysql:host=' . SERVEUR_BD . ';dbname=' . NOM_BD, LOGIN_BD, PASSE_BD);
```

Explication :

- Cree une connexion PDO.
- `mysql:host=...` indique le serveur.
- `dbname=...` indique la base.
- `LOGIN_BD` et `PASSE_BD` servent a s'authentifier.
- `$conn` est ensuite utilise dans tout le site pour faire les requetes.

```php
catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
```

Explication :

- Si la connexion echoue, le site affiche une erreur.
- Pendant le developpement, c'est utile pour comprendre.
- En production, on eviterait d'afficher le detail exact au public.

## 6. Fichier `fonctions.php`

`fonctions.php` centralise la logique metier du site.

### 6.1 Pourquoi centraliser les fonctions ?

Cela evite de recopier les memes requetes dans plusieurs pages.

Exemple :

- `reservation.php` a besoin de creer une reservation.
- `admin.php` a besoin de lire et modifier une reservation.
- `page_connexion.php` a besoin de retrouver les reservations d'un visiteur.

Toutes ces pages utilisent donc les fonctions de `fonctions.php`.

### 6.2 Fonction `getSalles()`

```php
function getSalles(PDO $conn): array
```

Explication :

- Cree une fonction qui prend une connexion PDO.
- `: array` indique que la fonction retourne un tableau.

```php
$req = $conn->prepare('SELECT * FROM salles ORDER BY numero_salle ASC');
```

Explication :

- Prepare la requete SQL.
- On trie les salles dans l'ordre numerique affiche.

```php
$req->execute();
return $req->fetchAll();
```

Explication :

- Execute la requete.
- Retourne toutes les lignes sous forme de tableau.

### 6.3 Fonction `getPlacesRestantes()`

Cette fonction fait le calcul de jauge.

Etapes :

1. Elle recupere la capacite maximale dans `salle_creneaux`.
2. Elle additionne les personnes deja reservees dans `reservation`.
3. Elle retourne `capacite - reservations`.
4. Elle utilise `max(0, ...)` pour ne jamais retourner un nombre negatif.

A l'oral :

> Cette fonction est essentielle parce qu'elle permet d'eviter de depasser les 12 places par salle et par creneau.

### 6.4 Fonction `identifierAlreadyUsed()`

Cette fonction evite les doublons d'identifiant.

Elle verifie :

- si l'identifiant existe deja dans la table `admin` ;
- si l'identifiant existe deja dans la table `visiteurs`.

Pourquoi verifier les deux tables ?

- Un visiteur ne doit pas pouvoir prendre le meme identifiant qu'un admin.
- Deux visiteurs ne doivent pas avoir le meme email ou telephone.

### 6.5 Fonctions de session

`startUserSession()` :

- demarre `session_start()` seulement si aucune session n'est active.

`connectAdminSession()` :

- connecte un admin ;
- met `$_SESSION['auth_role'] = 'admin'` ;
- stocke l'id admin ;
- supprime les donnees visiteur de la session.

`connectVisitorSession()` :

- connecte un visiteur ;
- met `$_SESSION['auth_role'] = 'visiteur'` ;
- stocke l'id visiteur, son nom et son contact ;
- supprime les donnees admin de la session.

`requireAdminSession()` :

- verifie que l'utilisateur est admin ;
- sinon redirige vers la page de connexion avec `access=admin`.

### 6.6 Fonctions de reservation

`createVisiteur()` :

- verifie que l'identifiant n'est pas deja utilise ;
- insere le visiteur ;
- retourne son nouvel id.

`createReservation()` :

- insere une ligne dans `reservation`.

`getReservationDetailsByVisitorId()` :

- recupere toutes les reservations d'un visiteur connecte.

`getReservationDetailsForVisitor()` :

- recupere une reservation precise seulement si elle appartient au visiteur connecte.

`updateAdminReservation()` :

- modifie le visiteur et la reservation ;
- utilise une transaction.

`deleteVisitorReservation()` :

- supprime une reservation seulement si elle appartient au visiteur.

`deleteAdminReservation()` :

- supprime une reservation en admin ;
- supprime aussi le visiteur s'il n'a plus de reservation.

### 6.7 Transactions

Exemple :

```php
$conn->beginTransaction();
```

Explication :

- Demarre une transaction.
- Une transaction permet de faire plusieurs requetes comme un seul bloc.

```php
$conn->commit();
```

Explication :

- Valide toutes les requetes.

```php
$conn->rollBack();
```

Explication :

- Annule tout si une erreur arrive.

A l'oral :

> On utilise une transaction quand plusieurs requetes doivent reussir ensemble. Par exemple, modifier un visiteur et sa reservation doit etre coherent : si une requete echoue, on annule tout.

### 6.8 Fonctions mail

Le site utilise `mail()` de PHP.

`sendConfirmationEmail()` :

- appelle `sendReservationNotificationEmail()` avec le type `created`.

`sendReservationNotificationEmail()` :

- verifie que le contact est une adresse email ;
- prepare le sujet ;
- construit le corps HTML ;
- envoie le mail avec `mail()`.

Le site peut envoyer :

- confirmation de creation ;
- confirmation de modification ;
- confirmation de suppression.

## 7. Page `reservation.php`

Cette page a deux roles :

- creer une nouvelle reservation ;
- modifier une reservation existante.

### 7.1 Chargement des donnees

La page recupere :

- les categories ;
- les salles ;
- les creneaux ;
- les disponibilites.

Elle construit un tableau `$registrationData`, ensuite transmis au JavaScript dans :

```php
<script id="registration-data" type="application/json">
```

Cela permet au JavaScript de connaitre :

- les jours ;
- les heures ;
- les salles ;
- les places restantes.

### 7.2 Verification du formulaire

Quand l'utilisateur envoie le formulaire, PHP verifie :

- prenom ;
- nom ;
- email ou telephone ;
- mot de passe ;
- categorie ;
- creneau ;
- nombre de personnes ;
- disponibilite restante.

Important :

> Meme si JavaScript affiche deja les places restantes, PHP refait la verification cote serveur. C'est indispensable pour eviter les erreurs ou les manipulations cote navigateur.

### 7.3 Creation

En creation :

1. On cree le visiteur.
2. On cree une ou plusieurs reservations.
3. On envoie un email si possible.
4. On stocke les derniers ids dans `$_SESSION`.
5. On redirige vers `confirmation.php`.

### 7.4 Modification

En modification :

1. On verifie que le visiteur est connecte.
2. On verifie que la reservation lui appartient.
3. On charge les anciennes valeurs.
4. On modifie la reservation.
5. On redirige vers `page_connexion.php?updated=...`.

## 8. Page `page_connexion.php`

Cette page gere deux types de comptes :

- admin ;
- visiteur.

### 8.1 Connexion admin

PHP cherche d'abord :

```php
$admin = getAdminByLogin($conn, $enteredContact);
```

Si l'identifiant correspond a un admin et que le mot de passe correspond :

```php
connectAdminSession($admin);
header('Location: admin.php');
exit;
```

Explication :

- On cree la session admin.
- On redirige vers l'admin.
- `exit` arrete le script apres la redirection.

### 8.2 Connexion visiteur

Si ce n'est pas un admin, PHP essaye :

```php
$connectedVisitor = getVisitorByCredentials($conn, $enteredContact, $enteredPassword);
```

Si le visiteur existe :

- il est connecte ;
- ses reservations sont recuperees ;
- elles sont affichees.

### 8.3 Suppression visiteur

Si le visiteur clique sur supprimer :

- PHP verifie qu'il est connecte ;
- verifie que la reservation lui appartient ;
- supprime la reservation ;
- envoie un mail si possible ;
- redirige vers `page_connexion.php?deleted=...`.

## 9. Page `admin.php`

Cette page est protegee par :

```php
requireAdminSession();
```

Si l'utilisateur n'est pas admin, il est redirige vers la connexion.

### 9.1 Fonctionnalites admin

La page permet :

- de filtrer les reservations ;
- d'editer une reservation ;
- de supprimer une reservation ;
- d'exporter en CSV ;
- de visualiser les disponibilites.

### 9.2 Export CSV

La fonction `exportAdminReservationsCsv()` :

- definit les headers HTTP pour telecharger un fichier ;
- ouvre `php://output` ;
- ecrit les colonnes avec `fputcsv()` ;
- ecrit chaque reservation ;
- termine le script avec `exit`.

### 9.3 Tableau de disponibilite

La page construit une matrice :

- lignes = salles ;
- colonnes = heures ;
- cellules = places restantes.

Exemple affiche :

```text
Salle 001 | 15:00 | 15:30 | 16:00
          | 10/12 | 8/12  | 7/12
```

## 10. Endpoint `admin_disponibilites.php`

Cette page ne renvoie pas du HTML, mais du JSON.

Elle sert au JavaScript admin.

Etapes :

1. Verifie que l'utilisateur est admin.
2. Recupere les disponibilites.
3. Filtre par jour si besoin.
4. Cree un tableau `cells`.
5. Definit le type de reponse :

```php
header('Content-Type: application/json; charset=UTF-8');
```

6. Renvoie :

```php
echo json_encode([...]);
```

A l'oral :

> Cette page sert d'API interne. Elle permet au JavaScript de mettre a jour le tableau admin sans recharger toute la page.

## 11. Header et footer

### 11.1 `header.php`

Le header est inclus sur plusieurs pages.

Il gere :

- le titre de page ;
- le lien vers le CSS ;
- les scripts supplementaires ;
- la navigation ;
- l'affichage du bouton connexion/deconnexion selon la session.

Ligne importante :

```php
$cssVersion = filemtime(__DIR__ . '/../assets/css/style.css');
```

Explication :

- Recupere la date de modification du fichier CSS.
- Ajoute cette valeur dans l'URL du CSS.
- Cela force le navigateur a recharger le CSS quand il change.

### 11.2 Fonction `e()`

```php
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
```

Explication :

- Cette fonction securise l'affichage HTML.
- Elle evite qu'un texte utilisateur soit interprete comme du code HTML.
- C'est une protection contre les failles XSS.

## 12. JavaScript ligne par ligne

### 12.1 `home.js`

Code :

```js
const slides = document.querySelectorAll('.carousel-slide');
```

Explication :

- Recupere toutes les slides du carrousel.
- `querySelectorAll` retourne une liste d'elements HTML.

```js
const buttons = document.querySelectorAll('[data-slide-button]');
```

Explication :

- Recupere les boutons de navigation du carrousel.
- Les boutons sont repérés avec l'attribut `data-slide-button`.

```js
let currentSlide = 0;
```

Explication :

- Stocke l'index de la slide actuellement visible.
- On commence a la slide 0, donc la premiere.

```js
function showSlide(index) {
```

Explication :

- Cree une fonction pour afficher une slide precise.
- `index` est le numero de la slide a afficher.

```js
slides[currentSlide].classList.remove('is-visible');
buttons[currentSlide].classList.remove('is-active');
```

Explication :

- Enleve la classe visible sur l'ancienne slide.
- Enleve l'etat actif sur l'ancien bouton.

```js
currentSlide = index;
```

Explication :

- Met a jour la slide courante.

```js
slides[currentSlide].classList.add('is-visible');
buttons[currentSlide].classList.add('is-active');
```

Explication :

- Affiche la nouvelle slide.
- Active le bouton correspondant.

```js
buttons.forEach((button) => {
```

Explication :

- Parcourt tous les boutons du carrousel.

```js
button.addEventListener('click', () => {
```

Explication :

- Ajoute un evenement au clic sur chaque bouton.

```js
const index = Number(button.dataset.slideButton);
```

Explication :

- Recupere le numero de slide stocke dans l'attribut HTML `data-slide-button`.
- `Number()` transforme le texte en nombre.

```js
showSlide(index);
```

Explication :

- Affiche la slide demandee.

```js
setInterval(() => {
```

Explication :

- Lance une action automatiquement toutes les X millisecondes.

```js
const nextSlide = (currentSlide + 1) % slides.length;
```

Explication :

- Calcule la slide suivante.
- Le modulo `%` permet de revenir a la premiere slide apres la derniere.

```js
showSlide(nextSlide);
```

Explication :

- Affiche la slide suivante.

```js
}, 4500);
```

Explication :

- Le changement automatique se fait toutes les 4500 ms, donc 4,5 secondes.

### 12.2 `reservation.js`

Ce script gere le formulaire de reservation.

```js
const registrationDataElement = document.getElementById('registration-data');
```

- Recupere la balise qui contient les donnees JSON generees par PHP.

```js
const registrationData = JSON.parse(registrationDataElement.textContent);
```

- Transforme le texte JSON en objet JavaScript utilisable.

```js
const form = document.querySelector('[data-registration-form]');
```

- Recupere le formulaire de reservation.

```js
const slotList = document.querySelector('[data-slot-list]');
```

- Recupere la zone qui contient les creneaux ajoutes.

```js
const addSlotButton = document.querySelector('[data-add-slot]');
```

- Recupere le bouton "Ajouter un creneau".

```js
const feedback = document.querySelector('[data-form-feedback]');
```

- Recupere la zone de message sous le formulaire.

```js
const submitButton = document.querySelector('[data-submit-registration]');
```

- Recupere le bouton de validation.

```js
const passwordInput = document.querySelector('[data-password-input]');
const passwordToggle = document.querySelector('[data-password-toggle]');
```

- Recupere le champ mot de passe et le bouton oeil.

Fonction `fillTimeOptions()` :

- recupere les heures disponibles pour un jour ;
- vide l'ancien select ;
- recree les options ;
- garde l'heure actuelle si elle existe encore, sinon met la premiere.

Fonction `updateSlotNames()` :

- met a jour les noms des champs ;
- exemple : `slots[0][day]`, `slots[0][time]`, `slots[0][room]` ;
- cela permet a PHP de recevoir un tableau de creneaux.

Fonction `updateSlot()` :

- recupere le jour, l'heure, la salle, le nombre de personnes ;
- construit une cle comme `jeudi|15:00|001` ;
- recupere les places restantes ;
- calcule les places apres selection ;
- met a jour le message de jauge ;
- ajoute des classes CSS si le creneau est faible ou plein.

Ligne importante :

```js
const remainingPlaces = registrationData.availability[key] ?? 12;
```

Explication :

- Cherche la disponibilite du creneau.
- Si elle n'existe pas, utilise 12 par defaut.

Ligne importante :

```js
capacity.classList.toggle('is-full', placesAfterSelection < 0);
```

Explication :

- Ajoute la classe `is-full` si l'utilisateur demande trop de places.

Fonction `updateAllSlots()` :

- parcourt tous les creneaux affiches ;
- met a jour leur nom et leur jauge.

Evenement `change` :

- quand on change jour, heure, salle ou nombre de personnes ;
- le script recalcule toutes les jauges.

Evenement `click` sur supprimer :

- si on clique sur "Supprimer" ;
- le creneau est retire ;
- les autres creneaux sont renumerotes.

Evenement `click` sur ajouter :

- clone le premier creneau ;
- remet des valeurs par defaut ;
- ajoute le nouveau creneau dans la page ;
- recalcule les jauges.

Bouton oeil :

```js
const isVisible = passwordInput.type === 'text';
passwordInput.type = isVisible ? 'password' : 'text';
```

Explication :

- Si le champ est visible, on le remasque.
- S'il est masque, on l'affiche.

Validation avant envoi :

- `form.checkValidity()` utilise les regles HTML : required, minlength, etc.
- si un champ est invalide, le formulaire n'est pas envoye.
- si un creneau depasse les places disponibles, le formulaire n'est pas envoye.

Important :

> JavaScript ameliore l'interface, mais PHP reste responsable de la verification finale.

### 12.3 `admin.js`

Ce script gere l'interface admin.

```js
const rows = Array.from(document.querySelectorAll('[data-admin-row]'));
```

- Recupere toutes les lignes de reservations admin.
- `Array.from()` transforme la liste en vrai tableau.

```js
const searchInput = document.querySelector('[data-admin-search]');
```

- Recupere le champ de recherche.

```js
const countOutput = document.querySelector('[data-admin-count]');
```

- Recupere l'affichage du nombre de resultats.

```js
const editForm = document.querySelector('[data-admin-edit-form]');
```

- Recupere le formulaire de modification admin.

```js
const availabilityMatrix = document.querySelector('[data-admin-availability-matrix]');
```

- Recupere le tableau des disponibilites.

Fonction `updateVisibleCount()` :

- compte les lignes non masquees ;
- met a jour le texte "x resultats".

Fonction `fillEditForm(row)` :

- lit les `data-*` de la ligne selectionnee ;
- remplit les champs du formulaire admin ;
- marque la ligne comme selectionnee.

Exemple :

```js
editForm.nom.value = row.dataset.nom || '';
```

Explication :

- Remplit le champ nom avec la valeur stockee dans `data-nom`.
- Si la valeur n'existe pas, met une chaine vide.

Fonction `applyAvailabilityData(data)` :

- lit les donnees JSON recues ;
- trouve la cellule correspondant a une salle et une heure ;
- met a jour son contenu, par exemple `8/12` ;
- ajoute la classe `is-full` si le creneau est complet.

Fonction `refreshAvailabilityTable()` :

- appelle `admin_disponibilites.php` avec `fetch()`;
- demande du JSON ;
- applique les nouvelles donnees au tableau ;
- affiche une erreur si la mise a jour echoue.

Recherche dynamique :

```js
searchInput?.addEventListener('input', () => {
```

- Quand l'admin tape dans la recherche, le script filtre les lignes en direct.
- `?.` evite une erreur si l'element n'existe pas.

Suppression :

```js
window.confirm('Supprimer définitivement cette réservation ?')
```

- Demande confirmation avant suppression.
- Si l'admin annule, `event.preventDefault()` bloque l'envoi du formulaire.

Mise a jour automatique :

```js
setInterval(refreshAvailabilityTable, 10000);
```

- Relance la mise a jour toutes les 10 secondes.
- Cela donne un tableau presque temps reel.

### 12.4 `rooms.js`

Ce script gere la navigation dans les pages salles.

```js
const sectionLinks = new Map(...)
```

- Cree une correspondance entre les ids de sections et les liens.

```js
const sections = Array.from(document.querySelectorAll('[data-room-section]'));
```

- Recupere les sections de la page salle.

```js
function setActiveSection(sectionId)
```

- Active le lien correspondant a la section visible.

```js
const observer = new IntersectionObserver(...)
```

- Observe les sections pendant le scroll.
- Quand une section devient visible, le lien de navigation correspondant devient actif.

```js
rootMargin: '-30% 0px -55% 0px'
```

- Regle la zone de detection dans l'ecran.

```js
threshold: [0.15, 0.3, 0.5, 0.75]
```

- Definit plusieurs niveaux de visibilite pour rendre la detection plus precise.

## 13. Interface graphique PHP/HTML/CSS

### 13.1 PHP + HTML

Les pages PHP melangent :

- logique serveur au debut ;
- affichage HTML ensuite.

Exemple de structure :

```php
require_once __DIR__ . '/../fonctions.php';
// preparation des donnees
require __DIR__ . '/includes/header.php';
?>
<main>
    <!-- HTML de la page -->
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
```

Explication :

- On charge d'abord les fonctions.
- On prepare les donnees.
- On inclut le header.
- On affiche la page.
- On inclut le footer.

### 13.2 CSS

Le CSS definit :

- les couleurs de la charte ;
- les polices ;
- les boutons ;
- les cartes ;
- les grilles ;
- les formulaires ;
- le responsive.

Exemples de principes :

- `display: grid` pour organiser les blocs.
- `flex` pour aligner les elements dans le header/footer.
- `@media` pour adapter mobile/tablette.
- classes `is-active`, `is-visible`, `is-full`, `is-error`, `is-success` pour les etats visuels.

### 13.3 Pourquoi des classes d'etat ?

Exemple :

- `is-visible` affiche une slide.
- `is-active` indique un lien actif.
- `is-full` montre un creneau complet.
- `is-error` affiche un message d'erreur.

Cela permet au JavaScript de modifier seulement les classes, et au CSS de gerer l'apparence.

## 14. Integration du CRUD dans l'interface

### 14.1 Create dans l'interface visiteur

Dans `reservation.php` :

- le formulaire est envoye en `POST` ;
- PHP lit `$_POST` ;
- PHP valide ;
- PHP appelle `createVisiteur()` ;
- PHP appelle `createReservation()` ;
- PHP redirige vers `confirmation.php`.

### 14.2 Read dans l'interface visiteur

Dans `page_connexion.php` :

- le visiteur entre son identifiant et son mot de passe ;
- PHP retrouve le visiteur ;
- PHP appelle `getReservationDetailsByVisitorId()`;
- les reservations sont affichees.

### 14.3 Update dans l'interface visiteur

- Le visiteur clique sur modifier.
- Il est envoye vers `reservation.php?modifier=id`.
- PHP charge l'ancienne reservation.
- Le formulaire est pre-rempli.
- A l'envoi, PHP appelle `updateAdminReservation()`.

### 14.4 Delete dans l'interface visiteur

- Le visiteur clique sur supprimer.
- Un formulaire POST est envoye.
- PHP verifie la session.
- PHP appelle `deleteVisitorReservation()`.

### 14.5 CRUD admin

Dans `admin.php` :

- Read : liste des reservations.
- Update : formulaire lateral.
- Delete : boutons de suppression.
- Export : generation CSV.

## 15. Tests de fonctionnement

### 15.1 Tests unitaires

Un test unitaire verifie une petite fonction isolee.

Exemples a tester :

- `registrationFormatTime('15:00:00')` retourne `15:00`.
- `passwordCorresponds()` retourne vrai si le mot de passe correspond.
- `identifierAlreadyUsed()` detecte un doublon.
- `getPlacesRestantes()` calcule bien `12 - reservations`.

### 15.2 Tests d'integration

Un test d'integration verifie plusieurs parties ensemble.

Exemples :

- creer un visiteur + une reservation en base ;
- se connecter avec le visiteur cree ;
- retrouver la reservation ;
- modifier la reservation ;
- verifier que la BDD a change ;
- supprimer la reservation ;
- verifier qu'elle n'apparait plus.

### 15.3 Tests de non regression

Un test de non regression verifie qu'une modification ne casse pas ce qui marchait avant.

Exemples apres ajout du bouton oeil :

- le champ mot de passe reste obligatoire ;
- le formulaire s'envoie toujours ;
- le bouton oeil ne valide pas le formulaire ;
- la creation de reservation fonctionne encore.

### 15.4 Tests W3C/WAI/CSS

Tests deja prevus :

- verifier le HTML avec un validateur ;
- verifier que les pages ont un `<!doctype html>` ;
- verifier les textes alternatifs des images ;
- verifier les labels de formulaires ;
- verifier que les boutons vides ont un label accessible ;
- verifier que les contrastes sont suffisants ;
- verifier le CSS avec un validateur CSS.

Point important :

> Pour faire une validation W3C officielle, il faut soit utiliser le validateur en ligne sur un site deploye, soit installer un validateur local. En local, certaines pages dependant de la BDD doivent avoir une connexion MySQL fonctionnelle avant le test.

### 15.5 Validation client

La validation client consiste a montrer le prototype a la prof ou au commanditaire.

Il faut verifier :

- est-ce que le parcours de reservation est clair ?
- est-ce que les places restantes sont comprehensibles ?
- est-ce que l'admin trouve rapidement les informations ?
- est-ce que le CSV correspond a ce qui est demande ?
- est-ce que le contenu correspond a l'exposition ?

## 16. Phrases utiles pour l'oral

### Sur la base de donnees

> Nous avons separe les informations en plusieurs tables pour eviter les repetitions : les visiteurs sont dans une table, les salles dans une autre, les horaires dans une autre, et la reservation relie un visiteur a un couple salle/creneau.

### Sur `salle_creneaux`

> La table `salle_creneaux` est centrale car elle associe une salle a un horaire et donne la capacite maximale. C'est grace a elle qu'on peut calculer les places restantes.

### Sur PDO

> Nous utilisons PDO pour communiquer avec MySQL. Les requetes sont preparees avec des parametres comme `:nom` ou `:id`, ce qui evite d'inserer directement les donnees utilisateur dans le SQL.

### Sur la securite

> On verifie les donnees cote JavaScript pour l'ergonomie, mais les vraies verifications sont refaites cote PHP avant l'enregistrement, car le JavaScript peut etre contourne.

### Sur les sessions

> Les sessions permettent de savoir si l'utilisateur connecte est un admin ou un visiteur. Selon le role, on affiche l'admin, les reservations visiteur ou la connexion.

### Sur le CRUD

> Le CRUD est present a deux niveaux : cote visiteur pour creer, lire, modifier et supprimer ses propres reservations, et cote admin pour gerer toutes les reservations.

### Sur le temps reel admin

> Le tableau admin est mis a jour automatiquement par JavaScript. Le script appelle un fichier PHP qui renvoie du JSON avec les places restantes. Cela evite de recharger toute la page.

### Sur les tests

> Nous avons prevu des tests unitaires pour les fonctions, des tests d'integration pour les parcours complets, des tests de non regression apres modification, et des tests qualite W3C/WAI/CSS pour verifier la conformite du prototype.

## 17. Limites et ameliorations possibles

Ameliorations possibles :

- utiliser `password_hash()` pour les mots de passe visiteurs ;
- augmenter la taille du champ `mdp`, car `varchar(15)` est trop court pour un vrai hash ;
- utiliser un vrai service SMTP pour les emails ;
- ajouter des tests automatises avec PHPUnit ;
- ajouter une contrainte unique sur `moyen_comm` directement en base ;
- ameliorer le message d'erreur de connexion BDD en production ;
- installer un validateur CSS local ou valider le site une fois deploye.

## 18. Resume ultra court

Le site e-llusion utilise PHP, MySQL, HTML, CSS et JavaScript. La base stocke les visiteurs, salles, jours, creneaux et reservations. PHP gere les requetes SQL avec PDO, les sessions, la creation/modification/suppression des reservations et l'interface admin. JavaScript rend l'interface plus fluide avec le carrousel, les jauges de places, l'ajout de creneaux et la mise a jour admin. Les tests doivent couvrir les fonctions, les parcours complets, la non regression et la conformite W3C/WAI/CSS.
