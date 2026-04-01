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
            $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
            $cert_code = isset($_GET['code']) ? $_GET['code'] : null;
            
            if ($cert_code) {
                // Vérifier un certificat par son code
                $stmt = $pdo->prepare("SELECT c.*, u.username, co.title as course_title, co.difficulty
                    FROM certificates c 
                    JOIN users u ON c.user_id = u.id 
                    JOIN cours co ON c.course_id = co.id 
                    WHERE c.certificate_code = ?");
                $stmt->execute([$cert_code]);
                $cert = $stmt->fetch();
                
                if ($cert) {
                    echo json_encode(['success' => true, 'valid' => true, 'certificate' => $cert]);
                } else {
                    echo json_encode(['success' => true, 'valid' => false, 'message' => 'Certificat non trouvé']);
                }
                exit;
            }
            
            if (!$user_id) {
                echo json_encode(['success' => false, 'message' => 'user_id requis']);
                exit;
            }
            
            // Récupérer les certificats de l'utilisateur
            $stmt = $pdo->prepare("SELECT c.*, co.title as course_title, co.difficulty 
                FROM certificates c 
                JOIN cours co ON c.course_id = co.id 
                WHERE c.user_id = ? 
                ORDER BY c.issued_at DESC");
            $stmt->execute([$user_id]);
            $certificates = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'certificates' => $certificates]);
            break;
            
        case 'POST':
            // Générer un certificat
            $data = json_decode(file_get_contents('php://input'), true);
            $user_id = (int)($data['user_id'] ?? 0);
            $course_id = (int)($data['course_id'] ?? 0);
            $score = (int)($data['score'] ?? 0);
            
            if (!$user_id || !$course_id) {
                echo json_encode(['success' => false, 'message' => 'user_id et course_id requis']);
                exit;
            }
            
            // Score minimum de 70% requis
            if ($score < 70) {
                echo json_encode(['success' => false, 'message' => 'Score minimum de 70% requis pour obtenir un certificat']);
                exit;
            }
            
            // Vérifier si le certificat existe déjà
            $stmt = $pdo->prepare("SELECT certificate_code, score FROM certificates WHERE user_id = ? AND course_id = ?");
            $stmt->execute([$user_id, $course_id]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Mettre à jour le score si meilleur
                if ($score > $existing['score']) {
                    $updateStmt = $pdo->prepare("UPDATE certificates SET score = ? WHERE user_id = ? AND course_id = ?");
                    $updateStmt->execute([$score, $user_id, $course_id]);
                }
                echo json_encode(['success' => true, 'certificate_code' => $existing['certificate_code'], 'message' => 'Certificat existant']);
                exit;
            }
            
            // Générer un code unique
            $code = 'CS-' . strtoupper(substr(md5($user_id . $course_id . time() . rand()), 0, 8));
            
            $stmt = $pdo->prepare("INSERT INTO certificates (user_id, course_id, certificate_code, score) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $course_id, $code, $score]);
            
            // Créer une notification
            $courseStmt = $pdo->prepare("SELECT title FROM cours WHERE id = ?");
            $courseStmt->execute([$course_id]);
            $course = $courseStmt->fetch();
            
            $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'certificate')");
            $notifStmt->execute([
                $user_id,
                'Certificat obtenu !',
                "Félicitations ! Vous avez obtenu le certificat pour \"{$course['title']}\" avec un score de {$score}%."
            ]);
            
            echo json_encode(['success' => true, 'certificate_code' => $code, 'message' => 'Certificat généré !']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
