<?php
/**
 * API Progression - CyberSens
 * Gestion de la progression des utilisateurs (sous-modules et modules)
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
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
            // Récupérer la progression d'un utilisateur
            $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
            
            if (!$user_id) {
                echo json_encode(['success' => false, 'message' => 'ID utilisateur requis']);
                exit;
            }

            // Progression des sous-modules
            $submoduleStmt = $pdo->prepare("
                SELECT p.*, s.title as submodule_title, s.module_id, m.title as module_title
                FROM progression p 
                JOIN submodules s ON p.submodule_id = s.id 
                JOIN modules m ON s.module_id = m.id
                WHERE p.user_id = ? AND p.submodule_id IS NOT NULL
            ");
            $submoduleStmt->execute([$user_id]);
            $submoduleProgress = $submoduleStmt->fetchAll(PDO::FETCH_ASSOC);

            // Résumé par module
            $moduleStmt = $pdo->prepare("
                SELECT 
                    m.id as module_id,
                    m.title as module_title,
                    m.difficulty,
                    COUNT(DISTINCT s.id) as total_submodules,
                    COUNT(DISTINCT CASE WHEN p.is_completed = 1 THEN s.id END) as completed_submodules,
                    MAX(qr.score) as best_quiz_score,
                    (SELECT COUNT(*) FROM quiz_results WHERE user_id = ? AND module_id = m.id AND score >= 70) > 0 as module_completed
                FROM modules m
                LEFT JOIN submodules s ON s.module_id = m.id
                LEFT JOIN progression p ON p.submodule_id = s.id AND p.user_id = ?
                LEFT JOIN quiz_results qr ON qr.module_id = m.id AND qr.user_id = ?
                WHERE m.is_published = 1
                GROUP BY m.id
                ORDER BY m.display_order
            ");
            $moduleStmt->execute([$user_id, $user_id, $user_id]);
            $moduleProgress = $moduleStmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true, 
                'submodules' => $submoduleProgress,
                'modules' => $moduleProgress
            ]);
            break;
            
        case 'POST':
            // Enregistrer/Mettre à jour la progression d'un sous-module
            $data = json_decode(file_get_contents('php://input'), true);
            
            $user_id = (int)($data['user_id'] ?? 0);
            $submodule_id = (int)($data['submodule_id'] ?? 0);
            $completed = (int)($data['completed'] ?? 0);
            
            if (!$user_id || !$submodule_id) {
                echo json_encode(['success' => false, 'message' => 'user_id et submodule_id requis']);
                exit;
            }

            // Récupérer le module_id du sous-module
            $moduleStmt = $pdo->prepare("SELECT module_id FROM submodules WHERE id = ?");
            $moduleStmt->execute([$submodule_id]);
            $submodule = $moduleStmt->fetch();
            
            if (!$submodule) {
                echo json_encode(['success' => false, 'message' => 'Sous-module non trouvé']);
                exit;
            }
            
            $module_id = $submodule['module_id'];
            
            // Vérifier si l'entrée existe
            $stmt = $pdo->prepare("SELECT id FROM progression WHERE user_id = ? AND submodule_id = ?");
            $stmt->execute([$user_id, $submodule_id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Mettre à jour
                $stmt = $pdo->prepare("
                    UPDATE progression 
                    SET is_completed = ?, completed_at = CASE WHEN ? = 1 THEN NOW() ELSE completed_at END
                    WHERE user_id = ? AND submodule_id = ?
                ");
                $stmt->execute([$completed, $completed, $user_id, $submodule_id]);
            } else {
                // Créer
                $stmt = $pdo->prepare("
                    INSERT INTO progression (user_id, module_id, submodule_id, is_completed, completed_at) 
                    VALUES (?, ?, ?, ?, CASE WHEN ? = 1 THEN NOW() ELSE NULL END)
                ");
                $stmt->execute([$user_id, $module_id, $submodule_id, $completed, $completed]);
            }

            // Récupérer les XP du sous-module si complété
            if ($completed) {
                $xpStmt = $pdo->prepare("SELECT xp_reward FROM submodules WHERE id = ?");
                $xpStmt->execute([$submodule_id]);
                $xp = $xpStmt->fetch()['xp_reward'] ?? 0;
                
                // Mettre à jour les XP de l'utilisateur
                if ($xp > 0) {
                    $updateXp = $pdo->prepare("UPDATE users SET xp = xp + ? WHERE id = ?");
                    $updateXp->execute([$xp, $user_id]);
                }
                
                echo json_encode(['success' => true, 'message' => 'Progression enregistrée', 'xp_earned' => $xp]);
            } else {
                echo json_encode(['success' => true, 'message' => 'Progression enregistrée']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
