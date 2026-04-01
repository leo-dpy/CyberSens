<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require 'db.php';

try {
    $stats = [];
    
    // Nombre d'utilisateurs
    $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    // Nombre de cours (exclure les cours cachés)
    $stats['courses'] = $pdo->query("SELECT COUNT(*) FROM cours WHERE is_hidden = 0")->fetchColumn();
    
    // Nombre de questions (exclure celles des cours cachés)
    $stats['questions'] = $pdo->query("SELECT COUNT(*) FROM questions q JOIN cours c ON q.course_id = c.id WHERE c.is_hidden = 0")->fetchColumn();
    
    // Nombre de certificats
    $stats['certificates'] = $pdo->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
    
    // Taux de réussite (Moyenne des scores des certifications, ou 0 si vide)
    $avgScore = $pdo->query("SELECT AVG(score) FROM certificates")->fetchColumn();
    $stats['successRate'] = $avgScore ? round($avgScore, 1) : 0;
    
    // Nombre de modules terminés
    $stats['completions'] = $pdo->query("SELECT COUNT(*) FROM progression WHERE is_completed = 1")->fetchColumn();
    
    // Derniers cours
    $stmt = $pdo->query("SELECT id, title, difficulty FROM cours ORDER BY id DESC LIMIT 5");
    $stats['recentCourses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Derniers utilisateurs
    $stmt = $pdo->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    $stats['recentUsers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Derniers certificats (Pour le fil d'actualité)
    $stmt = $pdo->query("SELECT c.id, u.username, co.title as course_title, c.issued_at 
                         FROM certificates c 
                         JOIN users u ON c.user_id = u.id 
                         JOIN cours co ON c.course_id = co.id 
                         ORDER BY c.issued_at DESC 
                         LIMIT 5");
    $stats['recentCertificates'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'stats' => $stats]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
