<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $postId = intval($_POST['post_id']);
    $action = $_POST['action']; // 'like' ou 'dislike'
    $userId = $_SESSION['user_id'];

    try {
        $conn = new PDO('mysql:host=linkspw179.mysql.db;dbname=linkspw179;charset=utf8', 'linkspw179', 'Albagumy205');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Supprimer l'ancien like/dislike de cet utilisateur pour ce post
        $stmt = $conn->prepare("DELETE FROM linkspw179.likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $userId]);

        if (in_array($action, ['like', 'dislike'])) {
            // Ajouter le nouveau like/dislike
            $stmt = $conn->prepare("INSERT INTO linkspw179.likes (post_id, user_id, action) VALUES (?, ?, ?)");
            $stmt->execute([$postId, $userId, $action]);
        }

        // Compter les likes et dislikes
        $likes = $conn->query("SELECT COUNT(*) FROM linkspw179.likes WHERE post_id = $postId AND action = 'like'")->fetchColumn();
        $dislikes = $conn->query("SELECT COUNT(*) FROM linkspw179.likes WHERE post_id = $postId AND action = 'dislike'")->fetchColumn();

        echo json_encode(['success' => true, 'likes' => $likes, 'dislikes' => $dislikes]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Requête invalide ou utilisateur non authentifié.']);
}
