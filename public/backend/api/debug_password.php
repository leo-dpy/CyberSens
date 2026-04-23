<?php
/**
 * Script de diagnostic temporaire pour vérifier le hash du mot de passe.
 * À SUPPRIMER après utilisation !
 */
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['error' => 'Email et mot de passe requis']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, email, password, role FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode([
        'found' => false,
        'message' => 'Aucun utilisateur trouvé avec cet email'
    ]);
    exit;
}

$hash = $user['password'];
$isValid = password_verify($password, $hash);

// Vérifier si le hash est bien un hash bcrypt valide
$isBcrypt = (substr($hash, 0, 4) === '$2y$' || substr($hash, 0, 4) === '$2b$');
$hashLength = strlen($hash);

echo json_encode([
    'found' => true,
    'username' => $user['username'],
    'email' => $user['email'],
    'role' => $user['role'],
    'password_verify_result' => $isValid,
    'hash_is_bcrypt' => $isBcrypt,
    'hash_length' => $hashLength,
    'hash_preview' => substr($hash, 0, 10) . '...',  // Montre juste le début pour diagnostic
    'suggestion' => !$isValid ? 'Le mot de passe ne correspond pas au hash en base. Il faut le réinitialiser.' : 'Tout est OK, le mot de passe correspond.'
]);
?>
