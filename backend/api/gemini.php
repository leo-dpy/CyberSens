<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
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
    // 1. Récupérer la clé API
    $apiKey = getenv('OPENROUTER_API_KEY');
    if (!$apiKey) {
        throw new Exception('Clé API OpenRouter manquante. Définissez OPENROUTER_API_KEY.');
    }

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
    
    // Appel à l'API Gratuite de Google AI Studio
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;
    
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
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        throw new Exception('Erreur de connexion cURL : ' . $curlError);
    }
    
    $data = json_decode($response, true);
    
    if ($httpCode !== 200) {
        $errorMsg = $data['error']['message'] ?? 'Erreur API Google (HTTP ' . $httpCode . ')';
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