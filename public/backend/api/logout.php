<?php
/**
 * Endpoint de déconnexion sécurisé.
 * Détruit complètement la session PHP et supprime le cookie de session.
 * Supporte les appels AJAX (JSON) et les redirections directes.
 */

require_once __DIR__ . '/security.php';

// Démarrer la session pour pouvoir la détruire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Vider toutes les variables de session
$_SESSION = [];
session_unset();

// 2. Supprimer le cookie de session du navigateur
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        [
            'expires'  => time() - 3600,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly'  => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Strict'
        ]
    );
}

// 3. Détruire la session côté serveur
session_destroy();

// 4. Déterminer le type de réponse
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$wantsJson = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
$isApiCall = $isAjax || $wantsJson || isset($_GET['ajax']);

if ($isApiCall) {
    // Réponse JSON pour les appels AJAX depuis le frontend
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Déconnexion réussie']);
} else {
    // Redirection classique (si accès direct dans le navigateur)
    header("Location: ../../index.html");
}
exit;
?>
