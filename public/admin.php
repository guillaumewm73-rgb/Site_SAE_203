<?php

declare(strict_types=1);

require_once __DIR__ . '/../fonctions.php';

requireAdminSession();

$pageTitle = 'Administration - e-llusion';
$activePage = 'admin';
$bodyClass = 'admin-page';
$reserveHref = 'reservation.php';
$extraScripts = ['assets/js/admin.js'];

function adminFormatDate(string $date, ?string $dayName = null): string
{
    $months = [
        '01' => 'janvier',
        '02' => 'février',
        '03' => 'mars',
        '04' => 'avril',
        '05' => 'mai',
        '06' => 'juin',
        '07' => 'juillet',
        '08' => 'août',
        '09' => 'septembre',
        '10' => 'octobre',
        '11' => 'novembre',
        '12' => 'décembre',
    ];

    $parsedDate = DateTimeImmutable::createFromFormat('Y-m-d', $date);
    if (!$parsedDate) {
        return trim((string) $dayName . ' ' . $date);
    }

    $label = trim((string) $dayName);
    $day = $parsedDate->format('d');
    $month = $months[$parsedDate->format('m')] ?? $parsedDate->format('m');

    return trim($label . ' ' . $day . ' ' . $month);
}

function adminFormatShortDay(string $date, ?string $dayName = null): string
{
    $parsedDate = DateTimeImmutable::createFromFormat('Y-m-d', $date);
    $prefix = $dayName ? substr($dayName, 0, 1) : 'J';

    return strtoupper($prefix) . ($parsedDate ? $parsedDate->format('d') : '');
}

function adminFormatTime(string $time): string
{
    $parsedTime = DateTimeImmutable::createFromFormat('H:i:s', $time);

    return $parsedTime ? $parsedTime->format('H:i') : substr($time, 0, 5);
}

function adminBoolValue(mixed $value): int
{
    $normalized = trim((string) $value, "\0 \t\n\r");

    return in_array($normalized, ['1', 'oui', 'yes', 'true'], true) ? 1 : 0;
}

function exportAdminReservationsCsv(array $reservations): never
{
    $filename = 'reservation-e-llusion-' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo "\xEF\xBB\xBF";

    $output = fopen('php://output', 'w');

    fputcsv($output, [
        'ID reservation',
        'Nom',
        'Prenom',
        'Identifiant',
        'Categorie',
        'Jour',
        'Date',
        'Heure',
        'Salle',
        'Nom salle',
        'Nombre de personnes',
        'Buffet jeudi 19h',
    ], ';');

    foreach ($reservations as $reservation) {
        fputcsv($output, [
            $reservation['reservation_id'],
            $reservation['nom'],
            $reservation['prenom'],
            $reservation['moyen_comm'],
            $reservation['categorie'],
            $reservation['nom_jour'],
            $reservation['date_jour'],
            adminFormatTime((string) $reservation['heure_debut']),
            $reservation['numero_salle'],
            $reservation['nom_salle'],
            $reservation['nombre_personnes'],
            adminBoolValue($reservation['participe_buffet']) === 1 ? 'Oui' : 'Non',
        ], ';');
    }

    fclose($output);
    exit;
}

function redirectAdmin(string $status, string $search, string $day): never
{
    $params = ['status' => $status];

    if ($search !== '') {
        $params['search'] = $search;
    }

    if ($day !== '') {
        $params['day'] = $day;
    }

    header('Location: admin.php?' . http_build_query($params));
    exit;
}

