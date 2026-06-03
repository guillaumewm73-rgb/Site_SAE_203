<?php

declare(strict_types=1);

require_once __DIR__ . '/../fonctions.php';

startUserSession();

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

$enteredContact = '';
$enteredPassword = '';
$lookupAttempted = false;
$connectedVisitor = null;
$reservationResults = [];
$lookupError = null;
$accessDenied = (string) ($_GET['access'] ?? '') === 'admin';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !$accessDenied && isAdminConnected()) {
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !$accessDenied && isVisitorConnected()) {
    $connectedVisitor = ['id' => (int) $_SESSION['visiteur_id']];
    $reservationResults = getReservationDetailsByVisitorId($conn, (int) $_SESSION['visiteur_id']);
    $lookupAttempted = true;

    if (!$reservationResults) {
        $lookupError = 'Votre compte existe, mais aucune réservation n’est associée.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lookupAttempted = true;
    $enteredContact = trim((string) ($_POST['contact_value'] ?? ''));
    $enteredPassword = trim((string) ($_POST['password'] ?? ''));

    if ($enteredContact === '' || $enteredPassword === '') {
        $lookupError = 'Renseignez votre email ou téléphone et votre mot de passe.';
    } else {
        $admin = getAdminByLogin($conn, $enteredContact);

        if ($admin && passwordCorresponds($enteredPassword, (string) $admin['password_hash'])) {
            connectAdminSession($admin);
            header('Location: admin.php');
            exit;
        }

        $connectedVisitor = getVisitorByCredentials($conn, $enteredContact, $enteredPassword);

        if (!$connectedVisitor) {
            $lookupError = 'Identifiant ou mot de passe incorrect.';
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
                Connectez-vous avec le mail ou le téléphone renseigné lors de l'inscription,
                puis utilisez le mot de passe choisi pour retrouver directement votre réservation.
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
                        Visiteur : utilisez votre email ou téléphone. Administrateur :
                        utilisez le login présent dans la table admin.
                    </p>

                    <?php if ($accessDenied && !$lookupAttempted): ?>
                        <div class="auth-result is-error">
                            <h3>Accès administrateur requis</h3>
                            <p>Connectez-vous avec un compte administrateur pour ouvrir cette page.</p>
                        </div>
                    <?php endif; ?>

                    <form class="auth-form" method="post" action="page_connexion.php">
                        <label class="auth-field">
                            <span>Identifiant</span>
                            <input
                                type="text"
                                name="contact_value"
                                autocomplete="username"
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

                    <?php if ($lookupAttempted): ?>
                        <?php if ($connectedVisitor && $reservationResults): ?>
                            <div class="auth-result">
                                <h3>Réservation trouvée</h3>
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
                                <?php endforeach; ?>
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
                            Si la connexion ne fonctionne pas, vérifiez l'orthographe du
                            mail ou du téléphone et le mot de passe choisi lors de l'inscription.
                        </p>
                    </article>
                </aside>
            </div>
        </section>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
