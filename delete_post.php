<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
        exit;
    }

    $postId = intval($_POST['post_id'] ?? 0);
    $userId = $_SESSION['user_id'];

    if ($postId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de post invalide.']);
        exit;
    }

    try {
        $conn = new PDO('mysql:host=linkspw179.mysql.db;dbname=linkspw179;charset=utf8', 'linkspw179', 'Albagumy205');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérification que le post appartient à l'utilisateur connecté
        $stmt = $conn->prepare("DELETE FROM linkspw179.posts WHERE id = ? AND user_id = ?");
        $stmt->execute([$postId, $userId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Post introuvable ou non autorisé.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression : ' . $e->getMessage()]);
    }
}
