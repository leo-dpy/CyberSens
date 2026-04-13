<?php
/**
 * API Submodules - CyberSens
 * Gestion des sous-modules de formation
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            // Récupérer un sous-module spécifique ou tous les sous-modules d'un module
            if (isset($_GET['id'])) {
                // Récupérer un sous-module spécifique
                $id = (int)$_GET['id'];
                
                $stmt = $pdo->prepare("
                    SELECT s.*, m.title as module_title, m.theme as module_theme
                    FROM submodules s
                    JOIN modules m ON s.module_id = m.id
                    WHERE s.id = ?
                ");
                $stmt->execute([$id]);
                $submodule = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($submodule) {
                    echo json_encode(['success' => true, 'submodule' => $submodule]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Sous-module non trouvé']);
                }
            } elseif (isset($_GET['module_id'])) {
                // Récupérer tous les sous-modules d'un module
                $module_id = (int)$_GET['module_id'];
                $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

                // Vérifier que le module existe
                $moduleStmt = $pdo->prepare("SELECT * FROM modules WHERE id = ? AND is_published = 1");
                $moduleStmt->execute([$module_id]);
                $module = $moduleStmt->fetch(PDO::FETCH_ASSOC);

                if (!$module) {
                    echo json_encode(['success' => false, 'message' => 'Module non trouvé']);
                    exit;
                }

                // Récupérer les sous-modules
                $stmt = $pdo->prepare("
                    SELECT s.*
                    FROM submodules s
                    WHERE s.module_id = ?
                    ORDER BY s.display_order ASC, s.id ASC
                ");
                $stmt->execute([$module_id]);
                $submodules = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Récupérer le nombre de questions du module
                $questionsStmt = $pdo->prepare("SELECT COUNT(*) as count FROM questions WHERE module_id = ?");
                $questionsStmt->execute([$module_id]);
                $questionsCount = $questionsStmt->fetch()['count'];

                // Ajouter le statut de progression si user_id fourni
                if ($user_id) {
                    // Récupérer les sous-modules complétés
                    $progressStmt = $pdo->prepare("
                        SELECT submodule_id, is_completed 
                        FROM progression 
                        WHERE user_id = ? AND submodule_id IS NOT NULL
                    ");
                    $progressStmt->execute([$user_id]);
                    $progressMap = [];
                    while ($row = $progressStmt->fetch()) {
                        $progressMap[$row['submodule_id']] = (bool)$row['is_completed'];
                    }

                    foreach ($submodules as &$submodule) {
                        $submodule['is_read'] = isset($progressMap[$submodule['id']]);
                        $submodule['is_completed'] = $progressMap[$submodule['id']] ?? false;
                    }

                    // Vérifier si le quiz a été fait
                    $quizStmt = $pdo->prepare("
                        SELECT score, correct_answers, total_questions 
                        FROM quiz_results 
                        WHERE user_id = ? AND module_id = ?
                        ORDER BY created_at DESC
                        LIMIT 1
                    ");
                    $quizStmt->execute([$user_id, $module_id]);
                    $quizResult = $quizStmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    foreach ($submodules as &$submodule) {
                        $submodule['is_read'] = false;
                        $submodule['is_completed'] = false;
                    }
                    $quizResult = null;
                }

                echo json_encode([
                    'success' => true, 
                    'module' => $module,
                    'submodules' => $submodules,
                    'quiz' => [
                        'nb_questions' => (int)$questionsCount,
                        'last_result' => $quizResult
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'module_id ou id requis']);
            }
            break;

        case 'POST':
            // Créer un sous-module
            $data = json_decode(file_get_contents('php://input'), true);

            $module_id = (int)($data['module_id'] ?? 0);
            $title = trim($data['title'] ?? '');
            $description = trim($data['description'] ?? '');
            $content = $data['content'] ?? '';
            $icon = trim($data['icon'] ?? 'file-text');
            $xp_reward = (int)($data['xp_reward'] ?? 15);
            $estimated_time = (int)($data['estimated_time'] ?? 10);

            if (!$module_id || empty($title)) {
                echo json_encode(['success' => false, 'message' => 'module_id et titre requis']);
                exit;
            }

            // Vérifier que le module existe
            $moduleStmt = $pdo->prepare("SELECT id FROM modules WHERE id = ?");
            $moduleStmt->execute([$module_id]);
            if (!$moduleStmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Module non trouvé']);
                exit;
            }

            // Récupérer le prochain display_order pour ce module
            $orderStmt = $pdo->prepare("
                SELECT COALESCE(MAX(display_order), 0) + 1 as next_order 
                FROM submodules 
                WHERE module_id = ?
            ");
            $orderStmt->execute([$module_id]);
            $nextOrder = $orderStmt->fetch()['next_order'];

            $stmt = $pdo->prepare("
                INSERT INTO submodules (module_id, title, description, content, icon, xp_reward, estimated_time, display_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$module_id, $title, $description, $content, $icon, $xp_reward, $estimated_time, $nextOrder]);

            $id = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Sous-module créé', 'id' => $id]);
            break;

        case 'PUT':
            // Mettre à jour un sous-module
            $data = json_decode(file_get_contents('php://input'), true);

            // Action de réordonnancement
            if (isset($data['action']) && $data['action'] === 'reorder' && isset($data['order'])) {
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare("UPDATE submodules SET display_order = ? WHERE id = ?");
                    foreach ($data['order'] as $item) {
                        $stmt->execute([(int)$item['display_order'], (int)$item['id']]);
                    }
                    $pdo->commit();
                    echo json_encode(['success' => true, 'message' => 'Ordre mis à jour']);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
                }
                break;
            }

            $id = (int)($data['id'] ?? 0);
            $title = trim($data['title'] ?? '');
            $description = trim($data['description'] ?? '');
            $content = $data['content'] ?? '';
            $icon = trim($data['icon'] ?? 'file-text');
            $xp_reward = (int)($data['xp_reward'] ?? 15);
            $estimated_time = (int)($data['estimated_time'] ?? 10);

            if (!$id || empty($title)) {
                echo json_encode(['success' => false, 'message' => 'ID et titre requis']);
                exit;
            }

            $stmt = $pdo->prepare("
                UPDATE submodules 
                SET title = ?, description = ?, content = ?, icon = ?, xp_reward = ?, estimated_time = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $description, $content, $icon, $xp_reward, $estimated_time, $id]);

            echo json_encode(['success' => true, 'message' => 'Sous-module mis à jour']);
            break;

        case 'DELETE':
            // Supprimer un sous-module
            // L'ID peut être passé en query string ou dans le body
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if (!$id) {
                $data = json_decode(file_get_contents('php://input'), true);
                $id = (int)($data['id'] ?? 0);
            }

            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID requis']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM submodules WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Sous-module supprimé']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
