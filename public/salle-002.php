<?php

declare(strict_types=1);

require __DIR__ . '/includes/donnee_salles.php';

$room = getRoomByNumber('002');
$pageTitle = 'Salle 002 - ' . $room['title'] . ' - e-llusion';
$activePage = 'salles';
$bodyClass = 'room-page';
$reserveHref = 'inscription.php';
$extraScripts = ['assets/js/rooms.js'];

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/modele_salles.php';
require __DIR__ . '/includes/footer.php';
