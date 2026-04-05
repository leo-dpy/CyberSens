-- =====================================================
-- MIGRATION: cours → modules/submodules
-- CyberSens - Migration Script
-- =====================================================
-- ⚠️ ATTENTION: Ce script supprime la table cours et les données associées
-- Exécuter uniquement après backup de la base de données
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

-- =====================================================
-- 1. SUPPRIMER LES ANCIENNES TABLES (ordre important)
-- =====================================================

-- D'abord supprimer les tables qui ont des FK vers cours
DROP TABLE IF EXISTS `certificates`;
DROP TABLE IF EXISTS `quiz_results`;
DROP TABLE IF EXISTS `progression`;
DROP TABLE IF EXISTS `questions`;

-- Maintenant supprimer la table cours
DROP TABLE IF EXISTS `cours`;

-- =====================================================
-- 2. CRÉER LA TABLE MODULES
-- =====================================================

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. CRÉER LA TABLE SUBMODULES
-- =====================================================

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
  KEY `idx_order` (`display_order`),
  CONSTRAINT `fk_submodule_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. RECRÉER LA TABLE QUESTIONS (liée aux modules)
-- =====================================================

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
  KEY `idx_module` (`module_id`),
  CONSTRAINT `fk_question_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. ADAPTER LA TABLE PROGRESSION
-- =====================================================

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
  KEY `idx_user` (`user_id`),
  KEY `idx_module` (`module_id`),
  KEY `idx_submodule` (`submodule_id`),
  UNIQUE KEY `unique_user_submodule` (`user_id`, `submodule_id`),
  CONSTRAINT `fk_progression_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_progression_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_progression_submodule` FOREIGN KEY (`submodule_id`) REFERENCES `submodules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. ADAPTER LA TABLE CERTIFICATES (liée aux modules)
-- =====================================================

DROP TABLE IF EXISTS `certificates`;

CREATE TABLE IF NOT EXISTS `certificates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `module_id` int NOT NULL,
  `certificate_code` varchar(50) NOT NULL,
  `score` int DEFAULT '0',
  `issued_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `certificate_code` (`certificate_code`),
  UNIQUE KEY `unique_user_module` (`user_id`,`module_id`),
  KEY `module_id` (`module_id`),
  CONSTRAINT `fk_cert_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cert_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. ADAPTER LA TABLE QUIZ_RESULTS (liée aux modules)
