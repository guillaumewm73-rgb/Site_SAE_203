CREATE DATABASE IF NOT EXISTS sae203_ellusion
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE sae203_ellusion;

CREATE TABLE salles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  numero_salle VARCHAR(3) NOT NULL,
  tp VARCHAR(20) NOT NULL,
  nom VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  ordre_affichage INT NOT NULL
);

CREATE TABLE jours (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom_jour VARCHAR(30) NOT NULL,
  date_visite DATE NOT NULL
);

CREATE TABLE creneaux (
  id INT AUTO_INCREMENT PRIMARY KEY,
  jour_id INT NOT NULL,
  heure_debut TIME NOT NULL,
  FOREIGN KEY (jour_id) REFERENCES jours(id)
);

CREATE TABLE salle_creneaux (
  id INT AUTO_INCREMENT PRIMARY KEY,
  salle_id INT NOT NULL,
  creneau_id INT NOT NULL,
  capacite_max INT NOT NULL DEFAULT 12,
  FOREIGN KEY (salle_id) REFERENCES salles(id),
  FOREIGN KEY (creneau_id) REFERENCES creneaux(id)
);

