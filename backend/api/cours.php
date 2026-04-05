<?php
/**
 * ⚠️ DEPRECATED - Cette API est obsolète
 * Utilisez modules.php et submodules.php à la place
 * 
 * Cette API est conservée temporairement pour éviter les erreurs
 * mais retourne des données vides ou des erreurs appropriées.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Retourner un message indiquant que l'API est obsolète
echo json_encode([
    'success' => false, 
    'message' => 'Cette API (cours.php) est obsolète. Utilisez modules.php et submodules.php.',
    'courses' => [] // Pour la compatibilité
]);
?>
