<?php
// Configuration de la base de données CyberSens. Inclus par les scripts backend.

// Démarrer la session pour l'admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CONFIGURATION BASE DE DONNÉES
$host = 'localhost';
$dbname = 'cybersens';  // Nom de votre base de données
$user = 'root';
$pass = '';

// CONNEXION PDO
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
        $user, 
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // Gestion d'erreur formatée JSON
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => "Erreur de connexion à la base de données"
    ]);
    exit;
}
?>