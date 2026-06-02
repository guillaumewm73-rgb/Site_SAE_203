<?php

declare(strict_types=1);

$pageTitle = 'Administration - e-llusion';
$activePage = 'admin';
$bodyClass = 'admin-page';
$reserveHref = 'inscription.php';
$extraScripts = ['assets/js/admin.js'];

$reservations = [
    [
        'id' => '1042',
        'nom' => 'Martin',
        'prenom' => 'Alice',
        'contact' => 'alice.martin@email.fr',
        'jour' => 'J18',
        'heure' => '15:00',
        'salle' => '001',
        'places' => '2',
        'buffet' => 'Oui',
        'alerte' => false,
    ],
    [
        'id' => '1043',
        'nom' => 'Bernard',
        'prenom' => 'Lucas',
        'contact' => 'l.bernard@email.fr',
        'jour' => 'J18',
        'heure' => '16:30',
        'salle' => '002',
        'places' => '4',
        'buffet' => 'Non',
        'alerte' => true,
    ],
    [
        'id' => '1044',
        'nom' => 'Rattin',
        'prenom' => 'Olivia',
        'contact' => 'olivia.rattin@email.fr',
        'jour' => 'V19',
        'heure' => '09:30',
        'salle' => '005',
        'places' => '1',
        'buffet' => 'Non',
        'alerte' => false,
    ],
    [
        'id' => '1045',
        'nom' => 'Moulin',
        'prenom' => 'Guillaume',
        'contact' => 'guillaume@email.fr',
        'jour' => 'V19',
        'heure' => '10:00',
        'salle' => '021',
        'places' => '3',
        'buffet' => 'Non',
        'alerte' => false,
    ],
];