$formError = null;
$search = trim((string) ($_GET['search'] ?? $_POST['search'] ?? ''));
$selectedDay = trim((string) ($_GET['day'] ?? $_POST['day'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'update') {
            $reservationId = filter_input(INPUT_POST, 'reservation_id', FILTER_VALIDATE_INT);
            $visiteurId = filter_input(INPUT_POST, 'visiteur_id', FILTER_VALIDATE_INT);
            $categorieId = filter_input(INPUT_POST, 'categorie_id', FILTER_VALIDATE_INT);
            $salleCreneauxId = filter_input(INPUT_POST, 'salle_creneaux_id', FILTER_VALIDATE_INT);
            $participeBuffet = filter_input(INPUT_POST, 'participe_buffet', FILTER_VALIDATE_INT);
            $nombrePersonnes = filter_input(INPUT_POST, 'nombre_personnes', FILTER_VALIDATE_INT);
            $nom = trim((string) ($_POST['nom'] ?? ''));
            $prenom = trim((string) ($_POST['prenom'] ?? ''));
            $moyenComm = trim((string) ($_POST['moyen_comm'] ?? ''));

            if (!$reservationId || !$visiteurId || !$categorieId || !$salleCreneauxId) {
                throw new RuntimeException('La réservation sélectionnée est incomplète.');
            }

            if ($nom === '' || $moyenComm === '') {
                throw new RuntimeException('Le nom et le moyen de contact sont obligatoires.');
            }

            if (!$nombrePersonnes || $nombrePersonnes < 1 || $nombrePersonnes > 12) {
                throw new RuntimeException('Le nombre de personnes doit être compris entre 1 et 12.');
            }

            updateAdminReservation(
                $conn,
                $reservationId,
                $visiteurId,
                $nom,
                $prenom,
                $moyenComm,
                $categorieId,
                $salleCreneauxId,
                $participeBuffet ?? 0,
                $nombrePersonnes
            );

            redirectAdmin('updated', $search, $selectedDay);
        }

        if ($action === 'delete') {
            $reservationId = filter_input(INPUT_POST, 'reservation_id', FILTER_VALIDATE_INT);

            if (!$reservationId) {
                throw new RuntimeException('Aucune réservation sélectionnée pour la suppression.');
            }

            deleteAdminReservation($conn, $reservationId);
            redirectAdmin('deleted', $search, $selectedDay);
        }
    } catch (Throwable $exception) {
        $formError = $exception->getMessage();
    }
}

$categories = getAdminCategories($conn);
$slots = getAdminSlots($conn);
$reservations = getAdminReservations($conn, $search);

if ((string) ($_GET['export'] ?? '') === 'csv') {
    exportAdminReservationsCsv($reservations);
}

$availabilityRows = getAdminAvailability($conn);

$availabilityDays = [];
foreach ($availabilityRows as $row) {
    $date = (string) $row['date_jour'];
    $availabilityDays[$date] ??= [
        'label' => adminFormatDate($date, (string) $row['nom_jour']),
        'rows' => [],
    ];
    $availabilityDays[$date]['rows'][] = $row;
}

if ($selectedDay === '' || !isset($availabilityDays[$selectedDay])) {
    $selectedDay = array_key_first($availabilityDays) ?: '';
}

$activeAvailability = $selectedDay !== '' ? $availabilityDays[$selectedDay]['rows'] : [];
$availabilityTimes = [];
$availabilityRooms = [];
$availabilityMatrix = [];

foreach ($activeAvailability as $row) {
    $time = adminFormatTime((string) $row['heure_debut']);
    $room = (string) $row['numero_salle'];

    $availabilityTimes[$time] = $time;
    $availabilityRooms[$room] = $room;
    $availabilityMatrix[$room][$time] = $row;
}

ksort($availabilityRooms);
$matrixColumnCount = max(1, count($availabilityTimes));
$matrixMinWidth = 90 + ($matrixColumnCount * 82);

$roomSummary = [];
foreach ($availabilityRows as $row) {
    $room = (string) $row['numero_salle'];
    $reservedCount = (int) $row['reserved_count'];
    $capacity = (int) $row['capacite_max'];

    $roomSummary[$room] ??= [
        'salle' => $room,
        'total' => 0,
        'capacity' => 0,
        'peak' => '-',
        'peak_reserved' => -1,
        'critical' => false,
    ];

    $roomSummary[$room]['total'] += $reservedCount;
    $roomSummary[$room]['capacity'] += $capacity;

    if ($reservedCount > $roomSummary[$room]['peak_reserved']) {
        $roomSummary[$room]['peak_reserved'] = $reservedCount;
        $roomSummary[$room]['peak'] = adminFormatTime((string) $row['heure_debut']);
    }

    if ((int) $row['remaining_places'] === 0) {
        $roomSummary[$room]['critical'] = true;
    }
}

foreach ($roomSummary as &$summary) {
    $summary['percent'] = $summary['capacity'] > 0
        ? min(100, (int) round(($summary['total'] / $summary['capacity']) * 100))
        : 0;
}
unset($summary);

