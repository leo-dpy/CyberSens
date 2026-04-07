<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Configuration Vertex AI
define('GCP_PROJECT_ID', getenv('GCP_PROJECT_ID') ?: 'secure-bonbon-478520-g7');
define('GCP_REGION', getenv('GCP_REGION') ?: 'europe-west1');
define('GEMINI_MODEL', 'gemini-2.0-flash-001');

// Récupérer les credentials du compte de service
function getServiceAccountCredentials() {
    // Option 1: Variable d'environnement avec le JSON encodé en base64
    $saJson = getenv('GOOGLE_SERVICE_ACCOUNT_JSON');
    if ($saJson) {
        return json_decode(base64_decode($saJson), true);
    }
    
    // Option 2: Chemin vers le fichier (pour Coolify secrets)
    $saPath = getenv('GOOGLE_APPLICATION_CREDENTIALS');
    if ($saPath && file_exists($saPath)) {
        return json_decode(file_get_contents($saPath), true);
    }
    
    // Option 3: Fichier local pour dev (ne pas commiter!)
    $localPath = __DIR__ . '/../service-account.json';
    if (file_exists($localPath)) {
        return json_decode(file_get_contents($localPath), true);
    }
    
    return null;
}

// Générer un token JWT pour l'authentification
function generateJWT($credentials) {
    $now = time();
    $expiry = $now + 3600;
    
    $header = [
        'alg' => 'RS256',
        'typ' => 'JWT'
    ];
    
    $payload = [
        'iss' => $credentials['client_email'],
        'sub' => $credentials['client_email'],
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $expiry,
        'scope' => 'https://www.googleapis.com/auth/cloud-platform'
    ];
    
    $headerEncoded = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
    $payloadEncoded = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
    
    $signatureInput = $headerEncoded . '.' . $payloadEncoded;
    
    $privateKey = openssl_pkey_get_private($credentials['private_key']);
    openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    
    $signatureEncoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    
    return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
}

// Échanger le JWT contre un access token (AVEC DEBUG)
function getAccessToken($jwt) {
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // 1. Vérifier si le serveur a réussi à contacter Google
    if ($curlError) {
        throw new Exception("Erreur cURL vers Google : " . $curlError);
    }
    
    $data = json_decode($response, true);
    
    // 2. Vérifier si Google a renvoyé une erreur (ex: invalid_grant)
    if (!isset($data['access_token'])) {
        throw new Exception("Refus de Google : " . $response);
    }
    
    return $data['access_token'];
}

// System prompt pour le contexte cybersécurité
$systemPrompt = "Tu es CyberGuard, un assistant IA spécialisé en cybersécurité. Tu aides les utilisateurs à :
- Comprendre les menaces cyber (phishing, malware, ransomware, etc.)
- Analyser des emails ou images suspects pour détecter le phishing
- Donner des conseils de sécurité informatique
- Expliquer les bonnes pratiques de protection des données

Règles :
- Réponds toujours en français
- Sois pédagogue et accessible
- Si on te montre une image, analyse-la pour détecter des signes de phishing (URLs suspects, fautes, urgence artificielle, logos falsifiés, etc.)
- Ne donne jamais de conseils pour effectuer des attaques
- Reste concentré sur la cybersécurité, refuse poliment les sujets hors-sujet";

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Données invalides');
    }
    
    $userMessage = $input['message'] ?? '';
    $image = $input['image'] ?? null;
    $history = $input['history'] ?? [];
    
    if (empty($userMessage) && empty($image)) {
        throw new Exception('Message ou image requis');
    }
    
    // Récupérer les credentials
    $credentials = getServiceAccountCredentials();
    if (!$credentials) {
        throw new Exception('Configuration du compte de service manquante. Définissez GOOGLE_SERVICE_ACCOUNT_JSON ou GOOGLE_APPLICATION_CREDENTIALS.');
    }
    
    // Générer le token d'accès
    $jwt = generateJWT($credentials);
    $accessToken = getAccessToken($jwt);
    
    if (!$accessToken) {
        throw new Exception('Impossible d\'obtenir le token d\'accès Google');
    }
    
    // Construire les messages pour Gemini
    $contents = [];
    
    // Ajouter l'historique de conversation
    foreach ($history as $msg) {
        $contents[] = [
            'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
            'parts' => [['text' => $msg['content']]]
        ];
    }
    
    // Construire le message utilisateur actuel
    $currentParts = [];
    
    if ($userMessage) {
        $currentParts[] = ['text' => $userMessage];
    }
    
    if ($image) {
        if (preg_match('/^data:([^;]+);base64,(.+)$/', $image, $matches)) {
            $mimeType = $matches[1];
            $base64Data = $matches[2];
            
            $currentParts[] = [
                'inlineData' => [
                    'mimeType' => $mimeType,
                    'data' => $base64Data
                ]
            ];
            
            if (empty($userMessage)) {
                array_unshift($currentParts, ['text' => "Analyse cette image et dis-moi si elle présente des signes de phishing ou d'arnaque."]);
            }
        }
    }
    
    $contents[] = [
        'role' => 'user',
        'parts' => $currentParts
    ];
    
    // Appel à l'API Vertex AI
    $url = sprintf(
        'https://%s-aiplatform.googleapis.com/v1/projects/%s/locations/%s/publishers/google/models/%s:generateContent',
        GCP_REGION,
        GCP_PROJECT_ID,
        GCP_REGION,
        GEMINI_MODEL
    );
    
    $payload = [
        'contents' => $contents,
        'systemInstruction' => [
            'parts' => [['text' => $systemPrompt]]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 2048,
            'topP' => 0.9
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ],
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        throw new Exception('Erreur de connexion: ' . $curlError);
    }
    
    $data = json_decode($response, true);
    
    if ($httpCode !== 200) {
        $errorMsg = $data['error']['message'] ?? 'Erreur API Vertex AI (HTTP ' . $httpCode . ')';
        throw new Exception($errorMsg);
    }
    
    // Extraire la réponse
    $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    
    if (empty($reply)) {
        throw new Exception('Réponse vide de Gemini');
    }
    
    echo json_encode([
        'success' => true,
        'reply' => $reply
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
