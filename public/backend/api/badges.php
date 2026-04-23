<?php
header('Content-Type: application/json');

require 'db.php';
setCorsHeaders();
setSecurityHeaders();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
            
            // Récupérer tous les badges disponibles depuis la BDD
            $badgesStmt = $pdo->query("SELECT * FROM badges ORDER BY requirement_value ASC");
            $allBadges = $badgesStmt->fetchAll();
            
            if (!$user_id) {
                // Retourner juste la liste des badges
                echo json_encode(['success' => true, 'badges' => $allBadges, 'unlocked' => []]);
                exit;
            }
            
            // Récupérer les badges débloqués par l'utilisateur
            $unlockedStmt = $pdo->prepare("
                SELECT ub.*, b.name, b.description, b.icon, b.color
                FROM user_badges ub
                JOIN badges b ON ub.badge_id = b.id
                WHERE ub.user_id = ?
                ORDER BY ub.earned_at DESC
            ");
            $unlockedStmt->execute([$user_id]);
            $unlocked = $unlockedStmt->fetchAll();
            
            echo json_encode([
                'success' => true, 
                'badges' => $allBadges, 
                'unlocked' => $unlocked
            ]);
            break;
            
        case 'POST':
            // Vérifier et attribuer les badges automatiquement
            $data = json_decode(file_get_contents('php://input'), true);
            $user_id = (int)($data['user_id'] ?? 0);
            
            if (!$user_id) {
                echo json_encode(['success' => false, 'message' => 'user_id requis']);
                exit;
            }
            
            $newBadges = [];
            
            // Récupérer les statistiques de l'utilisateur
            $statsStmt = $pdo->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM progression WHERE user_id = ? AND is_completed = 1) as courses_completed,
                    (SELECT COUNT(*) FROM phishing_results WHERE user_id = ? AND is_correct = 1) as phishing_correct,
                    (SELECT COUNT(*) FROM phishing_results WHERE user_id = ?) as phishing_total,
                    (SELECT MAX(score) FROM progression WHERE user_id = ?) as best_quiz_score,
                    (SELECT COUNT(*) FROM progression WHERE user_id = ? AND score = 100) as perfect_quizzes,
                    (SELECT COUNT(*) FROM progression WHERE user_id = ? AND is_completed = 1) as quizzes_completed,
                    (SELECT level FROM users WHERE id = ?) as user_level
            ");
            $statsStmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
            $stats = $statsStmt->fetch();
            
            // Récupérer les badges déjà obtenus
            $existingStmt = $pdo->prepare("SELECT badge_id FROM user_badges WHERE user_id = ?");
            $existingStmt->execute([$user_id]);
            $existingBadges = $existingStmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Récupérer tous les badges et vérifier les conditions
            $badgesStmt = $pdo->query("SELECT * FROM badges");
            $badges = $badgesStmt->fetchAll();
            
            foreach ($badges as $badge) {
                // Ignorer si déjà obtenu
                if (in_array($badge['id'], $existingBadges)) {
                    continue;
                }
                
                $shouldUnlock = false;
                $reqType = $badge['requirement_type'] ?? $badge['condition_type'] ?? '';
                $reqValue = $badge['requirement_value'] ?? $badge['condition_value'] ?? 0;
                
                switch ($reqType) {
                    case 'courses_completed':
                        $shouldUnlock = ($stats['courses_completed'] ?? 0) >= $reqValue;
                        break;
                    case 'phishing_detected':
                    case 'phishing_score':
                        $shouldUnlock = ($stats['phishing_correct'] ?? 0) >= $reqValue;
                        break;
                    case 'phishing_perfect':
                        // 10 détections de phishing correctes sans erreur (total = correct)
                        $shouldUnlock = ($stats['phishing_correct'] ?? 0) >= $reqValue && 
                                       ($stats['phishing_correct'] ?? 0) == ($stats['phishing_total'] ?? 0);
                        break;
                    case 'quiz_completed':
                        // Nombre de quiz terminés
                        $shouldUnlock = ($stats['quizzes_completed'] ?? 0) >= $reqValue;
                        break;
                    case 'quiz_score':
                        $shouldUnlock = ($stats['best_quiz_score'] ?? 0) >= $reqValue;
                        break;
                    case 'perfect_quiz':
                        $shouldUnlock = ($stats['perfect_quizzes'] ?? 0) >= $reqValue;
                        break;
                    case 'level':
                        $shouldUnlock = ($stats['user_level'] ?? 1) >= $reqValue;
                        break;
                    case 'account_created':
                        // Toujours accordé pour les comptes existants
                        $shouldUnlock = true;
                        break;
                    case 'special':
                        // Les badges spéciaux sont attribués manuellement ou lors d'événements
                        break;
                }
                
                if ($shouldUnlock) {
                    $insertStmt = $pdo->prepare("INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)");
                    $insertStmt->execute([$user_id, $badge['id']]);
                    
                    if ($insertStmt->rowCount() > 0) {
                        $newBadges[] = $badge;
                        
                        // Créer une notification
                        $notifStmt = $pdo->prepare("
                            INSERT INTO notifications (user_id, title, message, type) 
                            VALUES (?, ?, ?, 'badge')
                        ");
                        $notifStmt->execute([
                            $user_id, 
                            'Nouveau badge débloqué !', 
                            "Vous avez obtenu le badge \"{$badge['name']}\" : {$badge['description']}"
                        ]);
                    }
                }
            }
            
            echo json_encode([
                'success' => true, 
                'new_badges' => $newBadges,
                'message' => count($newBadges) > 0 ? count($newBadges) . ' nouveau(x) badge(s) !' : 'Aucun nouveau badge'
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
