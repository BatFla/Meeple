
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Index - LinkSphere</title>
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
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Accueil</title>
</head>
<body class="bg-gray-100 text-gray-900">
<header class="bg-blue-600 text-white text-center p-6">
    <h1 class="text-3xl font-bold">Bienvenue sur LinkSphere</h1>
    <p class="text-lg">Connectez-vous avec le monde</p>
</header>
<main class="p-6 max-w-4xl mx-auto">
    <div class="bg-gradient-to-r from-blue-500 to-green-500 text-white rounded-lg shadow-lg p-8 mb-10">
        <h2 class="text-2xl font-bold mb-4">Rejoignez la communauté</h2>
        <p class="mb-4">Partagez vos idées, discutez avec vos amis, et découvrez de nouvelles opportunités.</p>
        <div class="flex justify-center space-x-4">
            <a href="register.php" class="bg-white text-blue-600 px-6 py-2 rounded-md font-semibold hover:bg-gray-100">Créer un compte</a>
            <a href="login.php" class="bg-white text-green-600 px-6 py-2 rounded-md font-semibold hover:bg-gray-100">Se connecter</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-xl font-bold mb-2">Publiez vos idées</h3>
            <p class="text-gray-600">Exprimez-vous librement et partagez ce qui compte pour vous.</p>
        </div>
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-xl font-bold mb-2">Connectez-vous</h3>
            <p class="text-gray-600">Rejoignez vos amis et faites de nouvelles rencontres.</p>
        </div>
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-xl font-bold mb-2">Explorez</h3>
            <p class="text-gray-600">Découvrez des contenus inspirants et des personnes intéressantes.</p>
        </div>
    </div>
</main>
</body>
</html>

    </main>

</body>
</html>
