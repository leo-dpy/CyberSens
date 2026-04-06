-- =====================================================
-- SCRIPT DE CORRECTION DES COLONNES
-- Renomme course_id en module_id si nécessaire
-- =====================================================

-- Désactiver les vérifications de clés étrangères
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- CERTIFICATES : Renommer course_id en module_id
-- =====================================================
-- Vérifier si la colonne course_id existe
SET @col_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'certificates' 
    AND COLUMN_NAME = 'course_id');

-- Si course_id existe, le renommer en module_id
SET @sql = IF(@col_exists > 0, 
    'ALTER TABLE `certificates` CHANGE COLUMN `course_id` `module_id` int NOT NULL',
    'SELECT "Column module_id already exists in certificates"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- QUIZ_RESULTS : Renommer course_id en module_id
-- =====================================================
SET @col_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'quiz_results' 
    AND COLUMN_NAME = 'course_id');

SET @sql = IF(@col_exists > 0, 
    'ALTER TABLE `quiz_results` CHANGE COLUMN `course_id` `module_id` int NOT NULL',
    'SELECT "Column module_id already exists in quiz_results"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- QUESTIONS : Renommer course_id en module_id
-- =====================================================
SET @col_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'questions' 
    AND COLUMN_NAME = 'course_id');

SET @sql = IF(@col_exists > 0, 
    'ALTER TABLE `questions` CHANGE COLUMN `course_id` `module_id` int NOT NULL',
    'SELECT "Column module_id already exists in questions"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- PROGRESSION : S'assurer que les colonnes existent
-- =====================================================
-- Vérifier si module_id existe dans progression
SET @col_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'progression' 
    AND COLUMN_NAME = 'module_id');

-- Si module_id n'existe pas mais course_id existe, renommer
SET @course_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'progression' 
    AND COLUMN_NAME = 'course_id');

SET @sql = IF(@col_exists = 0 AND @course_exists > 0, 
    'ALTER TABLE `progression` CHANGE COLUMN `course_id` `module_id` int DEFAULT NULL',
    'SELECT "Column module_id OK in progression"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Réactiver les vérifications de clés étrangères
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- VÉRIFICATION FINALE
-- =====================================================
SELECT 'Colonnes mises à jour avec succès!' AS message;

-- Afficher la structure des tables modifiées
SHOW COLUMNS FROM certificates WHERE Field = 'module_id';
SHOW COLUMNS FROM quiz_results WHERE Field = 'module_id';
SHOW COLUMNS FROM questions WHERE Field = 'module_id';
SHOW COLUMNS FROM progression WHERE Field = 'module_id';
