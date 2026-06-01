<?php

declare(strict_types=1);

$pageTitle = 'Contact - e-llusion';
$activePage = 'contact';
$reserveHref = 'inscription.php';

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
                        Tél : 06 77 65 97 77<br>
                        <a href="mailto:mmi-chambery@univ-smb.fr">mmi-chambery@univ-smb.fr</a>
                    </p>
                    <p>
                        Pour une question de salle, de créneau ou de jauge,
                        envoyez un message ou appelez directement le référent
                        de l’exposition.
                    </p>
                </div>
            </article>
            <a class="button button-primary contact-back" href="index.php">Retour accueil</a>
        </section>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
