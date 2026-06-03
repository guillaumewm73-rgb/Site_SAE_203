<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/donnee_salles.php';

$room = getRoomByNumber('001');
if ($room === null) {
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

$pageTitle = 'Salle 001 - ' . $room['title'] . ' - e-llusion';
$activePage = 'salles';
$bodyClass = 'room-page';
$reserveHref = 'reservation.php';
$extraScripts = ['assets/js/rooms.js'];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/modele_salles.php';
require_once __DIR__ . '/includes/footer.php';
