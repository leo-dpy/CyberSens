<?php
/**
 * API Modules - CyberSens
 * Gestion des modules de formation
 */

header('Content-Type: application/json');

require 'db.php';
setCorsHeaders();
setSecurityHeaders();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Récupérer un module spécifique ou tous les modules
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                
                $stmt = $pdo->prepare("
                    SELECT m.*, 
                        (SELECT COUNT(*) FROM submodules WHERE module_id = m.id) as nb_submodules,
                        (SELECT COUNT(*) FROM questions WHERE module_id = m.id) as nb_questions
                    FROM modules m 
                    WHERE m.id = ? AND m.is_published = 1
                ");
                $stmt->execute([$id]);
                $module = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($module) {
                    echo json_encode(['success' => true, 'module' => $module]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Module non trouvé']);
                }
            } else {
                // Récupérer le rôle de l'utilisateur si fourni
                $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
                $user_role = isset($_GET['role']) ? $_GET['role'] : 'user';

                // Récupérer tous les modules publiés
                $sql = "
                    SELECT m.*, 
                        (SELECT COUNT(*) FROM submodules WHERE module_id = m.id) as nb_submodules,
                        (SELECT COUNT(*) FROM questions WHERE module_id = m.id) as nb_questions
                    FROM modules m 
                    WHERE m.is_published = 1
                    ORDER BY m.display_order ASC, m.id ASC
                ";
                $stmt = $pdo->query($sql);
                $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Vérifier le verrouillage si un user_id est fourni
                if ($user_id) {
                    // Récupérer les modules complétés par l'utilisateur (quiz réussi à 70%+)
                    $completedStmt = $pdo->prepare("
                        SELECT DISTINCT module_id 
                        FROM quiz_results 
                        WHERE user_id = ? AND score >= 70
                    ");
                    $completedStmt->execute([$user_id]);
                    $completedModules = $completedStmt->fetchAll(PDO::FETCH_COLUMN);

                    // Récupérer les modules où l'utilisateur a commencé à lire des sous-modules
                    $progressStmt = $pdo->prepare("
                        SELECT DISTINCT s.module_id 
                        FROM progression p
                        JOIN submodules s ON p.submodule_id = s.id
                        WHERE p.user_id = ?
                    ");
                    $progressStmt->execute([$user_id]);
                    $startedModules = $progressStmt->fetchAll(PDO::FETCH_COLUMN);

                    // Compter les sous-modules complétés par module
                    $submoduleProgressStmt = $pdo->prepare("
                        SELECT s.module_id, COUNT(*) as completed_count
                        FROM progression p
                        JOIN submodules s ON p.submodule_id = s.id
                        WHERE p.user_id = ? AND p.is_completed = 1
                        GROUP BY s.module_id
                    ");
                    $submoduleProgressStmt->execute([$user_id]);
                    $submoduleProgress = [];
                    while ($row = $submoduleProgressStmt->fetch()) {
                        $submoduleProgress[$row['module_id']] = (int)$row['completed_count'];
                    }

                    // SuperAdmin : tout débloqué
                    $isSuperAdmin = ($user_role === 'superadmin');

                    // Ajouter le statut de verrouillage à chaque module
                    foreach ($modules as $index => &$module) {
                        $moduleId = $module['id'];
                        
                        if ($isSuperAdmin || $index === 0) {
                            // SuperAdmin ou premier module = toujours déverrouillé
                            $module['is_locked'] = false;
                        } else {
                            // Vérifie si le module précédent est complété (quiz réussi)
                            $previousModuleId = $modules[$index - 1]['id'];
                            $module['is_locked'] = !in_array($previousModuleId, $completedModules);
                        }
                        
                        $module['is_completed'] = in_array($moduleId, $completedModules);
                        $module['is_started'] = in_array($moduleId, $startedModules);
                        $module['completed_submodules'] = $submoduleProgress[$moduleId] ?? 0;
                    }
                } else {
                    // Pas d'utilisateur, tout est déverrouillé
                    foreach ($modules as &$module) {
                        $module['is_locked'] = false;
                        $module['is_completed'] = false;
                        $module['is_started'] = false;
                        $module['completed_submodules'] = 0;
                    }
                }

                echo json_encode(['success' => true, 'modules' => $modules]);
            }
            break;

        case 'POST':
            // Créer un module (admin uniquement)
            requireApiRole($pdo, 'creator');
            $data = json_decode(file_get_contents('php://input'), true);

            $title = trim($data['title'] ?? '');
            $description = trim($data['description'] ?? '');
            $difficulty = $data['difficulty'] ?? 'Facile';
            $icon = trim($data['icon'] ?? 'shield');
            $theme = trim($data['theme'] ?? 'blue');
            $xp_reward = (int)($data['xp_reward'] ?? 50);
            $is_published = isset($data['is_published']) ? (int)$data['is_published'] : 1;

            if (empty($title)) {
                echo json_encode(['success' => false, 'error' => 'Le titre est requis']);
                exit;
            }

            // Récupérer le prochain display_order
            $orderStmt = $pdo->query("SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM modules");
            $nextOrder = $orderStmt->fetch()['next_order'];

            $stmt = $pdo->prepare("
                INSERT INTO modules (title, description, difficulty, icon, theme, xp_reward, display_order, is_published) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $description, $difficulty, $icon, $theme, $xp_reward, $nextOrder, $is_published]);

            $id = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Module créé', 'id' => $id]);
            break;

        case 'PUT':
            // Mettre à jour un module (admin uniquement)
            requireApiRole($pdo, 'creator');
            $data = json_decode(file_get_contents('php://input'), true);

            // Action de réordonnancement
            if (isset($data['action']) && $data['action'] === 'reorder' && isset($data['order'])) {
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare("UPDATE modules SET display_order = ? WHERE id = ?");
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
            $difficulty = $data['difficulty'] ?? 'Facile';
            $icon = trim($data['icon'] ?? 'book-open');
            $theme = trim($data['theme'] ?? 'blue');
            $xp_reward = (int)($data['xp_reward'] ?? 50);
            $is_published = isset($data['is_published']) ? (int)$data['is_published'] : 1;

            if (!$id || empty($title)) {
                echo json_encode(['success' => false, 'message' => 'ID et titre requis']);
                exit;
            }

            $stmt = $pdo->prepare("
                UPDATE modules 
                SET title = ?, description = ?, difficulty = ?, icon = ?, theme = ?, xp_reward = ?, is_published = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $description, $difficulty, $icon, $theme, $xp_reward, $is_published, $id]);

            echo json_encode(['success' => true, 'message' => 'Module mis à jour']);
            break;

        case 'DELETE':
            // Supprimer un module (admin uniquement, cascade les sous-modules et questions)
            requireApiRole($pdo, 'admin');
            // L'ID peut être passé en query string ou dans le body
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if (!$id) {
                $data = json_decode(file_get_contents('php://input'), true);
                $id = (int)($data['id'] ?? 0);
            }

            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'ID requis']);
                exit;
            }

            // Supprimer d'abord les sous-modules (au cas où pas de FK CASCADE)
            $stmt = $pdo->prepare("DELETE FROM submodules WHERE module_id = ?");
            $stmt->execute([$id]);
            
            // Supprimer les questions
            $stmt = $pdo->prepare("DELETE FROM questions WHERE module_id = ?");
            $stmt->execute([$id]);
            
            // Supprimer le module
            $stmt = $pdo->prepare("DELETE FROM modules WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Module supprimé']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
