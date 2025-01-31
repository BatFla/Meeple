
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Register - LinkSphere</title>
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];

    try {
        $conn = new PDO('mysql:host=linkspw179.mysql.db;dbname=linkspw179;charset=utf8', 'linkspw179', 'Albagumy205');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $nom_table = 'linkspw179.users';

        $stmt = $conn->prepare("INSERT INTO $nom_table (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $email]);

        header('Location: login.php');
        exit;
    } catch (PDOException $e) {
        echo "<p class='text-red-600 text-center'>Erreur: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Créer un compte</title>
</head>
<body class="bg-gray-100 text-gray-900">
<form method="post" class="max-w-lg mx-auto bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg shadow-lg p-8 mt-10">
    <h2 class="text-2xl font-bold mb-6 text-center">Créer un compte</h2>
    <label for="username" class="block mb-2 text-lg font-medium">Nom d'utilisateur:</label>
    <input type="text" id="username" name="username" class="w-full p-3 border border-gray-300 rounded-md mb-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500" required>

    <label for="email" class="block mb-2 text-lg font-medium">Email:</label>
    <input type="email" id="email" name="email" class="w-full p-3 border border-gray-300 rounded-md mb-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500" required>

    <label for="password" class="block mb-2 text-lg font-medium">Mot de passe:</label>
    <input type="password" id="password" name="password" class="w-full p-3 border border-gray-300 rounded-md mb-6 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500" required>

    <button type="submit" class="bg-purple-600 text-white font-bold py-2 px-4 rounded-md w-full hover:bg-purple-700">Créer un compte</button>
</form>

<div class="max-w-lg mx-auto text-center mt-6">
    <p class="text-gray-600">Vous avez déjà un compte ?</p>
    <a href="login.php" class="text-blue-600 font-semibold hover:underline">Se connecter</a>
</div>
</body>
</html>

    </main>

</body>
</html>
