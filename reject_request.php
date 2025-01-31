<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['friend_id'])) {
    header('Location: login.php');
    exit;
}

try {
    $conn = new PDO('mysql:host=linkspw179.mysql.db;dbname=linkspw179;charset=utf8', 'linkspw179', 'Albagumy205');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'];
    $friend_id = $_GET['friend_id'];

    // Vérifier si la demande existe et est en attente
    $stmt = $conn->prepare("SELECT * FROM linkspw179.friends WHERE user_id = ? AND friend_id = ? AND status = 'pending'");
    $stmt->execute([$friend_id, $user_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        header('Location: profile.php?error=no_request');
        exit;
    }

    // Refuser la demande
    $delete_stmt = $conn->prepare("DELETE FROM linkspw179.friends WHERE user_id = ? AND friend_id = ? AND status = 'pending'");
    $delete_stmt->execute([$friend_id, $user_id]);

    header('Location: profile.php?success=request_rejected');
    exit;

} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
    exit;
}
?>