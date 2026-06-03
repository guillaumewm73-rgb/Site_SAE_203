-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : ijtebowcompte13.mysql.db
-- Généré le : mer. 03 juin 2026 à 11:02
-- Version du serveur : 5.6.51-log
-- Version de PHP : 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ijtebowcompte13`
--
CREATE DATABASE IF NOT EXISTS `ijtebowcompte13` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `ijtebowcompte13`;

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `login` varchar(45) NOT NULL,
  `password_hash` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`id`, `login`, `password_hash`) VALUES
(0, 'toto_des_fraises', 'toto_73@univ');

-- --------------------------------------------------------

--
-- Structure de la table `categories_visiteur`
--

CREATE TABLE `categories_visiteur` (
  `id` int(11) NOT NULL,
  `libelle` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `categories_visiteur`
--

INSERT INTO `categories_visiteur` (`id`, `libelle`) VALUES
(1, 'Enseignant'),
(2, 'Personnel USMB'),
(3, 'Visiteur extérieur'),
(4, 'Professionnel / Partenaire');

-- --------------------------------------------------------

--
-- Structure de la table `creneaux`
--

CREATE TABLE `creneaux` (
  `id` int(11) NOT NULL,
  `heure_debut` time NOT NULL,
  `jour_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `creneaux`
--

INSERT INTO `creneaux` (`id`, `heure_debut`, `jour_id`) VALUES
(1, '15:00:00', 1),
(2, '15:30:00', 1),
(3, '16:00:00', 1),
(4, '16:30:00', 1),
(5, '17:00:00', 1),
(6, '17:30:00', 1),
(7, '18:00:00', 1),
(8, '19:00:00', 1),
(9, '19:30:00', 1),
(10, '20:00:00', 1),
(11, '09:30:00', 2),
(12, '10:00:00', 2),
(13, '10:30:00', 2),
(14, '11:00:00', 2);

-- --------------------------------------------------------

--
-- Structure de la table `jour`
--

