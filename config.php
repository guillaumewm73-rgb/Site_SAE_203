<?php

// Fichier central pour les accès à la base de données.
// En local, ces valeurs correspondent à la base créée dans phpMyAdmin.
// Sur l'hébergement MMI Agence, on peut remplacer ces constantes par les accès fournis.
define('NOM_BD', 'sae203_bdd_v1');
define('SERVEUR_BD', 'localhost');

// Identifiants MySQL utilisés par PDO dans connexion.php.
// Le mot de passe peut être vide selon la configuration MAMP/XAMPP de la machine.
define('LOGIN_BD', 'root');
define('PASSE_BD', 'root');
