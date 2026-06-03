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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $prenom = trim((string) ($_POST['firstname'] ?? ''));
        $nom = trim((string) ($_POST['lastname'] ?? ''));
        $contact = trim((string) ($_POST['contact_value'] ?? ''));
        $motDePasse = trim((string) ($_POST['password'] ?? ''));
        $categorieId = filter_input(INPUT_POST, 'visitor_type', FILTER_VALIDATE_INT);
        $participeBuffet = filter_input(INPUT_POST, 'participates_buffet', FILTER_VALIDATE_INT);
        $postedSlots = $_POST['slots'] ?? [];

        if ($prenom === '' || $nom === '' || $contact === '' || $motDePasse === '') {
            throw new RuntimeException('Renseignez votre prénom, votre nom, un email ou téléphone et un mot de passe.');
        }

        if (strlen($motDePasse) < 4) {
            throw new RuntimeException('Le mot de passe doit contenir au moins 4 caractères.');
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

        foreach ($requestedSlots as $slot) {
            $remainingPlaces = getPlacesRestantes($conn, (int) $slot['salle_creneaux_id']);

            if ($slot['people'] > $remainingPlaces) {
                throw new RuntimeException(
                    'Il ne reste que ' . $remainingPlaces . ' place(s) pour la salle '
                    . $slot['room'] . ' à ' . $slot['time'] . '.'
                );
            }
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

        // Envoi de l'email de confirmation
        $emailSent = sendConfirmationEmail(
            $prenom,
            $nom,
            $contact,
            $createdReservations,
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

$pageTitle = 'Inscription - e-llusion';
$activePage = 'inscription';
$reserveHref = 'inscription.php';
$extraScripts = ['assets/js/inscription.js'];

require __DIR__ . '/includes/header.php';
?>

    <main class="registration-page">
        <section class="registration-hero" aria-labelledby="registration-title">
            <p class="eyebrow">Réservation de visite</p>
            <h1 id="registration-title">Inscription</h1>
            <p>
                Composez votre visite en choisissant un ou plusieurs créneaux.
                Indiquez le nombre de personnes présentes, puis choisissez votre moyen de confirmation.
            </p>
        </section>

        <section class="registration-section" id="registration-result" aria-label="Formulaire d'inscription">
            <?php if ($registrationError): ?>
                <div class="registration-feedback is-error" role="alert">
                    <?= e($registrationError); ?>
                </div>
            <?php endif; ?>

            <form class="registration-card" method="post" action="inscription.php" data-registration-form>
                <div class="registration-card-header">
                    <div>
                        <h2>Composer votre visite</h2>
                        <p>
                            Sélectionnez un ou les deux jours, puis ajoutez les créneaux souhaités.
                            La jauge indique les places restantes selon la salle, l’heure et le nombre de personnes.
                        </p>
                    </div>
                    <aside class="registration-alert">
                        <strong>12 places maximum</strong>
                        <span>par salle et par créneau</span>
                    </aside>
                </div>

                <fieldset class="registration-block">
                    <legend>1. Jours de visite</legend>
                    <p>Les visites sont proposées sur les deux journées suivantes. Le choix du jour se fait ensuite dans chaque créneau.</p>
                    <div class="day-choice-grid">
                        <?php foreach ($visitDays as $day): ?>
                            <article class="day-choice day-choice-info">
                                <span class="red-dot"></span>
                                <span>
                                    <strong><?= e($day['label']); ?></strong>
                                    <small><?= e($day['date']); ?></small>
                                </span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <fieldset class="registration-block">
                    <legend>2. Créneaux et salles</legend>
                    <p>Ajoutez un créneau si vous souhaitez visiter plusieurs salles.</p>

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
                                            <option value="<?= e($dayKey); ?>"><?= e($day['label']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>

                                <label>
                                    <span>Heure</span>
                                    <select name="slots[0][time]" data-slot-time>
                                        <?php foreach ($visitDays['jeudi']['times'] as $time): ?>
                                            <option value="<?= e($time); ?>"><?= e($time); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>

                                <label>
                                    <span>Salle</span>
                                    <select name="slots[0][room]" data-slot-room>
                                        <?php foreach ($rooms as $roomNumber => $room): ?>
                                            <option value="<?= e($roomNumber); ?>">
                                                Salle <?= e($roomNumber); ?> - <?= e($room['title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>

                                <label>
                                    <span>Nombre de personnes</span>
                                    <select name="slots[0][people]" data-slot-people>
                                        <?php for ($people = 1; $people <= 12; $people++): ?>
                                            <option value="<?= $people; ?>"><?= $people; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </label>
                            </div>

                            <div class="slot-capacity" data-slot-capacity></div>
                        </article>
                    </div>

                    <button class="button button-secondary add-slot-button" type="button" data-add-slot>
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
                                    <option value="<?= e((string) $category['id']); ?>">
                                        <?= e((string) $category['libelle']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label>
                            <span>Prénom</span>
                            <input type="text" name="firstname" autocomplete="given-name" required>
                        </label>

                        <label>
                            <span>Nom</span>
                            <input type="text" name="lastname" autocomplete="family-name" required>
                        </label>

                        <label>
                            <span>Email ou téléphone</span>
                            <input
                                type="text"
                                name="contact_value"
                                autocomplete="email tel"
                                placeholder="prenom.nom@email.fr ou 06 00 00 00 00"
                                required
                            >
                        </label>

                        <label>
                            <span>Mot de passe</span>
                            <input
                                type="password"
                                name="password"
                                autocomplete="new-password"
                                minlength="4"
                                required
                            >
                        </label>

                        <p class="visitor-grid-note">
                            Votre email ou téléphone servira d’identifiant de connexion.
                            Le mot de passe choisi ici servira à vous reconnecter pour consulter votre réservation.
                        </p>
                    </div>
                </fieldset>

                <fieldset class="registration-block buffet-block">
                    <legend>4. Buffet</legend>
                    <p>Indiquez si vous serez présent·e au buffet organisé le jeudi à 19h.</p>
                    <div class="buffet-choice-grid">
                        <label class="buffet-choice">
                            <input type="radio" name="participates_buffet" value="1">
                            <span class="red-dot"></span>
                            <span>
                                <strong>Oui, je serai présent·e</strong>
                                <small>Prévoir une place pour le buffet.</small>
                            </span>
                        </label>

                        <label class="buffet-choice">
                            <input type="radio" name="participates_buffet" value="0" checked>
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
                        Confirmer l’inscription
                    </button>
                </div>
            </form>
        </section>

        <script id="registration-data" type="application/json">
            <?= json_encode($registrationData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE); ?>
        </script>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
