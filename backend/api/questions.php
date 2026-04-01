<?php
// API Questions - Compatible avec cybersens_v4.sql
// Structure: question, option_a/b/c/d, correct_answer (A/B/C/D), explanation, points
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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
            if (isset($_GET['course_id'])) {
                // Questions d'un cours spécifique (vérifier que le cours n'est pas caché)
                $course_id = (int)$_GET['course_id'];
                $stmt = $pdo->prepare("SELECT q.*, c.title as course_title 
                    FROM questions q 
                    JOIN cours c ON q.course_id = c.id 
                    WHERE q.course_id = ? AND c.is_hidden = 0
                    ORDER BY q.id");
                $stmt->execute([$course_id]);
                $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'questions' => $questions]);
            } elseif (isset($_GET['id'])) {
                // Une question spécifique
                $id = (int)$_GET['id'];
                $stmt = $pdo->prepare("SELECT q.*, c.title as course_title 
                    FROM questions q 
                    JOIN cours c ON q.course_id = c.id 
                    WHERE q.id = ?");
                $stmt->execute([$id]);
                $question = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'question' => $question]);
            } else {
                // Toutes les questions groupées par cours
                $stmt = $pdo->query("SELECT q.*, c.title as course_title 
                    FROM questions q 
                    JOIN cours c ON q.course_id = c.id 
                    ORDER BY c.id, q.id");
                $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'questions' => $questions]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $course_id = (int)($data['course_id'] ?? 0);
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
            
            if (!$course_id || empty($question) || empty($option_a) || empty($option_b)) {
                echo json_encode(['success' => false, 'message' => 'course_id, question, option_a et option_b sont requis']);
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
            
            $stmt = $pdo->prepare("INSERT INTO questions (course_id, question, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty, xp_reward, points) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$course_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_answer, $explanation, $difficulty, $xp_reward, $points]);
            
            $id = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Question créée', 'id' => $id]);
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id = (int)($data['id'] ?? 0);
            $course_id = (int)($data['course_id'] ?? 0);
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
            
            $stmt = $pdo->prepare("UPDATE questions SET course_id = ?, question = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_answer = ?, explanation = ?, difficulty = ?, xp_reward = ?, points = ? WHERE id = ?");
            $stmt->execute([$course_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_answer, $explanation, $difficulty, $xp_reward, $points, $id]);
            
            echo json_encode(['success' => true, 'message' => 'Question mise à jour']);
            break;
            
        case 'DELETE':
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
