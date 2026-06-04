<?php

declare(strict_types=1);

$pageTitle = 'Contact - e-llusion';
$activePage = 'contact';
$reserveHref = 'reservation.php';

require __DIR__ . '/includes/donnee_salles.php';

// On réutilise le catalogue des salles pour afficher automatiquement les référents.
$roomCatalog = getRoomCatalog();

require __DIR__ . '/includes/header.php';
?>

    <main class="contact-page">
        <section class="contact-hero" aria-labelledby="contact-title">
            <h1 id="contact-title">Contact</h1>
            <h2>Besoin d’une info ?<br>On répond simplement.</h2>
            <p>
                La page contact reste simple : une adresse, un référent,
                et les liens utiles.
            </p>
        </section>

        <section class="contact-section" aria-label="Informations de contact">
            <article class="contact-card contact-card-main">
                <div>
                    <h2>Contacter référent expo</h2>
                    <p>
                        François PIRANDA - Référent exposition<br>
                        <a href="mailto:françois.piranda@univ-smb.fr">françois.piranda@univ-smb.fr</a>
                    </p>
                    <p>
                        Pour une question de salle, de créneau ou de jauge,
                        envoyez un message ou appelez directement le référent
                        de l’exposition.
                    </p>
                </div>
            </article>

            <div class="contact-room-grid" aria-label="Référents par salle">
                <?php foreach ($roomCatalog as $room): ?>
                    <?php $referent = $room['referent']; ?>
                    <!-- Une carte est générée par salle à partir des données centralisées. -->
                    <article class="contact-card contact-room-card">
                        <span>Salle <?= e($room['number']); ?> · <?= e($room['supportLabel']); ?></span>
                        <h3><?= e($referent['label']); ?></h3>
                        <p><?= e($referent['name']); ?></p>
                        <a href="<?= e($referent['href']); ?>"><?= e($referent['contact']); ?></a>
                    </article>
                <?php endforeach; ?>
            </div>

            <a class="button button-primary contact-back" href="index.php">Retour accueil</a>
        </section>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
