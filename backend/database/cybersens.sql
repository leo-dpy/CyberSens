-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 01 avr. 2026 à 14:04
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
-- Structure de la table `certificates`
--

DROP TABLE IF EXISTS `certificates`;
CREATE TABLE IF NOT EXISTS `certificates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `course_id` int NOT NULL,
  `certificate_code` varchar(50) NOT NULL,
  `score` int DEFAULT '0',
  `issued_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `certificate_code` (`certificate_code`),
  UNIQUE KEY `unique_user_course` (`user_id`,`course_id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `certificates`
--

INSERT INTO `certificates` (`id`, `user_id`, `course_id`, `certificate_code`, `score`, `issued_at`) VALUES
(4, 5, 10, 'CS-1D5E0F51', 100, '2026-01-08 09:57:59'),
(5, 1, 10, 'CS-4A496B82', 88, '2026-01-08 10:55:01'),
(10, 9, 10, 'CS-E37B03B5', 100, '2026-01-08 14:08:47'),
(11, 5, 11, 'CS-E89510E1', 83, '2026-01-08 15:34:56');

-- --------------------------------------------------------

--
-- Structure de la table `cours`
--

DROP TABLE IF EXISTS `cours`;
CREATE TABLE IF NOT EXISTS `cours` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `difficulty` enum('Facile','Intermédiaire','Difficile') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Facile',
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'shield',
  `theme` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'blue',
  `xp_reward` int DEFAULT '25',
  `estimated_time` int DEFAULT '15',
  `display_order` int DEFAULT '0',
  `is_published` tinyint(1) DEFAULT '1',
  `is_hidden` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_difficulty` (`difficulty`),
  KEY `idx_published` (`is_published`),
  KEY `idx_order` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cours`
--

INSERT INTO `cours` (`id`, `title`, `description`, `content`, `difficulty`, `icon`, `theme`, `xp_reward`, `estimated_time`, `display_order`, `is_published`, `is_hidden`, `created_at`, `updated_at`) VALUES
(1, 'Introduction à la Cybersécurité', 'Découvrez les bases de la sécurité informatique et les menaces courantes.', '<h2>Qu\'est-ce que la cybersécurité ?</h2><p>La <strong>cybersécurité</strong> est l\'ensemble des moyens techniques, organisationnels et humains mis en place pour protéger les systèmes informatiques, les réseaux et les données contre les attaques malveillantes.</p><h3>Les trois piliers de la sécurité</h3><ul><li><strong>Confidentialité</strong> : seules les personnes autorisées peuvent accéder aux informations</li><li><strong>Intégrité</strong> : les données ne peuvent pas être modifiées sans autorisation</li><li><strong>Disponibilité</strong> : les systèmes et données sont accessibles quand on en a besoin</li></ul><h3>Les principales menaces</h3><p>Les cyberattaques peuvent prendre de nombreuses formes :</p><ul><li><strong>Malwares</strong> : virus, ransomwares, chevaux de Troie</li><li><strong>Phishing</strong> : tentatives d\'hameçonnage par email ou SMS</li><li><strong>Attaques par force brute</strong> : tentatives de deviner les mots de passe</li><li><strong>Ingénierie sociale</strong> : manipulation psychologique</li></ul><h3>Pourquoi c\'est important ?</h3><p>En 2025, les cyberattaques coûtent des milliards d\'euros aux entreprises et particuliers. Chaque individu est une cible potentielle.</p>', 'Facile', 'shield', 'blue', 25, 15, 6, 1, 1, '2026-01-07 12:12:36', '2026-01-08 14:00:04'),
(2, 'Créer des mots de passe sécurisés', 'Apprenez à créer et gérer des mots de passe robustes pour protéger vos comptes.', '<h2>L\'importance des mots de passe</h2><p>Le mot de passe est souvent la <strong>première ligne de défense</strong> contre les intrusions. Un mot de passe faible, c\'est comme laisser la porte de sa maison grande ouverte.</p><h3>Les caractéristiques d\'un bon mot de passe</h3><ul><li><strong>Longueur</strong> : minimum 12 caractères (idéalement 16+)</li><li><strong>Complexité</strong> : mélange de majuscules, minuscules, chiffres et symboles</li><li><strong>Unicité</strong> : un mot de passe différent pour chaque compte</li><li><strong>Imprévisibilité</strong> : éviter les informations personnelles</li></ul><h3>La méthode de la phrase secrète</h3><p>Une technique efficace est de créer une phrase et de la transformer :</p><p><code>\"J\'aime le café le matin à 7h\"</code> devient <code>J@1m3L3C@f3L3M@t1n@7h!</code></p><h3>Les gestionnaires de mots de passe</h3><p>Utilisez un gestionnaire comme <strong>Bitwarden</strong>, <strong>1Password</strong> ou <strong>Dashlane</strong> pour :</p><ul><li>Générer des mots de passe complexes</li><li>Les stocker de façon sécurisée</li><li>Les remplir automatiquement</li></ul><h3>L\'authentification à deux facteurs (2FA)</h3><p>Activez toujours le 2FA quand c\'est possible ! Même si votre mot de passe est compromis, le pirate ne pourra pas accéder à votre compte.</p>', 'Facile', 'key', 'green', 30, 20, 7, 1, 1, '2026-01-07 12:12:36', '2026-01-08 14:00:13'),
(3, 'Reconnaître le Phishing', 'Identifiez les tentatives d\'hameçonnage et protégez-vous des arnaques en ligne.', '<h2>Qu\'est-ce que le phishing ?</h2><p>Le <strong>phishing</strong> (ou hameçonnage) est une technique utilisée par les cybercriminels pour voler vos informations personnelles en se faisant passer pour une entité de confiance.</p><h3>Les différents types de phishing</h3><ul><li><strong>Email phishing</strong> : faux emails imitant des entreprises légitimes</li><li><strong>Smishing</strong> : phishing par SMS</li><li><strong>Vishing</strong> : phishing par téléphone</li><li><strong>Spear phishing</strong> : attaques ciblées et personnalisées</li></ul><h3>Comment reconnaître un email de phishing ?</h3><p><strong>🚩 Signaux d\'alerte :</strong></p><ul><li>Adresse email suspecte (ex: support@amaz0n-secure.com)</li><li>Urgence excessive (\"Votre compte sera bloqué dans 24h\")</li><li>Fautes d\'orthographe et de grammaire</li><li>Liens suspects (survolez sans cliquer !)</li><li>Demande d\'informations sensibles</li><li>Pièces jointes inattendues</li></ul><h3>Que faire en cas de doute ?</h3><ol><li>Ne cliquez sur aucun lien</li><li>Ne téléchargez aucune pièce jointe</li><li>Contactez l\'entreprise via son site officiel</li><li>Signalez l\'email comme phishing</li></ol>', 'Intermédiaire', 'mail', 'red', 35, 25, 8, 1, 1, '2026-01-07 12:12:36', '2026-01-08 14:00:20'),
(4, 'Sécuriser son réseau Wi-Fi', 'Protégez votre réseau domestique contre les intrusions et les attaques.', '<h2>Pourquoi sécuriser son Wi-Fi ?</h2><p>Un réseau Wi-Fi non sécurisé est une porte ouverte pour les pirates. Ils peuvent intercepter vos données, utiliser votre connexion pour des activités illégales, ou accéder à vos appareils.</p><h3>Les bases de la sécurité Wi-Fi</h3><h4>1. Choisir le bon protocole de sécurité</h4><ul><li><strong>WPA3</strong> : le plus récent et sécurisé (recommandé)</li><li><strong>WPA2</strong> : encore acceptable si WPA3 non disponible</li><li><strong>WEP</strong> : obsolète et vulnérable, à éviter absolument !</li></ul><h4>2. Créer un mot de passe Wi-Fi solide</h4><p>Utilisez au moins 20 caractères avec un mélange de lettres, chiffres et symboles.</p><h4>3. Changer le nom du réseau (SSID)</h4><p>Évitez les noms par défaut qui révèlent le modèle de votre box (ex: \"Livebox-A1B2\").</p><h4>4. Désactiver le WPS</h4><p>Le WPS est pratique mais vulnérable aux attaques par force brute.</p><h3>Mesures avancées</h3><ul><li>Créer un réseau invité séparé</li><li>Filtrer les adresses MAC</li><li>Mettre à jour régulièrement le firmware de la box</li><li>Réduire la portée du signal si possible</li></ul>', 'Intermédiaire', 'wifi', 'cyan', 40, 30, 9, 1, 1, '2026-01-07 12:12:36', '2026-01-08 14:00:25'),
(10, 'Module 1 : Les Malwares', 'Comprendre les virus et attaques, du plus simple au plus complexe.', '<h2>🟢 Niveau 1 : Les Nuisances (Facile)</h2><p><em>Des menaces souvent plus gênantes que destructrices, mais qui demandent de la vigilance.</em></p><p><br></p><h3>📢 Logiciel Publicitaire (Adware)</h3><p>Logiciel qui inonde votre écran de publicités indésirables ou intrusives.</p><ul><li><strong>Impact :</strong> Principalement gênant, mais certaines formes peuvent espionner vos habitudes de navigation.</li><li><br></li></ul><h3>😱 Scareware (Logiciel d\'intimidation)</h3><p>Logiciel qui utilise la peur. Il affiche des messages d’alerte trompeurs et alarmistes pour vous pousser à acheter un produit inutile, voire nuisible.</p><p><br></p><p><strong>Exemple typique :</strong> Une fenêtre surgit et clignote : <em>\"Votre ordinateur est infecté par 34 virus ! Cliquez ici pour nettoyer votre PC immédiatement.\"</em></p><p><br></p><h3>🦠 Virus</h3><p>Un programme malveillant \"parasite\". Il s\'attache à un fichier légitime (souvent un exécutable <code>.exe</code> ou un document) et s\'y réplique.</p><ul><li><strong>Condition clé :</strong> Il a impérativement besoin d\'une <strong>interaction humaine</strong> (comme ouvrir le fichier infecté) pour s\'activer et se propager.</li></ul><p><br></p><p><br></p><h2>🟠 Niveau 2 : Les Menaces Actives (Intermédiaire)</h2><p><em>Des logiciels autonomes ou dissimulés, conçus pour voler ou détruire.</em></p><p><br></p><h3>🪱 Ver (Worm)</h3><p>Contrairement au virus, le Ver est <strong>autonome</strong>. Il n\'a besoin ni de fichier hôte, ni d\'intervention humaine pour voyager.</p><ul><li><strong>Méthode :</strong> Il exploite les failles de sécurité réseau pour se copier d\'un ordinateur à un autre à une vitesse fulgurante.</li><li><br></li></ul><p><strong>Exemple :</strong> Un Ver peut s\'envoyer automatiquement par e-mail à tous vos contacts en quelques secondes, sans que vous ne touchiez à rien.</p><p><br></p><h3>🔒 Ransomware (Rançongiciel)</h3><p>Le \"braquage\" numérique. Ce logiciel chiffre (encrypte) vos fichiers ou bloque l\'accès à votre ordinateur.</p><ul><li><strong>Le chantage :</strong> Les pirates exigent une rançon (souvent en cryptomonnaie) en échange de la clé de déchiffrement.</li></ul><p><br></p><h3>🕵️‍♂️ Logiciel Espion (Spyware)</h3><p>L\'espion silencieux. Il est conçu pour collecter des données sans votre consentement.</p><ul><li><strong>Ce qu\'il vole :</strong> Historique de navigation, mots de passe, informations bancaires, et parfois les touches tapées au clavier (<em>Keylogger</em>).</li></ul><p><br></p><h3>🐴 Cheval de Troie (Trojan)</h3><p>Le maître du déguisement. Il se présente comme un logiciel utile ou légitime (un jeu, un outil gratuit) pour vous inciter à l\'installer.</p><ul><li><strong>Le piège :</strong> Une fois à l\'intérieur, il libère sa charge malveillante. Il ne se réplique pas, mais sert souvent à ouvrir une <strong>Backdoor</strong> (porte dérobée).</li></ul><p><br></p><h2>🔴 Niveau 3 : L\'Architecture de l\'Attaque (Difficile)</h2><p><em>Les outils utilisés par les attaquants pour maintenir le contrôle et frapper fort.</em></p><p><br></p><h3>🚪 Backdoor (Porte dérobée)</h3><p>Un accès secret installé subrepticement (souvent via un Cheval de Troie ou une faille).</p><ul><li><strong>Fonctionnement :</strong> Elle ouvre un canal de communication caché, permettant au pirate de revenir quand il le souhaite, de modifier des fichiers ou de voler des données, sans être détecté par les systèmes de sécurité classiques.</li><li><strong>Note :</strong> Certaines backdoors peuvent être matérielles (cachées directement dans les puces électroniques).</li><li><br></li></ul><h3>👻 Rootkit</h3><p>L\'art de l\'invisibilité. C\'est un ensemble d\'outils complexes qui s\'installent au niveau le plus profond du système (<em>le root</em>).</p><ul><li><strong>But :</strong> Masquer l\'existence d\'autres virus ou de l\'intrus lui-même. Si votre antivirus ne voit rien alors que le PC agit bizarrement, c\'est peut-être un Rootkit.</li></ul><p><br></p><h3>🤖 Botnet (Réseau de robots)</h3><p>Une armée d\'ordinateurs infectés (<em>bots</em> ou <em>zombies</em>) contrôlés à distance par un chef d\'orchestre malveillant (<em>le botmaster</em>).</p><ul><li><strong>Usage :</strong> Ces ordinateurs sont utilisés simultanément pour lancer des attaques massives.</li></ul><p><br></p><h3>🌊 DDoS (Déni de Service Distribué)</h3><p>L\'attaque par saturation. Le but est de rendre un site web ou un service inaccessible.</p><ul><li><strong>Mécanisme :</strong> On utilise un <strong>Botnet</strong> pour submerger la cible d\'un trafic gigantesque. Comme un embouteillage monstre qui bloque l\'accès à une autoroute, le serveur ne peut plus répondre aux utilisateurs légitimes.</li></ul><p><br></p><h2>📚 Les Fondamentaux : Comprendre la Faille</h2><p><em>Tout commence souvent par une erreur de code...</em></p><p><br></p><h3>⚠️ Faille de sécurité (Vulnérabilité)</h3><p>Une faiblesse dans la conception ou le code d\'un logiciel. C\'est souvent une erreur involontaire (un bug).</p><ul><li><strong>Le risque :</strong> Si un attaquant crée un <strong>Exploit</strong> (un code spécifique pour tirer parti de cette faiblesse), il peut prendre le contrôle du système ou voler des données.</li></ul><p><br></p><h3>🔄 Le Cycle de la Vulnérabilité</h3><ol><li><strong>Découverte :</strong> La faille est trouvée (par des gentils ou des méchants).</li><li><strong>Correction :</strong> L\'éditeur crée un correctif (<em>Patch</em>).</li><li><strong>Divulgation :</strong> La faille est rendue publique.</li><li><strong>Mise à jour :</strong> L\'utilisateur applique le patch pour se protéger.</li></ol><p><br></p><h3>🚨 Zero-Day (Vulnérabilité Jour Zéro)</h3><p>C\'est le scénario catastrophe.</p><ul><li><strong>Définition :</strong> Une faille qui est découverte et exploitée par des pirates <strong>avant</strong> que l\'éditeur n\'ait eu le temps de créer ou de publier un correctif.</li><li><strong>Pourquoi \"Zéro\" ?</strong> Parce que les développeurs ont eu \"zéro jour\" pour réparer la faille avant qu\'elle ne soit utilisée. C\'est le moment où les systèmes sont totalement sans défense.</li></ul><p><br></p>', 'Facile', 'bug', 'blue', 25, 15, 1, 1, 0, '2026-01-08 09:33:52', '2026-01-08 10:49:18'),
(11, 'Module 2: Glossaire des Attaques', 'Comprendre les techniques de piratage, de la manipulation psychologique au code complexe.', '<h2>🟢 Niveau 1 : Le Facteur Humain (Facile)</h2><p><em>Ici, la faille n\'est pas l\'ordinateur, c\'est l\'être humain.</em></p><h3>🎭 Ingénierie Sociale (Social Engineering)</h3><p>L\'art de la manipulation psychologique. Le pirate ne force pas la porte, il convainc quelqu\'un de l\'ouvrir.</p><ul><li><strong>Objectif :</strong> Inciter la victime à divulguer des infos confidentielles ou à faire une action dangereuse.</li></ul><blockquote><strong>Le Scénario :</strong> Un pirate appelle un employé en se faisant passer pour le \"Support Informatique\". Il prétexte une urgence technique et, jouant sur le stress, demande le mot de passe de l\'employé pour \"éviter un blocage du compte\". L\'employé cède.</blockquote><h3>🎣 Hameçonnage (Phishing)</h3><p>La forme la plus courante d\'ingénierie sociale. Elle utilise des leurres numériques (Emails, SMS) pour vous piéger.</p><ul><li><strong>Mécanisme :</strong> Vous faire cliquer sur un lien piégé ou vous faire saisir vos identifiants sur un faux site (copie parfaite de l\'original).</li></ul><blockquote><strong>L\'Exemple du quotidien :</strong> Vous recevez un SMS \"Colissimo\" : <em>\"Votre colis est bloqué. Réglez 1,99 € de frais de douane.\"</em> Le lien mène à un faux site qui vole votre numéro de carte bancaire.</blockquote><h3>🔨 Attaque par Force Brute</h3><p>La méthode bourrine. Pas de finesse, juste de la puissance de calcul.</p><ul><li><strong>Principe :</strong> Tester toutes les combinaisons possibles (lettres, chiffres) jusqu\'à ce que la porte s\'ouvre. C\'est comme essayer d\'ouvrir un cadenas en testant 0000, 0001, 0002...</li></ul><blockquote><strong>En pratique :</strong> Un logiciel automatisé tente de se connecter à votre compte en testant des millions de mots de passe courants (\"123456\", \"azerty\", \"password\") à la seconde, jusqu\'à trouver le vôtre.</blockquote><h2>🟠 Niveau 2 : Interception &amp; Sabotage (Intermédiaire)</h2><p><em>Des attaques qui visent les communications ou la disponibilité des services.</em></p><h3>🛑 Attaque par Déni de Service (DoS / DDoS)</h3><p>Le but n\'est pas de voler (confidentialité), mais de casser (disponibilité).</p><ul><li><strong>DoS (Denial of Service) :</strong> Une attaque pour saturer une cible.</li><li><strong>DDoS (Distributed DoS) :</strong> La même chose, mais lancée par des milliers de machines en même temps (souvent via un <em>Botnet</em>).</li></ul><blockquote><strong>L\'Analogie du Standard :</strong> Imaginez un standardiste qui ne peut gérer que 10 appels/minute.</blockquote><ul><li><strong>DoS :</strong> Une personne appelle en boucle sans s\'arrêter.</li><li><strong>DDoS :</strong> 10 000 personnes appellent en même temps.</li></ul><blockquote>Résultat : Le standard sature et les vrais clients ne peuvent plus joindre l\'entreprise.</blockquote><h3>🕵️‍♂️ Attaque de l’Homme du Milieu (Man in the Middle)</h3><p>L\'écoute aux portes numérique. L\'attaquant s\'insère secrètement entre deux parties qui communiquent (vous et votre banque).</p><ul><li><strong>Danger :</strong> Il peut écouter, lire et <em>modifier</em> les messages à la volée sans que personne ne s\'en rende compte.</li></ul><blockquote><strong>L\'Exemple postal :</strong> Vous envoyez une lettre à votre banque. Le facteur (l\'attaquant) intercepte la lettre, l\'ouvre, change le numéro de compte du virement vers le sien, referme l\'enveloppe et la donne à la banque. La banque exécute l\'ordre, pensant qu\'il vient de vous.</blockquote><h2>🔴 Niveau 3 : L\'Exploitation Technique (Difficile)</h2><p><em>Des attaques qui manipulent le code des sites web et des logiciels.</em></p><h3>💉 Injection SQL</h3><p>Une attaque qui vise le cœur des données. Elle manipule la base de données d\'un site web (là où sont stockés les clients, les mots de passe) via un formulaire mal protégé.</p><ul><li><strong>La technique :</strong> L\'attaquant écrit du code (langage SQL) directement dans une barre de recherche ou de connexion.</li></ul><blockquote><strong>Le Tour de Magie :</strong> Sur un formulaire de connexion, au lieu de mettre un nom, le pirate écrit : <code>\' OR \'1\'=\'1</code>. Le site demande à sa base de données : <em>\"Trouve l\'utilisateur dont le nom est vide OU dont le chiffre 1 est égal à 1\"</em>. Comme 1 est toujours égal à 1 (vrai), la base de données répond \"OK\" et connecte le pirate (souvent en tant qu\'administrateur) sans mot de passe !</blockquote><h3>💣 Exploitation Zero-Day</h3><p>L\'attaque imparable (temporairement). Elle cible une faille inconnue de tous, sauf de l\'attaquant.</p><ul><li><strong>Pourquoi \"Zero-Day\" ?</strong> Le développeur a eu \"zéro jour\" pour créer un correctif (<em>patch</em>) avant que l\'attaque ne survienne.</li></ul><blockquote><strong>L\'Exemple du coffre-fort :</strong> Un cambrioleur découvre que si l\'on tape un code spécifique caché sur un nouveau modèle de coffre, il s\'ouvre. Le fabricant ne le sait pas. Le voleur vide les coffres. Les gardes (antivirus) ne réagissent pas car pour eux, le coffre a été ouvert \"normalement\".</blockquote><h3>🌐 Cross Site Scripting (XSS)</h3><p>Une attaque indirecte. Contrairement à l\'Injection SQL qui attaque le serveur, le XSS attaque les <strong>visiteurs</strong> du site.</p><ul><li><strong>Mécanisme :</strong> Le pirate injecte un script malveillant (souvent du JavaScript) dans une page légitime (via un commentaire, un profil).</li></ul><blockquote><strong>Le Piège :</strong></blockquote><ol><li>Sur un forum, le pirate poste un commentaire contenant un script invisible : <code>&lt;script&gt;...voler les cookies...&lt;/script&gt;</code>.</li><li>Vous (visiteur légitime) arrivez sur la page pour lire les commentaires.</li><li>Votre navigateur exécute automatiquement le script du pirate.</li><li>Vos identifiants de session (cookies) sont envoyés au pirate, qui peut alors usurper votre identité.</li></ol><p><br></p>', 'Facile', 'shield', 'blue', 25, 15, 2, 1, 0, '2026-01-08 09:50:32', '2026-01-08 10:49:18'),
(12, 'Module 3: Glossaire des Concepts de Défense', 'Glossaire des concepts de défense et de protection.', '<h2>🟢 Niveau 1 : Les Fondations de la Sécurité (Facile)</h2><p><em>Les dispositifs essentiels pour filtrer, piéger et cloisonner.</em></p><h3>🔥 Pare-feu (Firewall)</h3><p>Le douanier du réseau. C\'est un dispositif (logiciel ou matériel) qui agit comme une barrière filtrante entre un réseau de confiance (votre entreprise) et un réseau non fiable (Internet).</p><ul><li><strong>Rôle :</strong> Il examine chaque paquet de données qui entre ou sort et décide, selon des règles strictes, de le laisser passer ou de le bloquer.</li></ul><h3>🍯 Honeypot (Pot de miel)</h3><p>Le leurre numérique. C\'est un système volontairement vulnérable conçu pour attirer les pirates.</p><ul><li><strong>Objectif :</strong> Détourner l\'attention des vraies cibles et observer les méthodes de l\'attaquant.</li><li class=\"ql-indent-1\"><strong>Faible interaction :</strong> Un leurre simple, sans risque, qui imite juste l\'apparence d\'un système.</li><li class=\"ql-indent-1\"><strong>Forte interaction :</strong> Un environnement complexe (cloisonné) où le hacker peut réellement entrer, ce qui permet d\'analyser une attaque en situation réelle.</li></ul><h3>🏢 VLANs (Réseaux Locaux Virtuels)</h3><p>La stratégie du cloisonnement. Cela consiste à diviser un réseau physique unique en plusieurs sous-réseaux isolés virtuellement.</p><ul><li><strong>L\'intérêt :</strong> \"Diviser pour mieux régner\". Si un pirate infecte l\'ordinateur d\'un utilisateur, le VLAN l\'empêche de voir ou d\'attaquer directement les serveurs critiques qui sont isolés dans un autre compartiment étanche.</li></ul><h3>🔐 Authentification à Double Facteur (MFA / 2FA)</h3><p>La règle des deux clés. Ce système exige deux preuves d\'identité distinctes pour se connecter. Elle combine généralement \"ce que je sais\" (mot de passe) et \"ce que je possède\" (téléphone).</p><blockquote><strong>Le Scénario Salvateur :</strong> Un pirate vole votre mot de passe Facebook grâce à du Phishing. Il essaie de se connecter.</blockquote><ul><li><strong>Résultat :</strong> L\'accès lui est refusé car il ne possède pas votre smartphone pour recevoir le code SMS de validation. Votre compte est sauvé.</li></ul><h2>🟠 Niveau 2 : Confidentialité et Résilience (Intermédiaire)</h2><p><em>Comment rendre les données illisibles et indestructibles.</em></p><h3>🚇 VPN (Réseau Privé Virtuel)</h3><p>Le tunnel invisible. Cette technologie crée un canal sécurisé et chiffré entre votre appareil et un serveur distant.</p><ul><li><strong>Double effet :</strong> Il masque votre adresse IP (anonymat) et rend vos données illisibles pour les observateurs extérieurs.</li></ul><blockquote><strong>Exemple de vie réelle :</strong> Vous travaillez sur le Wi-Fi public d\'un aéroport. Sans VPN, un attaquant sur le même réseau pourrait intercepter vos données. Avec le VPN, vos données voyagent dans un tunnel chiffré ; elles sont illisibles pour lui.</blockquote><h3>📜 Chiffrement (Encryption)</h3><p>L\'art du code secret. C\'est un procédé mathématique qui transforme des informations lisibles en un code secret qui ne peut être lu qu\'avec une clé de déchiffrement spécifique.</p><ul><li><strong>Indicateur clé :</strong> Le petit cadenas (HTTPS) sur votre navigateur signifie que la communication est chiffrée. Cela garantit que personne ne peut lire vos données (comme votre numéro de carte bancaire) pendant leur transfert.</li></ul><h3>💾 Sauvegardes (Backups)</h3><p>L\'assurance-vie numérique. C\'est une copie de sécurité des données stockée sur un support indépendant (disque dur externe, cloud sécurisé). C\'est souvent la \"dernière ligne de défense\".</p><ul><li><strong>Utilité :</strong> En cas d\'attaque par Ransomware, au lieu de payer la rançon pour récupérer vos fichiers chiffrés, vous pouvez simplement effacer le système infecté et restaurer vos données saines depuis la sauvegarde.</li></ul><h2>🔴 Niveau 3 : Surveillance Active (Difficile)</h2><p><em>Les systèmes intelligents qui écoutent le réseau.</em></p><h3>🚨 IDS / IPS (Détection et Prévention d\'Intrusion)</h3><p>Si le Pare-feu est le douanier à l\'entrée, ces systèmes sont la sécurité qui patrouille à l\'intérieur du réseau. Ils analysent le trafic pour repérer des comportements anormaux.</p><p>Il est important de bien distinguer les deux :</p><p><strong>1. L\'IDS (Intrusion Detection System)</strong></p><ul><li><strong>Son rôle :</strong> C\'est l\'observateur.</li><li><strong>L\'analogie :</strong> C\'est une <strong>alarme silencieuse</strong>.</li><li><strong>Action :</strong> Il surveille le réseau et s\'il repère une signature d\'attaque, il prévient immédiatement l\'administrateur (\"Attention, une serrure est forcée !\"), mais il ne bloque pas l\'action lui-même.</li></ul><p><strong>2. L\'IPS (Intrusion Prevention System)</strong></p><ul><li><strong>Son rôle :</strong> C\'est l\'intervenant.</li><li><strong>L\'analogie :</strong> C\'est un <strong>garde du corps</strong>.</li><li><strong>Action :</strong> Il surveille aussi, mais il a le pouvoir d\'agir. S\'il détecte une tentative d\'intrusion, il intervient activement pour bloquer l\'intrus et couper la connexion instantanément.</li></ul><p><br></p>', 'Facile', 'shield', 'blue', 25, 15, 3, 1, 0, '2026-01-08 13:31:20', '2026-01-08 13:37:17'),
(13, 'Module 4 : Guide pour le Grand Public', 'Les réflexes essentiels pour sécuriser votre vie numérique au quotidien.', '<h1>👥 </h1><h3>Les réflexes essentiels pour sécuriser votre vie numérique au quotidien.</h3><p>La sécurité informatique n\'est pas qu\'une affaire d\'experts. 90% des piratages exploitent des erreurs simples. Voici les habitudes clés pour verrouiller votre vie numérique.</p><p><br></p><h2>🔐 1. L\'Art du Mot de Passe</h2><h3>🗣️ Mots de passe forts (La méthode \"Passphrase\")</h3><p>Oubliez les mots de passe courts remplis de symboles impossibles à taper. La sécurité réside désormais dans la <strong>longueur</strong>.</p><ul><li><strong>Le principe :</strong> Une phrase est mathématiquement beaucoup plus difficile à casser pour un ordinateur qu\'un mot court complexe, et elle est bien plus facile à retenir pour un humain.</li></ul><blockquote><strong>Comparatif :</strong></blockquote><ul><li>❌ <strong>Faible :</strong> <code>P@ssw0rd!</code> (Court, prévisible. Piraté en quelques secondes).</li><li>✅ <strong>Fort :</strong> <code>J\'aime_Manger_Des_Pommes_En_Hiver_2024</code> (Long, mémorable. Prendrait des siècles à deviner pour une machine).</li></ul><h3>🛡️ Gestionnaire de mots de passe</h3><p>Arrêtez d\'utiliser le même mot de passe partout. Utilisez un <strong>coffre-fort numérique</strong>.</p><ul><li><strong>Définition :</strong> C\'est un logiciel chiffré qui génère et stocke des mots de passe complexes et uniques pour chacun de vos comptes.</li><li><strong>Avantage :</strong> Vous n\'avez plus qu\'un seul mot de passe à retenir : le \"mot de passe maître\" qui ouvre le coffre.</li></ul><blockquote><strong>L\'Analogie :</strong> Imaginez un trousseau avec 50 clés différentes. Au lieu de les porter sur vous (risque de les perdre), vous les placez toutes dans un coffre blindé. Vous n\'avez besoin que de la combinaison du coffre pour accéder à toutes vos clés.</blockquote><h2>💾 2. La Survie des Données</h2><h3>🔄 La Règle du 3-2-1 (Sauvegardes)</h3><p>C\'est la stratégie ultime, utilisée par les professionnels, pour garantir que vous ne perdrez <strong>jamais</strong> vos photos ou documents importants.</p><p>Voici la recette :</p><ul><li><strong>3</strong> copies de vos données (au total).</li><li><strong>2</strong> supports différents (par exemple : votre ordinateur + un disque dur externe).</li><li><strong>1</strong> copie hors site (par exemple : sur le Cloud).</li></ul><blockquote><strong>Pourquoi c\'est infaillible ?</strong> Si un incendie détruit votre maison (adieu l\'ordinateur et le disque dur), il vous reste la copie Cloud. Si le Cloud est piraté ou inaccessible, il vous reste vos copies physiques à la maison.</blockquote><h2>🛠️ 3. Maintenance et Confidentialité</h2><h3>🩹 Mises à jour logicielles (Patchs)</h3><p>Ce petit bouton \"Mettre à jour\" que l\'on repousse souvent est votre meilleur allié.</p><ul><li><strong>Définition :</strong> Les mises à jour installent des correctifs de sécurité fournis par les fabricants (Apple, Microsoft, Google). Ce n\'est pas que pour le design, c\'est surtout pour combler les failles.</li></ul><blockquote><strong>L\'Analogie du Maçon :</strong> Imaginez que les murs de votre maison ont des trous. Une mise à jour, c\'est comme un maçon qui vient boucher ces trous gratuitement avant que les voleurs ne les repèrent. Repousser une mise à jour, c\'est choisir de laisser le trou ouvert volontairement.</blockquote><h3>📱 Permissions des Applications</h3><p>C\'est le contrôle de ce qu\'une application a le droit de faire sur votre téléphone (accès au micro, à la caméra, à la localisation).</p><ul><li><strong>Le réflexe :</strong> Demandez-vous toujours si l\'application a <em>réellement</em> besoin de cet accès pour fonctionner.</li></ul><blockquote><strong>L\'Exemple suspect :</strong> Vous installez une application \"Lampe de poche\". Si elle vous demande l\'accès à vos <strong>Contacts</strong> et à votre <strong>Position GPS</strong>, refusez et supprimez-la. Une lampe n\'a pas besoin de savoir où vous êtes ni qui sont vos amis. C\'est souvent une ruse pour voler et revendre vos données personnelles.</blockquote><p><br></p>', 'Facile', 'users', 'orange', 25, 5, 4, 1, 0, '2026-01-08 13:39:47', '2026-01-08 13:51:15'),
(14, 'Module 5: Guide Niveau Avancé', 'Stratégies et politiques de sécurité pour les organisations.', '<h2>👔 Politiques et Comportements Humains</h2><p><em>Gérer les droits d\'accès et les usages imprévus des collaborateurs.</em></p><h3>🔑 Principe du Moindre Privilège</h3><p>C\'est la règle d\'or de l\'administration système. Elle consiste à donner à un utilisateur <strong>uniquement</strong> les accès strictement nécessaires à son travail, et rien de plus.</p><ul><li><strong>Pourquoi ?</strong> Pour limiter la casse. Si un compte est piraté, les dégâts restent confinés à la zone autorisée de cet utilisateur.</li></ul><blockquote><strong>Exemple concret :</strong> Un stagiaire en communication doit avoir la clé du dossier \"Photos\", mais pas celle du dossier \"Comptabilité/Salaires\". Si le compte du stagiaire est compromis, le pirate sera bloqué à la porte de la comptabilité.</blockquote><h3>📱 BYOD (Bring Your Own Device)</h3><p>Littéralement \"Apportez votre propre appareil\". C\'est une pratique qui autorise les collaborateurs à utiliser leur matériel personnel (PC, smartphone) pour travailler.</p><ul><li><strong>Le Risque majeur :</strong> L\'ordinateur personnel est une zone de non-droit (jeux vidéo crackés, pas d\'antivirus professionnel, mises à jour en retard).</li><li><strong>Conséquence :</strong> S\'il se connecte au Wi-Fi de l\'entreprise, cet appareil peut servir de \"cheval de Troie\" et infecter tout le réseau professionnel sain.</li></ul><h3>👻 Shadow IT (Informatique de l\'ombre)</h3><p>C\'est l\'utilisation par les employés de logiciels ou services non approuvés par le service informatique. Ce n\'est généralement pas malveillant, mais fait pour \"gagner du temps\" ou \"contourner une restriction\".</p><ul><li><strong>Le danger :</strong> L\'entreprise perd totalement le contrôle et la visibilité sur ses données.</li></ul><blockquote><strong>Le scénario classique :</strong> Le serveur de l\'entreprise est sécurisé mais un peu lent. Pour aller plus vite, les employés s\'envoient des fichiers confidentiels via <strong>WeTransfer</strong> ou leur <strong>Gmail personnel</strong>. Résultat : Des documents sensibles sortent du périmètre sécurisé de l\'entreprise sans que personne ne le sache.</blockquote><h2>🏗️ Architecture et Stratégie</h2><p><em>Comment concevoir un système résilient face aux attaques modernes.</em></p><h3>🏰 Surface d\'attaque</h3><p>C\'est l\'ensemble des points d\'entrée (vulnérabilités potentielles) qu\'un attaquant peut utiliser pour pénétrer dans un système.</p><ul><li><strong>L\'objectif de la sécurité :</strong> Réduire cette surface au maximum. Moins il y a d\'entrées, plus c\'est facile à surveiller.</li></ul><blockquote><strong>L\'Analogie de la Forteresse :</strong></blockquote><ul><li><strong>Grande surface d\'attaque :</strong> Une forteresse avec 50 portes et 100 fenêtres. C\'est un cauchemar à surveiller.</li><li><strong>Réduire la surface :</strong> Vous murez 40 portes inutilisées et mettez des barreaux aux fenêtres.</li></ul><blockquote><em>En informatique, cela revient à désactiver les logiciels inutiles, fermer les ports réseaux non utilisés et supprimer les vieux comptes utilisateurs.</em></blockquote><h3>🆔 Zero Trust (Zéro Confiance)</h3><p>C\'est le modèle de sécurité moderne. Il part du principe qu\'aucun utilisateur ni appareil ne doit être approuvé par défaut, même s\'il est déjà \"à l\'intérieur\" du réseau ou des bureaux.</p><ul><li><strong>La Devise :</strong> \"Ne jamais faire confiance, toujours vérifier.\"</li></ul><blockquote><strong>L\'évolution du modèle :</strong></blockquote><ul><li><strong>Avant (Modèle Château Fort) :</strong> Une fois le pont-levis passé (mot de passe initial), on considère que vous êtes un \"gentil\" et vous pouvez vous balader partout librement.</li><li><strong>Aujourd\'hui (Zero Trust) :</strong> Il y a un garde de sécurité devant <strong>chaque</strong> porte à l\'intérieur du château. Même si vous êtes entré, on revérifie votre badge pour aller à la cafétéria, et on le revérifie encore pour aller aux archives.</li></ul><p><br></p>', 'Intermédiaire', 'users', 'purple', 40, 10, 5, 1, 0, '2026-01-08 13:47:19', '2026-01-08 13:51:15');

-- --------------------------------------------------------

--
-- Structure de la table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `groups`
--

INSERT INTO `groups` (`id`, `name`, `created_at`) VALUES
(4, 'Staff', '2026-04-01 14:01:45');

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
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(37, 9, 'Niveau supérieur!', 'Félicitations! Vous avez atteint le niveau Légende!', 'success', 1, NULL, '2026-01-08 14:08:47'),
(38, 9, 'Nouveau module débloqué !', 'Vous avez débloqué le module \"Module 2: Glossaire des Attaques\". Continuez votre progression !', '', 1, '#cours', '2026-01-08 14:08:47'),
(39, 9, 'Certificat obtenu !', 'Félicitations ! Vous avez obtenu le certificat pour \"Module 1 : Les Malwares\" avec un score de 100%.', '', 1, NULL, '2026-01-08 14:08:47'),
(40, 9, 'Nouveau badge débloqué !', 'Vous avez obtenu le badge \"Premier Pas\" : Terminez votre premier cours', '', 1, NULL, '2026-01-08 14:08:47'),
(41, 9, 'Nouveau badge débloqué !', 'Vous avez obtenu le badge \"Étudiant Assidu\" : Atteignez le niveau 5', '', 1, NULL, '2026-01-08 14:08:47'),
(42, 9, 'Nouveau badge débloqué !', 'Vous avez obtenu le badge \"Premier Quiz\" : Réussissez votre premier quiz', '', 1, NULL, '2026-01-08 14:08:47'),
(43, 9, 'Nouveau badge débloqué !', 'Vous avez obtenu le badge \"Sans Faute\" : Obtenez 100% à un quiz', '', 1, NULL, '2026-01-08 14:08:47'),
(44, 9, 'Nouveau badge débloqué !', 'Vous avez obtenu le badge \"Bienvenue\" : Créez votre compte CyberSens', '', 1, NULL, '2026-01-08 14:08:47'),
(45, 9, 'Nouveau module débloqué !', 'Vous avez débloqué le module \"Module 2: Glossaire des Attaques\". Continuez votre progression !', '', 1, '#cours', '2026-01-08 14:14:17'),
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(12, 1, 6, 1, 1, 9, 20, '2026-01-08 08:49:36');

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
  `course_id` int NOT NULL,
  `is_completed` tinyint(1) DEFAULT '0',
  `score` int DEFAULT '0',
  `best_score` int DEFAULT '0',
  `attempts` int DEFAULT '0',
  `time_spent` int DEFAULT '0',
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_course` (`user_id`,`course_id`),
  KEY `course_id` (`course_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_completed` (`is_completed`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `progression`
--

INSERT INTO `progression` (`id`, `user_id`, `course_id`, `is_completed`, `score`, `best_score`, `attempts`, `time_spent`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 100, 100, 1, 0, '2026-01-07 12:12:36', '2026-01-07 12:12:36', '2026-01-07 12:12:36'),
(2, 1, 2, 1, 100, 100, 2, 0, '2026-01-07 12:12:36', '2026-01-07 12:12:36', '2026-01-07 12:12:36'),
(3, 1, 3, 1, 90, 90, 1, 0, '2026-01-07 12:12:36', '2026-01-07 12:12:36', '2026-01-07 12:12:36'),
(4, 1, 4, 1, 80, 80, 3, 0, '2026-01-07 12:12:36', '2026-01-07 12:12:36', '2026-01-07 12:12:36'),
(12, 5, 10, 1, 0, 0, 0, 0, '2026-01-08 10:57:59', '2026-01-08 09:57:59', '2026-01-08 09:57:59'),
(13, 1, 10, 1, 0, 0, 0, 0, '2026-01-08 11:55:01', '2026-01-08 10:55:01', '2026-01-08 10:55:01'),
(14, 1, 11, 0, 0, 0, 0, 0, '2026-01-08 12:02:23', '2026-01-08 11:02:17', '2026-01-08 11:02:23'),
(19, 9, 10, 1, 88, 100, 4, 0, '2026-01-08 15:14:17', '2026-01-08 14:08:21', '2026-01-08 14:14:17'),
(20, 9, 14, 0, 0, 0, 1, 0, '2026-01-08 15:14:49', '2026-01-08 14:14:49', '2026-01-08 14:14:49'),
(21, 5, 11, 1, 83, 83, 2, 0, '2026-01-08 16:34:56', '2026-01-08 15:34:25', '2026-01-08 15:34:56');

-- --------------------------------------------------------

--
-- Structure de la table `questions`
--

DROP TABLE IF EXISTS `questions`;
CREATE TABLE IF NOT EXISTS `questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `question` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_a` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_b` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_c` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_d` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `correct_answer` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `explanation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `difficulty` enum('Facile','Intermédiaire','Difficile') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Facile',
  `xp_reward` int DEFAULT '10',
  `points` int DEFAULT '10',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_course` (`course_id`),
  KEY `idx_difficulty` (`difficulty`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `questions`
--

INSERT INTO `questions` (`id`, `course_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `explanation`, `difficulty`, `xp_reward`, `points`, `created_at`) VALUES
(1, 1, 'Quels sont les trois piliers de la sécurité informatique ?', 'Vitesse, Stockage, Réseau', 'Confidentialité, Intégrité, Disponibilité', 'Hardware, Software, Network', 'Antivirus, Firewall, VPN', 'B', 'La triade CIA (Confidentiality, Integrity, Availability) représente les trois objectifs fondamentaux de la sécurité informatique.', 'Facile', 10, 10, '2026-01-07 12:12:36'),
(2, 1, 'Qu\'est-ce qu\'un ransomware ?', 'Un logiciel antivirus', 'Un type de firewall', 'Un malware qui chiffre les fichiers et demande une rançon', 'Un protocole de sécurité réseau', 'C', 'Un ransomware chiffre vos données et exige un paiement pour les récupérer. Ne payez jamais !', 'Facile', 10, 10, '2026-01-07 12:12:36'),
(3, 1, 'Qu\'est-ce que l\'ingénierie sociale ?', 'La création de réseaux sociaux', 'La manipulation psychologique pour obtenir des informations', 'Un type de programmation', 'La maintenance des serveurs', 'B', 'L\'ingénierie sociale exploite la psychologie humaine plutôt que les failles techniques.', 'Facile', 10, 10, '2026-01-07 12:12:36'),
(4, 2, 'Quelle est la longueur minimale recommandée pour un mot de passe sécurisé ?', '6 caractères', '8 caractères', '12 caractères', '4 caractères', 'C', 'Un mot de passe de 12 caractères minimum offre une bien meilleure protection contre les attaques par force brute.', 'Facile', 10, 10, '2026-01-07 12:12:36'),
(5, 2, 'Qu\'est-ce que l\'authentification à deux facteurs (2FA) ?', 'Utiliser deux mots de passe différents', 'Une double vérification avec un code temporaire', 'Se connecter sur deux appareils', 'Changer son mot de passe deux fois', 'B', 'Le 2FA ajoute une couche de sécurité en demandant un code temporaire en plus du mot de passe.', 'Facile', 10, 10, '2026-01-07 12:12:36'),
(6, 2, 'Quel est le problème avec la réutilisation des mots de passe ?', 'C\'est plus difficile à retenir', 'Si un compte est compromis, tous les autres le sont aussi', 'Ça ralentit la connexion', 'Il n\'y a pas de problème', 'B', 'Une fuite de données sur un site compromet tous vos comptes utilisant le même mot de passe.', 'Facile', 10, 10, '2026-01-07 12:12:36'),
(7, 3, 'Quel est le premier réflexe à avoir face à un email suspect ?', 'Cliquer sur les liens pour vérifier', 'Répondre pour demander des explications', 'Ne pas cliquer et vérifier l\'expéditeur', 'Transférer à un ami', 'C', 'Ne jamais cliquer sur les liens d\'un email suspect. Vérifiez toujours l\'adresse de l\'expéditeur.', 'Facile', 10, 10, '2026-01-07 12:12:36'),
(8, 3, 'Comment reconnaître une URL suspecte ?', 'Elle contient des chiffres', 'Elle est trop longue', 'Elle imite un site connu avec des variations (amaz0n, g00gle)', 'Elle commence par https', 'C', 'Les URLs de phishing imitent souvent les sites légitimes avec de légères modifications.', 'Facile', 10, 10, '2026-01-07 12:12:36'),
(9, 3, 'Qu\'est-ce que le spear phishing ?', 'Un phishing via les réseaux sociaux', 'Un phishing ciblé et personnalisé', 'Un phishing par téléphone', 'Un logiciel anti-phishing', 'B', 'Le spear phishing cible des individus spécifiques avec des informations personnalisées pour être plus crédible.', 'Facile', 10, 10, '2026-01-07 12:12:36'),
(10, 4, 'Quel protocole de sécurité Wi-Fi est le plus sécurisé ?', 'WEP', 'WPA', 'WPA2', 'WPA3', 'D', 'WPA3 est le protocole le plus récent et le plus sécurisé pour les réseaux Wi-Fi.', 'Facile', 10, 10, '2026-01-07 12:12:36'),
(11, 4, 'Pourquoi faut-il désactiver le WPS ?', 'Il consomme trop de batterie', 'Il est vulnérable aux attaques par force brute', 'Il ralentit la connexion', 'Il n\'est pas compatible avec tous les appareils', 'B', 'Le WPS peut être cracké en quelques heures avec des outils spécialisés.', 'Facile', 10, 10, '2026-01-07 12:12:36'),
(12, 4, 'Qu\'est-ce qu\'un réseau invité ?', 'Un réseau pour les employés', 'Un réseau séparé pour les visiteurs sans accès au réseau principal', 'Un réseau sans mot de passe', 'Un réseau temporaire', 'B', 'Un réseau invité isole les appareils des visiteurs de votre réseau principal et de vos données.', 'Facile', 10, 10, '2026-01-07 12:12:36'),
(24, 10, 'Quelle est la fonction principale d\'un \"Adware\" (Logiciel Publicitaire) ?', 'Chiffrer les données de l\'utilisateur', 'Afficher des publicités indésirables ou intrusives', 'Détruire le disque dur', '', 'B', 'C\'est un logiciel qui affiche des publicités intrusives. Ils sont considérés comme plus gênants que dangereux.', 'Facile', 10, 10, '2026-01-08 09:35:12'),
(25, 10, 'Comment un \"Scareware\" tente-t-il de piéger une victime ?', 'En se faisant passer pour une mise à jour Windows officielle.', 'En ralentissant la connexion internet.', 'En utilisant des messages d\'alerte trompeurs et alarmistes pour effrayer l\'utilisateur.', '', 'C', 'Le Scareware utilise la peur (messages type \"Votre ordinateur est infecté !\") pour pousser l\'utilisateur à acheter un produit inutile ou nuisible.', 'Facile', 10, 10, '2026-01-08 09:35:59'),
(26, 10, 'Quelle est la différence majeure entre un Virus et un Ver (Worm) ?', 'Le Virus a besoin d\'un fichier hôte et d\'une action humaine, tandis que le Ver est autonome.', 'Le Ver est inoffensif, alors que le Virus est dangereux.', 'Le Virus se propage par email uniquement, le Ver par clé USB uniquement.', '', 'A', 'Un virus s\'attache à un fichier légitime et nécessite une interaction humaine pour s\'activer , alors qu\'un ver est un programme autonome qui se propage à travers les réseaux sans intervention humaine.', 'Facile', 10, 10, '2026-01-08 09:36:38'),
(27, 10, 'Que fait un Ransomware une fois installé sur un ordinateur ?', 'Il copie les mots de passe et les envoie à un pirate.', 'Il chiffre (encrypte) les fichiers et demande une rançon pour les débloquer.', 'Il affiche des publicités pour des produits de luxe.', '', 'B', 'Le Ransomware bloque l\'accès aux données ou au système et exige un paiement (souvent en cryptomonnaie) en échange de la clé de déchiffrement.', 'Facile', 10, 10, '2026-01-08 09:37:37'),
(28, 10, 'Qu\'est-ce qu\'un Cheval de Troie (Trojan) ?', 'Un logiciel qui se réplique à l\'infini pour saturer le disque dur.', 'Un programme qui se présente comme un logiciel utile mais contient un code malveillant caché.', 'Un outil utilisé uniquement par les banques pour sécuriser les transactions.', '', 'B', 'Comme dans le mythe grec, il semble inoffensif mais cache une charge malveillante. Contrairement aux virus, il ne se réplique généralement pas lui-même.', 'Facile', 10, 10, '2026-01-08 09:38:17'),
(29, 10, 'Quelle est la fonction principale d\'un Rootkit ?', 'Créer un réseau de bots (Botnet).', 'Lancer des attaques DDoS automatiquement.', 'Masquer l\'existence d\'autres logiciels malveillants ou d\'intrus en opérant au niveau profond du système.', '', 'C', 'Les Rootkits sont des outils conçus pour maintenir un accès persistant en cachant l\'existence d\'intrus, opérant souvent au niveau le plus profond (root) du système.', 'Facile', 10, 10, '2026-01-08 09:38:51'),
(30, 10, 'Qu\'est-ce qu\'une \"Backdoor\" (Porte dérobée) ?', 'Un mécanisme caché permettant de contourner la sécurité pour accéder au système à distance.', 'Un mot de passe écrit sur un post-it derrière l\'écran.', 'Une faille de sécurité qui n\'a jamais été découverte.', '', 'A', 'C\'est un moyen d\'accès dissimulé (souvent installé via un Cheval de Troie) qui permet à un attaquant de contrôler le système ou de voler des données sans être vu.', 'Facile', 10, 10, '2026-01-08 09:39:29'),
(31, 10, 'Quand parle-t-on d\'une vulnérabilité \"Zero-Day\" ?', 'Lorsque le virus s\'autodétruit après 24 heures.', 'Lorsqu\'une faille vient d\'être découverte et qu\'aucun correctif (patch) n\'est encore disponible.', 'Lorsque l\'attaque a lieu le premier jour de l\'année.', '', 'B', 'Le terme signifie que les développeurs ont eu \"zéro jour\" pour créer un correctif avant que la faille ne soit connue ou exploitée.', 'Facile', 10, 10, '2026-01-08 09:40:20'),
(32, 11, 'L\'ingénierie sociale repose principalement sur :', 'La manipulation psychologique d\'une personne pour obtenir des informations.', 'La puissance de calcul de l\'ordinateur du pirate.', 'L\'utilisation de bugs dans le système Windows.', '', 'A', 'C\'est l\'art de manipuler quelqu\'un pour qu\'il divulgue des informations confidentielles ou effectue une action compromettante.', 'Facile', 10, 10, '2026-01-08 09:51:44'),
(33, 11, 'Qu\'est-ce que l\'attaque par \"Hameçonnage\" (Phishing) ?', 'Essayer tous les mots de passe possibles jusqu\'à trouver le bon.', 'Inciter une victime (via email ou SMS) à cliquer sur un lien malveillant ou donner ses identifiants.', 'Voler un ordinateur portable dans un lieu public.', '', 'B', 'C\'est une forme d\'ingénierie sociale utilisant des messages (emails, SMS) pour tromper la victime et l\'amener sur un faux site.', 'Facile', 10, 10, '2026-01-08 09:52:47'),
(34, 11, 'Quel est l\'objectif d\'une attaque DDoS (Déni de Service Distribué) ?', 'Voler la base de données clients.', 'Rendre un service ou un site inaccessible en le submergeant de trafic.', 'Modifier la page d\'accueil du site web.', '', 'B', 'Le but est de saturer la cible avec un trafic massif pour bloquer l\'accès aux utilisateurs légitimes.', 'Facile', 10, 10, '2026-01-08 09:53:26'),
(35, 11, 'Dans une attaque \"Homme du milieu\" (Man in the Middle), que fait l\'attaquant ?', 'Il se fait passer pour le directeur de l\'entreprise au téléphone.', 'Il bloque l\'accès au réseau Wi-Fi.', 'Il s\'insère secrètement entre deux parties qui communiquent pour intercepter ou modifier les échanges.', '', 'C', 'L\'attaquant se place entre la victime et le service (ex: banque) pour écouter ou altérer les communications à l\'insu des deux correspondants.', 'Facile', 10, 10, '2026-01-08 09:55:05'),
(36, 11, 'L\'Injection SQL vise spécifiquement :', 'Les autres utilisateurs du site web.', 'La base de données d\'une application web pour manipuler ou voler des données.', 'Le pare-feu de l\'entreprise.', '', 'B', 'L\'attaquant insère des commandes SQL malveillantes dans un champ de saisie pour manipuler la base de données (là où sont stockés les mots de passe et infos clients).', 'Facile', 10, 10, '2026-01-08 09:55:36'),
(37, 11, 'Quelle est la particularité d\'une attaque XSS (Cross Site Scripting) ?', 'Elle injecte un script malveillant qui s\'exécute dans le navigateur des visiteurs du site (et non sur le serveur).', 'Elle efface le disque dur du serveur web.', 'Elle force le serveur à redémarrer en boucle.', '', 'A', 'Contrairement à l\'injection SQL qui vise le serveur, le XSS vise les autres utilisateurs (visiteurs) en exécutant un script dans leur navigateur pour voler des infos comme les cookies.', 'Facile', 10, 10, '2026-01-08 09:56:10'),
(39, 12, 'Quelle est la fonction principale d\'un Pare-feu (Firewall) ?', 'Accélérer la vitesse de connexion internet.', 'Agir comme une barrière filtrant le trafic entre un réseau interne et internet.', 'Supprimer automatiquement les emails de spam.', '', 'B', 'Il filtre le trafic entrant et sortant selon des règles de sécurité prédéfinies pour empêcher les intrusions.', 'Facile', 10, 10, '2026-01-08 13:32:12'),
(40, 12, 'En quoi consiste l\'Authentification à Double Facteur (MFA) ?', 'À utiliser un mot de passe deux fois plus long.', 'À changer son mot de passe tous les deux jours.', 'À exiger deux preuves d\'identité distinctes pour se connecter.', '', 'C', 'Cette méthode combine généralement \"ce que je sais\" (mot de passe) et \"ce que je possède\" (téléphone), rendant le vol de mot de passe insuffisant pour un pirate.', 'Facile', 10, 10, '2026-01-08 13:33:00'),
(41, 12, 'Quel est l\'avantage principal d\'utiliser un VPN (Réseau Privé Virtuel) sur un Wi-Fi public ?', 'Il permet de pirater les autres utilisateurs du réseau.', 'Il crée un tunnel chiffré qui rend vos données illisibles pour les autres.', 'Il augmente la qualité du signal Wi-Fi.', '', 'B', 'Le VPN masque votre adresse IP et chiffre vos données, empêchant un attaquant sur le même réseau d\'intercepter vos échanges.', 'Intermédiaire', 15, 10, '2026-01-08 13:33:45'),
(42, 12, 'Quelle est la différence entre un IDS (Détection) et un IPS (Prévention) ?', 'L\'IDS signale l\'intrusion, tandis que l\'IPS intervient activement pour la bloquer.', 'L\'IDS est matériel, l\'IPS est logiciel.', 'L\'IPS est moins cher que l\'IDS.', '', 'A', 'L\'IDS analyse le trafic pour repérer les anomalies et alerter, alors que l\'IPS peut bloquer l\'intrus dès la tentative d\'effraction.', 'Difficile', 30, 10, '2026-01-08 13:34:44'),
(43, 12, 'Quel est le but d\'un Honeypot (Pot de miel) ?', 'Stocker les mots de passe de manière sécurisée.', 'Attirer les pirates sur un système leurre pour analyser leurs méthodes.', 'Optimiser le flux de données du réseau.', '', 'B', 'C\'est un système conçu pour être une cible facile afin d\'étudier le comportement des attaquants sans risquer les données réelles.', 'Difficile', 30, 10, '2026-01-08 13:35:20'),
(44, 13, 'Qu\'est-ce qui rend une \"Passphrase\" plus sécurisée qu\'un mot de passe court et complexe ?', 'Sa longueur la rend mathématiquement plus difficile à deviner pour un ordinateur.', 'Elle contient toujours des chiffres.', 'Elle est impossible à écrire sur un clavier mais seulement à l\'oral.', '', 'A', 'Une phrase longue (ex: J\'aime_Manger...) est plus robuste face aux attaques qu\'un mot court bourré de symboles (ex: P@ssw0rd!).', 'Facile', 10, 10, '2026-01-08 13:40:47'),
(45, 13, 'Pourquoi faut-il faire les mises à jour logicielles (Patchs) rapidement ?', 'Pour avoir de nouvelles couleurs d\'interface.', 'Pour combler les failles de sécurité avant que les pirates ne les exploitent.', 'Pour vider l\'espace disque utilisé.', '', 'B', 'Les mises à jour agissent comme des \"maçons\" qui bouchent les trous (failles) dans la sécurité de votre système.', 'Facile', 10, 10, '2026-01-08 13:41:48'),
(46, 13, 'À quoi sert un gestionnaire de mots de passe ?', 'À utiliser le même mot de passe partout pour ne pas l\'oublier.', 'À générer et stocker des mots de passe complexes uniques dans un coffre-fort chiffré.', 'À partager ses mots de passe avec ses amis.', '', 'B', 'Il permet de n\'avoir qu\'un seul \"mot de passe maître\" à retenir tout en ayant des codes robustes et différents pour chaque compte.', 'Intermédiaire', 15, 10, '2026-01-08 13:42:38'),
(47, 13, 'Que faut-il vérifier lors de l\'installation d\'une application sur smartphone (Permissions) ?', 'Si l\'icône de l\'application est jolie.', 'Si l\'application a été téléchargée plus de 100 fois.', 'Si les accès demandés (micro, GPS, contacts) sont cohérents avec la fonction de l\'app.', '', 'C', 'Une application simple (comme une lampe de poche) ne devrait pas avoir besoin de votre position GPS ou de vos contacts.', 'Intermédiaire', 15, 10, '2026-01-08 13:43:30'),
(48, 13, 'En quoi consiste la règle du 3-2-1 pour les sauvegardes ?', '3 disques durs, 2 nuages, 1 ordinateur.', 'Sauvegarder 3 fois par jour, 2 fois par semaine, 1 fois par mois.', '3 copies de données, sur 2 supports différents, dont 1 copie hors site.', '', 'C', 'Cette stratégie assure qu\'en cas de sinistre physique (incendie, vol) détruisant les supports locaux, une copie distante (Cloud) reste accessible.', 'Difficile', 30, 10, '2026-01-08 13:44:30'),
(49, 14, 'Qu\'est-ce que le principe du \"Moindre Privilège\" ?', 'Interdire l\'accès à internet aux employés.', 'Donner tous les accès à tout le monde pour gagner du temps.', 'Donner à un utilisateur uniquement les accès strictement nécessaires à son travail.', '', 'B', 'Cela limite les dégâts si un compte est compromis, car le pirate ne pourra pas accéder aux données sensibles non autorisées pour cet utilisateur.', 'Facile', 10, 10, '2026-01-08 13:48:10'),
(50, 14, 'Quel est le risque principal du BYOD (Bring Your Own Device)', 'Les employés travaillent trop longtemps chez eux.', 'L\'entreprise doit payer les forfaits téléphoniques.', 'Les appareils personnels sont souvent moins sécurisés et peuvent infecter le réseau professionnel.', '', 'C', 'Un ordinateur personnel sans antivirus ou avec des logiciels piratés peut servir de cheval de Troie pour introduire des malwares dans l\'entreprise.', 'Intermédiaire', 15, 10, '2026-01-08 13:49:00'),
(51, 14, 'Qu\'appelle-t-on le \"Shadow IT\" (Informatique de l\'ombre) ?', 'L\'utilisation par les employés de logiciels non approuvés par le service informatique.', 'Le travail de nuit des informaticiens.', 'Un réseau de pirates cachés dans l\'entreprise.', '', 'A', 'Cela se produit quand les employés contournent les outils sécurisés (jugés trop lents) pour utiliser des solutions grand public (ex: WeTransfer perso), faisant perdre le contrôle des données à l\'entreprise.', 'Intermédiaire', 15, 10, '2026-01-08 13:49:35'),
(52, 14, 'Sur quelle devise repose le modèle de sécurité \"Zero Trust\" ?', '\"Faire confiance, mais vérifier.\"', '\"Ne jamais faire confiance, toujours vérifier.\"', '\"La sécurité avant tout.\"', '\"Ne jamais faire confiance, sauf à ses collègues.\"', 'B', 'Contrairement au modèle classique du \"château fort\", le Zero Trust considère qu\'aucun utilisateur ou appareil n\'est sûr par défaut, même s\'il est déjà à l\'intérieur du réseau.', 'Difficile', 30, 10, '2026-01-08 13:50:53');

-- --------------------------------------------------------

--
-- Structure de la table `quiz_results`
--

DROP TABLE IF EXISTS `quiz_results`;
CREATE TABLE IF NOT EXISTS `quiz_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `course_id` int NOT NULL,
  `score` int NOT NULL,
  `total_questions` int NOT NULL,
  `correct_answers` int NOT NULL,
  `time_taken` int DEFAULT '0',
  `xp_earned` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_quiz` (`user_id`),
  KEY `idx_course_quiz` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `group_name`, `xp`, `level`, `avatar`, `email_verified`, `verification_code`, `verification_expires`, `last_login`, `created_at`, `updated_at`, `is_protected`) VALUES
(1, 'admin', 'admin@cybersens.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Aucun', 885, 4, NULL, 1, NULL, NULL, '2026-01-08 12:52:11', '2026-01-07 12:12:36', '2026-01-08 11:52:11', 0),
(5, 'louis', 'louis@gmail.prout', '$2y$10$NUwTI6i6y3.5UUr/DHMn7.cQyTUbaIZZUyCTT0lUHNm5zJtzMVeWu', 'user', 'Aucun', 1192, 5, NULL, 1, NULL, NULL, '2026-01-08 16:31:43', '2026-01-07 14:44:56', '2026-01-08 15:34:56', 0),
(9, 'superadmin', 'superadmin@cybersens.local', '$2y$10$65k4Cs5wUDzxsxwj3tjC9eVEPUqYhxVnjvL2t878bbX1opmmvT.pG', 'superadmin', 'Staff', 10211, 7, NULL, 1, NULL, NULL, '2026-01-08 16:35:23', '2026-01-08 11:44:53', '2026-01-08 15:35:23', 1),
(10, 'jules', 'jules@gmail.com', '$2y$10$srsEbDJVeq/zMZg4P6JXSOeOfptzYjFCGHzhA6.wXDt5xX91TwqbG', 'creator', 'Staff', 0, 1, NULL, 1, NULL, NULL, NULL, '2026-01-08 11:57:33', '2026-01-08 11:57:33', 0);

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
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(32, 9, 12, '2026-01-08 14:08:47');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `cours` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `progression_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progression_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `cours` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `cours` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD CONSTRAINT `quiz_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_results_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `cours` (`id`) ON DELETE CASCADE;

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
