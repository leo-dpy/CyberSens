-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 06 avr. 2026 à 17:09
-- Version du serveur : 8.4.7
-- Version de PHP : 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `cybersens`
--

-- --------------------------------------------------------

--
-- Structure de la table `badges`
--

DROP TABLE IF EXISTS `badges`;
CREATE TABLE IF NOT EXISTS `badges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'award',
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#6366f1',
  `category` enum('progression','quiz','phishing','special','streak') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'progression',
  `requirement_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requirement_value` int DEFAULT '0',
  `xp_bonus` int DEFAULT '0',
  `is_secret` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `badges`
--

INSERT INTO `badges` (`id`, `name`, `description`, `icon`, `color`, `category`, `requirement_type`, `requirement_value`, `xp_bonus`, `is_secret`, `created_at`) VALUES
(1, 'Premier Pas', 'Terminez votre premier cours', 'footprints', '#10b981', 'progression', 'courses_completed', 1, 10, 0, '2026-01-07 12:12:36'),
(2, 'Apprenti', 'Terminez 3 cours', 'book-open', '#3b82f6', 'progression', 'courses_completed', 3, 25, 0, '2026-01-07 12:12:36'),
(3, 'Expert', 'Terminez tous les cours', 'graduation-cap', '#8b5cf6', 'progression', 'courses_completed', 5, 50, 0, '2026-01-07 12:12:36'),
(4, 'Étudiant Assidu', 'Atteignez le niveau 5', 'trending-up', '#f59e0b', 'progression', 'level', 5, 30, 0, '2026-01-07 12:12:36'),
(5, 'Maître Cyber', 'Atteignez le niveau 10', 'crown', '#eab308', 'progression', 'level', 10, 100, 0, '2026-01-07 12:12:36'),
(6, 'Premier Quiz', 'Réussissez votre premier quiz', 'check-circle', '#22c55e', 'quiz', 'quiz_completed', 1, 10, 0, '2026-01-07 12:12:36'),
(7, 'Sans Faute', 'Obtenez 100% à un quiz', 'star', '#fbbf24', 'quiz', 'perfect_quiz', 1, 25, 0, '2026-01-07 12:12:36'),
(8, 'Génie', 'Obtenez 100% à 5 quiz différents', 'brain', '#ec4899', 'quiz', 'perfect_quiz', 5, 75, 0, '2026-01-07 12:12:36'),
(9, 'Œil de Lynx', 'Identifiez correctement 5 tentatives de phishing', 'eye', '#06b6d4', 'phishing', 'phishing_detected', 5, 20, 0, '2026-01-07 12:12:36'),
(10, 'Détective', 'Identifiez correctement 10 tentatives de phishing', 'search', '#6366f1', 'phishing', 'phishing_detected', 10, 40, 0, '2026-01-07 12:12:36'),
(11, 'Incorruptible', 'Ne tombez dans aucun piège de phishing (10 scénarios)', 'shield-check', '#dc2626', 'phishing', 'phishing_perfect', 10, 60, 0, '2026-01-07 12:12:36'),
(12, 'Bienvenue', 'Créez votre compte CyberSens', 'user-plus', '#8b5cf6', 'special', 'account_created', 1, 5, 0, '2026-01-07 12:12:36');

-- --------------------------------------------------------

--
-- Structure de la table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `groups`
--

INSERT INTO `groups` (`id`, `name`, `created_at`) VALUES
(4, 'Staff', '2026-04-01 14:01:45'),
(6, 'ynov', '2026-04-05 21:09:17');

-- --------------------------------------------------------

--
-- Structure de la table `modules`
--

DROP TABLE IF EXISTS `modules`;
CREATE TABLE IF NOT EXISTS `modules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'book-open',
  `theme` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'blue',
  `difficulty` enum('Facile','Intermédiaire','Difficile') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Facile',
  `xp_reward` int DEFAULT '50',
  `display_order` int DEFAULT '0',
  `is_published` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`display_order`),
  KEY `idx_published` (`is_published`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `modules`
--

INSERT INTO `modules` (`id`, `title`, `description`, `icon`, `theme`, `difficulty`, `xp_reward`, `display_order`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 'Introduction à la Cybersécurité', 'Découvrez les fondamentaux de la sécurité informatique et les menaces qui pèsent sur vos données.', 'shield', 'blue', 'Facile', 50, 1, 1, '2026-04-05 19:57:05', '2026-04-05 19:57:05'),
(2, 'Sécurité des Mots de Passe', 'Apprenez à créer, gérer et protéger vos mots de passe efficacement.', 'key', 'green', 'Facile', 60, 2, 1, '2026-04-05 19:57:05', '2026-04-05 19:57:05'),
(3, 'Reconnaître le Phishing', 'Identifiez les tentatives d\'hameçonnage et protégez-vous des arnaques en ligne.', 'mail-warning', 'red', 'Intermédiaire', 75, 3, 1, '2026-04-05 19:57:05', '2026-04-05 19:57:05'),
(4, 'teste', 'je souhaite aider les gens', 'globe', 'red', 'Difficile', 50, 4, 1, '2026-04-05 20:34:54', '2026-04-05 20:34:54');

-- --------------------------------------------------------

--
-- Structure de la table `news`
--

DROP TABLE IF EXISTS `news`;
CREATE TABLE IF NOT EXISTS `news` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `event_date` date NOT NULL,
  `source` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `news`
--

INSERT INTO `news` (`id`, `title`, `description`, `event_date`, `source`, `link`, `created_at`) VALUES
(1, 'Kiabi', 'Fuite des IBAN de 20 000 clients via une attaque par Credential Stuffing.', '2026-01-07', 'Fuite Bancaire', 'https://www.kiabi.com', '2026-02-04 18:00:16'),
(2, 'Mondial Relay', 'Vol de données personnelles et détails de livraison touchant des millions de clients.', '2025-12-23', 'Vol de Données', '#', '2026-02-04 18:00:16'),
(3, 'La Poste & Banque Postale', 'Attaque DDoS massive rendant les services inaccessibles juste avant Noël.', '2025-12-22', 'Paralysie', '#', '2026-02-04 18:00:16'),
(4, 'Pass\'Sport / Ministère des Sports', 'Exfiltration de données de 3,5 millions de foyers (Identités, Sécu, IBAN).', '2025-12-19', 'Fuite Massive', '#', '2026-02-04 18:00:16'),
(5, 'Ministère de l\'Intérieur', 'Intrusion serveurs messagerie, accès fichiers police sensibles (TAJ, FPR).', '2025-12-11', 'Intrusion Critique', '#', '2026-02-04 18:00:16'),
(6, 'MédecinDirect', 'Violation de données de santé très sensibles (motifs consultation, échanges médicaux).', '2025-12-05', 'Données Santé', '#', '2026-02-04 18:00:16'),
(7, 'Missions Locales', 'Fuite impactant 1,6 million de jeunes suivis par le réseau.', '2025-12-01', 'Données Sociales', '#', '2026-02-04 18:00:16'),
(8, 'Fédération Française de Football', 'Troisième cyberattaque en deux ans, touchant les données des licenciés.', '2025-11-26', 'Piratage', '#', '2026-02-04 18:00:16'),
(9, 'Colis Privé', 'Compromission des données de contact de millions de clients (risque phishing).', '2025-11-21', 'Fuite Clients', '#', '2026-02-04 18:00:16'),
(10, 'Pajemploi / URSSAF', 'Vol de données touchant 1,2 million d\'usagers (employeurs/salariés).', '2025-11-14', 'Fuite Admin', '#', '2026-02-04 18:00:16'),
(11, 'Eurofiber France', 'Attaque critique infrastructure, données de 3600 organisations exposées (SNCF, Airbus...).', '2025-11-13', 'Infrastructure', '#', '2026-02-04 18:00:16'),
(12, 'France Travail', 'Nouvelle compromission ciblant 31 000 comptes via infostealers.', '2025-10-27', 'Piratage Compte', '#', '2026-02-04 18:00:16'),
(13, 'Lycées publics Hauts-de-France', 'Ransomware Qilin paralysant 60 000 ordinateurs (80% des lycées) et vol données.', '2025-10-10', 'Rançongiciel', '#', '2026-02-04 18:00:16'),
(14, 'Hôpitaux publics Hauts-de-France', 'Attaque visant les serveurs d\'identité des patients, retour au papier.', '2025-09-08', 'Hôpital', '#', '2026-02-04 18:00:16'),
(15, 'Auchan', 'Cyberattaque ciblant les comptes de fidélité (cagnottes, historiques d\'achat).', '2025-08-21', 'Commerce', '#', '2026-02-04 18:00:16'),
(16, 'Bouygues Telecom', 'Fuite massive 6,4 millions de clients (État civil, IBAN, Coordonnées).', '2025-08-06', 'Fuite Massive', '#', '2026-02-04 18:00:16'),
(17, 'Air France-KLM', 'Fuite de données via prestataire Salesforce, membres Flying Blue touchés.', '2025-08-06', 'Supply Chain', '#', '2026-02-04 18:00:16'),
(18, 'Sorbonne Université', 'Vol de données de 32 000 étudiants et employés.', '2025-06-16', 'Université', '#', '2026-02-04 18:00:16'),
(19, 'Disneyland Paris', 'Revendication de vol de 64 Go de données confidentielles par le groupe Anubis.', '2025-06-20', 'Vol de Données', '#', '2026-02-04 18:00:16'),
(20, 'Reduction-Impots.fr', 'Vente sur dark web de données fiscales de 2 millions de Français.', '2025-05-14', 'Dark Web', '#', '2026-02-04 18:00:16');

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type` enum('info','success','warning','achievement') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT '0',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_notif` (`user_id`),
  KEY `idx_unread` (`user_id`,`is_read`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES
(4, 5, 'Nouveau badge débloqué !', 'Vous avez obtenu le badge \"Œil de Lynx\" : Identifiez correctement 5 tentatives de phishing', '', 1, NULL, '2026-01-07 14:46:56'),
(5, 1, 'Certificat obtenu !', 'Félicitations ! Vous avez obtenu le certificat pour \"aaa\" avec un score de 100%.', '', 1, NULL, '2026-01-07 14:49:17'),
(6, 1, 'Niveau supérieur!', 'Félicitations! Vous avez atteint le niveau Expert!', 'success', 1, NULL, '2026-01-08 08:07:18'),
(7, 1, 'Certificat obtenu !', 'Félicitations ! Vous avez obtenu le certificat pour \"aaaadsezgfe\" avec un score de 100%.', '', 1, NULL, '2026-01-08 08:07:18'),
(8, 1, 'Certificat obtenu !', 'Félicitations ! Vous avez obtenu le certificat pour \"la fraise\" avec un score de 100%.', '', 1, NULL, '2026-01-08 08:48:44'),
(9, 5, 'Niveau supérieur!', 'Félicitations! Vous avez atteint le niveau Maître!', 'success', 1, NULL, '2026-01-08 09:57:59'),
(10, 5, 'Certificat obtenu !', 'Félicitations ! Vous avez obtenu le certificat pour \"Module 1 : Les Malwares\" avec un score de 100%.', '', 1, NULL, '2026-01-08 09:57:59'),
(11, 5, 'Nouveau badge débloqué !', 'Vous avez obtenu le badge \"Premier Pas\" : Terminez votre premier cours', '', 1, NULL, '2026-01-08 09:57:59'),
(12, 5, 'Nouveau badge débloqué !', 'Vous avez obtenu le badge \"Étudiant Assidu\" : Atteignez le niveau 5', '', 1, NULL, '2026-01-08 09:57:59'),
(13, 1, 'Certificat obtenu !', 'Félicitations ! Vous avez obtenu le certificat pour \"Module 1 : Les Malwares\" avec un score de 88%.', '', 1, NULL, '2026-01-08 10:55:01'),
(24, 5, 'Nouveau badge débloqué !', 'Vous avez obtenu le badge \"Premier Quiz\" : Réussissez votre premier quiz', '', 1, NULL, '2026-01-08 11:15:12'),
(25, 5, 'Nouveau badge débloqué !', 'Vous avez obtenu le badge \"Bienvenue\" : Créez votre compte CyberSens', '', 1, NULL, '2026-01-08 11:15:12'),
(46, 5, 'Nouveau module débloqué !', 'Vous avez débloqué le module \"Module 3: Glossaire des Concepts de Défense\". Continuez votre progression !', '', 0, '#cours', '2026-01-08 15:34:56'),
(47, 5, 'Certificat obtenu !', 'Félicitations ! Vous avez obtenu le certificat pour \"Module 2: Glossaire des Attaques\" avec un score de 83%.', '', 0, NULL, '2026-01-08 15:34:56');

-- --------------------------------------------------------

--
-- Structure de la table `phishing_results`
--

DROP TABLE IF EXISTS `phishing_results`;
CREATE TABLE IF NOT EXISTS `phishing_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `scenario_id` int NOT NULL,
  `user_answer` tinyint(1) NOT NULL,
  `is_correct` tinyint(1) NOT NULL,
  `time_taken` int DEFAULT '0',
  `xp_earned` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_scenario` (`user_id`,`scenario_id`),
  KEY `scenario_id` (`scenario_id`),
  KEY `idx_user_phishing` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `phishing_results`
--

INSERT INTO `phishing_results` (`id`, `user_id`, `scenario_id`, `user_answer`, `is_correct`, `time_taken`, `xp_earned`, `created_at`) VALUES
(1, 5, 1, 1, 1, 2, 15, '2026-01-07 14:46:08'),
(2, 5, 2, 0, 1, 6, 15, '2026-01-07 14:46:34'),
(3, 5, 5, 0, 1, 5, 15, '2026-01-07 14:46:41'),
(4, 5, 9, 0, 1, 5, 15, '2026-01-07 14:46:49'),
(5, 5, 10, 1, 1, 6, 15, '2026-01-07 14:46:56'),
(6, 5, 3, 1, 1, 6, 20, '2026-01-07 14:47:05'),
(7, 1, 1, 1, 1, 2, 0, '2026-01-08 08:03:43'),
(8, 1, 2, 0, 1, 3, 15, '2026-01-08 08:03:51'),
(10, 1, 9, 0, 1, 5, 15, '2026-01-08 08:07:58'),
(11, 1, 8, 1, 1, 10, 20, '2026-01-08 08:49:24'),
(12, 1, 6, 1, 1, 9, 20, '2026-01-08 08:49:36'),
(25, 9, 9, 0, 1, 1, 0, '2026-04-05 21:04:55'),
(26, 9, 1, 1, 1, 1, 15, '2026-04-05 21:06:58'),
(27, 9, 2, 1, 0, 1, 0, '2026-04-05 21:07:10'),
(28, 9, 10, 0, 0, 2, 0, '2026-04-05 21:07:14'),
(29, 9, 6, 1, 1, 1, 20, '2026-04-05 21:07:16'),
(32, 11, 1, 1, 1, 1, 15, '2026-04-06 15:12:00'),
(33, 11, 2, 1, 0, 1, 0, '2026-04-06 15:36:20'),
(34, 11, 5, 0, 1, 1, 15, '2026-04-06 15:36:28');

-- --------------------------------------------------------

--
-- Structure de la table `phishing_scenarios`
--

DROP TABLE IF EXISTS `phishing_scenarios`;
CREATE TABLE IF NOT EXISTS `phishing_scenarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('email','sms','website') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'email',
  `sender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_phishing` tinyint(1) NOT NULL DEFAULT '1',
  `difficulty` enum('facile','moyen','difficile') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'facile',
  `indicators` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `explanation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `xp_reward` int DEFAULT '15',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_difficulty` (`difficulty`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `phishing_scenarios`
--

INSERT INTO `phishing_scenarios` (`id`, `title`, `type`, `sender`, `subject`, `content`, `is_phishing`, `difficulty`, `indicators`, `explanation`, `xp_reward`, `created_at`) VALUES
(1, 'Email bancaire urgent', 'email', 'securite@bnp-paribas-secure.com', 'URGENT: Votre compte sera bloqué', 'Cher client,\r\n\r\nNous avons détecté une activité suspecte sur votre compte. Pour éviter le blocage, veuillez confirmer vos informations en cliquant sur le lien ci-dessous dans les 24h.\r\n\r\n[Vérifier mon compte]\r\n\r\nCordialement,\r\nService Sécurité BNP Paribas', 1, 'facile', 'Adresse email suspecte (bnp-paribas-secure.com au lieu de bnpparibas.com), Urgence excessive, Demande de cliquer sur un lien, Pas de personnalisation', 'C\'est du PHISHING ! L\'adresse email n\'est pas celle de BNP Paribas (bnpparibas.net). Une vraie banque ne vous demandera jamais de confirmer vos informations par email avec un lien.', 15, '2026-01-07 12:12:36'),
(2, 'Notification Amazon', 'email', 'ship-confirm@amazon.fr', 'Votre commande #402-8756321 a été expédiée', 'Bonjour,\r\n\r\nBonne nouvelle ! Votre commande a été expédiée et arrivera le 15 janvier.\r\n\r\nNuméro de suivi : 1Z999AA10123456784\r\n\r\nDétails de la commande :\r\n- Echo Dot (4ème génération) - 49,99€\r\n\r\nSuivre ma livraison : https://amazon.fr/track/1Z999AA10123456784\r\n\r\nL\'équipe Amazon', 0, 'facile', 'Adresse email légitime (@amazon.fr), Informations spécifiques et cohérentes, Lien vers le domaine officiel amazon.fr, Pas de demande d\'informations sensibles', 'Cet email est LÉGITIME. L\'adresse provient bien d\'amazon.fr, le contenu est informatif sans urgence ni menace, et le lien pointe vers le domaine officiel.', 15, '2026-01-07 12:12:36'),
(3, 'Remboursement impôts', 'email', 'ne-pas-repondre@impots-gouv-remboursement.fr', 'Vous avez un remboursement de 847,50€ en attente', 'Madame, Monsieur,\r\n\r\nSuite à votre déclaration de revenus, vous bénéficiez d\'un remboursement de 847,50€.\r\n\r\nPour recevoir votre virement sous 48h, veuillez mettre à jour vos coordonnées bancaires :\r\n\r\n[Mettre à jour mes informations]\r\n\r\nDirection Générale des Finances Publiques', 1, 'moyen', 'Domaine suspect (impots-gouv-remboursement.fr), Les impôts n\'envoient jamais ce type d\'email, Demande de coordonnées bancaires, Montant précis pour appâter', 'C\'est du PHISHING ! Le site officiel des impôts est impots.gouv.fr. L\'administration fiscale ne demande jamais vos coordonnées bancaires par email.', 20, '2026-01-07 12:12:36'),
(4, 'SMS colis en attente', 'sms', '+33644582147', 'La Poste', 'La Poste: Votre colis est en attente de livraison. Payez les frais de port (1,99€) pour le recevoir: https://laposte-livraison.info/tracking', 1, 'moyen', 'Numéro de téléphone inconnu, URL suspecte (laposte-livraison.info), Demande de paiement inattendue, La Poste ne demande pas de paiement par SMS', 'C\'est du SMISHING ! La Poste n\'envoie pas de SMS demandant un paiement. L\'URL n\'est pas le site officiel (laposte.fr).', 20, '2026-01-07 12:12:36'),
(5, 'Mise à jour LinkedIn', 'email', 'messages-noreply@linkedin.com', 'Vous avez 3 nouvelles invitations', 'Bonjour Jean,\r\n\r\nVous avez 3 nouvelles invitations de connexion :\r\n\r\n- Marie Dupont, Directrice Marketing chez TechCorp\r\n- Pierre Martin, Développeur Senior\r\n- Sophie Bernard, RH Manager\r\n\r\nVoir mes invitations : https://www.linkedin.com/mynetwork/invitation-manager/\r\n\r\nCordialement,\r\nL\'équipe LinkedIn', 0, 'facile', 'Adresse email officielle LinkedIn, Lien vers linkedin.com (vérifiable au survol), Contenu cohérent avec les fonctionnalités LinkedIn, Pas de demande urgente', 'Cet email est LÉGITIME. Il provient d\'une adresse officielle LinkedIn et le lien pointe vers le vrai site.', 15, '2026-01-07 12:12:36'),
(6, 'Support Microsoft', 'email', 'support@microsoft-account-verification.com', 'Action requise: Votre compte Microsoft expire', 'Attention,\r\n\r\nVotre compte Microsoft Office 365 expire dans 24 heures. Pour éviter la perte de vos données, renouvelez immédiatement :\r\n\r\n[Renouveler maintenant - GRATUIT]\r\n\r\nSi vous ne renouvelez pas, vous perdrez l\'accès à :\r\n- Vos emails Outlook\r\n- Vos fichiers OneDrive\r\n- Votre licence Office\r\n\r\nMicrosoft Support Team', 1, 'moyen', 'Domaine email non officiel (microsoft-account-verification.com), Urgence et menace de perte de données, Les comptes Microsoft n\'expirent pas comme ça, Bouton suspect', 'C\'est du PHISHING ! Microsoft utilise microsoft.com pour ses emails. Un compte Microsoft personnel n\'expire pas et cette urgence est fausse.', 20, '2026-01-07 12:12:36'),
(7, 'Offre d\'emploi attractive', 'email', 'recrutement@entreprise-job.net', 'Poste à 4500€/mois - Télétravail 100%', 'Félicitations !\r\n\r\nVotre profil a retenu notre attention pour un poste de Gestionnaire Administratif :\r\n\r\n💰 Salaire : 4500€ net/mois\r\n🏠 100% télétravail\r\n⏰ 25h/semaine\r\n✅ Aucune expérience requise\r\n\r\nPour postuler, envoyez-nous :\r\n- Copie de votre carte d\'identité\r\n- RIB pour le versement du salaire\r\n\r\nRépondez vite, il ne reste que 3 places !\r\n\r\nService Recrutement', 1, 'difficile', 'Offre trop belle pour être vraie, Demande de documents sensibles (CNI, RIB), Urgence artificielle, Domaine email générique, Pas de nom d\'entreprise réel', 'C\'est du PHISHING et une tentative d\'arnaque ! Aucun employeur légitime ne demande votre CNI et RIB avant un entretien. Cette offre irréaliste vise à voler votre identité.', 25, '2026-01-07 12:12:36'),
(8, 'Fausse page de connexion', 'website', 'https://facebook-login.secure-auth.net', 'Connexion Facebook', 'Votre session a expiré. Veuillez vous reconnecter pour continuer.\r\n\r\n[Champ email]\r\n[Champ mot de passe]\r\n[Bouton Se connecter]\r\n\r\nMot de passe oublié ? | Créer un compte', 1, 'moyen', 'URL qui n\'est pas facebook.com, Domaine suspect secure-auth.net, Page imitant Facebook, Demande de connexion inattendue', 'C\'est du PHISHING ! L\'URL n\'est pas facebook.com. C\'est une fausse page de connexion destinée à voler vos identifiants.', 20, '2026-01-07 12:12:36'),
(9, 'Newsletter légitime FNAC', 'email', 'newsletter@fnac.com', 'Les offres de la semaine', 'Cher client Fnac,\r\n\r\nDécouvrez nos offres exceptionnelles cette semaine :\r\n\r\n📱 iPhone 15 - 899€ (-10%)\r\n🎮 PS5 + 2 manettes - 499€\r\n📚 3 livres achetés = 1 offert\r\n\r\nVoir toutes les offres : https://www.fnac.com/promo\r\n\r\nSe désabonner | Préférences email\r\n\r\nFNAC SA - 9 rue des Bateaux-Lavoirs, 94200 Ivry-sur-Seine', 0, 'facile', 'Adresse email officielle @fnac.com, Lien vers fnac.com, Mentions légales présentes, Option de désabonnement, Pas de demande d\'informations personnelles', 'Cet email est LÉGITIME. C\'est une newsletter commerciale classique de la Fnac avec tous les éléments d\'un email professionnel.', 15, '2026-01-07 12:12:36'),
(10, 'Héritage surprise', 'email', 'avocat.succession@gmail.com', 'Succession de M. Jean DUPONT - 2.5 millions EUR', 'Madame, Monsieur,\r\n\r\nJe suis Maître Bernard, notaire. Je vous contacte concernant la succession de M. Jean DUPONT, décédé sans héritier.\r\n\r\nVous avez été désigné(e) comme bénéficiaire d\'un héritage de 2 500 000 EUR.\r\n\r\nPour débloquer ces fonds, merci d\'envoyer :\r\n- Vos coordonnées complètes\r\n- Une copie de votre passeport\r\n- Vos coordonnées bancaires\r\n- Frais de dossier : 350€\r\n\r\nMaître Pierre Bernard\r\nNotaire - Paris', 1, 'facile', 'Arnaque classique à l\'héritage, Adresse gmail (pas professionnelle pour un notaire), Demande de frais à l\'avance, Demande de documents d\'identité, Promesse d\'argent d\'un inconnu', 'C\'est une ARNAQUE classique ! Vous ne pouvez pas hériter d\'un inconnu. Un vrai notaire n\'utiliserait jamais gmail et ne demanderait jamais de frais à l\'avance.', 15, '2026-01-07 12:12:36');

-- --------------------------------------------------------

--
-- Structure de la table `progression`
--

DROP TABLE IF EXISTS `progression`;
CREATE TABLE IF NOT EXISTS `progression` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `module_id` int DEFAULT NULL,
  `submodule_id` int DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT '0',
  `score` int DEFAULT '0',
  `best_score` int DEFAULT '0',
  `attempts` int DEFAULT '0',
  `time_spent` int DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_submodule` (`user_id`,`submodule_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_module` (`module_id`),
  KEY `idx_submodule` (`submodule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `progression`
--

INSERT INTO `progression` (`id`, `user_id`, `module_id`, `submodule_id`, `is_completed`, `score`, `best_score`, `attempts`, `time_spent`, `completed_at`, `created_at`) VALUES
(1, 9, 1, 1, 0, 0, 0, 0, 0, NULL, '2026-04-05 19:57:14'),
(2, 9, 4, 12, 0, 0, 0, 0, 0, NULL, '2026-04-05 20:35:48'),
(3, 11, 1, 1, 1, 0, 0, 0, 0, '2026-04-06 15:30:56', '2026-04-06 15:22:36'),
(4, 11, 1, 3, 1, 0, 0, 0, 0, '2026-04-06 15:30:58', '2026-04-06 15:22:39'),
(5, 11, 1, 2, 1, 0, 0, 0, 0, '2026-04-06 15:31:12', '2026-04-06 15:22:49'),
(6, 11, 4, 12, 0, 0, 0, 0, 0, NULL, '2026-04-06 15:24:10'),
(7, 11, 2, 4, 1, 0, 0, 0, 0, '2026-04-06 15:31:19', '2026-04-06 15:31:18'),
(8, 11, 2, 5, 1, 0, 0, 0, 0, '2026-04-06 15:31:24', '2026-04-06 15:31:19'),
(9, 11, 2, 6, 1, 0, 0, 0, 0, '2026-04-06 15:31:26', '2026-04-06 15:31:25'),
(10, 11, 2, 7, 1, 0, 0, 0, 0, '2026-04-06 15:31:28', '2026-04-06 15:31:27');

-- --------------------------------------------------------

--
-- Structure de la table `questions`
--

DROP TABLE IF EXISTS `questions`;
CREATE TABLE IF NOT EXISTS `questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module_id` int NOT NULL,
  `question` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_a` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_b` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_c` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_d` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correct_answer` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `explanation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `difficulty` enum('Facile','Intermédiaire','Difficile') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Facile',
  `xp_reward` int DEFAULT '5',
  `points` int DEFAULT '10',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_module` (`module_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `questions`
--

INSERT INTO `questions` (`id`, `module_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `explanation`, `difficulty`, `xp_reward`, `points`, `created_at`) VALUES
(1, 1, 'Quels sont les trois piliers de la cybersécurité ?', 'Confidentialité, Intégrité, Disponibilité', 'Sécurité, Protection, Défense', 'Antivirus, Pare-feu, VPN', 'Mot de passe, Chiffrement, Sauvegarde', 'A', 'Le triangle CIA (Confidentiality, Integrity, Availability) représente les trois objectifs fondamentaux de la sécurité informatique.', 'Facile', 5, 10, '2026-04-05 19:57:05'),
(2, 1, 'Qu\'est-ce qu\'un ransomware ?', 'Un antivirus gratuit', 'Un logiciel qui chiffre vos fichiers et demande une rançon', 'Un pare-feu avancé', 'Un gestionnaire de mots de passe', 'B', 'Un ransomware (rançongiciel) chiffre vos données et exige un paiement pour les déchiffrer.', 'Facile', 5, 10, '2026-04-05 19:57:05'),
(3, 1, 'L\'ingénierie sociale consiste à :', 'Pirater des systèmes informatiques', 'Manipuler psychologiquement les personnes', 'Créer des virus', 'Réparer des ordinateurs', 'B', 'L\'ingénierie sociale exploite la psychologie humaine pour obtenir des informations confidentielles.', 'Intermédiaire', 7, 15, '2026-04-05 19:57:05'),
(4, 1, 'Quelle est la principale menace pour la sécurité informatique ?', 'Les bugs logiciels', 'Le facteur humain', 'Les pannes matérielles', 'Les coupures électriques', 'B', 'Plus de 80% des violations de données impliquent un facteur humain (erreur, négligence, manipulation).', 'Intermédiaire', 7, 15, '2026-04-05 19:57:05'),
(5, 2, 'Quelle est la longueur minimale recommandée pour un mot de passe ?', '6 caractères', '8 caractères', '12 caractères', '4 caractères', 'C', 'Les experts recommandent au moins 12 caractères, idéalement 16 ou plus pour une sécurité optimale.', 'Facile', 5, 10, '2026-04-05 19:57:05'),
(6, 2, 'Quel est le problème avec le mot de passe \"123456\" ?', 'Il est trop long', 'C\'est le mot de passe le plus utilisé au monde', 'Il contient des chiffres', 'Il n\'y a aucun problème', 'B', '\"123456\" est le mot de passe le plus courant et le premier testé par les pirates.', 'Facile', 5, 10, '2026-04-05 19:57:05'),
(7, 2, 'Qu\'est-ce que l\'authentification à deux facteurs (2FA) ?', 'Utiliser deux mots de passe', 'Changer son mot de passe deux fois par an', 'Combiner mot de passe et second facteur (code, téléphone)', 'Avoir deux comptes différents', 'C', 'Le 2FA ajoute une couche de sécurité en demandant quelque chose que vous savez (mot de passe) ET quelque chose que vous avez (téléphone).', 'Facile', 5, 10, '2026-04-05 19:57:05'),
(8, 2, 'Où est-il acceptable de stocker ses mots de passe ?', 'Dans un fichier texte sur le bureau', 'Sur un post-it collé à l\'écran', 'Dans un gestionnaire de mots de passe chiffré', 'Dans un email envoyé à soi-même', 'C', 'Un gestionnaire de mots de passe chiffré est la seule méthode sécurisée pour stocker vos mots de passe.', 'Facile', 5, 10, '2026-04-05 19:57:05'),
(9, 2, 'Pourquoi ne faut-il pas réutiliser le même mot de passe ?', 'C\'est plus facile à retenir', 'Si un compte est compromis, tous les autres le sont aussi', 'Les sites web l\'interdisent', 'Cela ralentit la connexion', 'B', 'Si un pirate obtient votre mot de passe sur un site compromis, il essaiera ce même mot de passe sur tous vos autres comptes.', 'Intermédiaire', 7, 15, '2026-04-05 19:57:05'),
(10, 3, 'Qu\'est-ce que le phishing ?', 'Un type de virus', 'Une technique pour voler des informations en se faisant passer pour une entité de confiance', 'Un logiciel de sécurité', 'Un jeu en ligne', 'B', 'Le phishing (hameçonnage) consiste à usurper l\'identité d\'une organisation pour voler vos données.', 'Facile', 5, 10, '2026-04-05 19:57:05'),
(11, 3, 'Quel élément doit vous alerter dans un email ?', 'Le logo de l\'entreprise', 'Un sentiment d\'urgence extrême', 'La présence d\'une signature', 'Un objet clair', 'B', 'Les emails de phishing créent souvent un sentiment d\'urgence pour vous pousser à agir sans réfléchir.', 'Facile', 5, 10, '2026-04-05 19:57:05'),
(12, 3, 'Comment vérifier si un lien est suspect ?', 'Cliquer dessus pour voir', 'Le survoler sans cliquer pour voir l\'URL réelle', 'Demander à un ami', 'Ignorer tous les liens', 'B', 'En survolant un lien, vous pouvez voir l\'URL de destination réelle sans risquer de cliquer.', 'Facile', 5, 10, '2026-04-05 19:57:05'),
(13, 3, 'Que faire si vous avez cliqué sur un lien de phishing ?', 'Rien, c\'est trop tard', 'Changer vos mots de passe immédiatement', 'Éteindre votre ordinateur définitivement', 'Attendre de voir si quelque chose se passe', 'B', 'Agissez rapidement : changez vos mots de passe, surveillez vos comptes et signalez l\'incident.', 'Intermédiaire', 7, 15, '2026-04-05 19:57:05'),
(14, 3, 'Qu\'est-ce que le smishing ?', 'Du phishing par SMS', 'Du phishing par email', 'Un type de malware', 'Une technique de hacking', 'A', 'Le smishing combine SMS + phishing. Ce sont des tentatives d\'hameçonnage par message texte.', 'Intermédiaire', 7, 15, '2026-04-05 19:57:05');

-- --------------------------------------------------------

--
-- Structure de la table `quiz_results`
--

DROP TABLE IF EXISTS `quiz_results`;
CREATE TABLE IF NOT EXISTS `quiz_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `module_id` int NOT NULL,
  `score` int DEFAULT '0',
  `total_questions` int DEFAULT '0',
  `correct_answers` int DEFAULT '0',
  `time_taken` int DEFAULT '0',
  `xp_earned` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_module` (`module_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `quiz_results`
--

INSERT INTO `quiz_results` (`id`, `user_id`, `module_id`, `score`, `total_questions`, `correct_answers`, `time_taken`, `xp_earned`, `created_at`) VALUES
(1, 11, 1, 25, 4, 1, 0, 7, '2026-04-06 15:23:03');

-- --------------------------------------------------------

--
-- Structure de la table `resources`
--

DROP TABLE IF EXISTS `resources`;
CREATE TABLE IF NOT EXISTS `resources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `category` enum('article','video','tool','documentation','external') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'article',
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'file-text',
  `difficulty` enum('debutant','intermediaire','avance') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'debutant',
  `views` int DEFAULT '0',
  `is_published` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_difficulty` (`difficulty`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `resources`
--

INSERT INTO `resources` (`id`, `title`, `description`, `category`, `url`, `content`, `icon`, `difficulty`, `views`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 'Les bases de la cybersécurité', 'Comprendre les fondamentaux de la sécurité informatique : menaces, risques et bonnes pratiques.', 'article', NULL, '## Introduction à la cybersécurité\r\n\r\nLa cybersécurité est devenue un enjeu majeur dans notre monde connecté. Cet article vous présente les concepts fondamentaux.\r\n\r\n### Les menaces principales\r\n\r\n- **Malwares** : virus, ransomwares, spywares\r\n- **Phishing** : tentatives d\'hameçonnage\r\n- **Attaques réseau** : man-in-the-middle, DDoS\r\n- **Ingénierie sociale** : manipulation humaine\r\n\r\n### Les bonnes pratiques\r\n\r\n1. Utilisez des mots de passe forts et uniques\r\n2. Activez l\'authentification à deux facteurs\r\n3. Maintenez vos logiciels à jour\r\n4. Méfiez-vous des emails suspects\r\n5. Sauvegardez régulièrement vos données', 'shield', 'debutant', 0, 1, '2026-01-07 12:12:36', '2026-01-07 12:12:36'),
(2, 'Guide des mots de passe sécurisés', 'Comment créer et gérer des mots de passe robustes pour protéger vos comptes.', 'article', NULL, '## Créer un mot de passe fort\r\n\r\nUn bon mot de passe doit contenir :\r\n- Au moins 12 caractères\r\n- Des majuscules et minuscules\r\n- Des chiffres\r\n- Des caractères spéciaux (!@#$%...)\r\n\r\n## Méthode de la phrase secrète\r\n\r\nPrenez une phrase que vous retenez facilement et transformez-la :\r\n\"J\'aime le café le matin à 7h\" → \"J@1m3L3C@f3L3M@t1n@7h!\"\r\n\r\n## Gestionnaires de mots de passe\r\n\r\nUtilisez un gestionnaire comme :\r\n- Bitwarden (gratuit, open source)\r\n- 1Password\r\n- Dashlane', 'key', 'debutant', 0, 1, '2026-01-07 12:12:36', '2026-01-07 12:12:36'),
(3, 'Comprendre le chiffrement', 'Introduction aux concepts de chiffrement et cryptographie pour protéger vos données.', 'article', NULL, '## Qu\'est-ce que le chiffrement ?\r\n\r\nLe chiffrement transforme des données lisibles en données illisibles sans la clé de déchiffrement.\r\n\r\n### Types de chiffrement\r\n\r\n**Symétrique** : même clé pour chiffrer et déchiffrer\r\n- Exemple : AES-256\r\n\r\n**Asymétrique** : clé publique + clé privée\r\n- Exemple : RSA, utilisé pour HTTPS\r\n\r\n### Où est-ce utilisé ?\r\n\r\n- HTTPS pour les sites web\r\n- Messageries chiffrées (Signal, WhatsApp)\r\n- VPN\r\n- Disques durs chiffrés', 'lock', 'intermediaire', 0, 1, '2026-01-07 12:12:36', '2026-01-07 12:12:36'),
(4, 'Sécuriser son smartphone', 'Tutoriel vidéo sur les paramètres de sécurité essentiels pour Android et iOS.', 'video', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', NULL, 'smartphone', 'debutant', 0, 1, '2026-01-07 12:12:36', '2026-01-07 12:12:36'),
(5, 'Have I Been Pwned', 'Vérifiez si votre email a été compromis dans une fuite de données.', 'tool', 'https://haveibeenpwned.com/', NULL, 'search', 'debutant', 0, 1, '2026-01-07 12:12:36', '2026-01-07 12:12:36'),
(6, 'VirusTotal', 'Analysez des fichiers et URLs suspects avec plusieurs antivirus.', 'tool', 'https://www.virustotal.com/', NULL, 'shield-check', 'debutant', 0, 1, '2026-01-07 12:12:36', '2026-01-07 12:12:36'),
(7, 'Bitwarden', 'Gestionnaire de mots de passe gratuit et open source.', 'tool', 'https://bitwarden.com/', NULL, 'key', 'debutant', 0, 1, '2026-01-07 12:12:36', '2026-01-07 12:12:36'),
(8, 'Guide ANSSI - Bonnes pratiques', 'Recommandations officielles de l\'Agence Nationale de la Sécurité des Systèmes d\'Information.', 'documentation', 'https://www.ssi.gouv.fr/guide/guide-dhygiene-informatique/', NULL, 'book-open', 'intermediaire', 0, 1, '2026-01-07 12:12:36', '2026-01-07 12:12:36'),
(9, 'Cybermalveillance.gouv.fr', 'Plateforme gouvernementale d\'assistance aux victimes de cybermalveillance.', 'external', 'https://www.cybermalveillance.gouv.fr/', NULL, 'external-link', 'debutant', 0, 1, '2026-01-07 12:12:36', '2026-01-07 12:12:36');

-- --------------------------------------------------------

--
-- Structure de la table `submodules`
--

DROP TABLE IF EXISTS `submodules`;
CREATE TABLE IF NOT EXISTS `submodules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'file-text',
  `xp_reward` int DEFAULT '15',
  `estimated_time` int DEFAULT '10',
  `display_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_module` (`module_id`),
  KEY `idx_order` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `submodules`
--

INSERT INTO `submodules` (`id`, `module_id`, `title`, `description`, `content`, `icon`, `xp_reward`, `estimated_time`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Qu\'est-ce que la cybersécurité ?', 'Comprendre les bases et l\'importance de la sécurité informatique.', '<h2>🛡️ La cybersécurité expliquée</h2><p>La <strong>cybersécurité</strong> est l\'ensemble des moyens techniques, organisationnels et humains mis en place pour protéger les systèmes informatiques, les réseaux et les données contre les attaques malveillantes.</p><h3>Pourquoi c\'est important ?</h3><p>En 2025, les cyberattaques coûtent des <strong>milliards d\'euros</strong> aux entreprises et particuliers. Chaque individu connecté est une cible potentielle.</p><ul><li>📧 Vos emails peuvent être interceptés</li><li>💳 Vos données bancaires peuvent être volées</li><li>🔐 Vos comptes peuvent être piratés</li><li>📸 Vos photos privées peuvent être exposées</li></ul><p>La bonne nouvelle ? Avec les <strong>bonnes pratiques</strong>, vous pouvez réduire considérablement les risques !</p>', 'book-open', 15, 5, 0, '2026-04-05 19:57:05', '2026-04-06 15:21:08'),
(2, 1, 'Les trois piliers de la sécurité', 'Confidentialité, Intégrité et Disponibilité : le triangle de la sécurité.', '<h2>🔺 Le Triangle CIA</h2><p>La sécurité informatique repose sur <strong>trois piliers fondamentaux</strong>, souvent appelés le \"Triangle CIA\" :</p><h3>🔒 Confidentialité</h3><p>Seules les personnes <strong>autorisées</strong> peuvent accéder aux informations sensibles.</p><p><em>Exemple : Vos messages privés ne doivent être lus que par vous et le destinataire.</em></p><h3>✅ Intégrité</h3><p>Les données ne peuvent pas être <strong>modifiées</strong> sans autorisation. Vous êtes sûr que l\'information n\'a pas été altérée.</p><p><em>Exemple : Un virement bancaire de 100€ ne doit pas pouvoir être modifié en 10 000€ par un pirate.</em></p><h3>⚡ Disponibilité</h3><p>Les systèmes et données sont <strong>accessibles</strong> quand on en a besoin.</p><p><em>Exemple : Votre banque en ligne doit être accessible 24h/24 pour consulter vos comptes.</em></p>', 'triangle', 15, 7, 2, '2026-04-05 19:57:05', '2026-04-06 15:21:13'),
(3, 1, 'Les principales menaces', 'Tour d\'horizon des cyberattaques les plus courantes.', '<h2>⚠️ Les menaces qui vous guettent</h2><p>Les cyberattaques peuvent prendre de nombreuses formes. Voici les plus courantes :</p><h3>🦠 Les Malwares</h3><ul><li><strong>Virus</strong> : Se propage en infectant d\'autres fichiers</li><li><strong>Ransomware</strong> : Chiffre vos fichiers et demande une rançon</li><li><strong>Cheval de Troie</strong> : Se cache dans un logiciel légitime</li><li><strong>Spyware</strong> : Espionne vos activités</li></ul><h3>🎣 Le Phishing</h3><p>Tentatives d\'hameçonnage par email, SMS ou téléphone pour voler vos identifiants.</p><h3>🔓 Attaques par force brute</h3><p>Tentatives automatisées de deviner votre mot de passe en testant des milliers de combinaisons.</p><h3>🧠 Ingénierie sociale</h3><p>Manipulation psychologique pour vous faire révéler des informations confidentielles.</p><p><strong>La clé ?</strong> Rester vigilant et appliquer les bonnes pratiques que vous apprendrez dans cette formation !</p>', 'alert-triangle', 20, 10, 1, '2026-04-05 19:57:05', '2026-04-06 15:21:13'),
(4, 2, 'L\'importance des mots de passe', 'Pourquoi vos mots de passe sont-ils si importants ?', '<h2>🔑 Votre première ligne de défense</h2><p>Le mot de passe est souvent la <strong>première et unique barrière</strong> entre un pirate et vos données personnelles.</p><h3>Un mot de passe faible, c\'est...</h3><p>...comme laisser la porte de sa maison grande ouverte avec un panneau \"Entrez !\" 🚪</p><h3>Les statistiques alarmantes</h3><ul><li>📊 <strong>81%</strong> des violations de données sont dues à des mots de passe faibles ou volés</li><li>⏱️ Un mot de passe de 6 caractères peut être cracké en <strong>quelques secondes</strong></li><li>🔄 <strong>65%</strong> des personnes réutilisent le même mot de passe partout</li></ul><h3>Ce que vous risquez</h3><ul><li>Accès à vos emails → Reset de tous vos autres comptes</li><li>Accès à vos réseaux sociaux → Usurpation d\'identité</li><li>Accès à vos comptes bancaires → Pertes financières</li></ul>', 'lock', 15, 5, 0, '2026-04-05 19:57:05', '2026-04-06 15:20:42'),
(5, 2, 'Créer un mot de passe robuste', 'Les règles d\'or pour des mots de passe inviolables.', '<h2>💪 Les caractéristiques d\'un bon mot de passe</h2><h3>Les règles d\'or</h3><ul><li>📏 <strong>Longueur</strong> : Minimum 12 caractères (idéalement 16+)</li><li>🔤 <strong>Complexité</strong> : Mélange de majuscules, minuscules, chiffres et symboles</li><li>🎯 <strong>Unicité</strong> : Un mot de passe différent pour chaque compte</li><li>🎲 <strong>Imprévisibilité</strong> : Éviter les informations personnelles (date de naissance, nom du chien...)</li></ul><h3>La méthode de la phrase secrète</h3><p>Une technique efficace est de créer une phrase et de la transformer :</p><p><code>\"J\'aime le café le matin à 7h\"</code></p><p>Devient :</p><p><code>J@1m3L3C@f3L3M@t1n@7h!</code></p><h3>Ce qu\'il faut éviter</h3><ul><li>❌ 123456, password, azerty</li><li>❌ Votre prénom + année de naissance</li><li>❌ Le nom de votre animal de compagnie</li><li>❌ Des mots du dictionnaire</li></ul>', 'shield-check', 20, 8, 1, '2026-04-05 19:57:05', '2026-04-06 15:20:42'),
(6, 2, 'Les gestionnaires de mots de passe', 'Comment retenir des dizaines de mots de passe complexes ?', '<h2>🗄️ Votre coffre-fort numérique</h2><p>Impossible de retenir des dizaines de mots de passe complexes ? C\'est normal ! C\'est pourquoi les <strong>gestionnaires de mots de passe</strong> existent.</p><h3>Comment ça marche ?</h3><ol><li>Vous créez UN mot de passe maître très solide</li><li>Le gestionnaire génère et stocke tous vos autres mots de passe</li><li>Ils sont chiffrés et sécurisés</li><li>Remplissage automatique sur vos sites</li></ol><h3>Les gestionnaires recommandés</h3><ul><li>🔐 <strong>Bitwarden</strong> (gratuit et open-source)</li><li>🔐 <strong>1Password</strong> (payant, très complet)</li><li>🔐 <strong>Dashlane</strong> (avec VPN inclus)</li><li>🔐 <strong>KeePass</strong> (local, pour les experts)</li></ul><h3>⚠️ Ne jamais faire</h3><ul><li>Stocker vos mots de passe dans un fichier texte</li><li>Les écrire sur un post-it</li><li>Les envoyer par email</li></ul>', 'database', 20, 10, 2, '2026-04-05 19:57:05', '2026-04-06 15:20:42'),
(7, 2, 'L\'authentification à deux facteurs', 'Doublez votre sécurité avec le 2FA.', '<h2>🔐 Le 2FA : Votre bouclier supplémentaire</h2><p>L\'<strong>authentification à deux facteurs (2FA)</strong> ajoute une couche de sécurité même si votre mot de passe est compromis.</p><h3>Le principe</h3><p>Pour vous connecter, vous devez fournir :</p><ol><li><strong>Ce que vous savez</strong> : Votre mot de passe</li><li><strong>Ce que vous avez</strong> : Votre téléphone (code SMS, app d\'authentification)</li></ol><h3>Les méthodes de 2FA</h3><ul><li>📱 <strong>SMS</strong> : Un code envoyé par texto (le moins sécurisé)</li><li>📲 <strong>Application</strong> : Google Authenticator, Authy, Microsoft Authenticator</li><li>🔑 <strong>Clé physique</strong> : YubiKey, Titan (le plus sécurisé)</li></ul><h3>Où l\'activer en priorité ?</h3><ul><li>✅ Votre email principal</li><li>✅ Vos comptes bancaires</li><li>✅ Vos réseaux sociaux</li><li>✅ Vos services cloud (Google, iCloud, Dropbox)</li></ul><p><strong>Conseil :</strong> Activez le 2FA partout où c\'est possible !</p>', 'smartphone', 15, 7, 3, '2026-04-05 19:57:05', '2026-04-06 15:20:42'),
(8, 3, 'Qu\'est-ce que le phishing ?', 'Comprendre cette menace omniprésente.', '<h2>🎣 L\'hameçonnage expliqué</h2><p>Le <strong>phishing</strong> (ou hameçonnage) est une technique utilisée par les cybercriminels pour voler vos informations personnelles en se faisant passer pour une entité de confiance.</p><h3>Comment ça fonctionne ?</h3><ol><li>Le pirate envoie un message imitant une source fiable (banque, impôts, Netflix...)</li><li>Le message crée un sentiment d\'<strong>urgence</strong> ou de <strong>peur</strong></li><li>Vous cliquez sur un lien vers un <strong>faux site</strong></li><li>Vous entrez vos identifiants... directement chez le pirate !</li></ol><h3>Les chiffres</h3><ul><li>📧 <strong>3,4 milliards</strong> d\'emails de phishing envoyés chaque jour</li><li>💰 <strong>17 700€</strong> : perte moyenne par victime en entreprise</li><li>⏱️ <strong>82%</strong> des violations de données impliquent un facteur humain</li></ul>', 'fish', 15, 5, 0, '2026-04-05 19:57:05', '2026-04-06 15:20:42'),
(9, 3, 'Les différents types de phishing', 'Email, SMS, téléphone : les pirates s\'adaptent.', '<h2>📱 Les variantes du phishing</h2><h3>📧 Email Phishing</h3><p>La forme la plus classique. Faux emails imitant des entreprises légitimes.</p><p><em>\"Votre colis est bloqué, cliquez ici pour payer 1,99€ de frais\"</em></p><h3>📱 Smishing (SMS Phishing)</h3><p>Phishing par SMS. Souvent des faux messages de livraison ou d\'administration.</p><p><em>\"AMELI : Votre carte vitale expire, mettez à jour vos informations\"</em></p><h3>📞 Vishing (Voice Phishing)</h3><p>Phishing par téléphone. Un \"conseiller\" vous appelle pour \"sécuriser\" votre compte.</p><h3>🎯 Spear Phishing</h3><p>Attaques <strong>ciblées et personnalisées</strong>. Le pirate a fait des recherches sur vous.</p><h3>🐋 Whaling</h3><p>Spear phishing ciblant les dirigeants d\'entreprise (les \"gros poissons\").</p>', 'mail-search', 20, 8, 1, '2026-04-05 19:57:05', '2026-04-06 15:20:42'),
(10, 3, 'Les signaux d\'alerte', 'Comment repérer un email frauduleux ?', '<h2>🚨 Les red flags du phishing</h2><p>Apprenez à reconnaître les signes qui doivent vous alerter :</p><h3>📧 L\'adresse de l\'expéditeur</h3><ul><li>❌ support@amaz0n-secure.com</li><li>❌ service-client@labanquepostale.security-update.com</li><li>✅ support@amazon.fr</li></ul><h3>⚠️ Le ton du message</h3><ul><li>❌ \"URGENT : Votre compte sera bloqué dans 24h\"</li><li>❌ \"Félicitations ! Vous avez gagné un iPhone\"</li><li>❌ \"Action immédiate requise\"</li></ul><h3>✍️ La qualité du texte</h3><ul><li>Fautes d\'orthographe et de grammaire</li><li>Formulations étranges</li><li>Ponctuation excessive !!!</li></ul><h3>🔗 Les liens suspects</h3><p><strong>Astuce :</strong> Survolez le lien SANS cliquer pour voir la vraie URL</p><ul><li>❌ http://www.paypa1.com-secure-login.xyz/</li><li>✅ https://www.paypal.com/</li></ul><h3>📎 Les pièces jointes</h3><p>Méfiance avec les .exe, .zip, .doc avec macros</p>', 'alert-octagon', 25, 12, 2, '2026-04-05 19:57:05', '2026-04-06 15:20:42'),
(11, 3, 'Que faire en cas de doute ?', 'Les bons réflexes face à un message suspect.', '<h2>✅ Les bons réflexes</h2><h3>En cas de doute sur un email</h3><ol><li>🚫 <strong>Ne cliquez sur aucun lien</strong></li><li>📎 <strong>Ne téléchargez aucune pièce jointe</strong></li><li>🔍 <strong>Vérifiez l\'adresse de l\'expéditeur</strong></li><li>🌐 <strong>Allez directement sur le site officiel</strong> (tapez l\'URL vous-même)</li><li>📞 <strong>Contactez l\'entreprise</strong> via ses canaux officiels</li></ol><h3>Si vous avez cliqué...</h3><ol><li>🔐 Changez immédiatement vos mots de passe</li><li>💳 Surveillez vos comptes bancaires</li><li>🛡️ Lancez un scan antivirus</li><li>📢 Signalez l\'incident</li></ol><h3>Où signaler ?</h3><ul><li>🇫🇷 <strong>signal-spam.fr</strong> : Pour les emails</li><li>🇫🇷 <strong>internet-signalement.gouv.fr</strong> : Plateforme PHAROS</li><li>📧 Transférez à <strong>phishing@votrebanque.fr</strong></li></ul>', 'check-circle', 15, 5, 3, '2026-04-05 19:57:05', '2026-04-06 15:20:42'),
(12, 4, 'aider les personne dans le besoin', '', 'comment faire pour aider les gens dans le besoins ?', 'key', 15, 10, 0, '2026-04-05 20:35:26', '2026-04-06 15:20:42');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','creator','admin','superadmin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `group_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Aucun',
  `xp` int DEFAULT '0',
  `level` int DEFAULT '1',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `verification_code` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verification_expires` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_protected` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_xp` (`xp` DESC)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `group_name`, `xp`, `level`, `avatar`, `email_verified`, `verification_code`, `verification_expires`, `last_login`, `created_at`, `updated_at`, `is_protected`) VALUES
(1, 'admin', 'admin@cybersens.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Staff', 885, 4, NULL, 1, NULL, NULL, '2026-01-08 12:52:11', '2026-01-07 12:12:36', '2026-04-05 21:08:46', 0),
(5, 'louis', 'louis@gmail.prout', '$2y$10$NUwTI6i6y3.5UUr/DHMn7.cQyTUbaIZZUyCTT0lUHNm5zJtzMVeWu', 'user', 'Aucun', 1192, 5, NULL, 1, NULL, NULL, '2026-01-08 16:31:43', '2026-01-07 14:44:56', '2026-01-08 15:34:56', 0),
(9, 'superadmin', 'superadmin@cybersens.local', '$2y$10$andZwyq.NbDZNtalz7LBse.lHYFf/x6qD3.YSgIT2YYSQ7hdAkis6', 'superadmin', 'Staff', 10246, 7, NULL, 1, NULL, NULL, '2026-04-06 19:09:05', '2026-01-08 11:44:53', '2026-04-06 17:09:05', 1),
(10, 'jules', 'jules@gmail.com', '$2y$10$srsEbDJVeq/zMZg4P6JXSOeOfptzYjFCGHzhA6.wXDt5xX91TwqbG', 'creator', 'Staff', 0, 1, NULL, 1, NULL, NULL, NULL, '2026-01-08 11:57:33', '2026-01-08 11:57:33', 0),
(11, 'leo', 'leo@cybersens.com', '$2y$10$MXsGa6Q0A2gyuEH8nM75Eux7pm/.rFCvHp4qwn3rmww8nrD/mF1Tu', 'user', 'Aucun', 222, 1, NULL, 0, NULL, NULL, '2026-04-06 17:22:23', '2026-04-06 15:11:09', '2026-04-06 15:36:28', 0);

-- --------------------------------------------------------

--
-- Structure de la table `user_badges`
--

DROP TABLE IF EXISTS `user_badges`;
CREATE TABLE IF NOT EXISTS `user_badges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `badge_id` int NOT NULL,
  `earned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_badge` (`user_id`,`badge_id`),
  KEY `badge_id` (`badge_id`),
  KEY `idx_user_badges` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user_badges`
--

INSERT INTO `user_badges` (`id`, `user_id`, `badge_id`, `earned_at`) VALUES
(1, 1, 1, '2026-01-07 12:12:36'),
(2, 1, 2, '2026-01-07 12:12:36'),
(3, 1, 3, '2026-01-07 12:12:36'),
(4, 1, 4, '2026-01-07 12:12:36'),
(5, 1, 6, '2026-01-07 12:12:36'),
(6, 1, 7, '2026-01-07 12:12:36'),
(7, 1, 9, '2026-01-07 12:12:36'),
(8, 1, 12, '2026-01-07 12:12:36'),
(13, 5, 9, '2026-01-07 14:46:56'),
(14, 5, 1, '2026-01-08 09:57:59'),
(15, 5, 4, '2026-01-08 09:57:59'),
(18, 5, 6, '2026-01-08 11:15:12'),
(19, 5, 12, '2026-01-08 11:15:12'),
(28, 9, 1, '2026-01-08 14:08:47'),
(29, 9, 4, '2026-01-08 14:08:47'),
(30, 9, 6, '2026-01-08 14:08:47'),
(31, 9, 7, '2026-01-08 14:08:47'),
(32, 9, 12, '2026-01-08 14:08:47'),
(33, 11, 12, '2026-04-06 15:11:09'),
(34, 11, 1, '2026-04-06 15:36:20'),
(35, 11, 2, '2026-04-06 15:36:20'),
(36, 11, 3, '2026-04-06 15:36:20'),
(37, 11, 6, '2026-04-06 15:36:20');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `phishing_results`
--
ALTER TABLE `phishing_results`
  ADD CONSTRAINT `phishing_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `phishing_results_ibfk_2` FOREIGN KEY (`scenario_id`) REFERENCES `phishing_scenarios` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `progression`
--
ALTER TABLE `progression`
  ADD CONSTRAINT `fk_progression_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_progression_submodule` FOREIGN KEY (`submodule_id`) REFERENCES `submodules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_progression_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `fk_question_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD CONSTRAINT `fk_quiz_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_quiz_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `submodules`
--
ALTER TABLE `submodules`
  ADD CONSTRAINT `fk_submodule_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_badges`
--
ALTER TABLE `user_badges`
  ADD CONSTRAINT `user_badges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
