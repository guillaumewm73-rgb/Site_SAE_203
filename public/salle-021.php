<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/donnee_salles.php';

// Chaque page de salle récupère ses données depuis le catalogue centralisé.
$room = getRoomByNumber('021');
if ($room === null) {
    // Sécurité : si le numéro n'existe pas, on renvoie une page 404 propre.
    http_response_code(404);
    $pageTitle = 'Salle introuvable - e-llusion';
    $activePage = 'salles';
    $bodyClass = 'room-page';
    $reserveHref = 'reservation.php';
    $extraScripts = ['assets/js/rooms.js'];

    require_once __DIR__ . '/includes/header.php';
    echo '<main class="room-page"><section class="room-panel"><h1>Salle introuvable</h1><p>La salle demandée est introuvable.</p><p><a href="index.php#salles">Retour aux salles</a></p></section></main>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = 'Salle 021 - ' . $room['title'] . ' - e-llusion';
$activePage = 'salles';
$bodyClass = 'room-page';
$reserveHref = 'reservation.php';
$extraScripts = ['assets/js/rooms.js'];

// Le contenu visuel est partagé dans modele_salles.php pour éviter de dupliquer le HTML.
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/modele_salles.php';
require_once __DIR__ . '/includes/footer.php';
