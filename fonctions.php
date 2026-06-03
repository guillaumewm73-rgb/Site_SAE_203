<?php
/* --------------------------------------------------------
   Fonctions BDD - e-llusion / SAE 203
   À inclure dans chaque page qui a besoin de la BDD.
   -------------------------------------------------------- */

require_once __DIR__ . '/connexion.php';

/* ========================================================
   SALLES
   ======================================================== */

function getSalles(PDO $conn): array
{
    $req = $conn->prepare('SELECT * FROM salles ORDER BY numero_salle ASC');
    $req->execute();

    return $req->fetchAll();
}

function getSalleById(PDO $conn, int $id): ?array
{
    $req = $conn->prepare('SELECT * FROM salles WHERE id = :id');
    $req->execute([':id' => $id]);
    $salle = $req->fetch();

    return $salle ?: null;
}

/* ========================================================
   CRÉNEAUX
   ======================================================== */

function getCreneaux(PDO $conn): array
{
    $req = $conn->prepare("
        SELECT c.id, c.heure_debut, j.date_jour, j.nom_jour
        FROM creneaux c
        JOIN jour j ON c.jour_id = j.id
        ORDER BY j.date_jour ASC, c.heure_debut ASC
    ");
    $req->execute();

    return $req->fetchAll();
}

function getAdminSlots(PDO $conn): array
{
    $req = $conn->prepare("
        SELECT
            sc.id AS salle_creneaux_id,
            sc.capacite_max,
            s.id AS salle_id,
            s.numero_salle,
            s.nom AS nom_salle,
            c.id AS creneau_id,
            c.heure_debut,
            j.id AS jour_id,
            j.nom_jour,
            j.date_jour
        FROM salle_creneaux sc
        JOIN salles s ON sc.salles_id = s.id
        JOIN creneaux c ON sc.creneaux_id = c.id
        JOIN jour j ON c.jour_id = j.id
        ORDER BY j.date_jour ASC, c.heure_debut ASC, s.numero_salle ASC
    ");
    $req->execute();

    return $req->fetchAll();
}

/* ========================================================
   PLACES RESTANTES
   ======================================================== */

function getPlacesRestantes(PDO $conn, int $salleCreneauxId): int
{
    $req = $conn->prepare('SELECT capacite_max FROM salle_creneaux WHERE id = :id');
    $req->execute([':id' => $salleCreneauxId]);
    $slot = $req->fetch();

    if (!$slot) {
        return 0;
    }

    $req2 = $conn->prepare('SELECT COALESCE(SUM(nombre_personnes), 0) AS nb FROM reservation WHERE salle_creneaux_id = :id');
    $req2->execute([':id' => $salleCreneauxId]);
    $nbReservations = (int) $req2->fetch()['nb'];

    return max(0, (int) $slot['capacite_max'] - $nbReservations);
}

function getAdminAvailability(PDO $conn): array
{
    $req = $conn->prepare("
        SELECT
            sc.id AS salle_creneaux_id,
            sc.capacite_max,
            s.numero_salle,
            c.heure_debut,
            j.nom_jour,
            j.date_jour,
            COALESCE(SUM(r.nombre_personnes), 0) AS reserved_count
        FROM salle_creneaux sc
        JOIN salles s ON sc.salles_id = s.id
        JOIN creneaux c ON sc.creneaux_id = c.id
        JOIN jour j ON c.jour_id = j.id
        LEFT JOIN reservation r ON r.salle_creneaux_id = sc.id
        GROUP BY
            sc.id,
            sc.capacite_max,
            s.numero_salle,
            c.heure_debut,
            j.nom_jour,
            j.date_jour
        ORDER BY j.date_jour ASC, c.heure_debut ASC, s.numero_salle ASC
    ");
    $req->execute();

    $availability = [];
    foreach ($req->fetchAll() as $row) {
        $reservedCount = (int) $row['reserved_count'];
        $capacity = (int) $row['capacite_max'];
        $row['remaining_places'] = max(0, $capacity - $reservedCount);
        $row['reserved_count'] = $reservedCount;
        $availability[] = $row;
    }

    return $availability;
}

/* ========================================================
   RÉSERVATIONS — VISITEUR
   ======================================================== */

function createVisiteur(
    PDO $conn,
    string $nom,
    ?string $prenom,
    string $moyenComm,
    int $categorieId,
    int $participeBuffet = 0,
    string $motDePasse = ''
): string {
    if (identifierAlreadyUsed($conn, $moyenComm)) {
        throw new RuntimeException('Cet email, téléphone ou identifiant est déjà utilisé.');
    }

    $req = $conn->prepare("
        INSERT INTO visiteurs (nom, prenom, moyen_comm, categories_visiteur_id, participe_buffet, mdp)
        VALUES (:nom, :prenom, :moyen_comm, :categorie_id, :participe_buffet, :mdp)
    ");
    $req->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':moyen_comm' => $moyenComm,
        ':categorie_id' => $categorieId,
        ':participe_buffet' => $participeBuffet,
        ':mdp' => $motDePasse,
    ]);

    return $conn->lastInsertId();
}

function passwordCorresponds(string $motDePasseSaisi, string $motDePasseStocke): bool
{
    if ($motDePasseStocke === '') {
        return false;
    }

    $hashInfo = password_get_info($motDePasseStocke);

    if (($hashInfo['algo'] ?? 0) !== 0) {
        return password_verify($motDePasseSaisi, $motDePasseStocke);
    }

    return hash_equals($motDePasseStocke, $motDePasseSaisi);
}

function identifierAlreadyUsed(PDO $conn, string $identifier, ?int $excludeVisitorId = null): bool
{
    $identifier = trim($identifier);

    if ($identifier === '') {
        return false;
    }

    $adminReq = $conn->prepare('SELECT COUNT(*) AS total FROM admin WHERE login = :identifier');
    $adminReq->execute([':identifier' => $identifier]);

    if ((int) $adminReq->fetch()['total'] > 0) {
        return true;
    }

    $visitorSql = 'SELECT COUNT(*) AS total FROM visiteurs WHERE moyen_comm = :identifier';
    $params = [':identifier' => $identifier];

    if ($excludeVisitorId !== null) {
        $visitorSql .= ' AND id <> :exclude_visitor_id';
        $params[':exclude_visitor_id'] = $excludeVisitorId;
    }

    $visitorReq = $conn->prepare($visitorSql);
    $visitorReq->execute($params);

    return (int) $visitorReq->fetch()['total'] > 0;
}

function startUserSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function connectAdminSession(array $admin): void
{
    startUserSession();
    session_regenerate_id(true);

    $_SESSION['auth_role'] = 'admin';
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_login'] = (string) $admin['login'];

    unset(
        $_SESSION['visiteur_id'],
        $_SESSION['visiteur_nom'],
        $_SESSION['visiteur_contact']
    );
}

function connectVisitorSession(array $visiteur): void
{
    startUserSession();
    session_regenerate_id(true);

    $_SESSION['auth_role'] = 'visiteur';
    $_SESSION['visiteur_id'] = (int) $visiteur['id'];
    $_SESSION['visiteur_nom'] = trim((string) ($visiteur['prenom'] ?? '') . ' ' . (string) ($visiteur['nom'] ?? ''));
    $_SESSION['visiteur_contact'] = (string) ($visiteur['moyen_comm'] ?? '');

    unset(
        $_SESSION['admin_id'],
        $_SESSION['admin_login']
    );
}

function isAdminConnected(): bool
{
    startUserSession();

    return ($_SESSION['auth_role'] ?? '') === 'admin'
        && isset($_SESSION['admin_id']);
}

function isVisitorConnected(): bool
{
    startUserSession();

    return ($_SESSION['auth_role'] ?? '') === 'visiteur'
        && isset($_SESSION['visiteur_id']);
}

function requireAdminSession(): void
{
    if (isAdminConnected()) {
        return;
    }

    header('Location: page_connexion.php?access=admin');
    exit;
}

function getAdminByLogin(PDO $conn, string $login): ?array
{
    $req = $conn->prepare('SELECT id, login, password_hash FROM admin WHERE login = :login LIMIT 1');
    $req->execute([':login' => $login]);
    $admin = $req->fetch();

    return $admin ?: null;
}

function getVisitorByCredentials(PDO $conn, string $contact, string $motDePasse): ?array
{
    $req = $conn->prepare('SELECT * FROM visiteurs WHERE moyen_comm = :contact ORDER BY id DESC');
    $req->execute([':contact' => $contact]);

    foreach ($req->fetchAll() as $visiteur) {
        if (passwordCorresponds($motDePasse, (string) ($visiteur['mdp'] ?? ''))) {
            return $visiteur;
        }
    }

    return null;
}

function getReservationDetailsByVisitorId(PDO $conn, int $visiteurId): array
{
    $req = $conn->prepare("
        SELECT
            r.id AS reservation_id,
            r.nombre_personnes,
            v.nom,
            v.prenom,
            v.moyen_comm,
            v.participe_buffet,
            s.numero_salle,
            s.nom AS nom_salle,
            s.description AS description_salle,
            j.nom_jour,
            j.date_jour,
            c.heure_debut
        FROM reservation r
        JOIN visiteurs v ON r.visiteurs_id = v.id
        JOIN salle_creneaux sc ON r.salle_creneaux_id = sc.id
        JOIN salles s ON sc.salles_id = s.id
        JOIN creneaux c ON sc.creneaux_id = c.id
        JOIN jour j ON c.jour_id = j.id
        WHERE v.id = :visiteur_id
        ORDER BY j.date_jour ASC, c.heure_debut ASC, s.numero_salle ASC
    ");
    $req->execute([':visiteur_id' => $visiteurId]);

    return $req->fetchAll();
}

function getReservationDetailsForVisitor(PDO $conn, int $reservationId, int $visiteurId): ?array
{
    $req = $conn->prepare("
        SELECT
            r.id AS reservation_id,
            r.visiteurs_id,
            r.salle_creneaux_id,
            r.nombre_personnes,
            v.nom,
            v.prenom,
            v.moyen_comm,
            v.categories_visiteur_id,
            v.participe_buffet,
            s.numero_salle,
            s.nom AS nom_salle,
            s.description AS description_salle,
            j.nom_jour,
            j.date_jour,
            c.heure_debut
        FROM reservation r
        JOIN visiteurs v ON r.visiteurs_id = v.id
        JOIN salle_creneaux sc ON r.salle_creneaux_id = sc.id
        JOIN salles s ON sc.salles_id = s.id
        JOIN creneaux c ON sc.creneaux_id = c.id
        JOIN jour j ON c.jour_id = j.id
        WHERE r.id = :reservation_id
          AND r.visiteurs_id = :visiteur_id
        LIMIT 1
    ");
    $req->execute([
        ':reservation_id' => $reservationId,
        ':visiteur_id' => $visiteurId,
    ]);
    $reservation = $req->fetch();

    return $reservation ?: null;
}

function createReservation(PDO $conn, int $visiteursId, int $salleCreneauxId, int $nombrePersonnes = 1): string
{
    $req = $conn->prepare("
        INSERT INTO reservation (visiteurs_id, salle_creneaux_id, nombre_personnes)
        VALUES (:visiteurs_id, :salle_creneaux_id, :nombre_personnes)
    ");
    $req->execute([
        ':visiteurs_id' => $visiteursId,
        ':salle_creneaux_id' => $salleCreneauxId,
        ':nombre_personnes' => $nombrePersonnes,
    ]);

    return $conn->lastInsertId();
}

function getSalleCreneauxIdBySelection(PDO $conn, string $dayKey, string $time, string $roomNumber): ?int
{
    $timeWithSeconds = strlen($time) === 5 ? $time . ':00' : $time;

    $req = $conn->prepare("
        SELECT sc.id
        FROM salle_creneaux sc
        JOIN salles s ON sc.salles_id = s.id
        JOIN creneaux c ON sc.creneaux_id = c.id
        JOIN jour j ON c.jour_id = j.id
        WHERE LOWER(j.nom_jour) = :day_key
          AND c.heure_debut = :heure_debut
          AND s.numero_salle = :numero_salle
        LIMIT 1
    ");
    $req->execute([
        ':day_key' => strtolower($dayKey),
        ':heure_debut' => $timeWithSeconds,
        ':numero_salle' => $roomNumber,
    ]);
    $slot = $req->fetch();

    return $slot ? (int) $slot['id'] : null;
}

function getPlacesRestantesForReservationUpdate(PDO $conn, int $salleCreneauxId, int $reservationId): int
{
    $req = $conn->prepare("
        SELECT
            sc.capacite_max,
            COALESCE(SUM(CASE WHEN r.id <> :reservation_id THEN r.nombre_personnes ELSE 0 END), 0) AS nb
        FROM salle_creneaux sc
        LEFT JOIN reservation r ON r.salle_creneaux_id = sc.id
        WHERE sc.id = :salle_creneaux_id
        GROUP BY sc.id, sc.capacite_max
    ");
    $req->execute([
        ':reservation_id' => $reservationId,
        ':salle_creneaux_id' => $salleCreneauxId,
    ]);
    $slot = $req->fetch();

    if (!$slot) {
        return 0;
    }

    return max(0, (int) $slot['capacite_max'] - (int) $slot['nb']);
}

function getReservationById(PDO $conn, int $reservationId): ?array
{
    $req = $conn->prepare("
        SELECT
            r.id AS reservation_id,
            r.visiteurs_id,
            r.salle_creneaux_id,
            r.nombre_personnes,
            v.nom,
            v.prenom,
            v.moyen_comm,
            v.participe_buffet
        FROM reservation r
        JOIN visiteurs v ON r.visiteurs_id = v.id
        WHERE r.id = :id
    ");
    $req->execute([':id' => $reservationId]);
    $reservation = $req->fetch();

    return $reservation ?: null;
}

function getReservationDetailsById(PDO $conn, int $reservationId): ?array
{
    $req = $conn->prepare("
        SELECT
            r.id AS reservation_id,
            r.nombre_personnes,
            v.nom,
            v.prenom,
            v.moyen_comm,
            v.participe_buffet,
            s.numero_salle,
            s.nom AS nom_salle,
            s.description AS description_salle,
            j.nom_jour,
            j.date_jour,
            c.heure_debut
        FROM reservation r
        JOIN visiteurs v ON r.visiteurs_id = v.id
        JOIN salle_creneaux sc ON r.salle_creneaux_id = sc.id
        JOIN salles s ON sc.salles_id = s.id
        JOIN creneaux c ON sc.creneaux_id = c.id
        JOIN jour j ON c.jour_id = j.id
        WHERE r.id = :id
    ");
    $req->execute([':id' => $reservationId]);
    $reservation = $req->fetch();

    return $reservation ?: null;
}

function updateReservation(PDO $conn, int $reservationId, int $nouveauSalleCreneauxId): void
{
    $req = $conn->prepare("
        UPDATE reservation
        SET salle_creneaux_id = :nouveau_id
        WHERE id = :id
    ");
    $req->execute([
        ':nouveau_id' => $nouveauSalleCreneauxId,
        ':id' => $reservationId,
    ]);
}

function cancelReservation(PDO $conn, int $reservationId): void
{
    deleteAdminReservation($conn, $reservationId);
}

function deleteVisitorReservation(PDO $conn, int $reservationId, int $visiteurId): bool
{
    $req = $conn->prepare("
        DELETE FROM reservation
        WHERE id = :reservation_id
          AND visiteurs_id = :visiteur_id
    ");
    $req->execute([
        ':reservation_id' => $reservationId,
        ':visiteur_id' => $visiteurId,
    ]);

    return $req->rowCount() > 0;
}

/* ========================================================
   RÉSERVATIONS — ADMIN
   ======================================================== */

function getAdminCategories(PDO $conn): array
{
    $req = $conn->prepare('SELECT id, libelle FROM categories_visiteur ORDER BY id ASC');
    $req->execute();

    return $req->fetchAll();
}

function getAdminReservations(PDO $conn, string $query = ''): array
{
    $sql = "
        SELECT
            r.id AS reservation_id,
            r.visiteurs_id,
            r.salle_creneaux_id,
            r.nombre_personnes,
            v.nom,
            v.prenom,
            v.moyen_comm,
            v.categories_visiteur_id,
            v.participe_buffet,
            cv.libelle AS categorie,
            s.numero_salle,
            s.nom AS nom_salle,
            c.heure_debut,
            j.nom_jour,
            j.date_jour
        FROM reservation r
        JOIN visiteurs v ON r.visiteurs_id = v.id
        JOIN categories_visiteur cv ON v.categories_visiteur_id = cv.id
        JOIN salle_creneaux sc ON r.salle_creneaux_id = sc.id
        JOIN salles s ON sc.salles_id = s.id
        JOIN creneaux c ON sc.creneaux_id = c.id
        JOIN jour j ON c.jour_id = j.id
    ";

    $params = [];
    if ($query !== '') {
        $like = '%' . $query . '%';
        $sql .= "
            WHERE
                v.nom LIKE :q_nom
                OR v.prenom LIKE :q_prenom
                OR v.moyen_comm LIKE :q_contact
                OR s.numero_salle LIKE :q_salle
                OR cv.libelle LIKE :q_categorie
        ";
        $params = [
            ':q_nom' => $like,
            ':q_prenom' => $like,
            ':q_contact' => $like,
            ':q_salle' => $like,
            ':q_categorie' => $like,
        ];
    }

    $sql .= ' ORDER BY j.date_jour ASC, c.heure_debut ASC, s.numero_salle ASC, v.nom ASC';

    $req = $conn->prepare($sql);
    $req->execute($params);

    return $req->fetchAll();
}

function updateAdminReservation(
    PDO $conn,
    int $reservationId,
    int $visiteurId,
    string $nom,
    string $prenom,
    string $moyenComm,
    int $categorieId,
    int $salleCreneauxId,
    int $participeBuffet,
    int $nombrePersonnes = 1
): void {
    if (identifierAlreadyUsed($conn, $moyenComm, $visiteurId)) {
        throw new RuntimeException('Cet email, téléphone ou identifiant est déjà utilisé par un autre compte.');
    }

    $conn->beginTransaction();

    try {
        $reqVisiteur = $conn->prepare("
            UPDATE visiteurs
            SET
                nom = :nom,
                prenom = :prenom,
                moyen_comm = :moyen_comm,
                categories_visiteur_id = :categorie_id,
                participe_buffet = :participe_buffet
            WHERE id = :visiteur_id
        ");
        $reqVisiteur->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':moyen_comm' => $moyenComm,
            ':categorie_id' => $categorieId,
            ':participe_buffet' => $participeBuffet,
            ':visiteur_id' => $visiteurId,
        ]);

        $reqReservation = $conn->prepare("
            UPDATE reservation
            SET
                salle_creneaux_id = :salle_creneaux_id,
                nombre_personnes = :nombre_personnes
            WHERE id = :reservation_id AND visiteurs_id = :visiteur_id
        ");
        $reqReservation->execute([
            ':salle_creneaux_id' => $salleCreneauxId,
            ':nombre_personnes' => $nombrePersonnes,
            ':reservation_id' => $reservationId,
            ':visiteur_id' => $visiteurId,
        ]);

        $conn->commit();
    } catch (Throwable $exception) {
        $conn->rollBack();
        throw $exception;
    }
}

function deleteAdminReservation(PDO $conn, int $reservationId): void
{
    $conn->beginTransaction();

    try {
        $reqFind = $conn->prepare('SELECT visiteurs_id FROM reservation WHERE id = :id');
        $reqFind->execute([':id' => $reservationId]);
        $reservation = $reqFind->fetch();

        if (!$reservation) {
            $conn->commit();
            return;
        }

        $visiteurId = (int) $reservation['visiteurs_id'];

        $reqDeleteReservation = $conn->prepare('DELETE FROM reservation WHERE id = :id');
        $reqDeleteReservation->execute([':id' => $reservationId]);

        $reqCount = $conn->prepare('SELECT COUNT(*) AS total FROM reservation WHERE visiteurs_id = :visiteur_id');
        $reqCount->execute([':visiteur_id' => $visiteurId]);
        $remainingReservations = (int) $reqCount->fetch()['total'];

        if ($remainingReservations === 0) {
            $reqDeleteVisiteur = $conn->prepare('DELETE FROM visiteurs WHERE id = :id');
            $reqDeleteVisiteur->execute([':id' => $visiteurId]);
        }

        $conn->commit();
    } catch (Throwable $exception) {
        $conn->rollBack();
        throw $exception;
    }
}

function getAdminPlanning(PDO $conn): array
{
    return getAdminReservations($conn);
}

function searchReservations(PDO $conn, string $query): array
{
    return getAdminReservations($conn, $query);
}

/* ========================================================
   EMAIL - NOTIFICATIONS RÉSERVATION
   ======================================================== */

function sendConfirmationEmail(
    string $prenom,
    string $nom,
    string $contact,
    array $reservations,
    array $roomCatalog = []
): bool {
    return sendReservationNotificationEmail('created', $prenom, $nom, $contact, $reservations, $roomCatalog);
}

function sendReservationNotificationEmail(
    string $type,
    string $prenom,
    string $nom,
    string $contact,
    array $reservations,
    array $roomCatalog = []
): bool {
    $email = extractEmailFromContact($contact);

    if ($email === '' || !mailConfigurationIsReady()) {
        return false;
    }

    $fromAddress = getMailFromAddress();
    $replyToAddress = getMailReplyToAddress($fromAddress);

    $subject = encodeMailHeader(reservationEmailSubject($type));
    $body = buildReservationEmailBody($type, $prenom, $nom, $reservations, $roomCatalog);
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . encodeMailHeader((string) MAIL_FROM_NAME) . ' <' . $fromAddress . '>',
        'Reply-To: ' . $replyToAddress,
        'X-Mailer: PHP/' . phpversion(),
    ];

    return mail($email, $subject, $body, implode("\r\n", $headers), '-f' . $fromAddress);
}

function mailConfigurationIsReady(): bool
{
    if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) {
        return false;
    }

    $requiredConstants = [
        'MAIL_FROM_ADDRESS',
        'MAIL_FROM_NAME',
    ];

    foreach ($requiredConstants as $constantName) {
        if (!defined($constantName) || trim((string) constant($constantName)) === '') {
            return false;
        }
    }

    return getMailFromAddress() !== '';
}

function getMailFromAddress(): string
{
    $configuredAddress = defined('MAIL_FROM_ADDRESS') ? trim((string) MAIL_FROM_ADDRESS) : '';

    if ($configuredAddress !== '' && !mailAddressUsesExternalProvider($configuredAddress)) {
        return filter_var($configuredAddress, FILTER_VALIDATE_EMAIL) ? $configuredAddress : '';
    }

    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $serverName = strtolower(trim(explode(':', $serverName)[0]));

    if ($serverName !== '' && $serverName !== 'localhost' && strpos($serverName, '.') !== false) {
        return 'no-reply@' . $serverName;
    }

    return filter_var($configuredAddress, FILTER_VALIDATE_EMAIL) ? $configuredAddress : '';
}

function getMailReplyToAddress(string $fallbackAddress): string
{
    $replyToAddress = defined('MAIL_REPLY_TO_ADDRESS') ? trim((string) MAIL_REPLY_TO_ADDRESS) : '';

    if (filter_var($replyToAddress, FILTER_VALIDATE_EMAIL)) {
        return $replyToAddress;
    }

    return $fallbackAddress;
}

function mailAddressUsesExternalProvider(string $email): bool
{
    $domain = strtolower(substr(strrchr($email, '@') ?: '', 1));

    return in_array($domain, ['gmail.com', 'outlook.com', 'hotmail.com', 'yahoo.com'], true);
}

function encodeMailHeader(string $value): string
{
    return '=?UTF-8?B?' . base64_encode($value) . '?=';
}

function extractEmailFromContact(string $contact): string
{
    $contact = trim($contact);

    if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
        return $contact;
    }

    return '';
}

function reservationEmailSubject(string $type): string
{
    switch ($type) {
        case 'updated':
            return 'Votre réservation e-llusion a été modifiée';
        case 'deleted':
            return 'Votre réservation e-llusion a été supprimée';
        default:
            return 'Votre réservation e-llusion est confirmée';
    }
}

function reservationEmailTitle(string $type): string
{
    switch ($type) {
        case 'updated':
            return 'Réservation modifiée';
        case 'deleted':
            return 'Réservation supprimée';
        default:
            return 'Réservation confirmée';
    }
}

function reservationEmailIntro(string $type): string
{
    switch ($type) {
        case 'updated':
            return 'Votre réservation pour l’exposition e-llusion vient d’être modifiée. Voici le nouveau récapitulatif.';
        case 'deleted':
            return 'Votre réservation pour l’exposition e-llusion vient d’être supprimée. Voici le récapitulatif de la réservation annulée.';
        default:
            return 'Votre réservation pour l’exposition e-llusion est bien enregistrée. Voici le récapitulatif de votre visite.';
    }
}

function reservationEmailStatusColor(string $type): string
{
    switch ($type) {
        case 'deleted':
            return '#e71919';
        case 'updated':
        default:
            return '#3ce8d7';
    }
}

function buildReservationEmailBody(
    string $type,
    string $prenom,
    string $nom,
    array $reservations,
    array $roomCatalog = []
): string {
    $title = reservationEmailTitle($type);
    $intro = reservationEmailIntro($type);
    $statusColor = reservationEmailStatusColor($type);
    $contactAddress = defined('MAIL_FROM_ADDRESS') ? (string) MAIL_FROM_ADDRESS : '';

    $body = '<html><body style="margin:0; padding:0; background:#071114; font-family:Helvetica, Arial, sans-serif; color:#ffffff;">';
    $body .= '<div style="max-width:680px; margin:0 auto; padding:28px 18px;">';
    $body .= '<div style="background:#000000; border:2px solid #3ce8d7; border-radius:18px; overflow:hidden;">';
    $body .= '<div style="padding:28px 30px; border-bottom:1px solid rgba(60,232,215,.35);">';
    $body .= '<p style="margin:0 0 12px; color:#3ce8d7; font-size:14px; font-weight:700;">e-llusion</p>';
    $body .= '<h1 style="margin:0; color:#ffffff; font-size:34px; line-height:1.05;">' . htmlspecialchars($title) . '</h1>';
    $body .= '<p style="margin:18px 0 0; color:#d6fbf7; font-size:17px; line-height:1.45;">Bonjour <strong>' . htmlspecialchars(trim($prenom . ' ' . $nom)) . '</strong>,<br>' . htmlspecialchars($intro) . '</p>';
    $body .= '</div>';

    $body .= '<div style="padding:26px 30px;">';
    $body .= '<table style="width:100%; border-collapse:separate; border-spacing:0 12px;">';

    foreach ($reservations as $reservation) {
        $body .= '<tr>';
        $body .= '<td style="padding:18px; background:#071114; border:1px solid rgba(60,232,215,.55); border-radius:14px;">';
        $body .= '<p style="margin:0 0 8px; color:' . $statusColor . '; font-size:13px; font-weight:800;">' . htmlspecialchars(reservationEmailIdLabel($reservation)) . '</p>';
        $body .= '<h2 style="margin:0 0 12px; color:#ffffff; font-size:22px;">' . htmlspecialchars(reservationEmailRoomLabel($reservation, $roomCatalog)) . '</h2>';
        $body .= '<p style="margin:0 0 6px; color:#d6fbf7; font-size:16px;"><strong>Date :</strong> ' . htmlspecialchars(reservationEmailDateLabel($reservation)) . '</p>';
        $body .= '<p style="margin:0 0 6px; color:#d6fbf7; font-size:16px;"><strong>Heure :</strong> ' . htmlspecialchars(reservationEmailTimeLabel($reservation)) . '</p>';
        $body .= '<p style="margin:0 0 6px; color:#d6fbf7; font-size:16px;"><strong>Nombre de personnes :</strong> ' . htmlspecialchars((string) reservationEmailPeople($reservation)) . '</p>';
        $body .= '<p style="margin:0; color:#d6fbf7; font-size:16px;"><strong>Buffet du jeudi à 19h :</strong> ' . htmlspecialchars(reservationEmailBuffetLabel($reservation)) . '</p>';
        $body .= '</td>';
        $body .= '</tr>';
    }

    $body .= '</table>';

    $body .= '<div style="margin-top:18px; padding:18px; background:#befbf5; border-radius:14px; color:#000000;">';
    $body .= '<p style="margin:0; font-size:15px; line-height:1.45;"><strong>À retenir :</strong> votre email sert aussi d’identifiant de connexion pour retrouver, modifier ou supprimer votre réservation.</p>';
    $body .= '</div>';

    if ($contactAddress !== '') {
        $body .= '<p style="margin:24px 0 0; color:#d6fbf7; font-size:14px;">Contact : <a style="color:#3ce8d7;" href="mailto:' . htmlspecialchars($contactAddress) . '">' . htmlspecialchars($contactAddress) . '</a></p>';
    }

    $body .= '</div>';
    $body .= '</div>';
    $body .= '</div>';
    $body .= '</body></html>';

    return $body;
}

function buildReservationEmailText(
    string $type,
    string $prenom,
    string $nom,
    array $reservations,
    array $roomCatalog = []
): string {
    $lines = [
        reservationEmailTitle($type),
        '',
        'Bonjour ' . trim($prenom . ' ' . $nom) . ',',
        reservationEmailIntro($type),
        '',
    ];

    foreach ($reservations as $reservation) {
        $lines[] = reservationEmailIdLabel($reservation);
        $lines[] = reservationEmailRoomLabel($reservation, $roomCatalog);
        $lines[] = 'Date : ' . reservationEmailDateLabel($reservation);
        $lines[] = 'Heure : ' . reservationEmailTimeLabel($reservation);
        $lines[] = 'Personnes : ' . reservationEmailPeople($reservation);
        $lines[] = 'Buffet jeudi 19h : ' . reservationEmailBuffetLabel($reservation);
        $lines[] = '';
    }

    return implode("\n", $lines);
}

function reservationEmailIdLabel(array $reservation): string
{
    $id = $reservation['reservation_id'] ?? $reservation['id'] ?? null;

    return $id ? 'Réservation #' . $id : 'Récapitulatif';
}

function reservationEmailRoomLabel(array $reservation, array $roomCatalog = []): string
{
    $roomNumber = (string) ($reservation['numero_salle'] ?? $reservation['room'] ?? '');
    $roomName = (string) ($reservation['nom_salle'] ?? '');

    if ($roomName === '' && isset($roomCatalog[$roomNumber])) {
        $roomName = (string) ($roomCatalog[$roomNumber]['title'] ?? '');
    }

    return trim('Salle ' . $roomNumber . ($roomName !== '' ? ' · ' . $roomName : ''));
}

function reservationEmailDateLabel(array $reservation): string
{
    $date = (string) ($reservation['date_jour'] ?? '');
    $dayName = (string) ($reservation['nom_jour'] ?? $reservation['day'] ?? '');

    if ($date === '') {
        return $dayName !== '' ? ucfirst($dayName) : 'Date à vérifier';
    }

    $parsedDate = DateTimeImmutable::createFromFormat('Y-m-d', $date);

    if (!$parsedDate) {
        return trim($dayName . ' ' . $date);
    }

    return trim($dayName . ' ' . $parsedDate->format('d/m/Y'));
}

function reservationEmailTimeLabel(array $reservation): string
{
    $time = (string) ($reservation['heure_debut'] ?? $reservation['time'] ?? '');
    $parsedTime = DateTimeImmutable::createFromFormat('H:i:s', $time);

    if ($parsedTime) {
        return $parsedTime->format('H:i');
    }

    return $time !== '' ? substr($time, 0, 5) : 'Horaire à vérifier';
}

function reservationEmailPeople(array $reservation): int
{
    return (int) ($reservation['nombre_personnes'] ?? $reservation['people'] ?? 1);
}

function reservationEmailBuffetLabel(array $reservation): string
{
    return !empty($reservation['participe_buffet']) ? 'Oui' : 'Non';
}
