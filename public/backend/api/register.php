<?php
header('Content-Type: application/json');

require 'db.php';

// Headers CORS sécurisés + headers de sécurité
setCorsHeaders();
setSecurityHeaders();

// Rate limiting : 3 inscriptions max par 15 minutes par IP
enforceRateLimit('register', 3, 900);

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$username || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis']);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email invalide']);
    exit;
}

// Vérifier si l'email ou le username existe déjà
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Ce nom d\'utilisateur est déjà pris']);
    exit;
}

try {
    // Création de l'utilisateur
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, xp, level, role) VALUES (?, ?, ?, 0, 1, 'user')");
    $stmt->execute([$username, $email, $passwordHash]);
    $userId = $pdo->lastInsertId();
    
    // Attribuer le badge "Bienvenue" si disponible
    $badge = $pdo->query("SELECT id FROM badges WHERE requirement_type = 'account_created' LIMIT 1")->fetch();
    if ($badge) {
        $pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)")->execute([$userId, $badge['id']]);
    }
    
    // Notification de bienvenue
    $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'success')")
        ->execute([$userId, 'Bienvenue sur CyberSens!', 'Votre compte a été créé avec succès.']);
    
    // Récupérer l'utilisateur pour connexion automatique
    $stmt = $pdo->prepare("SELECT id, username, email, xp, level, role, avatar, group_name, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'user' => $user, 'message' => 'Compte créé avec succès !']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription']);
}
?>