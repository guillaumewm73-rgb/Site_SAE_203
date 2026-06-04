<?php

require ('config.php');

// Ancienne version gardée en repère : elle montre comment ajouter le port et le charset
// si une configuration locale en a besoin. La connexion utilisée actuellement est plus simple.
//try {
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
    // PDO crée l'objet de connexion qui sera réutilisé dans toutes les fonctions BDD.
    // On assemble le DSN avec les constantes du fichier config.php pour éviter de répéter les accès.
    $conn = new PDO('mysql:host=' . SERVEUR_BD . ';dbname=' . NOM_BD, LOGIN_BD, PASSE_BD);
    }


catch (PDOException $e) {
    // En développement, on affiche l'erreur pour comprendre rapidement si le problème vient
    // du serveur, du nom de base, de l'utilisateur ou du mot de passe.
    die("Erreur de connexion : " . $e->getMessage()); }


?>
