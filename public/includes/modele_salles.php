<?php

declare(strict_types=1);
?>

    <main class="room-page">
        <section class="room-hero" aria-labelledby="room-title">
            <div class="room-hero-copy">
                <p class="eyebrow">Salle <?= e($room['number']); ?></p>
                <h1 id="room-title"><?= e($room['title']); ?></h1>
                <p class="room-support"><?= e($room['supportLabel']); ?></p>
                <p class="room-summary"><?= e($room['summary']); ?></p>

                <div class="room-meta-grid">
                    <article class="room-meta-card">
                        <span>Question directrice</span>
                        <strong><?= e($room['question']); ?></strong>
                    </article>

                    <article class="room-meta-card">
                        <span>Fil rouge</span>
                        <strong><?= e($room['focus']); ?></strong>
                    </article>

                    <article class="room-meta-card">
                        <span><?= e($room['referent']['label']); ?></span>
                        <strong><?= e($room['referent']['name']); ?></strong>
                        <a class="room-referent-link" href="<?= e($room['referent']['href']); ?>">
                            <?= e($room['referent']['contact']); ?>
                        </a>
                    </article>
                </div>
            </div>

            <div class="room-hero-visual">
                <img src="<?= e($room['heroImage']); ?>" alt="Illustration de la salle <?= e($room['number']); ?> - <?= e($room['title']); ?>">
                <div class="room-hero-overlay"></div>
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
        </section>
    </main>
