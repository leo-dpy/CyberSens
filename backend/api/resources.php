<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // La table resources existe déjà dans cybersens.sql
    // Pas besoin de la créer ni d'insérer des données ici

    // GET - Récupérer les ressources
    $category = $_GET['category'] ?? null;
    $difficulty = $_GET['difficulty'] ?? null;
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if ($id) {
        // Récupérer une ressource spécifique
        $stmt = $pdo->prepare("SELECT * FROM resources WHERE id = ?");
        $stmt->execute([$id]);
        $resource = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resource) {
            echo json_encode(['success' => true, 'resource' => $resource]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ressource non trouvée']);
        }
        exit;
    }
    
    // Récupérer la liste des ressources
    $sql = "SELECT id, title, description, category, url, icon, difficulty FROM resources WHERE 1=1";
    $params = [];
    
    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    
    if ($difficulty) {
        $sql .= " AND difficulty = ?";
        $params[] = $difficulty;
    }
    
    $sql .= " ORDER BY category, difficulty, title";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Grouper par catégorie
    $grouped = [];
    foreach ($resources as $r) {
        $cat = $r['category'];
        if (!isset($grouped[$cat])) {
            $grouped[$cat] = [];
        }
        $grouped[$cat][] = $r;
    }
    
    echo json_encode([
        'success' => true, 
        'resources' => $resources,
        'grouped' => $grouped,
        'categories' => ['article', 'video', 'tool', 'documentation', 'external']
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
