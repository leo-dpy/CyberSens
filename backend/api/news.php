<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require 'db.php';

try {
    $stmt = $pdo->query("SELECT * FROM news ORDER BY event_date DESC");
    $major_incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $news = [];

    foreach ($major_incidents as $inc) {
        $news[] = [
            'id' => $inc['id'],
            'title' => $inc['title'],
            'link' => $inc['link'],
            'description' => $inc['description'],
            'date' => strtotime($inc['event_date']),
            'source' => $inc['source'],

            'is_attack' => true
        ];
    }

    echo json_encode(['success' => true, 'news' => $news]);

}
catch (PDOException $e) {
    if ($e->getCode() == '42S02') {
        // Table pas encore créée, on renvoie une liste vide pour ne pas casser le front
        echo json_encode(['success' => true, 'news' => []]);
    }
    else {
        // Si la table n'existe pas encore, renvoyer une erreur explicite ou un tableau vide
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erreur de base de données (Avez-vous exécuté migrate_news.php ?) ' . $e->getMessage(), 'news' => []]);
    }
}
?>