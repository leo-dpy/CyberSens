<?php
header('Content-Type: application/json');

require 'db.php';
setCorsHeaders();
setSecurityHeaders();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
            $cert_code = isset($_GET['code']) ? $_GET['code'] : null;
            
            if ($cert_code) {
                // Vérifier un certificat par son code
                $stmt = $pdo->prepare("SELECT c.*, u.username, m.title as course_title, m.difficulty
                    FROM certificates c 
                    JOIN users u ON c.user_id = u.id 
                    JOIN modules m ON c.module_id = m.id 
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
            $stmt = $pdo->prepare("SELECT c.*, m.title as course_title, m.difficulty 
                FROM certificates c 
                JOIN modules m ON c.module_id = m.id 
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
            $module_id = (int)($data['module_id'] ?? $data['course_id'] ?? 0);
            $score = (int)($data['score'] ?? 0);
            
            if (!$user_id || !$module_id) {
                echo json_encode(['success' => false, 'message' => 'user_id et module_id requis']);
                exit;
            }
            
            // Score minimum de 70% requis
            if ($score < 70) {
                echo json_encode(['success' => false, 'message' => 'Score minimum de 70% requis pour obtenir un certificat']);
                exit;
            }
            
            // Vérifier si le certificat existe déjà
            $stmt = $pdo->prepare("SELECT certificate_code, score FROM certificates WHERE user_id = ? AND module_id = ?");
            $stmt->execute([$user_id, $module_id]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Mettre à jour le score si meilleur
                if ($score > $existing['score']) {
                    $updateStmt = $pdo->prepare("UPDATE certificates SET score = ? WHERE user_id = ? AND module_id = ?");
                    $updateStmt->execute([$score, $user_id, $module_id]);
                }
                echo json_encode(['success' => true, 'certificate_code' => $existing['certificate_code'], 'message' => 'Certificat existant']);
                exit;
            }
            
            // Générer un code unique
            $code = 'CS-' . strtoupper(substr(md5($user_id . $module_id . time() . rand()), 0, 8));
            
            $stmt = $pdo->prepare("INSERT INTO certificates (user_id, module_id, certificate_code, score) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $module_id, $code, $score]);
            
            // Créer une notification
            $moduleStmt = $pdo->prepare("SELECT title FROM modules WHERE id = ?");
            $moduleStmt->execute([$module_id]);
            $module = $moduleStmt->fetch();
            
            $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'certificate')");
            $notifStmt->execute([
                $user_id,
                'Certificat obtenu !',
                "Félicitations ! Vous avez obtenu le certificat pour \"{$module['title']}\" avec un score de {$score}%."
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
