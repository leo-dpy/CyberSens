<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$username || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email invalide']);
    exit;
}

// Vérifier si l'email existe déjà
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
    exit;
}

// Vérifier si le username existe déjà
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Ce nom d\'utilisateur est déjà pris']);
    exit;
}

// Création de l'utilisateur avec les valeurs par défaut (xp=0, level=1)
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, email, password, xp, level, role) VALUES (?, ?, ?, 0, 1, 'user')";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([$username, $email, $passwordHash]);
    $userId = $pdo->lastInsertId();
    
    // Attribuer le badge "Bienvenue" (account_created)
    $stmt = $pdo->prepare("SELECT id FROM badges WHERE requirement_type = 'account_created' LIMIT 1");
    $stmt->execute();
    $badge = $stmt->fetch();
    
    if ($badge) {
        $stmt = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
        $stmt->execute([$userId, $badge['id']]);
    }
    
    // Créer une notification de bienvenue
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'success')");
    $stmt->execute([$userId, 'Bienvenue sur CyberSens!', 'Votre compte a été créé avec succès. Commencez votre apprentissage de la cybersécurité!']);
    
    // Récupérer l'utilisateur créé
    $stmt = $pdo->prepare("SELECT id, username, email, avatar, xp, level, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Récupérer les badges de l'utilisateur
    $stmt = $pdo->prepare("SELECT b.* FROM badges b 
        JOIN user_badges ub ON b.id = ub.badge_id 
        WHERE ub.user_id = ?");
    $stmt->execute([$userId]);
    $user['badges'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true, 
        'user' => $user,
        'message' => 'Compte créé avec succès!'
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription: ' . $e->getMessage()]);
}
?>