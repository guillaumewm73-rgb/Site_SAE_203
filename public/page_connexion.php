<?php

declare(strict_types=1);

require_once __DIR__ . '/../fonctions.php';

function formatReservationDate(string $value): string
{
    $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);

    return $date ? $date->format('d/m/Y') : $value;
}

function formatReservationTime(string $value): string
{
    $time = DateTimeImmutable::createFromFormat('H:i:s', $value);

    return $time ? $time->format('H:i') : substr($value, 0, 5);
}

$pageTitle = 'Connexion réservation - e-llusion';
$activePage = 'connexion';
$bodyClass = 'auth-page';
$reserveHref = 'inscription.php';

$enteredReservationId = '';
$enteredContact = '';
$lookupAttempted = false;
$reservationResult = null;
$lookupError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lookupAttempted = true;
    $enteredReservationId = trim((string) ($_POST['reservation_id'] ?? ''));
    $enteredContact = trim((string) ($_POST['contact_value'] ?? ''));

    if ($enteredReservationId === '' || !ctype_digit($enteredReservationId)) {
        $lookupError = 'Le numéro de réservation doit contenir uniquement des chiffres.';
    } elseif ($enteredContact === '') {
        $lookupError = 'Ajoutez le mail ou le téléphone utilisé lors de la réservation.';
    } else {
        $reservationResult = getReservationDetailsById($conn, (int) $enteredReservationId);

        if (!$reservationResult) {
            $lookupError = 'Aucune réservation ne correspond à ce numéro.';
        } elseif (strcasecmp(trim((string) $reservationResult['moyen_comm']), $enteredContact) !== 0) {
            $lookupError = 'Le moyen de contact ne correspond pas à cette réservation.';
            $reservationResult = null;
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

    <main class="auth-page">
        <section class="auth-hero" aria-labelledby="reservation-login-title">
            <p class="eyebrow">Espace visiteur</p>
            <h1 id="reservation-login-title">Connexion réservation</h1>
            <p>
                Vérifiez votre réservation avec le numéro de téléphone ou le mail utilisé lors de la réservation. 
                Renseignez ensuite le mot de passe pour accéder à votre espace personnel et découvrir les détails de votre visite.
            </p>

            <div class="hero-buttons">
                <a class="button button-primary" href="inscription.php">Créer une réservation</a>
                <a class="button button-secondary" href="#verification">Vérifier ma réservation</a>
            </div>
        </section>

        <section class="auth-section">
            <div class="auth-grid">
                <article class="auth-card" id="verification">
                    <h2>Connexion à votre compte</h2>
                    <p>
                        Renseignez le numéro de réservation et le mail ou téléphone utilisé lors de la réservation.
                    </p>

                    <form class="auth-form" method="post" action="">
                        <label class="auth-field">
                            <span>Numéro de réservation</span>
                            <input
                                type="text"
                                name="reservation_id"
                                inputmode="numeric"
                                autocomplete="off"
                                required
                                value="<?= e($enteredReservationId); ?>"
                            >
                        </label>

                        <label class="auth-field">
                            <span>Email ou téléphone</span>
                            <input
                                type="text"
                                name="contact_value"
                                autocomplete="email tel"
                                required
                                value="<?= e($enteredContact); ?>"
                            >
                        </label>

                        <button class="button button-primary" type="submit">Vérifier ma réservation</button>
                    </form>

                    <?php if ($lookupAttempted): ?>
                        <?php if ($reservationResult): ?>
                            <div class="auth-result">
                                <h3>Réservation trouvée</h3>
                                <div class="auth-result-grid">
                                    <div class="auth-result-item">
                                        <span>Réservation</span>
                                        <strong>#<?= e((string) $reservationResult['reservation_id']); ?></strong>
                                    </div>
                                    <div class="auth-result-item">
                                        <span>Visiteur</span>
                                        <strong><?= e(trim($reservationResult['prenom'] . ' ' . $reservationResult['nom'])); ?></strong>
                                    </div>
                                    <div class="auth-result-item">
                                        <span>Contact</span>
                                        <strong><?= e((string) $reservationResult['moyen_comm']); ?></strong>
                                    </div>
                                    <div class="auth-result-item">
                                        <span>Salle</span>
                                        <strong>Salle <?= e((string) $reservationResult['numero_salle']); ?> · <?= e((string) $reservationResult['nom_salle']); ?></strong>
                                    </div>
                                    <div class="auth-result-item">
                                        <span>Date</span>
                                        <strong><?= e(formatReservationDate((string) $reservationResult['date_jour'])); ?></strong>
                                    </div>
                                    <div class="auth-result-item">
                                        <span>Horaire</span>
                                        <strong><?= e(formatReservationTime((string) $reservationResult['heure_debut'])); ?></strong>
                                    </div>
                                    <div class="auth-result-item">
                                        <span>Personnes</span>
                                        <strong><?= e((string) $reservationResult['nombre_personnes']); ?></strong>
                                    </div>
                                    <div class="auth-result-item">
                                        <span>Buffet</span>
                                        <strong><?= !empty($reservationResult['participe_buffet']) ? 'Oui' : 'Non'; ?></strong>
                                    </div>
                                    <div class="auth-result-item">
                                        <span>Repère salle</span>
                                        <strong><?= e((string) $reservationResult['description_salle']); ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="auth-result is-error">
                                <h3>Connexion impossible</h3>
                                <p><?= e($lookupError ?? 'Une erreur est survenue.'); ?></p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </article>

                <aside class="auth-sidebar">
                    <article class="auth-tip">
                        <h2>Ce que vous voyez</h2>
                        <p>
                            Le numéro de salle, le jour, l'heure et le nom de la salle
                            apparaissent directement après la vérification.
                        </p>
                    </article>

                    <article class="auth-tip">
                        <h2>Besoin d'aide ?</h2>
                        <p>
                            Si le numéro ne fonctionne pas, vérifiez l'orthographe du
                            mail ou du téléphone puis contactez l'équipe de l'exposition.
                        </p>
                    </article>
                </aside>
            </div>
        </section>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