$selectedReservation = $reservations[0] ?? null;
$statusMessages = [
    'updated' => 'Réservation modifiée dans la base de données.',
    'deleted' => 'Réservation supprimée de la base de données.',
];
$status = (string) ($_GET['status'] ?? '');

require __DIR__ . '/includes/header.php';
?>

    <main class="admin-interface">
        <section class="admin-hero" aria-labelledby="admin-title">
            <p class="eyebrow">Interface administratrice</p>
            <h1 id="admin-title">Administration</h1>
            <p>
                Suivez les réservations, filtrez les réservations et visualisez les places
                disponibles par salle et par créneau.
            </p>
            <a class="button button-secondary admin-logout-button" href="deconnexion.php">Déconnexion</a>
        </section>

        <?php if ($formError): ?>
            <section class="admin-feedback is-error" role="alert">
                <?= e($formError); ?>
            </section>
        <?php elseif (isset($statusMessages[$status])): ?>
            <section class="admin-feedback" role="status">
                <?= e($statusMessages[$status]); ?>
            </section>
        <?php endif; ?>

        <section class="admin-section admin-layout" aria-label="Tableau de bord administrateur">
            <aside class="admin-edit-card" aria-label="Panneau d'édition">
                <div>
                    <h2>Éditer une réservation</h2>
                    <p>
                        Sélectionnez une ligne puis modifiez le visiteur, la salle ou le créneau.
                    </p>
                </div>

                <form class="admin-edit-form" method="post" action="admin.php" data-admin-edit-form>
                    <input type="hidden" name="reservation_id" value="<?= e((string) ($selectedReservation['reservation_id'] ?? '')); ?>">
                    <input type="hidden" name="visiteur_id" value="<?= e((string) ($selectedReservation['visiteurs_id'] ?? '')); ?>">
                    <input type="hidden" name="search" value="<?= e($search); ?>">
                    <input type="hidden" name="day" value="<?= e($selectedDay); ?>">

                    <label>
                        <span>Nom</span>
                        <input
                            type="text"
                            name="nom"
                            required
                            value="<?= e((string) ($selectedReservation['nom'] ?? '')); ?>"
                        >
                    </label>

                    <label>
                        <span>Prénom</span>
                        <input
                            type="text"
                            name="prenom"
                            value="<?= e((string) ($selectedReservation['prenom'] ?? '')); ?>"
                        >
                    </label>

                    <label>
                        <span>Email ou téléphone</span>
                        <input
                            type="text"
                            name="moyen_comm"
                            required
                            value="<?= e((string) ($selectedReservation['moyen_comm'] ?? '')); ?>"
                        >
                    </label>

                    <label>
                        <span>Catégorie</span>
                        <select name="categorie_id" required>
                            <?php foreach ($categories as $category): ?>
                                <option
                                    value="<?= e((string) $category['id']); ?>"
                                    <?= (int) ($selectedReservation['categories_visiteur_id'] ?? 0) === (int) $category['id'] ? 'selected' : ''; ?>
                                >
                                    <?= e((string) $category['libelle']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        <span>Créneau et salle</span>
                        <select name="salle_creneaux_id" required>
                            <?php foreach ($slots as $slot): ?>
                                <option
                                    value="<?= e((string) $slot['salle_creneaux_id']); ?>"
                                    <?= (int) ($selectedReservation['salle_creneaux_id'] ?? 0) === (int) $slot['salle_creneaux_id'] ? 'selected' : ''; ?>
                                >
                                    <?= e(adminFormatDate((string) $slot['date_jour'], (string) $slot['nom_jour'])); ?>
                                    · <?= e(adminFormatTime((string) $slot['heure_debut'])); ?>
                                    · Salle <?= e((string) $slot['numero_salle']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        <span>Buffet du jeudi à 19h</span>
                        <select name="participe_buffet">
                            <?php $buffetValue = adminBoolValue($selectedReservation['participe_buffet'] ?? 0); ?>
                            <option value="0" <?= $buffetValue === 0 ? 'selected' : ''; ?>>Non</option>
                            <option value="1" <?= $buffetValue === 1 ? 'selected' : ''; ?>>Oui</option>
                        </select>
                    </label>

                    <label>
                        <span>Nombre de places</span>
                        <input
                            type="number"
                            name="nombre_personnes"
                            min="1"
                            max="12"
                            required
                            value="<?= e((string) ($selectedReservation['nombre_personnes'] ?? 1)); ?>"
                        >
                    </label>

                    <div class="admin-edit-actions">
                        <button
                            class="button button-primary"
                            type="submit"
                            name="action"
                            value="update"
                            <?= $selectedReservation ? '' : 'disabled'; ?>
                        >
                            Enregistrer
                        </button>
                        <button
                            class="button button-danger"
                            type="submit"
                            name="action"
                            value="delete"
                            data-admin-delete-current
                            formnovalidate
                            <?= $selectedReservation ? '' : 'disabled'; ?>
                        >
                            Supprimer
                        </button>
                    </div>

                    <p class="admin-edit-status" data-admin-edit-status>
                        <?= $selectedReservation
                            ? 'Réservation #' . e((string) $selectedReservation['reservation_id']) . ' chargée.'
                            : 'Aucune réservation à modifier pour le moment.'; ?>
                    </p>
                </form>
            </aside>

            <div class="admin-main-stack">
                <section class="admin-panel admin-reservations-panel" aria-labelledby="reservations-title">
                    <div class="admin-panel-header">
                        <div>
                            <h2 id="reservations-title">Réservations</h2>
                            <p>Recherche, édition et suppression</p>
                        </div>
                        <div class="admin-panel-actions">
                            <strong data-admin-count><?= count($reservations); ?> résultat<?= count($reservations) > 1 ? 's' : ''; ?></strong>
                            <a
                                class="button button-secondary"
                                href="admin.php?<?= e(http_build_query(array_filter([
                                    'search' => $search,
                                    'day' => $selectedDay,
                                    'export' => 'csv',
                                ]))); ?>"
                            >
                                Télécharger CSV
                            </a>
                        </div>
                    </div>

                    <form class="admin-search-field" method="get" action="admin.php">
                        <span>Filtrer par nom, prénom, mail, catégorie ou salle</span>
                        <input
                            type="search"
                            name="search"
                            placeholder="Ex : Rattin, Olivia, 005..."
                            value="<?= e($search); ?>"
                            data-admin-search
                        >
                        <input type="hidden" name="day" value="<?= e($selectedDay); ?>">
                        <button class="button button-secondary" type="submit">Rechercher</button>
                    </form>

                    <div class="admin-reservation-list" data-admin-reservation-list>
                        <div class="admin-reservation-row admin-reservation-head">
                            <span>Nom</span>
                            <span>Prénom</span>
                            <span>Contact</span>
                            <span>Jour</span>
                            <span>Heure</span>
                            <span>Salle</span>
                            <span>Nb</span>
                            <span>Actions</span>
                        </div>

                        <?php if (!$reservations): ?>
                            <p class="admin-empty-state">
                                Aucune réservation trouvée. Les réservations apparaîtront ici dès qu'elles seront enregistrées.
                            </p>
                        <?php endif; ?>

                        <?php foreach ($reservations as $reservation): ?>
                            <?php
                                $reservationId = (int) $reservation['reservation_id'];
                                $buffetValue = adminBoolValue($reservation['participe_buffet']);
                                $isSelected = $selectedReservation
                                    && $reservationId === (int) $selectedReservation['reservation_id'];
                            ?>
                            <article
                                class="admin-reservation-row <?= $isSelected ? 'is-selected' : ''; ?>"
                                data-admin-row
                                data-reservation-id="<?= e((string) $reservationId); ?>"
                                data-visiteur-id="<?= e((string) $reservation['visiteurs_id']); ?>"
                                data-nom="<?= e((string) $reservation['nom']); ?>"
                                data-prenom="<?= e((string) $reservation['prenom']); ?>"
                                data-contact="<?= e((string) $reservation['moyen_comm']); ?>"
                                data-categorie-id="<?= e((string) $reservation['categories_visiteur_id']); ?>"
                                data-slot-id="<?= e((string) $reservation['salle_creneaux_id']); ?>"
                                data-buffet="<?= e((string) $buffetValue); ?>"
                                data-people="<?= e((string) $reservation['nombre_personnes']); ?>"
                                data-search-text="<?= e(strtolower(implode(' ', [
                                    $reservation['nom'],
                                    $reservation['prenom'],
                                    $reservation['moyen_comm'],
                                    $reservation['categorie'],
                                    $reservation['numero_salle'],
                                    adminFormatShortDay((string) $reservation['date_jour'], (string) $reservation['nom_jour']),
                                    adminFormatTime((string) $reservation['heure_debut']),
                                ]))); ?>"
                            >
                                <strong><?= e((string) $reservation['nom']); ?></strong>
                                <strong><?= e((string) $reservation['prenom']); ?></strong>
                                <span><?= e((string) $reservation['moyen_comm']); ?></span>
                                <span><?= e(adminFormatShortDay((string) $reservation['date_jour'], (string) $reservation['nom_jour'])); ?></span>
                                <span><?= e(adminFormatTime((string) $reservation['heure_debut'])); ?></span>
                                <span><?= e((string) $reservation['numero_salle']); ?></span>
                                <span class="admin-places-count"><?= e((string) $reservation['nombre_personnes']); ?></span>
                                <span class="admin-row-actions">
                                    <button type="button" data-admin-edit>Modifier</button>
                                    <form method="post" action="admin.php" data-admin-delete-form>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="reservation_id" value="<?= e((string) $reservationId); ?>">
                                        <input type="hidden" name="search" value="<?= e($search); ?>">
                                        <input type="hidden" name="day" value="<?= e($selectedDay); ?>">
                                        <button type="submit">Suppr.</button>
                                    </form>
                                </span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="admin-panel admin-availability-panel" aria-labelledby="availability-title">
                    <div class="admin-panel-header admin-panel-header-wide">
                        <div>
                            <h2 id="availability-title">Places disponibles en temps réel</h2>
                            <p>Vue par salle et par créneau horaire</p>
                        </div>
                        <p class="admin-legend">Rouge = complet · Cyan = places disponibles</p>
                    </div>

                    <div class="admin-day-tabs" aria-label="Choix du jour affiché">
                        <?php foreach ($availabilityDays as $date => $day): ?>
                            <a
                                class="<?= $date === $selectedDay ? 'is-active' : ''; ?>"
                                href="admin.php?<?= e(http_build_query(array_filter([
                                    'search' => $search,
                                    'day' => $date,
                                ]))); ?>"
                            >
                                <?= e((string) $day['label']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div
                        class="admin-matrix"
                        role="table"
                        aria-label="Disponibilités par salle et par horaire"
                        style="grid-template-columns: 82px repeat(<?= e((string) $matrixColumnCount); ?>, 74px); min-width: <?= e((string) $matrixMinWidth); ?>px;"
                    >
                        <div class="admin-matrix-cell is-head">Salle</div>
                        <?php foreach ($availabilityTimes as $time): ?>
                            <div class="admin-matrix-cell is-head"><?= e($time); ?></div>
                        <?php endforeach; ?>

                        <?php foreach ($availabilityRooms as $roomNumber): ?>
                            <div class="admin-matrix-cell is-room"><?= e($roomNumber); ?></div>
                            <?php foreach ($availabilityTimes as $time): ?>
                                <?php
                                    $cell = $availabilityMatrix[$roomNumber][$time] ?? null;
                                    $remaining = $cell ? (int) $cell['remaining_places'] : 0;
                                    $capacity = $cell ? (int) $cell['capacite_max'] : 12;
                                ?>
                                <div class="admin-matrix-cell <?= $remaining === 0 ? 'is-full' : ''; ?>">
                                    <?= e((string) $remaining); ?>/<?= e((string) $capacity); ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="admin-panel admin-global-panel" aria-labelledby="global-title">
                    <div class="admin-panel-header">
                        <div>
                            <h2 id="global-title">Affichage global des réservations</h2>
                            <p>Répartition par salle, avec créneau le plus chargé.</p>
                        </div>
                    </div>

                    <div class="admin-summary-grid">
                        <?php foreach ($roomSummary as $summary): ?>
                            <article class="admin-summary-item">
                                <div>
                                    <strong>Salle <?= e((string) $summary['salle']); ?></strong>
                                    <span><?= e((string) $summary['total']); ?></span>
                                </div>
                                <i><b class="<?= $summary['critical'] ? 'is-critical' : ''; ?>" style="width: <?= e((string) $summary['percent']); ?>%"></b></i>
                                <p>Pic <?= e((string) $summary['salle']); ?> : <?= e((string) $summary['peak']); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </section>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
