<?php

declare(strict_types=1);

$roomNumbers = getRoomNumbers();
$currentRoomIndex = array_search($room['number'], $roomNumbers, true);
$previousRoom = $currentRoomIndex !== false && $currentRoomIndex > 0 ? getRoomByNumber($roomNumbers[$currentRoomIndex - 1]) : null;
$nextRoom = $currentRoomIndex !== false && $currentRoomIndex < count($roomNumbers) - 1 ? getRoomByNumber($roomNumbers[$currentRoomIndex + 1]) : null;
?>

    <main class="room-page">
        <section class="room-hero" aria-labelledby="room-title">
            <div class="room-hero-copy">
                <p class="eyebrow">Salle <?= e($room['number']); ?></p>
                <h1 id="room-title"><?= e($room['title']); ?></h1>
                <p class="room-support"><?= e($room['supportLabel']); ?></p>
                <p class="room-summary"><?= e($room['summary']); ?></p>

                <div class="hero-buttons">
                    <a class="button button-primary" href="#oeuvres">Voir les œuvres</a>
                    <a class="button button-secondary" href="index.php#salles">Retour aux salles</a>
                </div>

                <div class="room-meta-grid">
                    <article class="room-meta-card">
                        <span>Question directrice</span>
                        <strong><?= e($room['question']); ?></strong>
                    </article>

                    <article class="room-meta-card">
                        <span>Fil rouge</span>
                        <strong><?= e($room['focus']); ?></strong>
                    </article>
                </div>
            </div>

            <div class="room-hero-visual">
                <img src="<?= e($room['heroImage']); ?>" alt="Illustration de la salle <?= e($room['number']); ?> - <?= e($room['title']); ?>">
                <div class="room-hero-overlay"></div>
            </div>
        </section>

        <section class="room-toolbar" aria-label="Navigation de la salle">
            <div class="room-toolbar-group">
                <span class="room-toolbar-label">Sections</span>
                <nav class="room-section-nav" aria-label="Sections de la salle">
                    <a href="#concept" data-room-nav-link>Concept</a>
                    <a href="#oeuvres" data-room-nav-link>Œuvres</a>
                    <a href="#parcours" data-room-nav-link>Parcours</a>
                </nav>
            </div>

            <div class="room-toolbar-group">
                <span class="room-toolbar-label">Autres salles</span>
                <div class="room-switcher" aria-label="Autres salles du parcours">
                    <?php foreach ($roomNumbers as $roomNumber): ?>
                        <?php $roomLink = getRoomByNumber($roomNumber); ?>
                        <a
                            class="<?= $roomNumber === $room['number'] ? 'is-current' : ''; ?>"
                            href="<?= e($roomLink['slug']); ?>"
                        >
                            Salle <?= e($roomNumber); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="room-content">
            <article class="room-panel" id="concept" data-room-section>
                <div class="section-heading">
                    <h2>Concept de la salle</h2>
                    <p><?= e($room['concept']); ?></p>
                </div>
            </article>

            <section class="room-panel room-panel-dark" id="oeuvres" data-room-section>
                <div class="section-heading section-heading-dark">
                    <h2>Les œuvres</h2>
                </div>

                <div class="room-works-grid">
                    <?php foreach ($room['works'] as $work): ?>
                        <article class="room-work-card">
                            <div class="room-work-header">
                                <span class="red-dot"></span>
                                <div>
                                    <p class="room-work-group"><?= e($work['group']); ?></p>
                                    <h3><?= e($work['title']); ?></h3>
                                </div>
                            </div>

                            <?php foreach (preg_split('/\R{2,}/', trim($work['description'])) as $paragraph): ?>
                                <?php if ($paragraph !== ''): ?>
                                    <p><?= e($paragraph); ?></p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="room-panel room-panel-end" id="parcours" data-room-section>
                <div class="room-endcard">
                    <div class="room-endcard-copy">
                        <p class="eyebrow">Parcours</p>
                        <h2>Continuez la visite</h2>
                        <p>
                            Passez à la salle suivante, revenez à la précédente ou retrouvez
                            toutes les salles sur la page d’accueil.
                        </p>
                    </div>

                    <div class="room-pager">
                        <?php if ($previousRoom): ?>
                            <a class="button button-secondary" href="<?= e($previousRoom['slug']); ?>">Salle précédente</a>
                        <?php endif; ?>

                        <a class="button button-primary" href="index.php#salles">Accueil des salles</a>

                        <?php if ($nextRoom): ?>
                            <a class="button button-secondary" href="<?= e($nextRoom['slug']); ?>">Salle suivante</a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </section>
    </main>
