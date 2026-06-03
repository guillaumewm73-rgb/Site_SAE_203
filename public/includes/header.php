<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

$pageTitle = $pageTitle ?? 'e-llusion - Exposition MMI';
$activePage = $activePage ?? '';
$bodyClass = $bodyClass ?? '';
$reserveHref = $reserveHref ?? 'reservation.php';
$extraScripts = $extraScripts ?? [];
$cssVersion = filemtime(__DIR__ . '/../assets/css/style.css');
$navLinks = $navLinks ?? [
    ['key' => 'accueil', 'label' => 'Accueil', 'href' => 'index.php'],
    ['key' => 'salles', 'label' => 'Salles', 'href' => 'index.php#salles'],
    ['key' => 'reservation', 'label' => 'Réservation', 'href' => 'reservation.php'],
    ['key' => 'connexion', 'label' => 'Connexion', 'href' => 'page_connexion.php'],
    ['key' => 'contact', 'label' => 'Contact', 'href' => 'contact.php'],
];

$currentRole = (string) ($_SESSION['auth_role'] ?? '');

if ($currentRole === 'admin') {
    $navLinks[] = ['key' => 'admin', 'label' => 'Admin', 'href' => 'admin.php'];
}

if ($currentRole === 'visiteur') {
    foreach ($navLinks as &$link) {
        if ($link['key'] === 'connexion') {
            $link['label'] = 'Ma réservation';
            break;
        }
    }
    unset($link);
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
            <a class="button button-primary" href="<?= e($reserveHref); ?>">Réserver</a>
        </div>
    </header>