-- =====================================================

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
  KEY `idx_module` (`module_id`),
  CONSTRAINT `fk_quiz_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_quiz_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. DONNÉES D'EXEMPLE - MODULES
-- =====================================================

INSERT INTO `modules` (`id`, `title`, `description`, `icon`, `theme`, `difficulty`, `xp_reward`, `display_order`) VALUES
(1, 'Introduction à la Cybersécurité', 'Découvrez les fondamentaux de la sécurité informatique et les menaces qui pèsent sur vos données.', 'shield', 'blue', 'Facile', 50, 1),
(2, 'Sécurité des Mots de Passe', 'Apprenez à créer, gérer et protéger vos mots de passe efficacement.', 'key', 'green', 'Facile', 60, 2),
(3, 'Reconnaître le Phishing', 'Identifiez les tentatives d\'hameçonnage et protégez-vous des arnaques en ligne.', 'mail-warning', 'red', 'Intermédiaire', 75, 3);

-- =====================================================
-- 9. DONNÉES D'EXEMPLE - SOUS-MODULES
-- =====================================================

-- Module 1: Introduction à la Cybersécurité
INSERT INTO `submodules` (`module_id`, `title`, `description`, `content`, `icon`, `xp_reward`, `estimated_time`, `display_order`) VALUES
(1, 'Qu\'est-ce que la cybersécurité ?', 'Comprendre les bases et l\'importance de la sécurité informatique.', '<h2>🛡️ La cybersécurité expliquée</h2><p>La <strong>cybersécurité</strong> est l\'ensemble des moyens techniques, organisationnels et humains mis en place pour protéger les systèmes informatiques, les réseaux et les données contre les attaques malveillantes.</p><h3>Pourquoi c\'est important ?</h3><p>En 2025, les cyberattaques coûtent des <strong>milliards d\'euros</strong> aux entreprises et particuliers. Chaque individu connecté est une cible potentielle.</p><ul><li>📧 Vos emails peuvent être interceptés</li><li>💳 Vos données bancaires peuvent être volées</li><li>🔐 Vos comptes peuvent être piratés</li><li>📸 Vos photos privées peuvent être exposées</li></ul><p>La bonne nouvelle ? Avec les <strong>bonnes pratiques</strong>, vous pouvez réduire considérablement les risques !</p>', 'book-open', 15, 5, 1),
(1, 'Les trois piliers de la sécurité', 'Confidentialité, Intégrité et Disponibilité : le triangle de la sécurité.', '<h2>🔺 Le Triangle CIA</h2><p>La sécurité informatique repose sur <strong>trois piliers fondamentaux</strong>, souvent appelés le \"Triangle CIA\" :</p><h3>🔒 Confidentialité</h3><p>Seules les personnes <strong>autorisées</strong> peuvent accéder aux informations sensibles.</p><p><em>Exemple : Vos messages privés ne doivent être lus que par vous et le destinataire.</em></p><h3>✅ Intégrité</h3><p>Les données ne peuvent pas être <strong>modifiées</strong> sans autorisation. Vous êtes sûr que l\'information n\'a pas été altérée.</p><p><em>Exemple : Un virement bancaire de 100€ ne doit pas pouvoir être modifié en 10 000€ par un pirate.</em></p><h3>⚡ Disponibilité</h3><p>Les systèmes et données sont <strong>accessibles</strong> quand on en a besoin.</p><p><em>Exemple : Votre banque en ligne doit être accessible 24h/24 pour consulter vos comptes.</em></p>', 'triangle', 15, 7, 2),
(1, 'Les principales menaces', 'Tour d\'horizon des cyberattaques les plus courantes.', '<h2>⚠️ Les menaces qui vous guettent</h2><p>Les cyberattaques peuvent prendre de nombreuses formes. Voici les plus courantes :</p><h3>🦠 Les Malwares</h3><ul><li><strong>Virus</strong> : Se propage en infectant d\'autres fichiers</li><li><strong>Ransomware</strong> : Chiffre vos fichiers et demande une rançon</li><li><strong>Cheval de Troie</strong> : Se cache dans un logiciel légitime</li><li><strong>Spyware</strong> : Espionne vos activités</li></ul><h3>🎣 Le Phishing</h3><p>Tentatives d\'hameçonnage par email, SMS ou téléphone pour voler vos identifiants.</p><h3>🔓 Attaques par force brute</h3><p>Tentatives automatisées de deviner votre mot de passe en testant des milliers de combinaisons.</p><h3>🧠 Ingénierie sociale</h3><p>Manipulation psychologique pour vous faire révéler des informations confidentielles.</p><p><strong>La clé ?</strong> Rester vigilant et appliquer les bonnes pratiques que vous apprendrez dans cette formation !</p>', 'alert-triangle', 20, 10, 3);

-- Module 2: Sécurité des Mots de Passe
INSERT INTO `submodules` (`module_id`, `title`, `description`, `content`, `icon`, `xp_reward`, `estimated_time`, `display_order`) VALUES
(2, 'L\'importance des mots de passe', 'Pourquoi vos mots de passe sont-ils si importants ?', '<h2>🔑 Votre première ligne de défense</h2><p>Le mot de passe est souvent la <strong>première et unique barrière</strong> entre un pirate et vos données personnelles.</p><h3>Un mot de passe faible, c\'est...</h3><p>...comme laisser la porte de sa maison grande ouverte avec un panneau \"Entrez !\" 🚪</p><h3>Les statistiques alarmantes</h3><ul><li>📊 <strong>81%</strong> des violations de données sont dues à des mots de passe faibles ou volés</li><li>⏱️ Un mot de passe de 6 caractères peut être cracké en <strong>quelques secondes</strong></li><li>🔄 <strong>65%</strong> des personnes réutilisent le même mot de passe partout</li></ul><h3>Ce que vous risquez</h3><ul><li>Accès à vos emails → Reset de tous vos autres comptes</li><li>Accès à vos réseaux sociaux → Usurpation d\'identité</li><li>Accès à vos comptes bancaires → Pertes financières</li></ul>', 'lock', 15, 5, 1),
(2, 'Créer un mot de passe robuste', 'Les règles d\'or pour des mots de passe inviolables.', '<h2>💪 Les caractéristiques d\'un bon mot de passe</h2><h3>Les règles d\'or</h3><ul><li>📏 <strong>Longueur</strong> : Minimum 12 caractères (idéalement 16+)</li><li>🔤 <strong>Complexité</strong> : Mélange de majuscules, minuscules, chiffres et symboles</li><li>🎯 <strong>Unicité</strong> : Un mot de passe différent pour chaque compte</li><li>🎲 <strong>Imprévisibilité</strong> : Éviter les informations personnelles (date de naissance, nom du chien...)</li></ul><h3>La méthode de la phrase secrète</h3><p>Une technique efficace est de créer une phrase et de la transformer :</p><p><code>\"J\'aime le café le matin à 7h\"</code></p><p>Devient :</p><p><code>J@1m3L3C@f3L3M@t1n@7h!</code></p><h3>Ce qu\'il faut éviter</h3><ul><li>❌ 123456, password, azerty</li><li>❌ Votre prénom + année de naissance</li><li>❌ Le nom de votre animal de compagnie</li><li>❌ Des mots du dictionnaire</li></ul>', 'shield-check', 20, 8, 2),
(2, 'Les gestionnaires de mots de passe', 'Comment retenir des dizaines de mots de passe complexes ?', '<h2>🗄️ Votre coffre-fort numérique</h2><p>Impossible de retenir des dizaines de mots de passe complexes ? C\'est normal ! C\'est pourquoi les <strong>gestionnaires de mots de passe</strong> existent.</p><h3>Comment ça marche ?</h3><ol><li>Vous créez UN mot de passe maître très solide</li><li>Le gestionnaire génère et stocke tous vos autres mots de passe</li><li>Ils sont chiffrés et sécurisés</li><li>Remplissage automatique sur vos sites</li></ol><h3>Les gestionnaires recommandés</h3><ul><li>🔐 <strong>Bitwarden</strong> (gratuit et open-source)</li><li>🔐 <strong>1Password</strong> (payant, très complet)</li><li>🔐 <strong>Dashlane</strong> (avec VPN inclus)</li><li>🔐 <strong>KeePass</strong> (local, pour les experts)</li></ul><h3>⚠️ Ne jamais faire</h3><ul><li>Stocker vos mots de passe dans un fichier texte</li><li>Les écrire sur un post-it</li><li>Les envoyer par email</li></ul>', 'database', 20, 10, 3),
(2, 'L\'authentification à deux facteurs', 'Doublez votre sécurité avec le 2FA.', '<h2>🔐 Le 2FA : Votre bouclier supplémentaire</h2><p>L\'<strong>authentification à deux facteurs (2FA)</strong> ajoute une couche de sécurité même si votre mot de passe est compromis.</p><h3>Le principe</h3><p>Pour vous connecter, vous devez fournir :</p><ol><li><strong>Ce que vous savez</strong> : Votre mot de passe</li><li><strong>Ce que vous avez</strong> : Votre téléphone (code SMS, app d\'authentification)</li></ol><h3>Les méthodes de 2FA</h3><ul><li>📱 <strong>SMS</strong> : Un code envoyé par texto (le moins sécurisé)</li><li>📲 <strong>Application</strong> : Google Authenticator, Authy, Microsoft Authenticator</li><li>🔑 <strong>Clé physique</strong> : YubiKey, Titan (le plus sécurisé)</li></ul><h3>Où l\'activer en priorité ?</h3><ul><li>✅ Votre email principal</li><li>✅ Vos comptes bancaires</li><li>✅ Vos réseaux sociaux</li><li>✅ Vos services cloud (Google, iCloud, Dropbox)</li></ul><p><strong>Conseil :</strong> Activez le 2FA partout où c\'est possible !</p>', 'smartphone', 15, 7, 4);

-- Module 3: Reconnaître le Phishing
INSERT INTO `submodules` (`module_id`, `title`, `description`, `content`, `icon`, `xp_reward`, `estimated_time`, `display_order`) VALUES
(3, 'Qu\'est-ce que le phishing ?', 'Comprendre cette menace omniprésente.', '<h2>🎣 L\'hameçonnage expliqué</h2><p>Le <strong>phishing</strong> (ou hameçonnage) est une technique utilisée par les cybercriminels pour voler vos informations personnelles en se faisant passer pour une entité de confiance.</p><h3>Comment ça fonctionne ?</h3><ol><li>Le pirate envoie un message imitant une source fiable (banque, impôts, Netflix...)</li><li>Le message crée un sentiment d\'<strong>urgence</strong> ou de <strong>peur</strong></li><li>Vous cliquez sur un lien vers un <strong>faux site</strong></li><li>Vous entrez vos identifiants... directement chez le pirate !</li></ol><h3>Les chiffres</h3><ul><li>📧 <strong>3,4 milliards</strong> d\'emails de phishing envoyés chaque jour</li><li>💰 <strong>17 700€</strong> : perte moyenne par victime en entreprise</li><li>⏱️ <strong>82%</strong> des violations de données impliquent un facteur humain</li></ul>', 'fish', 15, 5, 1),
(3, 'Les différents types de phishing', 'Email, SMS, téléphone : les pirates s\'adaptent.', '<h2>📱 Les variantes du phishing</h2><h3>📧 Email Phishing</h3><p>La forme la plus classique. Faux emails imitant des entreprises légitimes.</p><p><em>\"Votre colis est bloqué, cliquez ici pour payer 1,99€ de frais\"</em></p><h3>📱 Smishing (SMS Phishing)</h3><p>Phishing par SMS. Souvent des faux messages de livraison ou d\'administration.</p><p><em>\"AMELI : Votre carte vitale expire, mettez à jour vos informations\"</em></p><h3>📞 Vishing (Voice Phishing)</h3><p>Phishing par téléphone. Un \"conseiller\" vous appelle pour \"sécuriser\" votre compte.</p><h3>🎯 Spear Phishing</h3><p>Attaques <strong>ciblées et personnalisées</strong>. Le pirate a fait des recherches sur vous.</p><h3>🐋 Whaling</h3><p>Spear phishing ciblant les dirigeants d\'entreprise (les \"gros poissons\").</p>', 'mail-search', 20, 8, 2),
(3, 'Les signaux d\'alerte', 'Comment repérer un email frauduleux ?', '<h2>🚨 Les red flags du phishing</h2><p>Apprenez à reconnaître les signes qui doivent vous alerter :</p><h3>📧 L\'adresse de l\'expéditeur</h3><ul><li>❌ support@amaz0n-secure.com</li><li>❌ service-client@labanquepostale.security-update.com</li><li>✅ support@amazon.fr</li></ul><h3>⚠️ Le ton du message</h3><ul><li>❌ \"URGENT : Votre compte sera bloqué dans 24h\"</li><li>❌ \"Félicitations ! Vous avez gagné un iPhone\"</li><li>❌ \"Action immédiate requise\"</li></ul><h3>✍️ La qualité du texte</h3><ul><li>Fautes d\'orthographe et de grammaire</li><li>Formulations étranges</li><li>Ponctuation excessive !!!</li></ul><h3>🔗 Les liens suspects</h3><p><strong>Astuce :</strong> Survolez le lien SANS cliquer pour voir la vraie URL</p><ul><li>❌ http://www.paypa1.com-secure-login.xyz/</li><li>✅ https://www.paypal.com/</li></ul><h3>📎 Les pièces jointes</h3><p>Méfiance avec les .exe, .zip, .doc avec macros</p>', 'alert-octagon', 25, 12, 3),
(3, 'Que faire en cas de doute ?', 'Les bons réflexes face à un message suspect.', '<h2>✅ Les bons réflexes</h2><h3>En cas de doute sur un email</h3><ol><li>🚫 <strong>Ne cliquez sur aucun lien</strong></li><li>📎 <strong>Ne téléchargez aucune pièce jointe</strong></li><li>🔍 <strong>Vérifiez l\'adresse de l\'expéditeur</strong></li><li>🌐 <strong>Allez directement sur le site officiel</strong> (tapez l\'URL vous-même)</li><li>📞 <strong>Contactez l\'entreprise</strong> via ses canaux officiels</li></ol><h3>Si vous avez cliqué...</h3><ol><li>🔐 Changez immédiatement vos mots de passe</li><li>💳 Surveillez vos comptes bancaires</li><li>🛡️ Lancez un scan antivirus</li><li>📢 Signalez l\'incident</li></ol><h3>Où signaler ?</h3><ul><li>🇫🇷 <strong>signal-spam.fr</strong> : Pour les emails</li><li>🇫🇷 <strong>internet-signalement.gouv.fr</strong> : Plateforme PHAROS</li><li>📧 Transférez à <strong>phishing@votrebanque.fr</strong></li></ul>', 'check-circle', 15, 5, 4);

-- =====================================================
-- 10. QUESTIONS D'EXEMPLE POUR LES QUIZ
-- =====================================================

-- Questions Module 1: Introduction à la Cybersécurité
INSERT INTO `questions` (`module_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `explanation`, `difficulty`, `xp_reward`, `points`) VALUES
(1, 'Quels sont les trois piliers de la cybersécurité ?', 'Confidentialité, Intégrité, Disponibilité', 'Sécurité, Protection, Défense', 'Antivirus, Pare-feu, VPN', 'Mot de passe, Chiffrement, Sauvegarde', 'A', 'Le triangle CIA (Confidentiality, Integrity, Availability) représente les trois objectifs fondamentaux de la sécurité informatique.', 'Facile', 5, 10),
(1, 'Qu\'est-ce qu\'un ransomware ?', 'Un antivirus gratuit', 'Un logiciel qui chiffre vos fichiers et demande une rançon', 'Un pare-feu avancé', 'Un gestionnaire de mots de passe', 'B', 'Un ransomware (rançongiciel) chiffre vos données et exige un paiement pour les déchiffrer.', 'Facile', 5, 10),
(1, 'L\'ingénierie sociale consiste à :', 'Pirater des systèmes informatiques', 'Manipuler psychologiquement les personnes', 'Créer des virus', 'Réparer des ordinateurs', 'B', 'L\'ingénierie sociale exploite la psychologie humaine pour obtenir des informations confidentielles.', 'Intermédiaire', 7, 15),
(1, 'Quelle est la principale menace pour la sécurité informatique ?', 'Les bugs logiciels', 'Le facteur humain', 'Les pannes matérielles', 'Les coupures électriques', 'B', 'Plus de 80% des violations de données impliquent un facteur humain (erreur, négligence, manipulation).', 'Intermédiaire', 7, 15);

