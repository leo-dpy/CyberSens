<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // La table notifications existe déjà dans cybersens.sql
    // Pas besoin de la créer ici

    switch ($method) {
        case 'GET':
            $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
            $unread_only = isset($_GET['unread']) && $_GET['unread'] === 'true';
            
            if (!$user_id) {
                echo json_encode(['success' => false, 'message' => 'user_id requis']);
                exit;
            }
            
            $sql = "SELECT * FROM notifications WHERE user_id = ?";
            if ($unread_only) {
                $sql .= " AND is_read = FALSE";
            }
            $sql .= " ORDER BY created_at DESC LIMIT 50";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Compter les non lues
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
            $stmt->execute([$user_id]);
            $unread_count = $stmt->fetchColumn();
            
            echo json_encode([
                'success' => true, 
                'notifications' => $notifications,
                'unread_count' => (int)$unread_count
            ]);
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $action = $data['action'] ?? 'create';
            
            if ($action === 'mark_read') {
                // Marquer une ou toutes les notifications comme lues
                $user_id = (int)($data['user_id'] ?? 0);
                $notification_id = isset($data['notification_id']) ? (int)$data['notification_id'] : null;
                
                if (!$user_id) {
                    echo json_encode(['success' => false, 'message' => 'user_id requis']);
                    exit;
                }
                
                if ($notification_id) {
                    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
                    $stmt->execute([$notification_id, $user_id]);
                } else {
                    // Marquer toutes comme lues
                    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                }
                
                echo json_encode(['success' => true, 'message' => 'Notifications marquées comme lues']);
                exit;
            }
            
            // Créer une notification
            $user_id = (int)($data['user_id'] ?? 0);
            $title = $data['title'] ?? '';
            $message = $data['message'] ?? '';
            $type = $data['type'] ?? 'info';
            $link = $data['link'] ?? null;
            
            if (!$user_id || !$title || !$message) {
                echo json_encode(['success' => false, 'message' => 'user_id, title et message requis']);
                exit;
            }
            
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $title, $message, $type, $link]);
            
            echo json_encode(['success' => true, 'notification_id' => $pdo->lastInsertId()]);
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            $notification_id = isset($data['notification_id']) ? (int)$data['notification_id'] : null;
            $user_id = (int)($data['user_id'] ?? 0);
            $delete_all = isset($data['delete_all']) && $data['delete_all'] === true;
            
            if (!$user_id) {
                echo json_encode(['success' => false, 'message' => 'user_id requis']);
                exit;
            }
            
            if ($delete_all) {
                $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
                $stmt->execute([$user_id]);
                echo json_encode(['success' => true, 'message' => 'Toutes les notifications ont été supprimées']);
            } elseif ($notification_id) {
                $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
                $stmt->execute([$notification_id, $user_id]);
                echo json_encode(['success' => true, 'message' => 'Notification supprimée']);
            } else {
                echo json_encode(['success' => false, 'message' => 'ID notification ou delete_all requis']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}

// Fonction utilitaire pour créer une notification (peut être appelée depuis d'autres scripts)
function createNotification($pdo, $user_id, $title, $message, $type = 'info', $link = null) {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $title, $message, $type, $link]);
}
?>
