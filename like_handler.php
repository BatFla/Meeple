<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Interdit
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = new PDO('mysql:host=linkspw179.mysql.db;dbname=linkspw179;charset=utf8', 'linkspw179', 'Albagumy205');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $post_id = intval($_POST['post_id']);
        $action = $_POST['action']; // 'like' ou 'dislike'

        if (!in_array($action, ['like', 'dislike'])) {
            http_response_code(400); // Mauvaise requête
            echo json_encode(['error' => 'Action invalide']);
            exit;
        }

        // Vérifier si l'utilisateur a déjà liké/disliké ce post
        $check_stmt = $conn->prepare("SELECT id, action FROM linkspw179.likes WHERE user_id = ? AND post_id = ?");
        $check_stmt->execute([$_SESSION['user_id'], $post_id]);
        $existing_like = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_like) {
            // Si l'action est différente, mettez à jour
            if ($existing_like['action'] !== $action) {
                $update_stmt = $conn->prepare("UPDATE linkspw179.likes SET action = ? WHERE id = ?");
                $update_stmt->execute([$action, $existing_like['id']]);
            }
        } else {
            // Ajoutez un nouveau like/dislike
            $insert_stmt = $conn->prepare("INSERT INTO linkspw179.likes (user_id, post_id, action) VALUES (?, ?, ?)");
            $insert_stmt->execute([$_SESSION['user_id'], $post_id, $action]);
        }

        // Comptez les likes et dislikes
        $count_stmt = $conn->prepare(
            "SELECT 
                SUM(action = 'like') AS likes, 
                SUM(action = 'dislike') AS dislikes 
             FROM linkspw179.likes 
             WHERE post_id = ?"
        );
        $count_stmt->execute([$post_id]);
        $counts = $count_stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode($counts);
    } catch (PDOException $e) {
        http_response_code(500); // Erreur serveur
        echo json_encode(['error' => 'Erreur du serveur : ' . $e->getMessage()]);
    }
} else {
    http_response_code(405); // Méthode non autorisée
    echo json_encode(['error' => 'Méthode non autorisée']);
}
?>
