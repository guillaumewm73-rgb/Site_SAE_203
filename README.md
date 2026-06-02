# Site e-llusion - SAE 203

Projet PHP simple pour le site de réservation de l'exposition e-llusion.

## Lancer le site en local

1. Ouvrir MAMP et démarrer les serveurs.

2. Créer et importer la base de données :

```bash
/Applications/MAMP/Library/bin/mysql80/bin/mysql -h 127.0.0.1 -P 8889 -u root -proot -e "CREATE DATABASE IF NOT EXISTS sae203_bdd CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
/Applications/MAMP/Library/bin/mysql80/bin/mysql -h 127.0.0.1 -P 8889 -u root -proot sae203_bdd < database/sae203_bdd_v1.sql
```

3. Depuis ce dossier, lancer le serveur PHP :

```bash
php -S 127.0.0.1:8000 -t public
```

4. Ouvrir le site :

```text
http://127.0.0.1:8000
```

La connexion locale utilise MAMP :

```text
serveur : 127.0.0.1
port : 8889
base : sae203_bdd
utilisateur : root
mot de passe : root
```

## Structure

```text
public/
├── index.php
└── assets/
    ├── css/style.css
    ├── js/home.js
    └── images/

database/
└── sae203_bdd_v1.sql
```
