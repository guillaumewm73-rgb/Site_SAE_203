<?php
/* --------------------------------------------------------
   Fonctions BDD - e-llusion / SAE 203
   À inclure dans chaque page qui a besoin de la BDD.
   -------------------------------------------------------- */

require_once __DIR__ . '/page_connexion.php';

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
    int $participeBuffet = 0
): string {
    $req = $conn->prepare("
        INSERT INTO visiteurs (nom, prenom, moyen_comm, categories_visiteur_id, participe_buffet)
        VALUES (:nom, :prenom, :moyen_comm, :categorie_id, :participe_buffet)
    ");
    $req->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':moyen_comm' => $moyenComm,
        ':categorie_id' => $categorieId,
        ':participe_buffet' => $participeBuffet,
    ]);

    return $conn->lastInsertId();
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
   EMAIL - CONFIRMATION RÉSERVATION
   ======================================================== */

function sendConfirmationEmail(
    string $prenom,
    string $nom,
    string $contact,
    array $reservations,
    array $roomCatalog = []
): bool {
    if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) {
        return false;
    }

    try {
        require_once __DIR__ . '/vendor/autoload.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = MAIL_SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_SMTP_USER;
        $mail->Password = MAIL_SMTP_PASSWORD;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = MAIL_SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        // Destinataire et expéditeur
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress(extractEmailFromContact($contact), trim($prenom . ' ' . $nom));
        $mail->addReplyTo(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);

        // Sujet et contenu
        $mail->isHTML(true);
        $mail->Subject = 'Confirmation de votre réservation - e-llusion';
        $mail->Body = buildConfirmationEmailBody($prenom, $nom, $reservations, $roomCatalog);
        $mail->AltBody = strip_tags($mail->Body);

        return $mail->send();
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log('Erreur PHPMailer: ' . $mail->ErrorInfo);
        return false;
    } catch (Exception $e) {
        error_log('Erreur lors de l\'envoi d\'email: ' . $e->getMessage());
        return false;
    }
}

function extractEmailFromContact(string $contact): string
{
    // Si c'est déjà un email (contient @)
    if (strpos($contact, '@') !== false) {
        return trim($contact);
    }
    // Sinon, c'est un téléphone, on ne peut pas envoyer de mail
    return '';
}

function buildConfirmationEmailBody(
    string $prenom,
    string $nom,
    array $reservations,
    array $roomCatalog = []
): string {
    $body = '<html><body style="font-family: Arial, sans-serif; color: #333;">';
    $body .= '<div style="max-width: 600px; margin: 0 auto; background-color: #f9f9f9; padding: 20px; border-radius: 8px;">';

    // En-tête
    $body .= '<h1 style="color: #2c3e50; margin-bottom: 20px;">Confirmation de réservation</h1>';
    $body .= '<p>Bonjour <strong>' . htmlspecialchars($prenom . ' ' . $nom) . '</strong>,</p>';
    $body .= '<p>Merci de votre réservation pour l\'exposition <strong>e-llusion</strong>. Voici le détail de vos créneaux :</p>';

    // Détail des réservations
    $body .= '<table style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: white;">';
    $body .= '<thead>';
    $body .= '<tr style="background-color: #34495e; color: white;">';
    $body .= '<th style="padding: 12px; text-align: left; border: 1px solid #bdc3c7;">Salle</th>';
    $body .= '<th style="padding: 12px; text-align: left; border: 1px solid #bdc3c7;">Créneau</th>';
    $body .= '<th style="padding: 12px; text-align: center; border: 1px solid #bdc3c7;">Personnes</th>';
    $body .= '</tr>';
    $body .= '</thead>';
    $body .= '<tbody>';

    foreach ($reservations as $reservation) {
        $room = isset($roomCatalog[$reservation['room']])
            ? $roomCatalog[$reservation['room']]['title'] ?? $reservation['room']
            : $reservation['room'];

        $body .= '<tr style="border-bottom: 1px solid #bdc3c7;">';
        $body .= '<td style="padding: 10px; border: 1px solid #bdc3c7;">' . htmlspecialchars($room) . '</td>';
        $body .= '<td style="padding: 10px; border: 1px solid #bdc3c7;">' . htmlspecialchars($reservation['time']) . '</td>';
        $body .= '<td style="padding: 10px; border: 1px solid #bdc3c7; text-align: center;">' . (int)$reservation['people'] . '</td>';
        $body .= '</tr>';
    }

    $body .= '</tbody>';
    $body .= '</table>';

    // Informations importantes
    $body .= '<div style="background-color: #ecf0f1; padding: 15px; border-left: 4px solid #3498db; margin: 20px 0;">';
    $body .= '<p><strong>Important :</strong></p>';
    $body .= '<ul style="margin: 10px 0; padding-left: 20px;">';
    $body .= '<li>Conservez ce mail comme preuve de votre réservation</li>';
    $body .= '<li>Présentez-vous 15 minutes avant votre créneau</li>';
    $body .= '<li>Pour toute modification, contactez-nous</li>';
    $body .= '</ul>';
    $body .= '</div>';

    // Pied de page
    $body .= '<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #bdc3c7; font-size: 12px; color: #7f8c8d;">';
    $body .= '<p>e-llusion - Exposition interactive</p>';
    $body .= '<p>Pour nous contacter : <a href="mailto:' . htmlspecialchars(MAIL_FROM_ADDRESS) . '">' . htmlspecialchars(MAIL_FROM_ADDRESS) . '</a></p>';
    $body .= '<p>© ' . date('Y') . ' - Tous droits réservés</p>';
    $body .= '</div>';

    $body .= '</div></body></html>';

    return $body;
}
