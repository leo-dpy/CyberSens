<?php
// Configuration de la base de données CyberSens. Inclus par les scripts backend.

// Charger le module de sécurité (configure les cookies sécurisés AVANT session_start)
require_once __DIR__ . '/security.php';

// Démarrer la session de manière sécurisée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nettoyage occasionnel du rate limiting
cleanupRateLimitFiles();

// CONFIGURATION BASE DE DONNÉES (Support des variables d'environnement pour Coolify/AWS)
$host = getenv('DB_HOST') ?: $_SERVER['DB_HOST'] ?? '127.0.0.1';
$dbname = getenv('DB_NAME') ?: $_SERVER['DB_NAME'] ?? 'cybersens';
$user = getenv('DB_USER') ?: $_SERVER['DB_USER'] ?? 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ($_SERVER['DB_PASS'] ?? '');

// CONNEXION PDO
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
        $user, 
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5
        ]
    );
} catch (PDOException $e) {
    // Détection du contexte (API JSON vs Page HTML)
    $isApiRequest = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) 
                 || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                 || (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false);

    // Ne JAMAIS exposer les détails d'erreur en production
    $isProduction = getenv('APP_ENV') !== 'development' && getenv('APP_ENV') !== 'dev';

    if ($isApiRequest) {
        header('Content-Type: application/json');
        http_response_code(500);
        $errorResponse = ['success' => false, 'message' => 'Service temporairement indisponible.'];
        if (!$isProduction) {
            $errorResponse['debug'] = $e->getMessage();
            $errorResponse['host'] = $host;
        }
        echo json_encode($errorResponse);
    } else {
        http_response_code(500);
        $debugInfo = $isProduction ? '' : '<p style="color: #666; font-size: 0.8em;">' . htmlspecialchars($e->getMessage()) . '</p>';
        die("
            <div style='font-family: sans-serif; text-align: center; padding: 2rem; color: #333;'>
                <h1><span style='color: #ef4444;'>⚠</span> Erreur de connexion</h1>
                <p>Impossible de se connecter à la base de données.</p>
                <p style='color: #666; font-size: 0.9em;'>Vérifiez la configuration ou réessayez plus tard.</p>
                $debugInfo
            </div>
        ");
    }
    exit;
}
?>