$availabilityTimes = ['15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '19:00', '19:30', '20:00'];
$availability = [
    '001' => [10, 8, 7, 6, 12, 11, 9, 5, 4, 6],
    '002' => [12, 11, 9, 0, 3, 8, 10, 7, 6, 5],
    '005' => [6, 9, 10, 12, 12, 7, 8, 6, 5, 2],
    '021' => [9, 10, 11, 8, 7, 6, 5, 4, 3, 2],
];

$roomSummary = [
    ['salle' => '001', 'total' => 18, 'peak' => '17h', 'percent' => 38, 'critical' => false],
    ['salle' => '002', 'total' => 24, 'peak' => '16h30', 'percent' => 50, 'critical' => true],
    ['salle' => '005', 'total' => 16, 'peak' => '15h30', 'percent' => 34, 'critical' => false],
    ['salle' => '021', 'total' => 14, 'peak' => '15h', 'percent' => 30, 'critical' => false],
];

require __DIR__ . '/includes/header.php';
?>

    <main class="admin-interface">
        <section class="admin-hero" aria-labelledby="admin-title">
            <p class="eyebrow">Interface administratrice</p>
            <h1 id="admin-title">Administration</h1>
            <p>
                Suivez les réservations, filtrez les inscriptions et visualisez les places
                disponibles par salle et par créneau.
            </p>
        </section>

        <section class="admin-section admin-layout" aria-label="Tableau de bord administrateur">
            <aside class="admin-edit-card" aria-label="Panneau d'édition">
                <div>
                    <h2>Éditer une réservation</h2>
                    <p>Sélectionnez une ligne puis modifiez l’horaire, la salle ou le nombre de places.</p>
                </div>

                <form class="admin-edit-form" data-admin-edit-form>
                    <label>
                        <span>Nom</span>
                        <input type="text" name="nom" value="Martin">
                    </label>

                    <label>
                        <span>Prénom</span>
                        <input type="text" name="prenom" value="Alice">
                    </label>

                    <label>
                        <span>Jour</span>
                        <select name="jour">
                            <option value="J18">Jeudi 18 juin</option>
                            <option value="V19">Vendredi 19 juin</option>
                        </select>
                    </label>

                    <label>
                        <span>Heure</span>
                        <select name="heure">
                            <?php foreach ($availabilityTimes as $time): ?>
                                <option value="<?= e($time); ?>"><?= e($time); ?></option>
                            <?php endforeach; ?>
                            <option value="09:30">09:30</option>
                            <option value="10:00">10:00</option>
                        </select>
                    </label>

                    <label>
                        <span>Salle</span>
                        <select name="salle">
                            <option value="001">Salle 001</option>
                            <option value="002">Salle 002</option>
                            <option value="005">Salle 005</option>
                            <option value="021">Salle 021</option>
                        </select>
                    </label>

                    <label>
                        <span>Nombre de places</span>
                        <input type="number" name="places" min="1" max="12" value="2">
                    </label>

                    <div class="admin-edit-actions">
                        <button class="button button-primary" type="submit">Enregistrer</button>
                        <button class="button button-danger" type="button" data-admin-delete-current>Supprimer</button>
                    </div>

                    <p class="admin-edit-status" data-admin-edit-status>
                        Modification prête. Les actions seront reliées à la BDD ensuite.
                    </p>
                </form>
            </aside>

            <div class="admin-main-stack">
                <section class="admin-panel admin-reservations-panel" aria-labelledby="reservations-title">
                    <div class="admin-panel-header">
                        <div>
                            <h2 id="reservations-title">Inscriptions</h2>
                            <p>Recherche, édition et suppression</p>
                        </div>
                        <strong data-admin-count><?= count($reservations); ?> résultats</strong>
                    </div>

                    <label class="admin-search-field">
                        <span>Filtrer par nom, prénom, mail ou salle</span>
                        <input type="search" placeholder="Ex : Rattin, Olivia, 005..." data-admin-search>
                    </label>

                    <div class="admin-reservation-list" data-admin-reservation-list>
                        <div class="admin-reservation-row admin-reservation-head">
                            <span>Nom</span>
                            <span>Prénom</span>
                            <span>Mail</span>
                            <span>Jour</span>
                            <span>Heure</span>
                            <span>Salle</span>
                            <span>Nb</span>
                            <span>Actions</span>
                        </div>

                        <?php foreach ($reservations as $reservation): ?>
                            <article
                                class="admin-reservation-row <?= $reservation['alerte'] ? 'is-alert' : ''; ?>"
                                data-admin-row
                                data-nom="<?= e($reservation['nom']); ?>"
                                data-prenom="<?= e($reservation['prenom']); ?>"
                                data-contact="<?= e($reservation['contact']); ?>"
                                data-jour="<?= e($reservation['jour']); ?>"
                                data-heure="<?= e($reservation['heure']); ?>"
                                data-salle="<?= e($reservation['salle']); ?>"
                                data-places="<?= e($reservation['places']); ?>"
                            >
                                <strong><?= e($reservation['nom']); ?></strong>
                                <strong><?= e($reservation['prenom']); ?></strong>
                                <span><?= e($reservation['contact']); ?></span>
                                <span><?= e($reservation['jour']); ?></span>
                                <span><?= e($reservation['heure']); ?></span>
                                <span><?= e($reservation['salle']); ?></span>
                                <span class="admin-places-count"><?= e($reservation['places']); ?></span>
                                <span class="admin-row-actions">
                                    <button type="button" data-admin-edit>Modifier</button>
                                    <button type="button" data-admin-delete>Suppr.</button>
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
                        <button class="is-active" type="button">Jeudi 18 juin</button>
                        <button type="button">Vendredi 19 juin</button>
                    </div>

                    <div class="admin-matrix" role="table" aria-label="Disponibilités par salle et par horaire">
                        <div class="admin-matrix-cell is-head">Salle</div>
                        <?php foreach ($availabilityTimes as $time): ?>
                            <div class="admin-matrix-cell is-head"><?= e($time); ?></div>
                        <?php endforeach; ?>

                        <?php foreach ($availability as $roomNumber => $values): ?>
                            <div class="admin-matrix-cell is-room"><?= e($roomNumber); ?></div>
                            <?php foreach ($values as $value): ?>
                                <div class="admin-matrix-cell <?= $value === 0 ? 'is-full' : ''; ?>">
                                    <?= e((string) $value); ?>/12
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
                                    <strong>Salle <?= e($summary['salle']); ?></strong>
                                    <span><?= e((string) $summary['total']); ?></span>
                                </div>
                                <i><b class="<?= $summary['critical'] ? 'is-critical' : ''; ?>" style="width: <?= e((string) $summary['percent']); ?>%"></b></i>
                                <p>Pic <?= e($summary['salle']); ?> : <?= e($summary['peak']); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </section>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
