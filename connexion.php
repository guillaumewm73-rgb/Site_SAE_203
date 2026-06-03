<?php

define('NOM_BD', 'sae203_bdd_v1');
define('SERVEUR_BD', '127.0.0.1');
define('PORT_BD', 8889);
define('LOGIN_BD', 'root');
define('PASSE_BD', 'root');

try {
    $port = defined('PORT_BD') && PORT_BD !== '' ? ';port=' . PORT_BD : '';
    $dsn = 'mysql:host=' . SERVEUR_BD . $port . ';dbname=' . NOM_BD . ';charset=utf8mb4';

    $conn = new PDO(
        $dsn,
        LOGIN_BD,
        PASSE_BD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
