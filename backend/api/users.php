<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Récupérer un utilisateur spécifique si ID fourni
    if (isset($_GET['id'])) {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id) {
            try {
                $sql = "SELECT u.id, u.username, u.email, u.role, u.xp, u.level, u.avatar, u.created_at, u.last_login,
                        (SELECT COUNT(*) FROM progression WHERE user_id = u.id AND is_completed = 1) as completed_courses,
                        (SELECT COUNT(*) FROM user_badges WHERE user_id = u.id) as badges_count
                        FROM users u WHERE u.id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $user['xp'] = (int)($user['xp'] ?? 0);
                    $user['level'] = (int)($user['level'] ?? 1);
                    $user['completed_courses'] = (int)($user['completed_courses'] ?? 0);
                    $user['badges_count'] = (int)($user['badges_count'] ?? 0);
                    echo json_encode(['success' => true, 'user' => $user]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
                }
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Erreur base de données']);
            }
            exit;
        }
    }

    // Liste des utilisateurs pour l'admin avec statistiques
    // Utiliser LEFT JOIN et gérer les tables qui peuvent ne pas exister
    try {
        $sql = "SELECT u.id, u.username, u.email, u.role, u.xp, u.level, u.avatar, u.created_at, u.last_login,
                (SELECT COUNT(*) FROM progression WHERE user_id = u.id AND is_completed = 1) as completed_courses,
                (SELECT COUNT(*) FROM user_badges WHERE user_id = u.id) as badges_count
                FROM users u ORDER BY u.id DESC";
        $stmt = $pdo->query($sql);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Solution de repli sans progression si erreur
        $sql = "SELECT u.id, u.username, u.email, u.role, u.xp, u.level, u.avatar, u.created_at, u.last_login
                FROM users u ORDER BY u.id DESC";
        $stmt = $pdo->query($sql);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as &$user) {
            $user['completed_courses'] = 0;
            $user['badges_count'] = 0;
        }
    }
    
    // Assurer les types corrects
    foreach ($users as &$user) {
        $user['xp'] = (int)($user['xp'] ?? 0);
        $user['level'] = (int)($user['level'] ?? 1);
        $user['completed_courses'] = (int)($user['completed_courses'] ?? 0);
        $user['badges_count'] = (int)($user['badges_count'] ?? 0);
    }
    
    echo json_encode($users);

} elseif ($method === 'POST') {
    // Actions spéciales (ajouter XP, etc.)
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    
    if ($action === 'add_xp') {
        $user_id = (int)($data['user_id'] ?? 0);
        $xp_amount = (int)($data['xp'] ?? 0);
        
        if (!$user_id || $xp_amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
            exit;
        }
        
        // Récupérer l'XP actuel
        $stmt = $pdo->prepare("SELECT xp, level FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
            exit;
        }
        
        $new_xp = (int)$user['xp'] + $xp_amount;
        
        // Calculer le nouveau niveau
        $new_level = 1;
        if ($new_xp >= 2500) $new_level = 7;
        elseif ($new_xp >= 1500) $new_level = 6;
        elseif ($new_xp >= 1000) $new_level = 5;
        elseif ($new_xp >= 600) $new_level = 4;
        elseif ($new_xp >= 300) $new_level = 3;
        elseif ($new_xp >= 100) $new_level = 2;
        
        $leveled_up = $new_level > (int)$user['level'];
        
        // Mettre à jour l'utilisateur
        $stmt = $pdo->prepare("UPDATE users SET xp = ?, level = ? WHERE id = ?");
        $stmt->execute([$new_xp, $new_level, $user_id]);
        
        // Si level up, créer une notification
        if ($leveled_up) {
            $level_names = [1=>'Novice', 2=>'Initié', 3=>'Confirmé', 4=>'Expert', 5=>'Maître', 6=>'Élite', 7=>'Légende'];
            $level_name = $level_names[$new_level] ?? "Niveau $new_level";
            
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'success')");
            $stmt->execute([$user_id, 'Niveau supérieur!', "Félicitations! Vous avez atteint le niveau $level_name!"]);
        }
        
        echo json_encode([
            'success' => true,
            'new_xp' => $new_xp,
            'new_level' => $new_level,
            'leveled_up' => $leveled_up
        ]);
        exit;
    }
    
    if ($action === 'update_avatar') {
        $user_id = (int)($data['user_id'] ?? 0);
        $avatar = $data['avatar'] ?? '';
        
        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'ID utilisateur requis']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$avatar, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Avatar mis à jour']);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Action non reconnue']);

} elseif ($method === 'DELETE') {
    // Supprimer un utilisateur
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);
    
    if ($id) {
        // Supprimer les dépendances d'abord
        $pdo->prepare("DELETE FROM progression WHERE user_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM user_badges WHERE user_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM certificates WHERE user_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM phishing_results WHERE user_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM notifications WHERE user_id = ?")->execute([$id]);
        
        // Supprimer l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ID manquant']);
    }

} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);
    
    // Basculer le rôle admin/utilisateur
    if (isset($data['toggle_admin']) && $data['toggle_admin']) {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $new_role = ($user['role'] === 'admin') ? 'user' : 'admin';
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$new_role, $id]);
            echo json_encode(['success' => true, 'new_role' => $new_role]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
        }
        exit;
    }
    // Changement de mot de passe (depuis Paramètres)
    if (isset($data['action']) && $data['action'] === 'update_password') {
        $password = $data['password'] ?? '';
        
        if (!$id || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Données manquantes']);
            exit;
        }
        
        if (strlen($password) < 17) {
            echo json_encode(['success' => false, 'message' => 'Le mot de passe doit faire 17 caractères minimum']);
            exit;
        }
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$passwordHash, $id]);
        
        echo json_encode(['success' => true]);
        exit;
    }

    // Mettre à jour les infos utilisateur (depuis Paramètres)
    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    
    if ($id && ($username || $email)) {
        // Vérifier d'abord s'ils n'existent pas déjà chez un AUTRE utilisateur
        if ($email) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Cet email est déjà pris']);
                exit;
            }
        }
        
        if ($username) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $id]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Ce pseudo est déjà pris']);
                exit;
            }
        }

        $updates = [];
        $params = [];
        
        if ($username) {
            $updates[] = "username = ?";
            $params[] = $username;
        }
        if ($email) {
            $updates[] = "email = ?";
            $params[] = $email;
        }
        
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    }
}
?>