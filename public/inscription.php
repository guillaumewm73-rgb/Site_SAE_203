<?php

declare(strict_types=1);

$visitDays = [
    'jeudi' => [
        'label' => 'Jeudi 18 juin',
        'date' => '18 juin 2026',
        'times' => ['15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '19:00', '19:30', '20:00'],
    ],
    'vendredi' => [
        'label' => 'Vendredi 19 juin',
        'date' => '19 juin 2026',
        'times' => ['09:30', '10:00', '10:30', '11:00'],
    ],
];

$rooms = [
    '001' => [
        'tp' => 'TP12',
        'title' => 'Miroirs numériques',
    ],
    '002' => [
        'tp' => 'TP21',
        'title' => 'Société parfaite ?',
    ],
    '005' => [
        'tp' => 'TP22',
        'title' => 'Présences augmentées',
    ],
    '021' => [
        'tp' => 'TP11',
        'title' => 'Identités numériques',
    ],
];

$availability = [];

foreach (array_keys($visitDays) as $dayIndex => $dayKey) {
    foreach ($visitDays[$dayKey]['times'] as $timeIndex => $time) {
        foreach (array_keys($rooms) as $roomIndex => $roomNumber) {
            $usedPlaces = ($dayIndex * 3 + $timeIndex * 2 + $roomIndex) % 8;
            $availability[$dayKey . '|' . $time . '|' . $roomNumber] = 12 - $usedPlaces;
        }
    }
}

$registrationData = [
    'days' => $visitDays,
    'rooms' => $rooms,
    'availability' => $availability,
];

$pageTitle = 'Inscription - e-llusion';
$activePage = 'inscription';
$reserveHref = 'inscription.php';
$extraScripts = ['assets/js/inscription.js'];

require __DIR__ . '/includes/header.php';
?>

    <main class="registration-page">
        <section class="registration-hero" aria-labelledby="registration-title">
            <p class="eyebrow">Réservation individuelle</p>
            <h1 id="registration-title">Inscription</h1>
            <p>
                Composez votre visite en choisissant un ou plusieurs créneaux.
                L’inscription reste individuelle : chaque visiteur remplit son propre formulaire.
            </p>
        </section>

        <section class="registration-section" aria-label="Formulaire d'inscription">
            <form class="registration-card" data-registration-form>
                <div class="registration-card-header">
                    <div>
                        <h2>Composer votre visite</h2>
                        <p>
                            Sélectionnez un ou les deux jours, puis ajoutez les créneaux souhaités.
                            La jauge indique les places restantes pour chaque salle.
                        </p>
                    </div>
                    <aside class="registration-alert">
                        <strong>12 places maximum</strong>
                        <span>par salle et par créneau</span>
                    </aside>
                </div>

                <fieldset class="registration-block">
                    <legend>1. Jours de visite</legend>
                    <p>Sélectionnez un jour ou les deux selon votre parcours.</p>
                    <div class="day-choice-grid">
                        <?php foreach ($visitDays as $dayKey => $day): ?>
                            <label class="day-choice">
                                <input
                                    type="checkbox"
                                    name="selected_days[]"
                                    value="<?= e($dayKey); ?>"
                                    data-day-toggle
                                    <?= $dayKey === 'jeudi' ? 'checked' : ''; ?>
                                >
                                <span class="red-dot"></span>
                                <span>
                                    <strong><?= e($day['label']); ?></strong>
                                    <small><?= e($day['date']); ?></small>
                                </span>
                            </label>
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
                                <option value="visiteur">Visiteur·se</option>
                                <option value="etudiant">Étudiant·e MMI</option>
                                <option value="personnel">Personnel IUT</option>
                                <option value="autre">Autre</option>
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
                            <span>Email de confirmation</span>
                            <input type="email" name="email" autocomplete="email" required>
                        </label>

                        <label>
                            <span>Téléphone</span>
                            <input type="tel" name="phone" autocomplete="tel" placeholder="Optionnel">
                        </label>
                    </div>
                </fieldset>

                <div class="registration-footer">
                    <p data-form-feedback>
                        Les places restantes se recalculent selon le jour, l’heure et la salle.
                    </p>
                    <button class="button button-primary" type="submit">Confirmer l’inscription</button>
                </div>
            </form>
        </section>

        <script id="registration-data" type="application/json">
            <?= json_encode($registrationData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE); ?>
        </script>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
