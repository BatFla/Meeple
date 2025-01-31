<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    $conn = new PDO('mysql:host=linkspw179.mysql.db;dbname=linkspw179;charset=utf8', 'linkspw179', 'Albagumy205');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $users_table = 'users';
    $messages_table = 'messages';

    // Récupérer toutes les conversations
    $conversations_stmt = $conn->prepare(
        "SELECT DISTINCT 
            CASE 
                WHEN sender_id = :user_id THEN receiver_id 
                ELSE sender_id 
            END AS friend_id,
            u.username,
            u.profile_picture,
            u.last_active,
            (SELECT message 
             FROM $messages_table 
             WHERE (sender_id = :user_id AND receiver_id = friend_id) 
                OR (sender_id = friend_id AND receiver_id = :user_id)
             ORDER BY created_at DESC 
             LIMIT 1) AS last_message
        FROM $messages_table m
        JOIN $users_table u ON u.id = CASE 
            WHEN m.sender_id = :user_id THEN m.receiver_id 
            ELSE m.sender_id 
        END
        WHERE m.sender_id = :user_id OR m.receiver_id = :user_id
        ORDER BY u.last_active DESC"
    );
    $conversations_stmt->execute(['user_id' => $_SESSION['user_id']]);
    $conversations = $conversations_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<p class='text-red-600 text-center'>Erreur: " . $e->getMessage() . "</p>";
    exit;
}

// Fonction pour déterminer si un utilisateur est en ligne
function isOnline($last_active) {
    $now = new DateTime();
    $last_active_time = new DateTime($last_active);
    $diff = $now->getTimestamp() - $last_active_time->getTimestamp();
    return $diff <= 300; // En ligne si actif dans les 5 dernières minutes
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Messagerie - LinkSphere</title>
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


<main class="flex-1 max-w-5xl mx-auto p-4 md:p-8">
    <h1 class="text-2xl font-bold mb-6">Messagerie</h1>

    <?php if (!empty($conversations)): ?>
        <ul class="space-y-4">
            <?php foreach ($conversations as $conversation): ?>
                <li class="p-4 bg-white shadow-md rounded-lg hover:bg-gray-100 transition cursor-pointer flex items-center space-x-4 w-full"
                    onclick="location.href='messages.php?receiver_id=<?php echo $conversation['friend_id']; ?>'">
                    <div class="w-16 h-16 bg-gray-200 rounded-full overflow-hidden flex-shrink-0">
                        <?php if ($conversation['profile_picture']): ?>
                            <img src="<?php echo htmlspecialchars($conversation['profile_picture']); ?>" alt="Photo de profil" class="w-full h-full object-cover">
                        <?php else: ?>
                            <p class="text-gray-400 text-center">Aucune photo</p>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <p class="text-lg font-semibold text-gray-900 truncate"><?php echo htmlspecialchars($conversation['username']); ?></p>
                        <p class="text-sm text-gray-600 truncate"><?php echo htmlspecialchars($conversation['last_message']); ?></p>
                    </div>
                    <div class="text-sm flex items-center space-x-2">
                        <?php if (isOnline($conversation['last_active'])): ?>
                            <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                            <span class="text-green-600">En ligne</span>
                        <?php else: ?>
                            <span class="text-gray-500"><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($conversation['last_active']))); ?></span>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="text-gray-500">Vous n'avez pas encore de conversations.</p>
    <?php endif; ?>
</main>
</body>
</html>
