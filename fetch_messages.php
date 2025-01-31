<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

try {
    $conn = new PDO('mysql:host=linkspw179.mysql.db;dbname=linkspw179;charset=utf8', 'linkspw179', 'Albagumy205');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;

    // Récupérer les messages
    $messages_stmt = $conn->prepare(
        "SELECT m.message, m.created_at, m.sender_id, u.username, u.profile_picture 
         FROM messages m 
         JOIN users u ON m.sender_id = u.id 
         WHERE (m.sender_id = :user_id AND m.receiver_id = :receiver_id) 
         OR (m.sender_id = :receiver_id AND m.receiver_id = :user_id) 
         ORDER BY m.created_at ASC"
    );
    $messages_stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':receiver_id' => $receiver_id
    ]);

    $messages = $messages_stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($messages);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
