<?php
/* --------------------------------------------------------
   Connexion à la base de données - e-llusion / SAE 203
   Serveur : OVH
   -------------------------------------------------------- */

// À modifier avec tes identifiants OVH (disponibles dans ton espace client OVH)
$utilisateur = "TON_UTILISATEUR_OVH";   // ex : "ellusion"
$mdp         = "TON_MOT_DE_PASSE_OVH";  // défini lors de la création de la BDD sur OVH
$base        = "sae203_bdd";            // nom de ta base de données
$serveur     = "TON_SERVEUR_OVH";       // ex : "sql123.main-hosting.eu" (visible dans l'espace OVH)
$port        = 3306;

// Connexion PDO
try {
    $conn = new PDO(
        "mysql:host=$serveur;port=$port;dbname=$base;charset=utf8mb4",
        $utilisateur,
        $mdp
    );
    // Affiche les erreurs SQL sous forme d'exceptions
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Retourne les résultats sous forme de tableau associatif par défaut
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // En production, ne pas afficher le message d'erreur au visiteur
    die("Erreur de connexion : " . $e->getMessage());
}
?>
