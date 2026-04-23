<?php
/**
 * ⚠️ DEPRECATED - Cette API est obsolète
 * Utilisez modules.php et submodules.php à la place
 * 
 * Cette API est conservée temporairement pour éviter les erreurs
 * mais retourne des données vides ou des erreurs appropriées.
 */

header('Content-Type: application/json');

require 'db.php';
setCorsHeaders();
setSecurityHeaders();

$method = $_SERVER['REQUEST_METHOD'];

// Retourner un message indiquant que l'API est obsolète
echo json_encode([
    'success' => false, 
    'message' => 'Cette API (cours.php) est obsolète. Utilisez modules.php et submodules.php.',
    'courses' => [] // Pour la compatibilité
]);
?>
