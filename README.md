# Site e-llusion - SAE 203

Projet PHP simple pour le site de réservation de l'exposition e-llusion.

## Lancer le site en local

1. Ouvrir MAMP et démarrer les serveurs.

2. Créer et importer la base de données :

```bash
/Applications/MAMP/Library/bin/mysql80/bin/mysql -h 127.0.0.1 -P 8889 -u root -proot -e "CREATE DATABASE IF NOT EXISTS sae203_bdd CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
/Applications/MAMP/Library/bin/mysql80/bin/mysql -h 127.0.0.1 -P 8889 -u root -proot sae203_bdd < database/sae203_bdd_v1.sql
```

Pour réinitialiser complètement la base avant un nouvel import :

```bash
/Applications/MAMP/Library/bin/mysql80/bin/mysql -h 127.0.0.1 -P 8889 -u root -proot -e "DROP DATABASE IF EXISTS sae203_bdd; CREATE DATABASE sae203_bdd CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
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
