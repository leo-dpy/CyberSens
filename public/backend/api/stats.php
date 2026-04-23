<?php
header('Content-Type: application/json');

require 'db.php';
setCorsHeaders();
setSecurityHeaders();

try {
    $stats = [];
    
    // Nombre de questions disponibles
    $stats['questions'] = $pdo->query("SELECT COUNT(*) FROM questions q JOIN modules m ON q.module_id = m.id WHERE m.is_published = 1")->fetchColumn();
    
    // Nombre de modules publiés (anciennement cours)
    $stats['courses'] = $pdo->query("SELECT COUNT(*) FROM modules WHERE is_published = 1")->fetchColumn();
    
    // Taux de réussite (basé sur les quiz complétés)
    $avgScore = $pdo->query("SELECT AVG(score) FROM quiz_results")->fetchColumn();
    $stats['successRate'] = $avgScore ? round($avgScore, 1) : 0;
    
    // Nombre de badges délivrés
    $stats['badges'] = $pdo->query("SELECT COUNT(*) FROM user_badges")->fetchColumn();
    
    echo json_encode(['success' => true, 'stats' => $stats]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
