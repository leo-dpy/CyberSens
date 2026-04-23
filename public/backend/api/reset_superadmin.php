<?php
/**
 * Script temporaire pour réinitialiser le mot de passe superadmin.
 * À SUPPRIMER IMMÉDIATEMENT après utilisation !
 */
header('Content-Type: application/json');
require 'db.php';

$email = 'superadmin@cybersens.local';
$newPassword = 'EuDPGE8DH9KUTKY!';

// Hasher le nouveau mot de passe
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

// Mettre à jour en base
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
$result = $stmt->execute([$hash, $email]);
$affected = $stmt->rowCount();

if ($affected > 0) {
    // Vérification immédiate
    $stmt2 = $pdo->prepare("SELECT password FROM users WHERE email = ?");
    $stmt2->execute([$email]);
    $user = $stmt2->fetch();
    $verify = password_verify($newPassword, $user['password']);
    
    echo json_encode([
        'success' => true,
        'message' => "Mot de passe réinitialisé pour $email",
        'verification' => $verify ? 'OK - Le nouveau mot de passe fonctionne' : 'ERREUR - Vérification échouée'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Aucun utilisateur trouvé avec cet email'
    ]);
}
?>
