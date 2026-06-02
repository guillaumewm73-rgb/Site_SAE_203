# Site e-llusion - SAE 203

Projet PHP simple pour le site de réservation de l'exposition e-llusion.

## Lancer le site en local

1. Récupérer le projet puis créer votre configuration locale :

```bash
git pull
cp config.example.php config.php
```

Le fichier `config.php` est volontairement ignoré par Git. Chaque personne garde donc ses propres identifiants de base de données sur sa machine.

2. Ouvrir MAMP et démarrer les serveurs.

3. Créer et importer la base de données :

```bash
/Applications/MAMP/Library/bin/mysql80/bin/mysql -h 127.0.0.1 -P 8889 -u root -proot -e "CREATE DATABASE IF NOT EXISTS sae203_bdd CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
/Applications/MAMP/Library/bin/mysql80/bin/mysql -h 127.0.0.1 -P 8889 -u root -proot sae203_bdd < database/sae203_bdd_v1.sql
```

Pour réinitialiser complètement la base avant un nouvel import :

```bash
/Applications/MAMP/Library/bin/mysql80/bin/mysql -h 127.0.0.1 -P 8889 -u root -proot -e "DROP DATABASE IF EXISTS sae203_bdd; CREATE DATABASE sae203_bdd CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
/Applications/MAMP/Library/bin/mysql80/bin/mysql -h 127.0.0.1 -P 8889 -u root -proot sae203_bdd < database/sae203_bdd_v1.sql
```

4. Depuis ce dossier, lancer le serveur PHP :

```bash
php -S 127.0.0.1:8000 -t public
```

5. Ouvrir le site :

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

## Travailler à deux avec GitHub

Chacun travaille avec le même code, mais avec sa propre base MySQL locale :

```bash
git pull
composer install
cp config.example.php config.php
```

Ensuite, chacun importe `database/sae203_bdd_v1.sql` dans son phpMyAdmin/MAMP.

À ne pas envoyer sur GitHub :

```text
config.php
vendor/
composer.phar
```

Quand une personne modifie le code :

```bash
git add .
git commit -m "Message clair"
git push
```

Quand l’autre veut récupérer les changements :

```bash
git pull
```

Si vous voulez tester avec une seule base commune, il faut mettre le site en ligne sur l’hébergement qui a accès à cette base distante. Depuis vos ordinateurs, l’adresse `ijtebowcompte13.mysql.db` n’est pas résolue, donc la connexion directe à cette base distante ne fonctionne pas en local.

## 📧 Configuration de l'envoi d'emails

Le site envoie des **emails de confirmation après chaque réservation** via **Gmail SMTP**.

### Configuration requise

1. **Installez les dépendances Composer** :
   ```bash
   composer install
   ```

2. **Configurez `config.php`** (copié depuis `config.example.php`) :
   - Entrez vos identifiants Gmail
   - Générez un [mot de passe d'application Gmail](https://myaccount.google.com/apppasswords)

3. **Testez la configuration** :
   ```
   http://127.0.0.1:8000/test-email.php
   ```

📘 **Documentation complète** : Voir [EMAIL_SETUP.md](EMAIL_SETUP.md)

## 📦 Dépendances

- **PHP** ≥ 7.4
- **PHPMailer** (v7.1.1) - Installation via Composer
- **MySQL** - MAMP inclus
- **Composer** - Pour gérer les dépendances

## Structure

```text
public/
├── index.php
├── test-email.php (nouveau)
└── assets/
    ├── css/style.css
    ├── js/home.js
    └── images/

vendor/
├── phpmailer/
└── ...

config.php (à configurer)
config.example.php (référence)
EMAIL_SETUP.md (documentation email)

database/
└── sae203_bdd_v1.sql
```
