<?php

declare(strict_types=1);

require_once __DIR__ . '/../fonctions.php';

requireAdminSession();

function adminApiFormatTime(string $time): string
{
    $parsedTime = DateTimeImmutable::createFromFormat('H:i:s', $time);

    return $parsedTime ? $parsedTime->format('H:i') : substr($time, 0, 5);
}

$selectedDay = trim((string) ($_GET['day'] ?? ''));
$availabilityRows = getAdminAvailability($conn);
$cells = [];

foreach ($availabilityRows as $row) {
    $date = (string) $row['date_jour'];

    if ($selectedDay !== '' && $date !== $selectedDay) {
        continue;
    }

    $cells[] = [
        'day' => $date,
        'room' => (string) $row['numero_salle'],
        'time' => adminApiFormatTime((string) $row['heure_debut']),
        'remaining' => (int) $row['remaining_places'],
        'capacity' => (int) $row['capacite_max'],
        'reserved' => (int) $row['reserved_count'],
    ];
}

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

echo json_encode([
    'updated_at' => date('H:i:s'),
    'cells' => $cells,
], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
