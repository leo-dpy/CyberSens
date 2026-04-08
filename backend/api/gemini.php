<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// System prompt
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
    // Récupération de la clé OpenRouter
    $apiKey = getenv('OPENROUTER_API_KEY');
    if (!$apiKey) {
        throw new Exception('Clé API manquante.');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) throw new Exception('Données invalides');
    
    $userMessage = $input['message'] ?? '';
    $image = $input['image'] ?? null;
    $history = $input['history'] ?? [];
    
    if (empty($userMessage) && empty($image)) {
        throw new Exception('Message ou image requis');
    }
    
    // Construction de l'historique (Format standard OpenRouter/OpenAI)
    $messages = [
        ['role' => 'system', 'content' => $systemPrompt]
    ];
    
    foreach ($history as $msg) {
        $messages[] = [
            'role' => $msg['role'] === 'assistant' ? 'assistant' : 'user',
            'content' => $msg['content']
        ];
    }
    
    // Construction du message actuel
    $currentContent = [];
    
    if ($userMessage) {
        $currentContent[] = ['type' => 'text', 'text' => $userMessage];
    }
    
    if ($image) {
        $currentContent[] = [
            'type' => 'image_url',
            'image_url' => [
                'url' => $image 
            ]
        ];
        
        if (empty($userMessage)) {
            array_unshift($currentContent, ['type' => 'text', 'text' => "Analyse cette capture d'écran et dis-moi si elle présente des signes de phishing ou d'arnaque."]);
        }
    }
    
    $messages[] = [
        'role' => 'user',
        'content' => $currentContent
    ];
    
    // Appel au BON serveur : OpenRouter
    $url = 'https://openrouter.ai/api/v1/chat/completions';
    
    $payload = [
        'model' => 'google/gemini-2.0-flash-lite-preview-02-05:free', 
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 2048
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'HTTP-Referer: https://cybersens.fr'
        ],
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) throw new Exception('Erreur cURL : ' . $curlError);
    
    $data = json_decode($response, true);
    
    if ($httpCode !== 200) {
        $errorMsg = $data['error']['message'] ?? 'Erreur API (HTTP ' . $httpCode . ')';
        throw new Exception($errorMsg);
    }
    
    // Extraction de la réponse
    $reply = $data['choices'][0]['message']['content'] ?? '';
    
    if (empty($reply)) throw new Exception('Réponse vide de l\'IA');
    
    echo json_encode(['success' => true, 'reply' => $reply]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>