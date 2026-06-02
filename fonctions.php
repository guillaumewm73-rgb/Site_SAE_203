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

    $req2 = $conn->prepare('SELECT COUNT(*) AS nb FROM reservation WHERE salle_creneaux_id = :id');
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
            COUNT(r.id) AS reserved_count
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

function createReservation(PDO $conn, int $visiteursId, int $salleCreneauxId): string
{
    $req = $conn->prepare("
        INSERT INTO reservation (visiteurs_id, salle_creneaux_id)
        VALUES (:visiteurs_id, :salle_creneaux_id)
    ");
    $req->execute([
        ':visiteurs_id' => $visiteursId,
        ':salle_creneaux_id' => $salleCreneauxId,
    ]);

    return $conn->lastInsertId();
}

function getReservationById(PDO $conn, int $reservationId): ?array
{
    $req = $conn->prepare("
        SELECT
            r.id AS reservation_id,
            r.visiteurs_id,
            r.salle_creneaux_id,
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
    int $participeBuffet
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
            SET salle_creneaux_id = :salle_creneaux_id
            WHERE id = :reservation_id AND visiteurs_id = :visiteur_id
        ");
        $reqReservation->execute([
            ':salle_creneaux_id' => $salleCreneauxId,
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
