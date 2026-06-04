<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../fonctions.php';
require __DIR__ . '/includes/donnee_salles.php';

function registrationFormatDate(string $date, string $dayName): string
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
        return trim($dayName . ' ' . $date);
    }

    return trim($dayName . ' ' . $parsedDate->format('d') . ' ' . ($months[$parsedDate->format('m')] ?? $parsedDate->format('m')));
}

function registrationFormatTime(string $time): string
{
    $parsedTime = DateTimeImmutable::createFromFormat('H:i:s', $time);

    return $parsedTime ? $parsedTime->format('H:i') : substr($time, 0, 5);
}

function registrationDayKey(string $dayName): string
{
    return strtolower(trim($dayName));
}

$roomCatalog = getRoomCatalog();
$categories = getAdminCategories($conn);
$slotsFromDatabase = getAdminSlots($conn);
$availabilityRows = getAdminAvailability($conn);

$visitDays = [];
$rooms = [];

foreach ($slotsFromDatabase as $slot) {
    $dayKey = registrationDayKey((string) $slot['nom_jour']);
    $time = registrationFormatTime((string) $slot['heure_debut']);
    $roomNumber = (string) $slot['numero_salle'];

    $visitDays[$dayKey] ??= [
        'label' => registrationFormatDate((string) $slot['date_jour'], (string) $slot['nom_jour']),
        'date' => registrationFormatDate((string) $slot['date_jour'], ''),
        'times' => [],
    ];

    if (!in_array($time, $visitDays[$dayKey]['times'], true)) {
        $visitDays[$dayKey]['times'][] = $time;
    }

    $room = $roomCatalog[$roomNumber] ?? null;
    $rooms[$roomNumber] = [
        'tp' => $room['supportLabel'] ?? '',
        'title' => $room['title'] ?? (string) $slot['nom_salle'],
    ];
}

$availability = [];

foreach ($availabilityRows as $row) {
    $dayKey = registrationDayKey((string) $row['nom_jour']);
    $time = registrationFormatTime((string) $row['heure_debut']);
    $roomNumber = (string) $row['numero_salle'];

    $availability[$dayKey . '|' . $time . '|' . $roomNumber] = (int) $row['remaining_places'];
}

$registrationData = [
    'days' => $visitDays,
    'rooms' => $rooms,
    'availability' => $availability,
];

$registrationError = null;
$editReservationId = filter_input(INPUT_POST, 'reservation_id', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_GET, 'modifier', FILTER_VALIDATE_INT);
$isEditMode = false;
$editingReservation = null;

if ($editReservationId) {
    if (!isVisitorConnected()) {
        header('Location: page_connexion.php?access=reservation');
        exit;
    }

    $editingReservation = getReservationDetailsForVisitor($conn, $editReservationId, (int) $_SESSION['visiteur_id']);

    if ($editingReservation) {
        $isEditMode = true;
    } else {
        $registrationError = 'Cette réservation est introuvable ou ne vous appartient pas.';
    }
}

$initialDayKey = $isEditMode ? registrationDayKey((string) $editingReservation['nom_jour']) : array_key_first($visitDays);
$initialTime = $isEditMode ? registrationFormatTime((string) $editingReservation['heure_debut']) : ($visitDays[$initialDayKey]['times'][0] ?? '');
$initialRoom = $isEditMode ? (string) $editingReservation['numero_salle'] : array_key_first($rooms);
$initialPeople = $isEditMode ? (int) $editingReservation['nombre_personnes'] : 1;
$initialCategoryId = $isEditMode ? (int) $editingReservation['categories_visiteur_id'] : null;
$initialBuffet = $isEditMode ? (int) $editingReservation['participe_buffet'] : 0;

