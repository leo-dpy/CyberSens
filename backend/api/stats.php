<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require 'db.php';

try {
    $stats = [];
    
    // Nombre de questions disponibles
    $stats['questions'] = $pdo->query("SELECT COUNT(*) FROM questions q JOIN modules m ON q.module_id = m.id WHERE m.is_published = 1")->fetchColumn();
    
    // Nombre de modules publiés (anciennement cours)
    $stats['courses'] = $pdo->query("SELECT COUNT(*) FROM modules WHERE is_published = 1")->fetchColumn();
    
    // Taux de réussite (Moyenne des scores des certifications, ou 0 si vide)
    $avgScore = $pdo->query("SELECT AVG(score) FROM certificates")->fetchColumn();
    $stats['successRate'] = $avgScore ? round($avgScore, 1) : 0;
    
    // Nombre de certificats délivrés
    $stats['certificates'] = $pdo->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
    
    echo json_encode(['success' => true, 'stats' => $stats]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
