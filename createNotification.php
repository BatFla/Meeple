<?php
function createNotification($conn, $userId, $senderId, $type, $content, $link) {
    $stmt = $conn->prepare(
        "INSERT INTO notifications (user_id, sender_id, type, content, link) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$userId, $senderId, $type, $content, $link]);
}
?>
