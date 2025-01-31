<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    $conn = new PDO('mysql:host=linkspw179.mysql.db;dbname=linkspw179;charset=utf8', 'linkspw179', 'Albagumy205');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $nom_table = 'linkspw179.users';

    $stmt = $conn->prepare("SELECT username, email, profile_picture, created_at FROM $nom_table WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<p class='text-red-600 text-center'>Utilisateur introuvable.</p>";
        exit;
    }
} catch (PDOException $e) {
    echo "<p class='text-red-600 text-center'>Erreur: " . $e->getMessage() . "</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? $user['username'];
    $email = $_POST['email'] ?? $user['email'];
    $profile_picture = $user['profile_picture'];

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $profile_picture = $upload_dir . uniqid('profile_', true) . '.' . $file_extension;
        if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture)) {
            echo "<p class='text-red-600 text-center'>Erreur lors du téléchargement de la photo.</p>";
        }
    }

    try {
        $stmt = $conn->prepare("UPDATE $nom_table SET username = ?, email = ?, profile_picture = ? WHERE id = ?");
        $stmt->execute([$username, $email, $profile_picture, $_SESSION['user_id']]);

        header('Location: profile.php');
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
    <title>Modifier_profile - LinkSphere</title>
</head>
<body class="flex flex-col min-h-screen bg-gray-100 text-gray-900">
    
<header class="bg-white shadow-md sticky top-0 z-50">
    <!-- Navigation Desktop -->
    <div class="hidden sm:flex max-w-7xl mx-auto items-center justify-between p-4">
        <a href="index.php" class="text-lg sm:text-xl font-bold text-blue-600">LinkSphere</a>
        <nav class="flex space-x-6 text-sm sm:text-base">
            <a href="homepage.php" class="text-gray-700 hover:text-blue-600 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 1.293a1 1 0 00-1.414 0l-8 8A1 1 0 002 10h2v7a1 1 0 001 1h10a1 1 0 001-1v-7h2a1 1 0 00.707-1.707l-8-8zM6 18V9.414l4-4 4 4V18H6z" />
                </svg>
                Accueil
            </a>
            <a href="search.php" class="text-gray-700 hover:text-blue-600 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM8 14a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                </svg>
                Recherche
            </a>
            <a href="profile.php" class="text-gray-700 hover:text-blue-600 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a6 6 0 100 12 6 6 0 000-12zm-7 15a7 7 0 0114 0H3z" clip-rule="evenodd" />
                </svg>
                Profil
            </a>
        </nav>
    </div>

    <!-- Navigation Mobile -->
    <div class="flex sm:hidden fixed inset-x-0 bottom-0 bg-white shadow-lg p-3 justify-around items-center z-50 border-t border-gray-200">
        <a href="homepage.php" class="text-gray-700 hover:text-blue-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.707 1.293a1 1 0 00-1.414 0l-8 8A1 1 0 002 10h2v7a1 1 0 001 1h10a1 1 0 001-1v-7h2a1 1 0 00.707-1.707l-8-8zM6 18V9.414l4-4 4 4V18H6z" />
            </svg>
        </a>
        <a href="search.php" class="text-gray-700 hover:text-blue-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM8 14a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
            </svg>
        </a>
        <a href="profile.php" class="text-gray-700 hover:text-blue-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 2a6 6 0 100 12 6 6 0 000-12zm-7 15a7 7 0 0114 0H3z" clip-rule="evenodd" />
            </svg>
        </a>
    </div>
</header>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Modifier Profil</title>
</head>
<body class="bg-gray-100 text-gray-900">
    <main class="p-6 max-w-4xl mx-auto bg-white shadow-lg rounded-lg mt-10">
        <form method="post" enctype="multipart/form-data" class="space-y-6">
            <div class="text-center">
                <div class="w-32 h-32 bg-gray-200 rounded-full mx-auto mb-4 overflow-hidden">
                    <?php if ($user['profile_picture']): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Photo de profil" class="w-full h-full object-cover">
                    <?php else: ?>
                        <p class="text-gray-400">Aucune photo</p>
                    <?php endif; ?>
                </div>
                <input type="file" name="profile_picture" class="block mx-auto">
            </div>

            <div>
                <label for="username" class="block text-lg font-medium">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full p-3 border border-gray-300 rounded-md text-gray-900">
            </div>

            <div>
                <label for="email" class="block text-lg font-medium">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="w-full p-3 border border-gray-300 rounded-md text-gray-900">
            </div>

            <div class="text-center">
                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-blue-700">Enregistrer</button>
                <a href="profile.php" class="bg-gray-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-gray-700 ml-4">Annuler</a>
            </div>
        </form>
    </main>
</body>
</html>

    </main>

</body>
</html>
