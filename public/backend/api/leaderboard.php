<?php
header('Content-Type: application/json');

require 'db.php';
setCorsHeaders();
setSecurityHeaders();

try {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $limit = min($limit, 100); // Max 100 utilisateurs
    $groupName = isset($_GET['group_name']) ? trim($_GET['group_name']) : '';
    
    // Vérifier quelle colonne existe (role ou is_admin)
    $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    $hasRole = in_array('role', $columns);
    $hasXp = in_array('xp', $columns);
    $hasLevel = in_array('level', $columns);
    
    // Construire le filtre admin
    if ($hasRole) {
        $adminFilter = "role != 'admin' AND role != 'superadmin'";
    } else {
        $adminFilter = "(is_admin IS NULL OR is_admin = 0)";
    }

    // Filtre par groupe
    $groupFilter = "";
    $groupParams = [];
    if (!empty($groupName) && $groupName !== 'Aucun') {
        $groupFilter = " AND group_name = ?";
        $groupParams[] = $groupName;
        // Afficher tout le monde (y compris les admins) lorsqu'on regarde un groupe spécifique
        $adminFilter = "1=1";
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
    WHERE $adminFilter $groupFilter
    ORDER BY xp DESC
    LIMIT $limit";
    
    if (!empty($groupParams)) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($groupParams);
    } else {
        $stmt = $pdo->query($sql);
    }
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
    
    // Stats globales (filtrées par groupe si applicable)
    if (!empty($groupParams)) {
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE $adminFilter $groupFilter");
        $countStmt->execute($groupParams);
        $total_users = $countStmt->fetchColumn();
        
        $xpStmt = $pdo->prepare("SELECT COALESCE(SUM(xp), 0) FROM users WHERE $adminFilter $groupFilter");
        $xpStmt->execute($groupParams);
        $total_xp = $xpStmt->fetchColumn();
    } else {
        $total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE $adminFilter")->fetchColumn();
        $total_xp = $hasXp 
            ? $pdo->query("SELECT COALESCE(SUM(xp), 0) FROM users")->fetchColumn()
            : $pdo->query("SELECT COUNT(*) * 100 FROM progression WHERE is_completed = 1")->fetchColumn();
    }
    
    echo json_encode([
        'success' => true, 
        'leaderboard' => $leaderboard,
        'group_name' => $groupName ?: 'Global',
        'stats' => [
            'total_users' => (int)$total_users,
            'total_xp' => (int)$total_xp
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>