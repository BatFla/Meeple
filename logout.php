
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Logout - LinkSphere</title>
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
session_unset();
session_destroy();
header('Location: login.php');
exit;
?>

    </main>

</body>
</html>
