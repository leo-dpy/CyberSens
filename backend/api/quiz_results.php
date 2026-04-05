<?php
/**
 * API Quiz Results - CyberSens
 * Sauvegarde et récupération des résultats de quiz par module
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            // Récupérer les résultats d'un utilisateur
            $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
            $module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : null;

            if (!$user_id) {
                echo json_encode(['success' => false, 'message' => 'user_id requis']);
                exit;
            }

            if ($module_id) {
                // Résultats pour un module spécifique
                $stmt = $pdo->prepare("
                    SELECT qr.*, m.title as module_title
                    FROM quiz_results qr
                    JOIN modules m ON qr.module_id = m.id
                    WHERE qr.user_id = ? AND qr.module_id = ?
                    ORDER BY qr.created_at DESC
                ");
                $stmt->execute([$user_id, $module_id]);
            } else {
                // Tous les résultats de l'utilisateur
                $stmt = $pdo->prepare("
                    SELECT qr.*, m.title as module_title
                    FROM quiz_results qr
                    JOIN modules m ON qr.module_id = m.id
                    WHERE qr.user_id = ?
                    ORDER BY qr.created_at DESC
                ");
                $stmt->execute([$user_id]);
            }

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'results' => $results]);
            break;

        case 'POST':
            // Enregistrer un résultat de quiz
            $data = json_decode(file_get_contents('php://input'), true);

            $user_id = (int)($data['user_id'] ?? 0);
            $module_id = (int)($data['module_id'] ?? 0);
            $score = (int)($data['score'] ?? 0);
            $total_questions = (int)($data['total_questions'] ?? 0);
            $correct_answers = (int)($data['correct_answers'] ?? 0);
            $time_taken = (int)($data['time_taken'] ?? 0);
            $xp_earned = (int)($data['xp_earned'] ?? 0);

            if (!$user_id || !$module_id) {
                echo json_encode(['success' => false, 'message' => 'user_id et module_id requis']);
                exit;
            }

            // Insérer le résultat
            $stmt = $pdo->prepare("
                INSERT INTO quiz_results (user_id, module_id, score, total_questions, correct_answers, time_taken, xp_earned)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $module_id, $score, $total_questions, $correct_answers, $time_taken, $xp_earned]);

            // Mettre à jour les XP de l'utilisateur
            if ($xp_earned > 0) {
                $updateXp = $pdo->prepare("UPDATE users SET xp = xp + ? WHERE id = ?");
                $updateXp->execute([$xp_earned, $user_id]);
            }

            // Si score >= 70%, créer une notification de réussite
            if ($score >= 70) {
                $moduleStmt = $pdo->prepare("SELECT title FROM modules WHERE id = ?");
                $moduleStmt->execute([$module_id]);
                $moduleTitle = $moduleStmt->fetch()['title'] ?? 'Module';

                $notifStmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, title, message, type, link)
                    VALUES (?, ?, ?, 'success', '#cours')
                ");
                $notifStmt->execute([
                    $user_id,
                    'Module validé !',
                    "Félicitations ! Vous avez réussi le quiz \"$moduleTitle\" avec un score de $score%."
                ]);
            }

            echo json_encode([
                'success' => true, 
                'message' => 'Résultat enregistré',
                'passed' => $score >= 70,
                'xp_earned' => $xp_earned
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
