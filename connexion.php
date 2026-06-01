<?php
require_once('config.php');

try {
    $conn = new PDO(
        "mysql:host=" . SERVEUR_BD . ";port=" . PORT_BD . ";dbname=" . NOM_BD . ";charset=utf8mb4",
        LOGIN_BD,
        PASSE_BD
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>