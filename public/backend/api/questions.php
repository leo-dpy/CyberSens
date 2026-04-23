<?php
/**
 * API Questions - CyberSens
 * Structure: question, option_a/b/c/d, correct_answer (A/B/C/D), explanation, points
 * Lié aux modules (module_id)
 */
header('Content-Type: application/json');

require 'db.php';
setCorsHeaders();
setSecurityHeaders();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['module_id'])) {
                // Questions d'un module spécifique
                $module_id = (int)$_GET['module_id'];
                $stmt = $pdo->prepare("SELECT q.*, m.title as module_title 
                    FROM questions q 
                    JOIN modules m ON q.module_id = m.id 
                    WHERE q.module_id = ? AND m.is_published = 1
                    ORDER BY q.id");
                $stmt->execute([$module_id]);
                $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'questions' => $questions]);
            } elseif (isset($_GET['id'])) {
                // Une question spécifique
                $id = (int)$_GET['id'];
                $stmt = $pdo->prepare("SELECT q.*, m.title as module_title 
                    FROM questions q 
                    JOIN modules m ON q.module_id = m.id 
                    WHERE q.id = ?");
                $stmt->execute([$id]);
                $question = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'question' => $question]);
            } else {
                // Toutes les questions groupées par module
                $stmt = $pdo->query("SELECT q.*, m.title as module_title 
                    FROM questions q 
                    JOIN modules m ON q.module_id = m.id 
                    ORDER BY m.id, q.id");
                $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'questions' => $questions]);
            }
            break;
            
        case 'POST':
            requireApiRole($pdo, 'creator');
            $data = json_decode(file_get_contents('php://input'), true);
            
            $module_id = (int)($data['module_id'] ?? 0);
            $question = trim($data['question'] ?? '');
            $option_a = trim($data['option_a'] ?? '');
            $option_b = trim($data['option_b'] ?? '');
            $option_c = trim($data['option_c'] ?? '');
            $option_d = trim($data['option_d'] ?? '');
            $correct_answer = strtoupper(trim($data['correct_answer'] ?? 'A'));
            $explanation = trim($data['explanation'] ?? '');
            $difficulty = $data['difficulty'] ?? 'Facile';
            $xp_reward = (int)($data['xp_reward'] ?? 10);
            $points = (int)($data['points'] ?? 10);
            
            if (!$module_id || empty($question) || empty($option_a) || empty($option_b)) {
                echo json_encode(['success' => false, 'message' => 'module_id, question, option_a et option_b sont requis']);
                exit;
            }
            
            // Valider la réponse correcte
            if (!in_array($correct_answer, ['A', 'B', 'C', 'D'])) {
                $correct_answer = 'A';
            }
            
            // Valider la difficulté
            if (!in_array($difficulty, ['Facile', 'Intermédiaire', 'Difficile'])) {
                $difficulty = 'Facile';
            }
            
            $stmt = $pdo->prepare("INSERT INTO questions (module_id, question, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty, xp_reward, points) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$module_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_answer, $explanation, $difficulty, $xp_reward, $points]);
            
            $id = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Question créée', 'id' => $id]);
            break;
            
        case 'PUT':
            requireApiRole($pdo, 'creator');
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id = (int)($data['id'] ?? 0);
            $module_id = (int)($data['module_id'] ?? 0);
            $question = trim($data['question'] ?? '');
            $option_a = trim($data['option_a'] ?? '');
            $option_b = trim($data['option_b'] ?? '');
            $option_c = trim($data['option_c'] ?? '');
            $option_d = trim($data['option_d'] ?? '');
            $correct_answer = strtoupper(trim($data['correct_answer'] ?? 'A'));
            $explanation = trim($data['explanation'] ?? '');
            $difficulty = $data['difficulty'] ?? 'Facile';
            $xp_reward = (int)($data['xp_reward'] ?? 10);
            $points = (int)($data['points'] ?? 10);
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID requis']);
                exit;
            }
            
            // Valider la difficulté
            if (!in_array($difficulty, ['Facile', 'Intermédiaire', 'Difficile'])) {
                $difficulty = 'Facile';
            }
            
            $stmt = $pdo->prepare("UPDATE questions SET module_id = ?, question = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_answer = ?, explanation = ?, difficulty = ?, xp_reward = ?, points = ? WHERE id = ?");
            $stmt->execute([$module_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_answer, $explanation, $difficulty, $xp_reward, $points, $id]);
            
            echo json_encode(['success' => true, 'message' => 'Question mise à jour']);
            break;
            
        case 'DELETE':
            requireApiRole($pdo, 'admin');
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)($data['id'] ?? 0);
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID requis']);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Question supprimée']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
