-- =====================================================
-- SCRIPT DE CORRECTION DES COLONNES
-- Renomme course_id en module_id si nécessaire
-- =====================================================

-- Désactiver les vérifications de clés étrangères
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- SUPPRIMER LA TABLE CERTIFICATES (plus utilisée)
-- =====================================================
DROP TABLE IF EXISTS `certificates`;

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

-- =====================================================
-- SUBMODULES : Initialiser display_order si nécessaire
-- =====================================================
-- Mettre à jour les display_order à 0 pour les valeurs NULL
UPDATE submodules SET display_order = 0 WHERE display_order IS NULL;

-- Initialiser display_order pour les sous-modules de chaque module
-- (séquentiellement par id si display_order est 0 pour tous)
SET @current_module = 0;
SET @order_num = 0;

UPDATE submodules s
JOIN (
    SELECT id, module_id,
           @order_num := IF(@current_module = module_id, @order_num + 1, 0) AS new_order,
           @current_module := module_id
    FROM submodules
    ORDER BY module_id, display_order, id
) AS ordered ON s.id = ordered.id
SET s.display_order = ordered.new_order;

-- Réactiver les vérifications de clés étrangères
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- VÉRIFICATION FINALE
-- =====================================================
SELECT 'Colonnes mises à jour avec succès!' AS message;

-- Afficher la structure des tables modifiées
SHOW COLUMNS FROM quiz_results WHERE Field = 'module_id';
SHOW COLUMNS FROM questions WHERE Field = 'module_id';
SHOW COLUMNS FROM progression WHERE Field = 'module_id';
