<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

try {
    $conn = new PDO('mysql:host=linkspw179.mysql.db;dbname=linkspw179;charset=utf8', 'linkspw179', 'Albagumy205');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $notifications_table = 'notifications';
    $user_id = $_SESSION['user_id'];

    // Récupérer les notifications non lues
    $stmt = $conn->prepare(
        "SELECT n.id, n.content, n.link, n.created_at, u.username, u.profile_picture
         FROM $notifications_table n
         LEFT JOIN users u ON n.sender_id = u.id
         WHERE n.user_id = ? AND n.is_read = 0
         ORDER BY n.created_at DESC"
    );
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($notifications);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur : ' . $e->getMessage()]);
    exit;
}
?>
