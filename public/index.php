<?php

declare(strict_types=1);

$slides = [
    [
        'image' => 'assets/images/carousel-1.svg',
        'alt' => 'Image de substitution pour le carrousel e-llusion',
    ],
    [
        'image' => 'assets/images/carousel-2.svg',
        'alt' => 'Deuxième image de substitution pour le carrousel e-llusion',
    ],
    [
        'image' => 'assets/images/carousel-3.svg',
        'alt' => 'Troisième image de substitution pour le carrousel e-llusion',
    ],
    [
        'image' => 'assets/images/carousel-4.svg',
        'alt' => 'Quatrième image de substitution pour le carrousel e-llusion',
    ],
];

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

$rooms = [
    [
        'number' => '001',
        'tp' => 'TP12',
        'title' => 'Miroirs numériques',
        'description' => 'Un dédale de reflets, d’écrans et de perceptions qui se déforment.',
    ],
    [
        'number' => '002',
        'tp' => 'TP21',
        'title' => 'Société parfaite ?',
        'description' => 'Un parcours qui questionne l’image sociale, le contrôle et la mise en scène.',
    ],
    [
        'number' => '005',
        'tp' => 'TP22',
        'title' => 'Présences augmentées',
        'description' => 'Des présences invisibles apparaissent selon les actions du public.',
    ],
    [
        'number' => '021',
        'tp' => 'TP11',
        'title' => 'Identités numériques',
        'description' => 'Une réflexion sur le regard des autres et la validation sociale.',
    ],
];

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>e-llusion - Exposition MMI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/home.js" defer></script>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="Retour à l'accueil">
            <span class="brand-line"></span>
            <span>e-llusion</span>
        </a>

        <nav class="main-nav" aria-label="Navigation principale">
            <a class="is-active" href="index.php">Accueil</a>
            <a href="#salles">Salles</a>
            <a href="#creneaux">Créneaux</a>
            <a href="#contact">Contact</a>
        </nav>

        <div class="header-actions">
            <span>18 &amp; 19 juin 2026</span>
            <a class="button button-primary" href="#reservation">Réserver</a>
        </div>
    </header>

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
                    <h1 id="hero-title">e-llusion</h1>
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
                <p>Chaque créneau est limité à 12 visiteurs par salle. Le visiteur s’inscrit uniquement pour lui-même.</p>
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
                        <span class="room-badge"><?= e($room['tp']); ?></span>
                        <h4><?= e($room['title']); ?></h4>
                        <p><?= e($room['description']); ?></p>
                        <a href="#reservation">Voir la salle</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="reservation-preview" id="reservation" aria-labelledby="reservation-title">
            <div>
                <h2 id="reservation-title">Composer votre visite</h2>
                <p>
                    Ajoutez un créneau, choisissez le jour, l’heure et la salle.
                    L’inscription reste individuelle : 1 formulaire = 1 visiteur.
                </p>
            </div>
            <a class="button button-primary" href="inscription.php">Commencer l’inscription</a>
        </section>
    </main>

    <footer class="site-footer" id="contact">
        <div>
            <h2>e-llusion</h2>
            <p>Exposition d’œuvres multimédia interactives des étudiant·es MMI.</p>
            <p>28 Av du Lac d’Annecy, 73370 Le Bourget-du-Lac</p>
        </div>
        <div class="footer-links">
            <a href="https://www.instagram.com/mmichambery/" target="_blank" rel="noreferrer">Instagram MMI</a>
            <a href="https://mmi.univ-smb.fr/" target="_blank" rel="noreferrer">Site web MMI</a>
            <a href="mailto:mmi-chambery@univ-smb.fr">mmi-chambery@univ-smb.fr</a>
        </div>
    </footer>
</body>
</html>
