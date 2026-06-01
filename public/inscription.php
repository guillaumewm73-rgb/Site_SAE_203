<?php

declare(strict_types=1);

$pageTitle = 'Inscription - e-llusion';
$activePage = 'inscription';
$reserveHref = 'inscription.php';

require __DIR__ . '/includes/header.php';
?>

    <main class="placeholder-page">
        <section>
            <p class="eyebrow">Prochaine étape</p>
            <h1>Inscription</h1>
            <p>
                Cette page servira à coder le formulaire : choix du jour, choix du créneau,
                choix de la salle et affichage des places restantes.
            </p>
            <a class="button button-primary" href="index.php">Retour accueil</a>
        </section>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
