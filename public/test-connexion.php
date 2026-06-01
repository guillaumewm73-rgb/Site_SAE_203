<?php
require_once __DIR__ . '/../connexion.php';

$requete = $conn->query('SELECT COUNT(*) AS total_salles FROM salles');
$resultat = $requete->fetch();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test connexion BDD</title>
</head>
<body>
    <h1>Connexion réussie ✅</h1>
    <p>Nombre de salles dans la base : <?= htmlspecialchars($resultat['total_salles']) ?></p>
    <p>Ce fichier sert uniquement à tester la connexion. Tu peux le supprimer après vérification.</p>
</body>
</html>
