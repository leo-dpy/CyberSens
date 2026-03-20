<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Récupérer tous les groupes
            $stmt = $pdo->query("SELECT g.*, (SELECT COUNT(*) FROM users WHERE group_name = g.name) as user_count FROM `groups` g ORDER BY g.name ASC");
            $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'groups' => $groups]);
            break;

        case 'POST':
            // Créer un nouveau groupe
            $data = json_decode(file_get_contents('php://input'), true);
            $name = trim($data['name'] ?? '');

            if (empty($name)) {
                echo json_encode(['success' => false, 'message' => 'Le nom du groupe est requis']);
                exit;
            }

            if ($name === 'Aucun') {
                echo json_encode(['success' => false, 'message' => '"Aucun" est un nom réservé']);
                exit;
            }

            // Vérifier si le groupe existe déjà
            $stmt = $pdo->prepare("SELECT id FROM `groups` WHERE name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Ce groupe existe déjà']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO `groups` (`name`) VALUES (?)");
            $stmt->execute([$name]);

            echo json_encode(['success' => true, 'message' => 'Groupe créé', 'id' => $pdo->lastInsertId()]);
            break;

        case 'DELETE':
            // Supprimer un groupe
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)($data['id'] ?? 0);

            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID requis']);
                exit;
            }

            // Récupérer le nom du groupe avant suppression
            $stmt = $pdo->prepare("SELECT name FROM `groups` WHERE id = ?");
            $stmt->execute([$id]);
            $group = $stmt->fetch();

            if (!$group) {
                echo json_encode(['success' => false, 'message' => 'Groupe non trouvé']);
                exit;
            }

            // Remettre les utilisateurs de ce groupe à "Aucun"
            $stmt = $pdo->prepare("UPDATE users SET group_name = 'Aucun' WHERE group_name = ?");
            $stmt->execute([$group['name']]);

            // Supprimer le groupe
            $stmt = $pdo->prepare("DELETE FROM `groups` WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Groupe supprimé']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
