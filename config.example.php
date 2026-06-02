<?php
    /* ========================================================
       DATABASE
       ======================================================== */
    define('NOM_BD', 'sae203_bdd');
    define('SERVEUR_BD', '127.0.0.1');
    define('PORT_BD', 8889);
    define('LOGIN_BD', 'root');
    define('PASSE_BD', 'root');

    /* ========================================================
       EMAIL CONFIGURATION - Gmail SMTP
       ======================================================== */
    define('MAIL_SMTP_HOST', 'smtp.gmail.com');
    define('MAIL_SMTP_PORT', 587);
    define('MAIL_SMTP_USER', 'votre-email@gmail.com');
    define('MAIL_SMTP_PASSWORD', 'votre_mot_de_passe_app'); // Généré via https://myaccount.google.com/apppasswords
    define('MAIL_FROM_ADDRESS', 'votre-email@gmail.com');
    define('MAIL_FROM_NAME', 'e-llusion - Exposition');
    define('MAIL_ENABLED', true); // Set to false to disable email sending

?>