-- Questions Module 2: Sécurité des Mots de Passe
INSERT INTO `questions` (`module_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `explanation`, `difficulty`, `xp_reward`, `points`) VALUES
(2, 'Quelle est la longueur minimale recommandée pour un mot de passe ?', '6 caractères', '8 caractères', '12 caractères', '4 caractères', 'C', 'Les experts recommandent au moins 12 caractères, idéalement 16 ou plus pour une sécurité optimale.', 'Facile', 5, 10),
(2, 'Quel est le problème avec le mot de passe \"123456\" ?', 'Il est trop long', 'C\'est le mot de passe le plus utilisé au monde', 'Il contient des chiffres', 'Il n\'y a aucun problème', 'B', '\"123456\" est le mot de passe le plus courant et le premier testé par les pirates.', 'Facile', 5, 10),
(2, 'Qu\'est-ce que l\'authentification à deux facteurs (2FA) ?', 'Utiliser deux mots de passe', 'Changer son mot de passe deux fois par an', 'Combiner mot de passe et second facteur (code, téléphone)', 'Avoir deux comptes différents', 'C', 'Le 2FA ajoute une couche de sécurité en demandant quelque chose que vous savez (mot de passe) ET quelque chose que vous avez (téléphone).', 'Facile', 5, 10),
(2, 'Où est-il acceptable de stocker ses mots de passe ?', 'Dans un fichier texte sur le bureau', 'Sur un post-it collé à l\'écran', 'Dans un gestionnaire de mots de passe chiffré', 'Dans un email envoyé à soi-même', 'C', 'Un gestionnaire de mots de passe chiffré est la seule méthode sécurisée pour stocker vos mots de passe.', 'Facile', 5, 10),
(2, 'Pourquoi ne faut-il pas réutiliser le même mot de passe ?', 'C\'est plus facile à retenir', 'Si un compte est compromis, tous les autres le sont aussi', 'Les sites web l\'interdisent', 'Cela ralentit la connexion', 'B', 'Si un pirate obtient votre mot de passe sur un site compromis, il essaiera ce même mot de passe sur tous vos autres comptes.', 'Intermédiaire', 7, 15);

-- Questions Module 3: Reconnaître le Phishing
INSERT INTO `questions` (`module_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `explanation`, `difficulty`, `xp_reward`, `points`) VALUES
(3, 'Qu\'est-ce que le phishing ?', 'Un type de virus', 'Une technique pour voler des informations en se faisant passer pour une entité de confiance', 'Un logiciel de sécurité', 'Un jeu en ligne', 'B', 'Le phishing (hameçonnage) consiste à usurper l\'identité d\'une organisation pour voler vos données.', 'Facile', 5, 10),
(3, 'Quel élément doit vous alerter dans un email ?', 'Le logo de l\'entreprise', 'Un sentiment d\'urgence extrême', 'La présence d\'une signature', 'Un objet clair', 'B', 'Les emails de phishing créent souvent un sentiment d\'urgence pour vous pousser à agir sans réfléchir.', 'Facile', 5, 10),
(3, 'Comment vérifier si un lien est suspect ?', 'Cliquer dessus pour voir', 'Le survoler sans cliquer pour voir l\'URL réelle', 'Demander à un ami', 'Ignorer tous les liens', 'B', 'En survolant un lien, vous pouvez voir l\'URL de destination réelle sans risquer de cliquer.', 'Facile', 5, 10),
(3, 'Que faire si vous avez cliqué sur un lien de phishing ?', 'Rien, c\'est trop tard', 'Changer vos mots de passe immédiatement', 'Éteindre votre ordinateur définitivement', 'Attendre de voir si quelque chose se passe', 'B', 'Agissez rapidement : changez vos mots de passe, surveillez vos comptes et signalez l\'incident.', 'Intermédiaire', 7, 15),
(3, 'Qu\'est-ce que le smishing ?', 'Du phishing par SMS', 'Du phishing par email', 'Un type de malware', 'Une technique de hacking', 'A', 'Le smishing combine SMS + phishing. Ce sont des tentatives d\'hameçonnage par message texte.', 'Intermédiaire', 7, 15);

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
