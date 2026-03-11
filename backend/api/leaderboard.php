<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require 'db.php';

try {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $limit = min($limit, 100); // Max 100 utilisateurs
    
    // Vérifier quelle colonne existe (role ou is_admin)
    $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    $hasRole = in_array('role', $columns);
    $hasXp = in_array('xp', $columns);
    $hasLevel = in_array('level', $columns);
    
    // Construire le filtre admin
    if ($hasRole) {
        $adminFilter = "role != 'admin'";
    } else {
        $adminFilter = "(is_admin IS NULL OR is_admin = 0)";
    }
    
    // Construire la requête
    $xpSelect = $hasXp ? "xp" : "(SELECT COUNT(*) FROM progression WHERE user_id = users.id AND is_completed = 1) * 100";
    $levelSelect = $hasLevel ? "level" : "1";
    
    $sql = "SELECT 
        id,
        username,
        $xpSelect as xp,
        $levelSelect as level,
        (SELECT COUNT(*) FROM progression WHERE user_id = users.id AND is_completed = 1) as courses_completed,
        (SELECT COUNT(*) FROM user_badges WHERE user_id = users.id) as badges_count
    FROM users 
    WHERE $adminFilter
    ORDER BY xp DESC
    LIMIT $limit";
    
    $stmt = $pdo->query($sql);
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ajouter le rang
    $rank = 1;
    foreach ($leaderboard as &$user) {
        $user['rank'] = $rank++;
        $user['xp'] = (int)$user['xp'];
        $user['level'] = (int)$user['level'];
        $user['courses_completed'] = (int)$user['courses_completed'];
        $user['badges_count'] = (int)$user['badges_count'];
    }
    
    // Stats globales
    $total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE $adminFilter")->fetchColumn();
    $total_xp = $hasXp 
        ? $pdo->query("SELECT COALESCE(SUM(xp), 0) FROM users")->fetchColumn()
        : $pdo->query("SELECT COUNT(*) * 100 FROM progression WHERE is_completed = 1")->fetchColumn();
    
    echo json_encode([
        'success' => true, 
        'leaderboard' => $leaderboard,
        'stats' => [
            'total_users' => (int)$total_users,
            'total_xp' => (int)$total_xp
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>