CREATE TABLE `jour` (
  `id` int(11) NOT NULL,
  `date_jour` date NOT NULL,
  `nom_jour` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `jour`
--

INSERT INTO `jour` (`id`, `date_jour`, `nom_jour`) VALUES
(1, '2026-06-18', 'Jeudi'),
(2, '2026-06-19', 'Vendredi');

-- --------------------------------------------------------

--
-- Structure de la table `reservation`
--

CREATE TABLE `reservation` (
  `id` int(11) NOT NULL,
  `visiteurs_id` int(11) NOT NULL,
  `salle_creneaux_id` int(11) NOT NULL,
  `nombre_personnes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `salles`
--

CREATE TABLE `salles` (
  `id` int(11) NOT NULL,
  `numero_salle` varchar(45) DEFAULT NULL,
  `nom` varchar(45) NOT NULL,
  `description` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `salles`
--

INSERT INTO `salles` (`id`, `numero_salle`, `nom`, `description`) VALUES
(1, '001', 'Horizon', 'Salle TP12'),
(2, '002', 'L\'Envers du Décor', 'Salle TP21'),
(3, '005', 'La Pépinière', 'Salle TP22'),
(4, '021', 'Societ-e', 'Salle TP11');

-- --------------------------------------------------------

--
-- Structure de la table `salle_creneaux`
--

CREATE TABLE `salle_creneaux` (
  `id` int(11) NOT NULL,
  `capacite_max` int(11) NOT NULL,
  `salles_id` int(11) NOT NULL,
  `creneaux_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `salle_creneaux`
--

INSERT INTO `salle_creneaux` (`id`, `capacite_max`, `salles_id`, `creneaux_id`) VALUES
(1, 12, 1, 1),
(2, 12, 1, 2),
(3, 12, 1, 3),
(4, 12, 1, 4),
(5, 12, 1, 5),
(6, 12, 1, 6),
(7, 12, 1, 7),
(8, 12, 1, 8),
(9, 12, 1, 9),
(10, 12, 1, 10),
(11, 12, 1, 11),
(12, 12, 1, 12),
(13, 12, 1, 13),
(14, 12, 1, 14),
(15, 12, 2, 1),
(16, 12, 2, 2),
(17, 12, 2, 3),
(18, 12, 2, 4),
(19, 12, 2, 5),
(20, 12, 2, 6),
(21, 12, 2, 7),
(22, 12, 2, 8),
(23, 12, 2, 9),
(24, 12, 2, 10),
(25, 12, 2, 11),
(26, 12, 2, 12),
(27, 12, 2, 13),
(28, 12, 2, 14),
(29, 12, 3, 1),
(30, 12, 3, 2),
(31, 12, 3, 3),
(32, 12, 3, 4),
(33, 12, 3, 5),
(34, 12, 3, 6),
(35, 12, 3, 7),
(36, 12, 3, 8),
(37, 12, 3, 9),
(38, 12, 3, 10),
(39, 12, 3, 11),
(40, 12, 3, 12),
(41, 12, 3, 13),
(42, 12, 3, 14),
(43, 12, 4, 1),
(44, 12, 4, 2),
(45, 12, 4, 3),
(46, 12, 4, 4),
(47, 12, 4, 5),
(48, 12, 4, 6),
(49, 12, 4, 7),
(50, 12, 4, 8),
(51, 12, 4, 9),
(52, 12, 4, 10),
(53, 12, 4, 11),
(54, 12, 4, 12),
(55, 12, 4, 13),
(56, 12, 4, 14);

-- --------------------------------------------------------

--
-- Structure de la table `visiteurs`
--

CREATE TABLE `visiteurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(45) NOT NULL,
  `prenom` varchar(45) DEFAULT NULL,
  `moyen_comm` varchar(45) NOT NULL,
  `categories_visiteur_id` int(11) NOT NULL,
  `participe_buffet` binary(2) DEFAULT NULL,
  `commanditaire` varchar(45) DEFAULT NULL,
  `flag` binary(2) DEFAULT NULL,
  `mdp` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `categories_visiteur`
--
ALTER TABLE `categories_visiteur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`);

--
-- Index pour la table `creneaux`
--
ALTER TABLE `creneaux`
  ADD PRIMARY KEY (`id`,`jour_id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`),
  ADD KEY `fk_creneaux_jour1_idx` (`jour_id`);

--
-- Index pour la table `jour`
--
ALTER TABLE `jour`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`);

--
-- Index pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`id`,`visiteurs_id`,`salle_creneaux_id`),
  ADD KEY `fk_reservation_visiteurs1_idx` (`visiteurs_id`),
  ADD KEY `fk_reservation_salle_creneaux1_idx` (`salle_creneaux_id`);

--
-- Index pour la table `salles`
--
ALTER TABLE `salles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`);

--
-- Index pour la table `salle_creneaux`
--
ALTER TABLE `salle_creneaux`
  ADD PRIMARY KEY (`id`,`salles_id`,`creneaux_id`),
  ADD KEY `fk_salle_creneaux_salles1_idx` (`salles_id`),
  ADD KEY `fk_salle_creneaux_creneaux1_idx` (`creneaux_id`);

--
-- Index pour la table `visiteurs`
--
ALTER TABLE `visiteurs`
  ADD PRIMARY KEY (`id`,`categories_visiteur_id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`),
  ADD KEY `fk_visiteurs_categories_visiteur1_idx` (`categories_visiteur_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categories_visiteur`
--
ALTER TABLE `categories_visiteur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `creneaux`
--
ALTER TABLE `creneaux`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `visiteurs`
--
ALTER TABLE `visiteurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `creneaux`
--
ALTER TABLE `creneaux`
  ADD CONSTRAINT `fk_creneaux_jour1` FOREIGN KEY (`jour_id`) REFERENCES `jour` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `fk_reservation_salle_creneaux1` FOREIGN KEY (`salle_creneaux_id`) REFERENCES `salle_creneaux` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_reservation_visiteurs1` FOREIGN KEY (`visiteurs_id`) REFERENCES `visiteurs` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `salle_creneaux`
--
ALTER TABLE `salle_creneaux`
  ADD CONSTRAINT `fk_salle_creneaux_creneaux1` FOREIGN KEY (`creneaux_id`) REFERENCES `creneaux` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_salle_creneaux_salles1` FOREIGN KEY (`salles_id`) REFERENCES `salles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `visiteurs`
--
ALTER TABLE `visiteurs`
  ADD CONSTRAINT `fk_visiteurs_categories_visiteur1` FOREIGN KEY (`categories_visiteur_id`) REFERENCES `categories_visiteur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
