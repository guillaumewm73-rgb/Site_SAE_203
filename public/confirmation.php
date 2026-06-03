<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../fonctions.php';

function confirmationFormatDate(string $value): string
{
    $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);

    if (!$date) {
        return $value;
    }

    $months = [
        '01' => 'janvier',
        '02' => 'février',
        '03' => 'mars',
        '04' => 'avril',
        '05' => 'mai',
        '06' => 'juin',
        '07' => 'juillet',
        '08' => 'août',
        '09' => 'septembre',
        '10' => 'octobre',
        '11' => 'novembre',
        '12' => 'décembre',
    ];

    return $date->format('d') . ' ' . ($months[$date->format('m')] ?? $date->format('m')) . ' ' . $date->format('Y');
}

function confirmationFormatTime(string $value): string
{
    $time = DateTimeImmutable::createFromFormat('H:i:s', $value);

    return $time ? $time->format('H:i') : substr($value, 0, 5);
}

$reservationIds = $_SESSION['latest_reservation_ids'] ?? [];
$visitorName = (string) ($_SESSION['latest_confirmation_visitor'] ?? '');
$contact = (string) ($_SESSION['latest_confirmation_contact'] ?? '');
$emailSent = (bool) ($_SESSION['latest_confirmation_email_sent'] ?? false);
$reservations = [];

if (is_array($reservationIds)) {
    foreach ($reservationIds as $reservationId) {
        $reservationId = filter_var($reservationId, FILTER_VALIDATE_INT);

        if (!$reservationId) {
            continue;
        }

        $reservation = getReservationDetailsById($conn, $reservationId);

        if ($reservation) {
            $reservations[] = $reservation;
        }
    }
}

$pageTitle = 'Confirmation - e-llusion';
$activePage = 'reservation';
$bodyClass = 'confirmation-page';
$reserveHref = 'reservation.php';

require __DIR__ . '/includes/header.php';
?>

    <main class="confirmation-page">
        <section class="confirmation-hero" aria-labelledby="confirmation-title">
            <p class="eyebrow">Réservation confirmée</p>
            <h1 id="confirmation-title">Votre créneau est réservé</h1>
            <p>
                Retrouvez le récapitulatif de votre visite. Gardez votre numéro de réservation :
                il permettra de vérifier ou modifier votre réservation si besoin.
            </p>
        </section>

        <section class="confirmation-section" aria-label="Récapitulatif de réservation">
            <?php if ($reservations): ?>
                <article class="confirmation-card">
                    <div class="confirmation-card-header">
                        <div>
                            <h2>Récapitulatif</h2>
                            <p>
                                <?= e($visitorName !== '' ? $visitorName : 'Votre réservation'); ?>
                                est bien enregistrée avec le contact
                                <strong><?= e($contact); ?>. <br> Veuillez consulter vos spam si vous n'avz pas reçu le mail.</strong>.
                            </p>
                        </div>
                        <span class="confirmation-status">Confirmé</span>
                    </div>

                    <?php if ($emailSent): ?>
                        <p class="confirmation-note">
                            Un email de confirmation a été envoyé à <?= e($contact); ?>.
                        </p>
                    <?php else: ?>
                        <p class="confirmation-note">
                            Notez votre numéro de réservation.
                        </p>
                    <?php endif; ?>

                    <div class="confirmation-list">
                        <?php foreach ($reservations as $reservation): ?>
                            <article class="confirmation-reservation">
                                <div>
                                    <span>Réservation</span>
                                    <strong>#<?= e((string) $reservation['reservation_id']); ?></strong>
                                </div>
                                <div>
                                    <span>Salle</span>
                                    <strong>Salle <?= e((string) $reservation['numero_salle']); ?> · <?= e((string) $reservation['nom_salle']); ?></strong>
                                </div>
                                <div>
                                    <span>Date</span>
                                    <strong>
                                        <?= e((string) $reservation['nom_jour']); ?>
                                        <?= e(confirmationFormatDate((string) $reservation['date_jour'])); ?>
                                    </strong>
                                </div>
                                <div>
                                    <span>Horaire</span>
                                    <strong><?= e(confirmationFormatTime((string) $reservation['heure_debut'])); ?></strong>
                                </div>
                                <div>
                                    <span>Personnes</span>
                                    <strong><?= e((string) $reservation['nombre_personnes']); ?></strong>
                                </div>
                                <div>
                                    <span>Buffet du jeudi à 19h</span>
                                    <strong><?= !empty($reservation['participe_buffet']) ? 'Présent·e' : 'Non présent·e'; ?></strong>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="confirmation-actions">
                        <a class="button button-secondary" href="page_connexion.php">Vérifier ma réservation</a>
                        <a class="button button-primary" href="index.php">Retour à l'accueil</a>
                    </div>
                </article>
            <?php else: ?>
                <article class="confirmation-card">
                    <h2>Aucune réservation récente</h2>
                    <p>
                        Cette page affiche la confirmation juste après l'envoi du formulaire.
                        Créez une réservation pour générer un récapitulatif.
                    </p>
                    <div class="confirmation-actions">
                        <a class="button button-primary" href="reservation.php">Créer une réservation</a>
                        <a class="button button-secondary" href="page_connexion.php">Vérifier une réservation</a>
                    </div>
                </article>
            <?php endif; ?>
        </section>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
