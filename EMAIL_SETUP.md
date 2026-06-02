# Configuration de l'envoi d'emails - Gmail SMTP

## 📋 Vue d'ensemble

Le site e-llusion est maintenant configuré pour envoyer des **emails de confirmation de réservation** via **Gmail SMTP**.

## ⚙️ Configuration requise

### 1. Génération du mot de passe d'application Gmail

Gmail n'accepte pas les mots de passe classiques pour les applications tierces. Vous devez générer un **mot de passe d'application**.

#### Étapes :

1. **Allez sur votre compte Google** : https://myaccount.google.com/
2. **Sécurité** (colonne de gauche)
3. **Authentification à 2 étapes** → Assurez-vous que c'est **activé**
4. **Mots de passe d'application** (apparaît en bas après 2FA)
5. Sélectionnez :
   - **App** : `Mail`
   - **Device** : `Windows, Mac, Linux` (ou autre)
6. Cliquez sur **Generate**
7. **Copiez le mot de passe généré** (16 caractères)

### 2. Configuration du projet

Ouvrez le fichier `config.php` et remplacez :

```php
define('MAIL_SMTP_PASSWORD', 'your_app_password_here');
```

Par :

```php
define('MAIL_SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx'); // Le mot de passe généré par Google
```

⚠️ **Important** : Ne partagez jamais ce mot de passe et ne le commitez pas sur Git !

## 🔧 Configuration complète (config.php)

```php
/* ========================================================
   EMAIL CONFIGURATION - Gmail SMTP
   ======================================================== */
define('MAIL_SMTP_HOST', 'smtp.gmail.com');
define('MAIL_SMTP_PORT', 587);
define('MAIL_SMTP_USER', 'guillaumewm73@gmail.com');
define('MAIL_SMTP_PASSWORD', 'votre_mot_de_passe_app');
define('MAIL_FROM_ADDRESS', 'guillaumewm73@gmail.com');
define('MAIL_FROM_NAME', 'e-llusion - Exposition');
define('MAIL_ENABLED', true); // Set to false to disable email sending
```

## 📧 Fonctionnalités d'envoi

### Quand les emails sont envoyés ?

- ✓ Après chaque **réservation validée** sur la page `/public/inscription.php`
- L'utilisateur reçoit un **email HTML formaté** avec :
  - Confirmation de ses réservations
  - Détail des salles, horaires et nombre de personnes
  - Informations importantes
  - Contact de l'exposition

### À qui sont envoyés les emails ?

- Les emails sont envoyés **uniquement si le contact est une adresse email**
- Si l'utilisateur a fourni un **téléphone** au lieu d'un email, **pas d'envoi**

## 🐛 Dépannage

### L'email n'est pas envoyé ?

1. **Vérifiez la configuration** dans `config.php`
   - Adresse correcte ?
   - Mot de passe d'application correct ?
   - `MAIL_ENABLED` est à `true` ?

2. **Vérifiez les logs PHP** :
   ```bash
   tail -f /Applications/MAMP/logs/php_error.log
   ```

3. **Désactivez temporairement** pour tester :
   ```php
   define('MAIL_ENABLED', false);
   ```

4. **Vérifiez que** :
   - L'authentification 2FA est activée sur Gmail
   - Le mot de passe d'application a été créé correctement
   - La connexion Internet est active

### Erreur "SMTP connect() failed" ?

- Vérifiez le port (587 pour STARTTLS)
- Vérifiez que votre firewall n'bloque pas le port
- Essayez le port 465 si 587 ne fonctionne pas

## 📝 Fichiers modifiés

- `config.php` - Ajout des constantes de configuration SMTP
- `fonctions.php` - Ajout des fonctions d'envoi d'email :
  - `sendConfirmationEmail()` - Fonction principale
  - `extractEmailFromContact()` - Extraction de l'email
  - `buildConfirmationEmailBody()` - Construction du corps HTML
- `public/inscription.php` - Intégration de l'envoi après réservation
- `composer.json` - Ajout de PHPMailer comme dépendance

## 🚀 Déploiement en production

1. **Générez un mot de passe d'application** sur Gmail
2. **Configurez `config.php`** sur le serveur
3. **Installez les dépendances** :
   ```bash
   cd /chemin/vers/le/site
   composer install
   ```
4. **Testez** en créant une réservation
5. **Vérifiez** que l'email est reçu

## 📦 Dépendances

- **PHPMailer** (v7.1.1) - Pour l'envoi d'emails via SMTP
  - Installation : `composer require phpmailer/phpmailer`

## 💡 Améliorations futures

- [ ] Ajouter des modèles d'emails personnalisables
- [ ] Envoyer des rappels avant la visite
- [ ] Permettre aux utilisateurs de modifier leurs emails
- [ ] Ajouter des pièces jointes (plan, tickets, etc.)
- [ ] Support pour plusieurs adresses en copie (CC/BCC)