if ($isEditMode) {
    $editAvailabilityKey = $initialDayKey . '|' . $initialTime . '|' . $initialRoom;
    $registrationData['availability'][$editAvailabilityKey] = getPlacesRestantesForReservationUpdate(
        $conn,
        (int) $editingReservation['salle_creneaux_id'],
        (int) $editingReservation['reservation_id']
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $formAction = (string) ($_POST['form_action'] ?? 'create');
        $prenom = trim((string) ($_POST['firstname'] ?? ''));
        $nom = trim((string) ($_POST['lastname'] ?? ''));
        $contact = strtolower(trim((string) ($_POST['contact_value'] ?? '')));
        $motDePasse = trim((string) ($_POST['password'] ?? ''));
        $categorieId = filter_input(INPUT_POST, 'visitor_type', FILTER_VALIDATE_INT);
        $participeBuffet = filter_input(INPUT_POST, 'participates_buffet', FILTER_VALIDATE_INT);
        $postedSlots = $_POST['slots'] ?? [];

        if ($formAction === 'update_reservation' && (!$editReservationId || !isVisitorConnected())) {
            throw new RuntimeException('Reconnectez-vous avant de modifier votre réservation.');
        }

        if ($prenom === '' || $nom === '' || $contact === '') {
            throw new RuntimeException('Renseignez votre prénom, votre nom et votre email.');
        }

        if (!filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Renseignez une adresse email valide.');
        }

        if ($formAction !== 'update_reservation' && $motDePasse === '') {
            throw new RuntimeException('Renseignez votre prénom, votre nom, votre email et un mot de passe.');
        }

        if ($formAction !== 'update_reservation' && strlen($motDePasse) < 4) {
            throw new RuntimeException('Le mot de passe doit contenir au moins 4 caractères.');
        }

        if ($formAction === 'update_reservation' && identifierAlreadyUsed($conn, $contact, (int) $_SESSION['visiteur_id'])) {
            throw new RuntimeException('Cet email est déjà utilisé par un autre compte.');
        }

        if ($formAction !== 'update_reservation' && identifierAlreadyUsed($conn, $contact)) {
            throw new RuntimeException('Cet email est déjà utilisé. Connectez-vous pour retrouver votre réservation.');
        }

        if (!$categorieId) {
            throw new RuntimeException('Sélectionnez votre catégorie de visiteur.');
        }

        if (!is_array($postedSlots) || count($postedSlots) === 0) {
            throw new RuntimeException('Ajoutez au moins un créneau de visite.');
        }

        $requestedSlots = [];

        foreach ($postedSlots as $slot) {
            if (!is_array($slot)) {
                continue;
            }

            $day = trim((string) ($slot['day'] ?? ''));
            $time = trim((string) ($slot['time'] ?? ''));
            $room = trim((string) ($slot['room'] ?? ''));
            $people = filter_var($slot['people'] ?? null, FILTER_VALIDATE_INT);

            if ($day === '' || $time === '' || $room === '' || !$people) {
                throw new RuntimeException('Un des créneaux est incomplet.');
            }

            if ($people < 1 || $people > 12) {
                throw new RuntimeException('Le nombre de personnes doit être compris entre 1 et 12.');
            }

            $salleCreneauxId = getSalleCreneauxIdBySelection($conn, $day, $time, $room);

            if (!$salleCreneauxId) {
                throw new RuntimeException('Un créneau sélectionné n’existe pas dans la base de données.');
            }

            $requestedSlots[$salleCreneauxId] ??= [
                'salle_creneaux_id' => $salleCreneauxId,
                'day' => $day,
                'time' => $time,
                'room' => $room,
                'people' => 0,
            ];
            $requestedSlots[$salleCreneauxId]['people'] += $people;
        }

        if ($formAction === 'update_reservation' && count($requestedSlots) !== 1) {
            throw new RuntimeException('La modification concerne une seule réservation à la fois.');
        }

        foreach ($requestedSlots as $slot) {
            $remainingPlaces = $formAction === 'update_reservation'
                ? getPlacesRestantesForReservationUpdate($conn, (int) $slot['salle_creneaux_id'], (int) $editReservationId)
                : getPlacesRestantes($conn, (int) $slot['salle_creneaux_id']);

            if ($slot['people'] > $remainingPlaces) {
                throw new RuntimeException(
                    'Il ne reste que ' . $remainingPlaces . ' place(s) pour la salle '
                    . $slot['room'] . ' à ' . $slot['time'] . '.'
                );
            }
        }

        if ($formAction === 'update_reservation') {
            $slot = array_values($requestedSlots)[0];
            $visiteurId = (int) $_SESSION['visiteur_id'];

            updateAdminReservation(
                $conn,
                (int) $editReservationId,
                $visiteurId,
                $nom,
                $prenom,
                $contact,
                (int) $categorieId,
                (int) $slot['salle_creneaux_id'],
                $participeBuffet ?? 0,
                (int) $slot['people']
            );

            $updatedReservation = getReservationDetailsById($conn, (int) $editReservationId);
            if ($updatedReservation) {
                sendReservationNotificationEmail(
                    'updated',
                    (string) $updatedReservation['prenom'],
                    (string) $updatedReservation['nom'],
                    (string) $updatedReservation['moyen_comm'],
                    [$updatedReservation],
                    $roomCatalog
                );
            }

            $_SESSION['visiteur_nom'] = trim($prenom . ' ' . $nom);
            $_SESSION['visiteur_contact'] = $contact;

            header('Location: page_connexion.php?updated=' . (int) $editReservationId);
            exit;
        }

        $conn->beginTransaction();

        try {
            $visiteurId = (int) createVisiteur(
                $conn,
                $nom,
                $prenom,
                $contact,
                $categorieId,
                $participeBuffet ?? 0,
                $motDePasse
            );

            $createdReservations = [];
            foreach ($requestedSlots as $slot) {
                $reservationId = createReservation(
                    $conn,
                    $visiteurId,
                    (int) $slot['salle_creneaux_id'],
                    (int) $slot['people']
                );

                $createdReservations[] = [
                    'id' => $reservationId,
                    'room' => $slot['room'],
                    'time' => $slot['time'],
                    'people' => $slot['people'],
                ];
            }

            $conn->commit();
        } catch (Throwable $exception) {
            $conn->rollBack();
            throw $exception;
        }

        $createdReservationDetails = [];
        foreach ($createdReservations as $createdReservation) {
            $reservationDetails = getReservationDetailsById($conn, (int) $createdReservation['id']);
            if ($reservationDetails) {
                $createdReservationDetails[] = $reservationDetails;
            }
        }

        // Envoi de l'email de confirmation.
        $emailSent = sendConfirmationEmail(
            $prenom,
            $nom,
            $contact,
            $createdReservationDetails ?: $createdReservations,
            $roomCatalog
        );

        $_SESSION['latest_reservation_ids'] = array_column($createdReservations, 'id');
        $_SESSION['latest_confirmation_visitor'] = trim($prenom . ' ' . $nom);
        $_SESSION['latest_confirmation_contact'] = $contact;
        $_SESSION['latest_confirmation_email_sent'] = $emailSent;

        header('Location: confirmation.php');
        exit;
    } catch (Throwable $exception) {
        $registrationError = $exception->getMessage();
    }
}

