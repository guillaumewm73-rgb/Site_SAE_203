<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Petite fonction de sécurité utilisée dans les templates.
// Elle évite qu'un texte venant de la BDD puisse être interprété comme du HTML.
if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

// Valeurs par défaut : chaque page peut les modifier avant d'inclure le header.
$pageTitle = $pageTitle ?? 'e-llusion - Exposition MMI';
$activePage = $activePage ?? '';
$bodyClass = $bodyClass ?? '';
$reserveHref = $reserveHref ?? 'reservation.php';
$extraScripts = $extraScripts ?? [];

// Le filemtime ajoute une version automatique au CSS.
// Comme ça, le navigateur recharge le style après une modification au lieu de garder l'ancien cache.
$cssVersion = filemtime(__DIR__ . '/../assets/css/style.css');

// Navigation commune à toutes les pages publiques.
$navLinks = $navLinks ?? [
    ['key' => 'accueil', 'label' => 'Accueil', 'href' => 'index.php'],
    ['key' => 'salles', 'label' => 'Salles', 'href' => 'index.php#salles'],
    ['key' => 'contact', 'label' => 'Contact', 'href' => 'contact.php'],
];

$currentRole = (string) ($_SESSION['auth_role'] ?? '');

// Le lien Admin n'est ajouté que si la session indique un compte administrateur.
if ($currentRole === 'admin') {
    $navLinks[] = ['key' => 'admin', 'label' => 'Admin', 'href' => 'admin.php'];
}


?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle); ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= e((string) $cssVersion); ?>">
    <?php foreach ($extraScripts as $script): ?>
        <?php
            // Chaque page peut demander ses scripts JS dans $extraScripts.
            // On applique le même système de version que pour le CSS.
            $scriptPath = __DIR__ . '/../' . $script;
            $scriptVersion = file_exists($scriptPath) ? filemtime($scriptPath) : time();
        ?>
        <script src="<?= e($script); ?>?v=<?= e((string) $scriptVersion); ?>" defer></script>
    <?php endforeach; ?>
</head>
<body class="<?= e($bodyClass); ?>">
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="Retour à l'accueil">
            <span class="brand-line"></span>
            <span>E-llusion</span>
        </a>

        <nav class="main-nav" aria-label="Navigation principale">
            <?php foreach ($navLinks as $link): ?>
                <a
                    class="<?= $activePage === $link['key'] ? 'is-active' : ''; ?>"
                    href="<?= e($link['href']); ?>"
                ><?= e($link['label']); ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="header-actions">
            <span>18 &amp; 19 juin 2026</span>
            <a class="button button-primary" href="reservation.php">Réserver</a>
            <?php if ($currentRole === 'admin' || isset($_SESSION['visiteur_id'])): ?>
                <a class="button button-secondary" href="deconnexion.php">Déconnexion</a>
            <?php else: ?>
                <a class="button button-primary" href="page_connexion.php">Connexion</a>
            <?php endif; ?>        </div>
    </header>
