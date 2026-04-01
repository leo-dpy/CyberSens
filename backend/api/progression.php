<?php
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
            
            $stmt = $pdo->prepare("SELECT p.*, p.is_completed as completed, c.title as course_title, c.difficulty 
                FROM progression p 
                JOIN cours c ON p.course_id = c.id 
                WHERE p.user_id = ?");
            $stmt->execute([$user_id]);
            $progression = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'progression' => $progression]);
            break;
            
        case 'POST':
            // Enregistrer/Mettre à jour la progression
            $data = json_decode(file_get_contents('php://input'), true);
            
            $user_id = (int)($data['user_id'] ?? 0);
            $course_id = (int)($data['course_id'] ?? 0);
            $completed = (int)($data['completed'] ?? 0);
            $score = isset($data['score']) ? (int)$data['score'] : null;
            
            if (!$user_id || !$course_id) {
                echo json_encode(['success' => false, 'message' => 'user_id et course_id requis']);
                exit;
            }
            
            // Vérifier si l'entrée existe
            $stmt = $pdo->prepare("SELECT id, best_score, attempts FROM progression WHERE user_id = ? AND course_id = ?");
            $stmt->execute([$user_id, $course_id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Mettre à jour
                $best_score = $existing['best_score'] ?? 0;
                $attempts = ($existing['attempts'] ?? 0) + 1;
                
                // Mettre à jour le best_score si le nouveau score est meilleur
                if ($score !== null && $score > $best_score) {
                    $best_score = $score;
                }
                
                $stmt = $pdo->prepare("UPDATE progression SET is_completed = ?, score = COALESCE(?, score), best_score = ?, attempts = ?, completed_at = NOW() WHERE user_id = ? AND course_id = ?");
                $stmt->execute([$completed, $score, $best_score, $attempts, $user_id, $course_id]);
            } else {
                // Créer
                $stmt = $pdo->prepare("INSERT INTO progression (user_id, course_id, is_completed, score, best_score, attempts, completed_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
                $stmt->execute([$user_id, $course_id, $completed, $score ?? 0, $score ?? 0]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Progression enregistrée']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
