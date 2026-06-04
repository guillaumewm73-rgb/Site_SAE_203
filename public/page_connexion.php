<?php

declare(strict_types=1);

require_once __DIR__ . '/../fonctions.php';

// La page connexion sert à deux profils : visiteurs et administrateurs.
startUserSession();

function formatReservationDate(string $value): string
{
    // Affichage court dans le récapitulatif visiteur.
    $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);

    return $date ? $date->format('d/m/Y') : $value;
}

function formatReservationTime(string $value): string
{
    // Même format horaire que sur le reste du site.
    $time = DateTimeImmutable::createFromFormat('H:i:s', $value);

    return $time ? $time->format('H:i') : substr($value, 0, 5);
}

$pageTitle = 'Connexion réservation - e-llusion';
$activePage = 'connexion';
$bodyClass = 'auth-page';
$reserveHref = 'reservation.php';

$enteredContact = '';
$enteredPassword = '';
$lookupAttempted = false;
$connectedVisitor = null;
$reservationResults = [];
$lookupError = null;
$lookupMessage = null;
$accessDenied = (string) ($_GET['access'] ?? '') === 'admin';
$postedAction = (string) ($_POST['action'] ?? '');
$deletedReservationId = filter_input(INPUT_GET, 'deleted', FILTER_VALIDATE_INT);
$updatedReservationId = filter_input(INPUT_GET, 'updated', FILTER_VALIDATE_INT);

if ($deletedReservationId) {
    // Message affiché après une suppression réussie et une redirection GET.
    $lookupMessage = 'La réservation #' . $deletedReservationId . ' a été supprimée.';
}

