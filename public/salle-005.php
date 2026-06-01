<?php

declare(strict_types=1);

require __DIR__ . '/includes/donnee_salles.php';

$room = getRoomByNumber('005');
$pageTitle = 'Salle 005 - ' . $room['title'] . ' - e-llusion';
$activePage = 'salles';
$bodyClass = 'room-page';
$reserveHref = 'inscription.php';
$extraScripts = ['assets/js/rooms.js'];

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/room-template.php';
require __DIR__ . '/includes/footer.php';
