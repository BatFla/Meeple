
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Add_friends - LinkSphere</title>
</head>
<body class="flex flex-col min-h-screen bg-gray-100 text-gray-900">
    
<header class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-6xl mx-auto flex items-center justify-between p-4">
        <a href="index.php" class="text-2xl font-bold text-blue-600">LinkSphere</a>
        <nav class="flex space-x-4">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="text-gray-700 hover:text-blue-600">Profil</a>
                <a href="search.php" class="text-gray-700 hover:text-blue-600">Recherche</a>
                <a href="logout.php" class="text-red-600 hover:text-red-800">Déconnexion</a>
            <?php else: ?>
                <a href="login.php" class="text-blue-600 hover:text-blue-800">Connexion</a>
                <a href="register.php" class="text-green-600 hover:text-green-800">Créer un compte</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

    <main class="flex-1 max-w-6xl mx-auto p-4">
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

    // Vérifiez si la relation existe déjà
    $check_stmt = $conn->prepare("SELECT * FROM linkspw179.friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
    $check_stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
    $friendship = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($friendship) {
        header('Location: search.php?error=already_friends');
        exit;
    }

    // Ajouter l'ami
    $stmt = $conn->prepare("INSERT INTO linkspw179.friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$user_id, $friend_id]);

    header('Location: search.php?success=friend_requested');
    exit;
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
    exit;
}
?>

    </main>
    
<footer class="bg-gray-800 text-white text-center p-4 mt-auto">
    <p>&copy; 2025 LinkSphere. Tous droits réservés.</p>
</footer>

</body>
</html>
