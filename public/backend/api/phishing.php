<?php
header('Content-Type: application/json');

require 'db.php';
setCorsHeaders();
setSecurityHeaders();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $action = $_GET['action'] ?? 'list';
            $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

            // Action stats : retourner uniquement les statistiques
            if ($action === 'stats') {
                $stmt = $pdo->prepare("SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct,
                    AVG(time_taken) as avg_time
                    FROM phishing_results WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $stats = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $pdo->prepare("SELECT scenario_id FROM phishing_results WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $completed = $stmt->fetchAll(PDO::FETCH_COLUMN);

                echo json_encode([
                    'success' => true,
                    'stats' => $stats,
                    'completed_scenarios' => $completed
                ]);
                exit;
            }

            // Récupérer tous les scénarios
            $type = $_GET['type'] ?? null;
            $difficulty = $_GET['difficulty'] ?? null;

            $sql = "SELECT id, title, type, sender, subject, difficulty, created_at FROM phishing_scenarios WHERE 1=1";
            $params = [];

            if ($type) {
                $sql .= " AND type = ?";
                $params[] = $type;
            }
            if ($difficulty) {
                $sql .= " AND difficulty = ?";
                $params[] = $difficulty;
            }

            $sql .= " ORDER BY difficulty ASC, id ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $scenarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Si user_id fourni, ajouter le statut de complétion
            if ($user_id) {
                $stmt = $pdo->prepare("SELECT scenario_id, is_correct FROM phishing_results WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $results = [];
                while ($row = $stmt->fetch()) {
                    $results[$row['scenario_id']] = $row['is_correct'];
                }

                foreach ($scenarios as &$s) {
                    $s['completed'] = isset($results[$s['id']]);
                    $s['correct'] = $results[$s['id']] ?? null;
                }
            }

            // Ajouter les stats globales
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM phishing_scenarios");
            $total_scenarios = $stmt->fetchColumn();

            if ($user_id) {
                $stmt = $pdo->prepare("SELECT COUNT(*) as completed, SUM(is_correct) as correct FROM phishing_results WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $user_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            echo json_encode([
                'success' => true,
                'scenarios' => $scenarios,
                'stats' => [
                    'total' => (int)$total_scenarios,
                    'completed' => ($user_id && isset($user_stats)) ? (int)$user_stats['completed'] : 0,
                    'correct' => ($user_id && isset($user_stats)) ? (int)$user_stats['correct'] : 0
                ]
            ]);
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $action = $data['action'] ?? 'answer';

            if ($action === 'get_scenario') {
                // Récupérer un scénario complet
                $scenario_id = (int)($data['scenario_id'] ?? 0);
                $stmt = $pdo->prepare("SELECT * FROM phishing_scenarios WHERE id = ?");
                $stmt->execute([$scenario_id]);
                $scenario = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($scenario) {
                    // Ne pas révéler la réponse
                    unset($scenario['is_phishing']);
                    unset($scenario['indicators']);
                    unset($scenario['explanation']);
                    echo json_encode(['success' => true, 'scenario' => $scenario]);
                }
                else {
                    echo json_encode(['success' => false, 'message' => 'Scénario non trouvé']);
                }
                exit;
            }

            if ($action === 'answer') {
                // Soumettre une réponse
                $user_id = (int)($data['user_id'] ?? 0);
                $scenario_id = (int)($data['scenario_id'] ?? 0);
                $user_answer = (bool)($data['is_phishing'] ?? false);
                $time_taken = (int)($data['time_taken'] ?? 0);

                if (!$scenario_id) {
                    echo json_encode(['success' => false, 'message' => 'Paramètre scenario_id manquant']);
                    exit;
                }

                // Vérifier si l'utilisateur a déjà répondu à ce scénario (seulement si connecté)
                $existingResult = false;
                if ($user_id) {
                    $stmt = $pdo->prepare("SELECT id FROM phishing_results WHERE user_id = ? AND scenario_id = ?");
                    $stmt->execute([$user_id, $scenario_id]);
                    $existingResult = $stmt->fetch();
                }

                // Récupérer la bonne réponse et les XP du scénario
                $stmt = $pdo->prepare("SELECT is_phishing, indicators, explanation, xp_reward FROM phishing_scenarios WHERE id = ?");
                $stmt->execute([$scenario_id]);
                $scenario = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$scenario) {
                    echo json_encode(['success' => false, 'message' => 'Scénario non trouvé']);
                    exit;
                }

                $is_correct = ($user_answer == $scenario['is_phishing']);
                $xp_reward = (int)($scenario['xp_reward'] ?? 15);
                $xp_earned = 0;

                // Donner XP seulement si bonne réponse et première fois (et connecté)
                if ($is_correct && !$existingResult && $user_id) {
                    $xp_earned = $xp_reward;

                    // Mettre à jour les XP de l'utilisateur
                    $stmt = $pdo->prepare("UPDATE users SET xp = xp + ? WHERE id = ?");
                    $stmt->execute([$xp_earned, $user_id]);
                }

                // Enregistrer le résultat (seulement si connecté)
                if ($user_id) {
                    $stmt = $pdo->prepare("INSERT INTO phishing_results (user_id, scenario_id, user_answer, is_correct, time_taken, xp_earned) 
                        VALUES (?, ?, ?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE user_answer = VALUES(user_answer), is_correct = VALUES(is_correct), time_taken = VALUES(time_taken)");
                    $stmt->execute([$user_id, $scenario_id, $user_answer, $is_correct, $time_taken, $xp_earned]);
                }

                echo json_encode([
                    'success' => true,
                    'is_correct' => $is_correct,
                    'correct_answer' => (bool)$scenario['is_phishing'],
                    'indicators' => $scenario['indicators'],
                    'explanation' => $scenario['explanation'],
                    'xp_earned' => $xp_earned
                ]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
    }
}
catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