$pageTitle = ($isEditMode ? 'Modifier une réservation' : 'Réservation') . ' - e-llusion';
$activePage = 'reservation';
$reserveHref = 'reservation.php';
$extraScripts = ['assets/js/reservation.js'];

require __DIR__ . '/includes/header.php';
?>

    <main class="registration-page">
        <section class="registration-hero" aria-labelledby="registration-title">
            <p class="eyebrow">Réservation de visite</p>
            <h1 id="registration-title"><?= $isEditMode ? 'Modifier' : 'Réservation'; ?></h1>
            <p>
                <?= $isEditMode
                    ? 'Modifiez le créneau, la salle ou les informations liées à votre réservation.'
                    : 'Composez votre visite en choisissant un ou plusieurs créneaux. Indiquez le nombre de personnes présentes, puis choisissez votre moyen de confirmation.'; ?>
            </p>
        </section>

        <section class="registration-section" id="registration-result" aria-label="Formulaire de réservation">
            <?php if ($registrationError): ?>
                <div class="registration-feedback is-error" role="alert">
                    <?= e($registrationError); ?>
                </div>
            <?php endif; ?>

            <form
                class="registration-card"
                method="post"
                action="reservation.php<?= $isEditMode ? '?modifier=' . e((string) $editReservationId) : ''; ?>"
                data-registration-form
            >
                <input type="hidden" name="form_action" value="<?= $isEditMode ? 'update_reservation' : 'create'; ?>">
                <?php if ($isEditMode): ?>
                    <input type="hidden" name="reservation_id" value="<?= e((string) $editReservationId); ?>">
                <?php endif; ?>

                <div class="registration-card-header">
                    <div>
                        <h2><?= $isEditMode ? 'Modifier votre réservation' : 'Composer votre visite'; ?></h2>
                        <p>
                            <?= $isEditMode
                                ? 'Ajustez la réservation sélectionnée. Votre compte visiteur reste le même.'
                                : 'Sélectionnez un ou les deux jours, puis ajoutez les créneaux souhaités. La jauge indique les places restantes selon la salle, l’heure et le nombre de personnes.'; ?>
                        </p>
                    </div>
                </div>

                <fieldset class="registration-block">
                    <legend>1. Jours de visite</legend>
                    <p>Les visites sont proposées sur deux journées. Le jour exact se sélectionne dans chaque créneau à l’étape suivante.</p>
                    <div class="day-info-list">
                        <?php foreach ($visitDays as $day): ?>
                            <p>
                                <strong><?= e($day['label']); ?></strong>
                                <span><?= e($day['date']); ?></span>
                            </p>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <fieldset class="registration-block">
                    <legend>2. Créneaux et salles</legend>
                    <p>
                        <?= $isEditMode
                            ? 'Vous modifiez uniquement la réservation sélectionnée.'
                            : 'Ajoutez un créneau si vous souhaitez visiter plusieurs salles.'; ?>
                    </p>

                    <div class="slot-list" data-slot-list>
                        <article class="slot-entry" data-slot-entry>
                            <div class="slot-entry-header">
                                <h3 data-slot-title>Créneau 1</h3>
                                <button class="slot-remove" type="button" data-remove-slot>Supprimer</button>
                            </div>

                            <div class="slot-fields">
                                <label>
                                    <span>Jour</span>
                                    <select name="slots[0][day]" data-slot-day>
                                        <?php foreach ($visitDays as $dayKey => $day): ?>
                                            <option value="<?= e($dayKey); ?>" <?= $dayKey === $initialDayKey ? 'selected' : ''; ?>>
                                                <?= e($day['label']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>

                                <label>
                                    <span>Heure</span>
                                    <select name="slots[0][time]" data-slot-time>
                                        <?php foreach ($visitDays[$initialDayKey]['times'] as $time): ?>
                                            <option value="<?= e($time); ?>" <?= $time === $initialTime ? 'selected' : ''; ?>>
                                                <?= e($time); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>

                                <label>
                                    <span>Salle</span>
                                    <select name="slots[0][room]" data-slot-room>
                                        <?php foreach ($rooms as $roomNumber => $room): ?>
                                            <option value="<?= e($roomNumber); ?>" <?= $roomNumber === $initialRoom ? 'selected' : ''; ?>>
                                                Salle <?= e($roomNumber); ?> - <?= e($room['title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>

                                <label>
                                    <span>Nombre de personnes</span>
                                    <select name="slots[0][people]" data-slot-people>
                                        <?php for ($people = 1; $people <= 12; $people++): ?>
                                            <option value="<?= $people; ?>" <?= $people === $initialPeople ? 'selected' : ''; ?>>
                                                <?= $people; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </label>
                            </div>

                            <div class="slot-capacity" data-slot-capacity></div>
                        </article>
                    </div>

                    <button class="button button-secondary add-slot-button" type="button" data-add-slot <?= $isEditMode ? 'hidden' : ''; ?>>
                        Ajouter un créneau
                    </button>
                </fieldset>

                <fieldset class="registration-block visitor-block">
                    <legend>3. Informations visiteur</legend>
                    <div class="visitor-grid">
                        <label>
                            <span>Qui êtes-vous ?</span>
                            <select name="visitor_type" required>
                                <?php foreach ($categories as $category): ?>
                                    <option
                                        value="<?= e((string) $category['id']); ?>"
                                        <?= $initialCategoryId === (int) $category['id'] ? 'selected' : ''; ?>
                                    >
                                        <?= e((string) $category['libelle']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label>
                            <span>Prénom</span>
                            <input
                                type="text"
                                name="firstname"
                                autocomplete="given-name"
                                required
                                value="<?= e((string) ($editingReservation['prenom'] ?? '')); ?>"
                            >
                        </label>

                        <label>
                            <span>Nom</span>
                            <input
                                type="text"
                                name="lastname"
                                autocomplete="family-name"
                                required
                                value="<?= e((string) ($editingReservation['nom'] ?? '')); ?>"
                            >
                        </label>

                        <label>
                            <span>Email</span>
                            <input
                                type="email"
                                name="contact_value"
                                autocomplete="email"
                                placeholder="prenom.nom@email.fr"
                                required
                                value="<?= e((string) ($editingReservation['moyen_comm'] ?? '')); ?>"
                            >
                        </label>

                        <?php if (!$isEditMode): ?>
                            <div class="password-field">
                                <label for="reservation-password">Mot de passe</label>
                                <span class="password-input-wrap">
                                    <input
                                        id="reservation-password"
                                        type="password"
                                        name="password"
                                        autocomplete="new-password"
                                        minlength="4"
                                        required
                                        data-password-input
                                    >
                                    <button
                                        class="password-toggle"
                                        type="button"
                                        aria-label="Afficher le mot de passe"
                                        aria-pressed="false"
                                        data-password-toggle
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                            <path class="password-eye-slash" d="M4 4l16 16"></path>
                                        </svg>
                                    </button>
                                </span>
                            </div>
                        <?php endif; ?>

                        <p class="visitor-grid-note">
                            <?= $isEditMode
                                ? 'Vous êtes connecté·e : la modification garde votre compte existant, sans recréer de mot de passe.'
                                : 'Votre email servira d’identifiant de connexion. Le mot de passe choisi ici servira à vous reconnecter pour consulter votre réservation.'; ?>
                        </p>
                    </div>
                </fieldset>

                <fieldset class="registration-block buffet-block">
                    <legend>4. Buffet</legend>
                    <p>Indiquez si vous serez présent·e au buffet organisé le jeudi à 19h.</p>
                    <div class="buffet-choice-grid">
                        <label class="buffet-choice">
                            <input type="radio" name="participates_buffet" value="1" <?= $initialBuffet === 1 ? 'checked' : ''; ?>>
                            <span class="red-dot"></span>
                            <span>
                                <strong>Oui, je serai présent·e</strong>
                                <small>Prévoir une place pour le buffet.</small>
                            </span>
                        </label>

                        <label class="buffet-choice">
                            <input type="radio" name="participates_buffet" value="0" <?= $initialBuffet === 0 ? 'checked' : ''; ?>>
                            <span class="red-dot"></span>
                            <span>
                                <strong>Non, je ne participe pas</strong>
                                <small>Réservation uniquement pour la visite.</small>
                            </span>
                        </label>
                    </div>
                </fieldset>

                <div class="registration-footer">
                    <p data-form-feedback>
                        Les places restantes se recalculent selon le jour, l’heure et la salle.
                    </p>
                    <button class="button button-primary" type="submit" data-submit-registration>
                        <?= $isEditMode ? 'Enregistrer les modifications' : 'Confirmer la réservation'; ?>
                    </button>
                </div>
            </form>
        </section>

        <script id="registration-data" type="application/json">
            <?= json_encode($registrationData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE); ?>
        </script>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
