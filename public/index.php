<?php

declare(strict_types=1);

$slides = [
    [
        'image' => 'assets/images/carousel-expo-1.jpg',
        'alt' => 'Salle immersive avec projections inspirées de La Nuit étoilée',
    ],
    [
        'image' => 'assets/images/carousel-expo-2.jpg',
        'alt' => 'Installation lumineuse immersive avec projections colorées',
    ],
    [
        'image' => 'assets/images/carousel-expo-3.jpg',
        'alt' => 'Exposition immersive avec grandes fresques projetées',
    ],
    [
        'image' => 'assets/images/carousel-expo-4.webp',
        'alt' => 'Parcours de musée futuriste avec projections au sol',
    ],
];

require __DIR__ . '/includes/donnee_salles.php';

$days = [
    [
        'label' => 'Jeudi',
        'date' => '18 juin 2026',
        'times' => ['15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '19:00', '19:30', '20:00'],
    ],
    [
        'label' => 'Vendredi',
        'date' => '19 juin 2026',
        'times' => ['09:30', '10:00', '10:30', '11:00'],
    ],
];

$rooms = getHomeRoomCards();

$pageTitle = 'e-llusion - Exposition MMI';
$activePage = 'accueil';
$reserveHref = 'reservation.php';
$extraScripts = ['assets/js/home.js'];

require __DIR__ . '/includes/header.php';
?>

    <main>
        <section class="hero" aria-labelledby="hero-title">
            <div class="hero-frame">
                <div class="carousel" data-carousel>
                    <?php foreach ($slides as $index => $slide): ?>
                        <img
                            class="carousel-slide <?= $index === 0 ? 'is-visible' : ''; ?>"
                            src="<?= e($slide['image']); ?>"
                            alt="<?= e($slide['alt']); ?>"
                        >
                    <?php endforeach; ?>
                    <div class="carousel-overlay"></div>
                </div>

                <div class="hero-content">
                    <p class="eyebrow">Exposition interactive MMI</p>
                    <h1 id="hero-title">E-llusion</h1>
                    <p>
                        Un parcours d’œuvres multimédia interactives où les perceptions
                        se déforment, se déclenchent et se partagent.
                    </p>
                    <div class="hero-buttons">
                        <a class="button button-primary" href="#reservation">Choisir un créneau</a>
                        <a class="button button-secondary" href="#salles">Explorer les salles</a>
                    </div>
                    <p class="highlight">Vernissage jeudi 18 juin, 19h</p>
                </div>

                <div class="carousel-dots" aria-label="Navigation du carrousel">
                    <?php foreach ($slides as $index => $slide): ?>
                        <button
                            class="<?= $index === 0 ? 'is-active' : ''; ?>"
                            type="button"
                            data-slide-button="<?= $index; ?>"
                            aria-label="Afficher l’image <?= $index + 1; ?>"
                        ></button>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="visit-section" id="creneaux" aria-labelledby="visit-title">
            <div class="section-heading">
                <h2 id="visit-title">Créneaux de visite</h2>
                <p>Chaque créneau est limité à 12 visiteurs par salle. Le formulaire permet d’indiquer le nombre de personnes présentes.</p>
            </div>

            <div class="capacity-banner">
                Places limitées, pensez à réserver votre visite !
            </div>

            <div class="day-grid">
                <?php foreach ($days as $day): ?>
                    <article class="day-card">
                        <h3><?= e($day['label']); ?></h3>
                        <p><?= e($day['date']); ?></p>
                        <div class="time-list">
                            <?php foreach ($day['times'] as $time): ?>
                                <span><?= e($time); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="rooms-section" id="salles" aria-labelledby="rooms-title">
            <div class="section-heading section-heading-dark">
                <h2 id="rooms-title">4 salles, 4 expériences</h2>
                <p>Chaque salle possède son concept, son contenu et sa jauge de réservation.</p>
            </div>

            <div class="room-grid">
                <?php foreach ($rooms as $room): ?>
                    <article class="room-card">
                        <div class="room-title">
                            <span class="red-dot"></span>
                            <h3>Salle <?= e($room['number']); ?></h3>
                        </div>
                        <span class="room-badge"><?= e($room['badge']); ?></span>
                        <h4><?= e($room['title']); ?></h4>
                        <p><?= e($room['description']); ?></p>
                        <a href="<?= e($room['href']); ?>">Voir la salle</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="reservation-preview" id="reservation" aria-labelledby="reservation-title">
            <div>
                <h2 id="reservation-title">Composer votre visite</h2>
                <p>
                    Ajoutez un créneau, choisissez le jour, l’heure et la salle.
                    Vous pouvez réserver une ou plusieurs places selon les disponibilités.
                </p>
            </div>
            <a class="button button-primary" href="reservation.php">Commencer la réservation</a>
        </section>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
