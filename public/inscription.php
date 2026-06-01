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
            <p class="eyebrow">Réservation de visite</p>
            <h1 id="registration-title">Inscription</h1>
            <p>
                Composez votre visite en choisissant un ou plusieurs créneaux.
                Indiquez le nombre de personnes présentes, puis choisissez votre moyen de confirmation.
            </p>
        </section>

        <section class="registration-section" aria-label="Formulaire d'inscription">
            <form class="registration-card" data-registration-form>
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
                                <option value="enseignant">Enseignant</option>
                                <option value="personnel_usmb">Personnel de l’USMB</option>
                                <option value="visiteur_exterieur">Visiteur extérieur</option>
                                <option value="professionnel_partenaire">Professionnel / partenaire</option>
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
                    <button class="button button-primary" type="submit">Confirmer l’inscription</button>
                </div>
            </form>
        </section>

        <script id="registration-data" type="application/json">
            <?= json_encode($registrationData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE); ?>
        </script>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
