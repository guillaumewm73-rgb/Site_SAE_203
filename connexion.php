<?php

define('NOM_BD', 'ijtebowcompte13');
define('SERVEUR_BD', 'ijtebowcompte13.mysql.db');
define('PORT_BD', '');
define('LOGIN_BD', 'ijtebowcompte13');
define('PASSE_BD', 'v8ng67SF2026');

try {
  //  $port = defined('PORT_BD') && PORT_BD !== '' ? ';port=' . PORT_BD : '';
  //  $dsn = 'mysql:host=' . SERVEUR_BD . $port . ';dbname=' . NOM_BD . ';charset=utf8mb4';
//
  //  $conn = new PDO(
    //    $dsn,
      //  LOGIN_BD,
        //PASSE_BD,
//        [
  //        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    //        PDO::ATTR_EMULATE_PREPARES => false,
      //  ]
   // );
//} catch (PDOException $e) {
  //  die("Erreur de connexion : " . $e->getMessage()); }

try {
    $pdo = new PDO('mysql:host=' . SERVEUR_BD . ';dbname=' . NOM_BD, LOGIN_BD, PASSE_BD);}

} 
catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage()); }


?>
