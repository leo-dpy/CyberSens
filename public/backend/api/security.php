<?php
/**
 * CyberSens - Module de sécurité centralisé
 * 
 * Ce fichier doit être inclus EN PREMIER dans db.php (avant session_start).
 * Il configure la session sécurisée, le CORS, le CSRF, le rate limiting, et les headers HTTP.
 */

// ============================================================
// 1. CONFIGURATION DES COOKIES DE SESSION SÉCURISÉS
// ============================================================

// Détecter si on est derrière un reverse proxy HTTPS (Coolify/Nginx)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

// Configuration du cookie de session AVANT session_start()
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    
    session_set_cookie_params([
        'lifetime' => 0,           // Expire à la fermeture du navigateur
        'path'     => '/',
        'domain'   => '',          // Domaine courant automatiquement
        'secure'   => $isHttps,    // Cookie uniquement via HTTPS si disponible
        'httponly'  => true,       // Pas accessible via JavaScript
        'samesite' => 'Strict'     // Protection CSRF navigateur
    ]);
}

// ============================================================
// 2. CORS - ORIGINES AUTORISÉES
// ============================================================

/**
 * Configure les en-têtes CORS de manière restrictive.
 * Seules les origines autorisées peuvent accéder aux API.
 */
function setCorsHeaders() {
    // Origines autorisées (production + développement local)
    $allowedOrigins = array_filter([
        'https://cybersens.leodupuy.fr',
        'https://cybersens.fr',
        'https://www.cybersens.fr',
        'http://cybersens.leodupuy.fr',
        'http://cybersens.fr',
        getenv('APP_URL') ?: null,  // Variable d'environnement additionnelle
    ]);

    // En développement local, autoriser localhost
    if (getenv('APP_ENV') === 'development' || getenv('APP_ENV') === 'dev') {
        $allowedOrigins[] = 'http://localhost';
        $allowedOrigins[] = 'http://localhost:8080';
        $allowedOrigins[] = 'http://127.0.0.1';
    }

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array($origin, $allowedOrigins, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
    } else {
        // Même domaine (pas de header Origin) → pas de CORS nécessaire
        // Si Origin présent mais non autorisé → pas de header CORS = bloqué
    }

    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
    header('Access-Control-Max-Age: 86400');

    // Requête preflight OPTIONS → répondre immédiatement
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// ============================================================
// 3. HEADERS DE SÉCURITÉ HTTP
// ============================================================

function setSecurityHeaders() {
    // Empêcher le sniffing de type MIME
    header('X-Content-Type-Options: nosniff');
    // Protection contre le clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    // Ne pas envoyer le Referrer vers d'autres domaines
    header('Referrer-Policy: strict-origin-when-cross-origin');
    // Désactiver les fonctionnalités navigateur non utilisées
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    // Cache : pas de cache pour les réponses API authentifiées
    header('Cache-Control: no-store, no-cache, must-revalidate, private');
}

// ============================================================
// 4. AUTHENTIFICATION API
// ============================================================

/**
 * Vérifie que l'utilisateur a une session PHP valide.
 * Renvoie 401 JSON si non authentifié.
 */
function requireApiAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Session expirée. Veuillez vous reconnecter.'
        ]);
        exit;
    }
    
    return (int) $_SESSION['user_id'];
}

/**
 * Vérifie que l'utilisateur connecté a au moins le rôle requis.
 * Usage : requireApiRole($pdo, 'admin') ou requireApiRole($pdo, 'creator')
 */
function requireApiRole($pdo, $minimumRole) {
    $userId = requireApiAuth();
    
    $roleHierarchy = [
        'user' => 1,
        'creator' => 2,
        'admin' => 3,
        'superadmin' => 4
    ];
    
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
        exit;
    }
    
    $currentLevel = $roleHierarchy[$user['role']] ?? 0;
    $requiredLevel = $roleHierarchy[$minimumRole] ?? 999;
    
    if ($currentLevel < $requiredLevel) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès interdit']);
        exit;
    }
    
    return $userId;
}

// ============================================================
// 5. PROTECTION CSRF
// ============================================================

