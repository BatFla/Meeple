<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<p class='text-red-600 text-center'>Erreur : vous n'\xeates pas connect\xe9.</p>";
    exit;
}

try {
    $conn = new PDO('mysql:host=linkspw179.mysql.db;dbname=linkspw179;charset=utf8', 'linkspw179', 'Albagumy205');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $nom_table = 'linkspw179.users';
    $friends_table = 'linkspw179.friends';

    // Recherche d'utilisateurs
    $search_results = [];
    if (isset($_GET['search'])) {
        $search = '%' . strtolower($_GET['search']) . '%';
        $search_stmt = $conn->prepare("SELECT id, username, profile_picture FROM $nom_table WHERE LOWER(username) LIKE ? AND id != ?");
        $search_stmt->execute([$search, $_SESSION['user_id']]);
        $search_results = $search_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Suggestions d'amis (utilisateurs qui ne sont pas encore amis)
    $suggested_friends_stmt = $conn->prepare(
        "SELECT u.id, u.username, u.profile_picture 
         FROM $nom_table u 
         WHERE u.id != ? 
         AND u.id NOT IN (
             SELECT CASE WHEN f.user_id = ? THEN f.friend_id ELSE f.user_id END 
             FROM $friends_table f 
             WHERE f.status = 'accepted' AND (f.user_id = ? OR f.friend_id = ?)
         )
         LIMIT 5"
    );
    $suggested_friends_stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    $suggested_friends = $suggested_friends_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<p class='text-red-600 text-center'>Erreur: " . $e->getMessage() . "</p>";
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Search - LinkSphere</title>
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



<main class="flex-1 max-w-6xl mx-auto p-4">
    <section class="mb-6 text-center">
        <h2 class="text-2xl font-bold mb-4">Recherche d'utilisateurs</h2>
        <form method="GET" action="" class="flex justify-center mb-4">
            <input type="text" name="search" placeholder="Rechercher un utilisateur" class="w-2/3 p-3 border border-gray-300 rounded-l-md text-gray-900" required>
            <button type="submit" class="bg-blue-600 text-white px-4 py-3 rounded-r-md hover:bg-blue-700">Rechercher</button>
        </form>
        <?php if (!empty($search_results)): ?>
            <ul class="space-y-4">
                <?php foreach ($search_results as $result): ?>
                    <li class="flex items-center justify-between p-4 border border-gray-300 rounded-md">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gray-200 rounded-full overflow-hidden">
                                <?php if ($result['profile_picture']): ?>
                                    <img src="<?php echo htmlspecialchars($result['profile_picture']); ?>" alt="Photo de profil" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <p class="text-gray-400">Aucune photo</p>
                                <?php endif; ?>
                            </div>
                            <p class="text-gray-900 font-semibold">@<?php echo htmlspecialchars($result['username']); ?></p>
                        </div>
                        <a href="add_friends.php?friend_id=<?php echo $result['id']; ?>" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Ajouter en ami</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php elseif (isset($_GET['search'])): ?>
            <p class="text-gray-500">Aucun utilisateur trouv\xe9.</p>
        <?php endif; ?>
    </section>

    <section class="mt-10">
        <h2 class="text-2xl font-bold mb-4">Suggestions d'amis</h2>
        <?php if (!empty($suggested_friends)): ?>
            <ul class="space-y-4">
                <?php foreach ($suggested_friends as $suggestion): ?>
                    <li class="flex items-center justify-between p-4 border border-gray-300 rounded-md">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gray-200 rounded-full overflow-hidden">
                                <?php if ($suggestion['profile_picture']): ?>
                                    <img src="<?php echo htmlspecialchars($suggestion['profile_picture']); ?>" alt="Photo de profil" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <p class="text-gray-400">Aucune photo</p>
                                <?php endif; ?>
                            </div>
                            <p class="text-gray-900 font-semibold">@<?php echo htmlspecialchars($suggestion['username']); ?></p>
                        </div>
                        <a href="add_friends.php?friend_id=<?php echo $suggestion['id']; ?>" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Ajouter en ami</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500">Aucune suggestion disponible.</p>
        <?php endif; ?>
    </section>
</main>

</body>
</html>
