<?php
/* --------------------------------------------------------
   Fonctions BDD - e-llusion / SAE 203
   À inclure dans chaque page qui a besoin de la BDD
   -------------------------------------------------------- */

require_once 'connexion.php'; // charge la connexion $conn


/* ========================================================
   SALLES
   ======================================================== */

// Retourne toutes les salles
function getSalles($conn) {
    $req = $conn->prepare("SELECT * FROM salles ORDER BY numero_salle ASC");
    $req->execute();
    return $req->fetchAll();
}

// Retourne une salle par son id
function getSalleById($conn, $id) {
    $req = $conn->prepare("SELECT * FROM salles WHERE id = :id");
    $req->execute([':id' => $id]);
    return $req->fetch();
}


/* ========================================================
   CRÉNEAUX
   ======================================================== */

// Retourne tous les créneaux avec leur jour associé
function getCreneaux($conn) {
    $req = $conn->prepare("
        SELECT c.id, c.heure_debut, j.date_jour, j.nom_jour
        FROM creneaux c
        JOIN jour j ON c.jour_id = j.id
        ORDER BY j.date_jour ASC, c.heure_debut ASC
    ");
    $req->execute();
    return $req->fetchAll();
}


/* ========================================================
   PLACES RESTANTES
   ======================================================== */

// Retourne le nombre de places restantes pour une salle + créneau donnés
function getPlacesRestantes($conn, $salle_creneaux_id) {
    // Capacité max de ce slot
    $req = $conn->prepare("
        SELECT capacite_max FROM salle_creneaux WHERE id = :id
    ");
    $req->execute([':id' => $salle_creneaux_id]);
    $slot = $req->fetch();

    // Nombre de réservations confirmées sur ce slot
    $req2 = $conn->prepare("
        SELECT COUNT(*) AS nb
        FROM reservation_creneaux
        WHERE salle_creneaux_id = :id
    ");
    $req2->execute([':id' => $salle_creneaux_id]);
    $nb = $req2->fetch()['nb'];

    return $slot['capacite_max'] - $nb;
}


/* ========================================================
   RÉSERVATIONS — VISITEUR
   ======================================================== */

// Crée un visiteur et retourne son id
function createVisiteur($conn, $nom, $prenom, $moyen_comm, $categorie_id) {
    $req = $conn->prepare("
        INSERT INTO visiteurs (nom, prenom, moyen_comm, categories_visiteur_id)
        VALUES (:nom, :prenom, :moyen_comm, :categorie_id)
    ");
    $req->execute([
        ':nom'          => $nom,
        ':prenom'       => $prenom,
        ':moyen_comm'   => $moyen_comm,
        ':categorie_id' => $categorie_id
    ]);
    return $conn->lastInsertId();
}

// Crée une réservation et retourne son id
function createReservation($conn, $visiteurs_id, $participe_buffet = 0) {
    $req = $conn->prepare("
        INSERT INTO reservation (visiteurs_id, participe_buffet)
        VALUES (:visiteurs_id, :participe_buffet)
    ");
    $req->execute([
        ':visiteurs_id'    => $visiteurs_id,
        ':participe_buffet'=> $participe_buffet
    ]);
    return $conn->lastInsertId();
}

// Associe une réservation à un slot salle+créneau
function createReservationCreneau($conn, $reservation_id, $salle_creneaux_id) {
    $req = $conn->prepare("
        INSERT INTO reservation_creneaux (reservation_id1, salle_creneaux_id)
        VALUES (:reservation_id, :salle_creneaux_id)
    ");
    $req->execute([
        ':reservation_id'    => $reservation_id,
        ':salle_creneaux_id' => $salle_creneaux_id
    ]);
}

// Retourne une réservation avec les infos visiteur
function getReservationById($conn, $reservation_id) {
    $req = $conn->prepare("
        SELECT r.id, r.participe_buffet, v.nom, v.prenom, v.moyen_comm
        FROM reservation r
        JOIN visiteurs v ON r.visiteurs_id = v.id
        WHERE r.id = :id
    ");
    $req->execute([':id' => $reservation_id]);
    return $req->fetch();
}

// Modifie le slot (salle + créneau) d'une réservation
function updateReservation($conn, $reservation_creneaux_id, $nouveau_salle_creneaux_id) {
    $req = $conn->prepare("
        UPDATE reservation_creneaux
        SET salle_creneaux_id = :nouveau_id
        WHERE id = :id
    ");
    $req->execute([
        ':nouveau_id' => $nouveau_salle_creneaux_id,
        ':id'         => $reservation_creneaux_id
    ]);
}

// Supprime une réservation (et ses lignes reservation_creneaux)
function cancelReservation($conn, $reservation_id) {
    // Supprime d'abord les lignes associées
    $req = $conn->prepare("
        DELETE FROM reservation_creneaux WHERE reservation_id1 = :id
    ");
    $req->execute([':id' => $reservation_id]);

    // Supprime ensuite la réservation
    $req2 = $conn->prepare("DELETE FROM reservation WHERE id = :id");
    $req2->execute([':id' => $reservation_id]);
}


/* ========================================================
   RÉSERVATIONS — ADMIN
   ======================================================== */

// Retourne toutes les réservations avec infos visiteur, salle et créneau
function getAdminPlanning($conn) {
    $req = $conn->prepare("
        SELECT
            r.id AS reservation_id,
            v.nom, v.prenom, v.moyen_comm,
            s.numero_salle, s.nom AS nom_salle,
            j.nom_jour, j.date_jour,
            c.heure_debut,
            r.participe_buffet
        FROM reservation r
        JOIN visiteurs v ON r.visiteurs_id = v.id
        JOIN reservation_creneaux rc ON rc.reservation_id1 = r.id
        JOIN salle_creneaux sc ON rc.salle_creneaux_id = sc.id
        JOIN salles s ON sc.salles_id = s.id
        JOIN creneaux c ON sc.creneaux_id = c.id
        JOIN jour j ON c.jour_id = j.id
        ORDER BY j.date_jour ASC, c.heure_debut ASC, s.numero_salle ASC
    ");
    $req->execute();
    return $req->fetchAll();
}

// Recherche une réservation par nom, prénom ou mail
function searchReservations($conn, $query) {
    $like = "%" . $query . "%";
    $req = $conn->prepare("
        SELECT r.id AS reservation_id, v.nom, v.prenom, v.moyen_comm,
               s.numero_salle, j.nom_jour, c.heure_debut
        FROM reservation r
        JOIN visiteurs v ON r.visiteurs_id = v.id
        JOIN reservation_creneaux rc ON rc.reservation_id1 = r.id
        JOIN salle_creneaux sc ON rc.salle_creneaux_id = sc.id
        JOIN salles s ON sc.salles_id = s.id
        JOIN creneaux c ON sc.creneaux_id = c.id
        JOIN jour j ON c.jour_id = j.id
        WHERE v.nom LIKE :q OR v.prenom LIKE :q OR v.moyen_comm LIKE :q
    ");
    $req->execute([':q' => $like]);
    return $req->fetchAll();
}
?>