if ($updatedReservationId) {
    // Message affiché après une modification réussie depuis reservation.php.
    $lookupMessage = 'La réservation #' . $updatedReservationId . ' a été modifiée.';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !$accessDenied && isAdminConnected()) {
    // Un admin déjà connecté est directement renvoyé vers son tableau de bord.
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $postedAction === 'delete_reservation') {
    // Suppression côté visiteur depuis son espace personnel.
    $lookupAttempted = true;
    $connectedVisitor = isVisitorConnected() ? ['id' => (int) $_SESSION['visiteur_id']] : null;
    $reservationId = filter_input(INPUT_POST, 'reservation_id', FILTER_VALIDATE_INT);
    $reservationBeforeDelete = $reservationId && $connectedVisitor
        ? getReservationDetailsForVisitor($conn, $reservationId, (int) $_SESSION['visiteur_id'])
        : null;

    // On vérifie que la réservation appartient bien au visiteur connecté.
    if (!$connectedVisitor) {
        $lookupError = 'Reconnectez-vous avant de supprimer une réservation.';
    } elseif (!$reservationId) {
        $lookupError = 'La réservation à supprimer est introuvable.';
    } elseif (!$reservationBeforeDelete) {
        $lookupError = 'Cette réservation est introuvable ou ne vous appartient pas.';
    } elseif (!deleteVisitorReservation($conn, $reservationId, (int) $_SESSION['visiteur_id'])) {
        $lookupError = 'Cette réservation ne peut pas être supprimée.';
    } else {
        sendReservationNotificationEmail(
            'deleted',
            (string) $reservationBeforeDelete['prenom'],
            (string) $reservationBeforeDelete['nom'],
            (string) $reservationBeforeDelete['moyen_comm'],
            [$reservationBeforeDelete]
        );

        header('Location: page_connexion.php?deleted=' . $reservationId);
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] !== 'POST' && !$accessDenied && isVisitorConnected()) {
    // Si le visiteur est déjà connecté, on affiche directement ses réservations.
    $connectedVisitor = ['id' => (int) $_SESSION['visiteur_id']];
    $reservationResults = getReservationDetailsByVisitorId($conn, (int) $_SESSION['visiteur_id']);
    $lookupAttempted = true;

    if (!$reservationResults) {
        $lookupError = 'Votre compte existe, mais aucune réservation n’est associée.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Connexion classique : le même formulaire peut recevoir un email visiteur ou un login admin.
    $lookupAttempted = true;
    $enteredContact = trim((string) ($_POST['contact_value'] ?? ''));
    $enteredPassword = trim((string) ($_POST['password'] ?? ''));

    if ($enteredContact === '' || $enteredPassword === '') {
        $lookupError = 'Renseignez votre email ou votre login admin, puis votre mot de passe.';
    } else {
        // On teste d'abord la table admin, car un admin n'utilise pas forcément un email.
        $admin = getAdminByLogin($conn, $enteredContact);

        if ($admin && passwordCorresponds($enteredPassword, (string) $admin['password_hash'])) {
            connectAdminSession($admin);
            header('Location: admin.php');
            exit;
        }

        // Si ce n'est pas un admin valide, on cherche un visiteur avec email + mot de passe.
        $connectedVisitor = getVisitorByCredentials($conn, $enteredContact, $enteredPassword);

        if (!$connectedVisitor) {
            $lookupError = 'Email, login admin ou mot de passe incorrect.';
        } else {
            connectVisitorSession($connectedVisitor);
            $reservationResults = getReservationDetailsByVisitorId($conn, (int) $connectedVisitor['id']);

            if (!$reservationResults) {
                $lookupError = 'Votre compte existe, mais aucune réservation n’est associée.';
            }
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
                Visiteur : connectez-vous avec l’email renseigné lors de la réservation.
                Administrateur : utilisez votre login admin.
            </p>

            <div class="hero-buttons">
                <a class="button button-primary" href="reservation.php">Créer une réservation</a>
                <a class="button button-secondary" href="#verification">Vérifier ma réservation</a>
            </div>
        </section>

        <section class="auth-section">
            <div class="auth-grid">
                <article class="auth-card" id="verification">

                    <?php if ($accessDenied && !$lookupAttempted): ?>
                        <div class="auth-result is-error">
                            <h3>Accès administrateur requis</h3>
                            <p>Connectez-vous avec un compte administrateur pour ouvrir cette page.</p>
                        </div>
                    <?php endif; ?>

                    <?php if (!isVisitorConnected() && !isAdminConnected()): ?>
                    <!-- Formulaire unique : il connecte soit un visiteur, soit un administrateur. -->
                    <form class="auth-form" method="post" action="page_connexion.php">
                        <label class="auth-field">
                            <span>Email ou login admin</span>
                            <input
                                type="text"
                                name="contact_value"
                                autocomplete="username"
                                placeholder="prenom.nom@email.fr ou login admin"
                                required
                                value="<?= e($enteredContact); ?>"
                            >
                        </label>

                        <label class="auth-field">
                            <span>Mot de passe</span>
                            <input
                                type="password"
                                name="password"
                                autocomplete="current-password"
                                required
                            >
                        </label>

                        <button class="button button-primary" type="submit">Se connecter</button>
                    </form>
                    <?php endif; ?>

    


                    <?php if ($lookupAttempted): ?>
                        <?php if ($connectedVisitor && $reservationResults): ?>
                            <div class="auth-result">
                                <div class="auth-result-heading">
                                    <h3>Réservation trouvée</h3>
                                    <a class="button button-secondary" href="deconnexion.php">Déconnexion</a>
                                </div>
                                <?php if ($lookupMessage): ?>
                                    <p class="auth-result-message"><?= e($lookupMessage); ?></p>
                                <?php endif; ?>
                                <?php foreach ($reservationResults as $reservationResult): ?>
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
                                            <span>Email</span>
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
                                    <div class="auth-reservation-actions">
                                        <a
                                            class="button button-secondary"
                                            href="reservation.php?modifier=<?= e((string) $reservationResult['reservation_id']); ?>#registration-result"
                                        >
                                            Modifier
                                        </a>
                                        <!-- Suppression visiteur : l'id est caché, la vérification d'appartenance reste faite en PHP. -->
                                        <form method="post" action="page_connexion.php">
                                            <input type="hidden" name="action" value="delete_reservation">
                                            <input
                                                type="hidden"
                                                name="reservation_id"
                                                value="<?= e((string) $reservationResult['reservation_id']); ?>"
                                            >
                                            <button
                                                class="button button-danger"
                                                type="submit"
                                                onclick="return confirm('Supprimer cette réservation ?');"
                                            >
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ($connectedVisitor && $lookupMessage): ?>
                            <div class="auth-result">
                                <div class="auth-result-heading">
                                    <h3>Réservation supprimée</h3>
                                    <a class="button button-secondary" href="deconnexion.php">Déconnexion</a>
                                </div>
                                <p class="auth-result-message"><?= e($lookupMessage); ?></p>
                                <p>Vous n’avez plus de réservation active pour le moment.</p>
                                <a class="button button-primary" href="reservation.php">Créer une nouvelle réservation</a>
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
                            Si la connexion ne fonctionne pas, vérifiez l'orthographe de
                            l'email et le mot de passe choisi lors de la réservation.
                        </p>
                    </article>
                </aside>
            </div>
        </section>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