/**
 * Génère un token CSRF et le stocke en session.
 */
function generateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie le token CSRF d'un formulaire POST.
 * Arrête l'exécution si le token est invalide.
 */
function verifyCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        
        // Détection du contexte
        $isApi = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
        
        if ($isApi) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
        } else {
            die('<div style="font-family:sans-serif;text-align:center;padding:2rem;color:#ef4444;">
                <h1>⚠️ Erreur de sécurité</h1>
                <p>Le formulaire a expiré. Veuillez recharger la page et réessayer.</p>
                <a href="javascript:history.back()" style="color:#3b82f6;">← Retour</a>
            </div>');
        }
        exit;
    }
}

/**
 * Retourne le champ hidden HTML pour un formulaire.
 */
function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

// ============================================================
// 6. RATE LIMITING (basé sur fichier, sans Redis)
// ============================================================

/**
 * Rate limiting simple basé sur des fichiers temporaires.
 * Bloque si trop de tentatives depuis une même IP.
 *
 * @param string $action   Nom de l'action (ex: 'login', 'register')
 * @param int    $maxAttempts  Nombre max de tentatives
 * @param int    $windowSeconds  Fenêtre de temps en secondes
 * @return bool  true si autorisé, false si bloqué
 */
function checkRateLimit($action, $maxAttempts = 5, $windowSeconds = 300) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    // Prendre la première IP si X-Forwarded-For contient plusieurs
    if (strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
    }
    
    $rateLimitDir = sys_get_temp_dir() . '/cybersens_ratelimit';
    if (!is_dir($rateLimitDir)) {
        @mkdir($rateLimitDir, 0700, true);
    }
    
    // Nom de fichier basé sur IP + action (hashé pour éviter les problèmes de caractères)
    $key = md5($ip . '_' . $action);
    $file = $rateLimitDir . '/' . $key . '.json';
    
    $now = time();
    $attempts = [];
    
    // Lire les tentatives existantes
    if (file_exists($file)) {
        $data = @json_decode(@file_get_contents($file), true);
        if (is_array($data)) {
            // Filtrer les tentatives qui sont dans la fenêtre de temps
            $attempts = array_filter($data, function($timestamp) use ($now, $windowSeconds) {
                return ($now - $timestamp) < $windowSeconds;
            });
        }
    }
    
    // Vérifier si la limite est atteinte
    if (count($attempts) >= $maxAttempts) {
        return false; // Bloqué
    }
    
    // Ajouter la tentative actuelle
    $attempts[] = $now;
    @file_put_contents($file, json_encode(array_values($attempts)), LOCK_EX);
    
    return true; // Autorisé
}

/**
 * Appliquer le rate limiting et bloquer si dépassé.
 * Renvoie une réponse 429 si la limite est dépassée.
 */
function enforceRateLimit($action, $maxAttempts = 5, $windowSeconds = 300) {
    if (!checkRateLimit($action, $maxAttempts, $windowSeconds)) {
        http_response_code(429);
        header('Content-Type: application/json');
        header('Retry-After: ' . $windowSeconds);
        echo json_encode([
            'success' => false,
            'message' => 'Trop de tentatives. Veuillez réessayer dans ' . ceil($windowSeconds / 60) . ' minutes.'
        ]);
        exit;
    }
}

// ============================================================
// 7. NETTOYAGE AUTOMATIQUE DU RATE LIMITING
// ============================================================

/**
 * Nettoie les fichiers de rate limiting expirés (à appeler occasionnellement).
 */
function cleanupRateLimitFiles() {
    $rateLimitDir = sys_get_temp_dir() . '/cybersens_ratelimit';
    if (!is_dir($rateLimitDir)) return;
    
    // 1 chance sur 100 de nettoyer (pour ne pas ralentir chaque requête)
    if (mt_rand(1, 100) !== 1) return;
    
    $files = glob($rateLimitDir . '/*.json');
    $now = time();
    
    foreach ($files as $file) {
        // Supprimer les fichiers de plus de 10 minutes
        if ($now - filemtime($file) > 600) {
            @unlink($file);
        }
    }
}
?>
