<?php

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

$pageTitle = $pageTitle ?? 'e-llusion - Exposition MMI';
$activePage = $activePage ?? '';
$reserveHref = $reserveHref ?? 'inscription.php';
$extraScripts = $extraScripts ?? [];
$navLinks = $navLinks ?? [
    ['key' => 'accueil', 'label' => 'Accueil', 'href' => 'index.php'],
    ['key' => 'salles', 'label' => 'Salles', 'href' => 'index.php#salles'],
    ['key' => 'inscription', 'label' => 'Inscription', 'href' => 'inscription.php'],
    ['key' => 'contact', 'label' => 'Contact', 'href' => 'index.php#contact'],
];
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php foreach ($extraScripts as $script): ?>
        <script src="<?= e($script); ?>" defer></script>
    <?php endforeach; ?>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="Retour à l'accueil">
            <span class="brand-line"></span>
            <span>e-llusion</span>
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
