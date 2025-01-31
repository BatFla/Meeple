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
    $friends_table = 'friends';
    $posts_table = 'posts';
    $messages_table = 'messages';
    $notifications_table = 'notifications';

    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];
    $is_own_profile = ($user_id === $_SESSION['user_id']);

    // Récupérer les informations de l'utilisateur
    $stmt = $conn->prepare("SELECT username, email, profile_picture, created_at FROM $users_table WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<p class='text-red-600 text-center'>Utilisateur introuvable.</p>";
        exit;
    }

    // Récupérer les demandes d'amis en attente
    if ($is_own_profile) {
        $pending_requests_stmt = $conn->prepare(
            "SELECT u.id, u.username, u.profile_picture 
             FROM $friends_table f 
             JOIN $users_table u ON u.id = f.user_id 
             WHERE f.friend_id = ? AND f.status = 'pending'"
        );
        $pending_requests_stmt->execute([$user_id]);
        $pending_requests = $pending_requests_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Nombre d'amis
    $friends_stmt = $conn->prepare("SELECT COUNT(*) AS friend_count FROM $friends_table WHERE (user_id = ? OR friend_id = ?) AND status = 'accepted'");
    $friends_stmt->execute([$user_id, $user_id]);
    $friend_count = $friends_stmt->fetch(PDO::FETCH_ASSOC)['friend_count'];

    // Liste des amis
    $friend_list_stmt = $conn->prepare(
        "SELECT u.id, u.username, u.profile_picture 
         FROM $friends_table f 
         JOIN $users_table u 
         ON (u.id = f.friend_id OR u.id = f.user_id) 
         WHERE u.id != ? 
         AND (f.user_id = ? OR f.friend_id = ?) 
         AND f.status = 'accepted'"
    );
    $friend_list_stmt->execute([$user_id, $user_id, $user_id]);
    $friend_list = $friend_list_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Publications de l'utilisateur
    $user_posts_stmt = $conn->prepare(
        "SELECT content, created_at 
         FROM $posts_table 
         WHERE user_id = ? 
         ORDER BY created_at DESC"
    );
    $user_posts_stmt->execute([$user_id]);
    $user_posts = $user_posts_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Conversations récentes
    if ($is_own_profile) {
        $conversations_stmt = $conn->prepare(
            "SELECT DISTINCT 
                CASE 
                    WHEN sender_id = :user_id THEN receiver_id 
                    ELSE sender_id 
                END AS friend_id, 
                u.username, u.profile_picture, 
                (SELECT message FROM $messages_table WHERE 
                 (sender_id = :user_id AND receiver_id = friend_id) OR 
                 (sender_id = friend_id AND receiver_id = :user_id) 
                 ORDER BY created_at DESC LIMIT 1) AS last_message 
            FROM $messages_table m
            JOIN $users_table u ON u.id = CASE 
                WHEN m.sender_id = :user_id THEN m.receiver_id 
                ELSE m.sender_id 
            END
            WHERE m.sender_id = :user_id OR m.receiver_id = :user_id"
        );
        $conversations_stmt->execute(['user_id' => $user_id]);
        $conversations = $conversations_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Accepter une demande d'ami
    if (isset($_GET['accept_friend_id']) && $is_own_profile) {
        $accept_friend_id = intval($_GET['accept_friend_id']);
        $accept_stmt = $conn->prepare("UPDATE $friends_table SET status = 'accepted' WHERE friend_id = ? AND user_id = ?");
        $accept_stmt->execute([$user_id, $accept_friend_id]);

        // Créer une notification
        $notification_stmt = $conn->prepare("INSERT INTO $notifications_table (user_id, sender_id, type, content, link) VALUES (?, ?, ?, ?, ?)");
        $notification_stmt->execute([$accept_friend_id, $user_id, 'friend_request', 'Votre demande d\'ami a été acceptée.', 'profile.php?user_id=' . $user_id]);

        header("Location: profile.php");
        exit;
    }

    // Refuser une demande d'ami
    if (isset($_GET['reject_friend_id']) && $is_own_profile) {
        $reject_friend_id = intval($_GET['reject_friend_id']);
        $reject_stmt = $conn->prepare("DELETE FROM $friends_table WHERE friend_id = ? AND user_id = ?");
        $reject_stmt->execute([$user_id, $reject_friend_id]);
        header("Location: profile.php");
        exit;
    }

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
    <title>Profil - LinkSphere</title>
    <script>
        function toggleFriendsPopup() {
            const popup = document.getElementById('friends-popup');
            if (popup) {
                popup.classList.toggle('hidden');
            }
        }
    </script>
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

<main class="p-4 sm:p-8 max-w-5xl mx-auto bg-white shadow-lg rounded-lg mt-10 relative">
    <!-- Bouton Paramètres -->
    <?php if ($is_own_profile): ?>
    <div class="absolute top-4 right-4">
        <a href="settings.php" class="text-gray-700 hover:text-gray-900">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-10 sm:w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.591 1.009c1.527-.878 3.307.902 2.43 2.43a1.724 1.724 0 001.008 2.59c1.757.426 1.757 2.924 0 3.35a1.724 1.724 0 00-1.009 2.591c.878 1.527-.902 3.307-2.43 2.43a1.724 1.724 0 00-2.59 1.008c-.426 1.757-2.924 1.757-3.35 0a1.724 1.724 0 00-2.591-1.009c-1.527.878-3.307-.902-2.43-2.43a1.724 1.724 0 00-1.008-2.59c-1.757-.426-1.757-2.924 0-3.35a1.724 1.724 0 001.009-2.591c-.878-1.527.902-3.307 2.43-2.43.996.572 2.233-.15 2.59-1.008zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        </a>
    </div>
    <?php endif; ?>

    <!-- Section des informations utilisateur -->
    <section class="text-center">
        <div class="flex flex-col items-center">
            <div class="w-24 h-24 sm:w-32 sm:h-32 bg-gray-200 rounded-full mb-4 overflow-hidden">
                <?php if ($user['profile_picture']): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Photo de profil" class="w-full h-full object-cover">
                <?php else: ?>
                    <p class="text-gray-400">Aucune photo</p>
                <?php endif; ?>
            </div>
            <h2 class="text-xl sm:text-2xl font-semibold mb-2">@<?php echo htmlspecialchars($user['username']); ?></h2>
            <p class="text-sm sm:text-base text-gray-600">Membre depuis le: <?php echo htmlspecialchars(date('d M Y', strtotime($user['created_at']))); ?></p>
            <p class="text-sm sm:text-base text-gray-600 mt-4">
                Nombre d'amis: 
                <button onclick="toggleFriendsPopup()" class="text-blue-600 hover:underline"><?php echo $friend_count; ?></button>
            </p>
        </div>
    </section>

    <div id="friends-popup" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white w-11/12 sm:w-1/2 p-6 rounded-lg shadow-lg relative">
            <h3 class="text-lg sm:text-2xl font-bold mb-4">Vos amis</h3>
            <button class="absolute top-4 right-4 text-gray-600 hover:text-gray-900" onclick="toggleFriendsPopup()">&#x2715;</button>
            <div class="space-y-4">
                <?php foreach ($friend_list as $friend): ?>
                    <div class="flex items-center justify-between bg-gray-100 p-4 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gray-200 rounded-full overflow-hidden mr-4">
                                <?php if ($friend['profile_picture']): ?>
                                    <img src="<?php echo htmlspecialchars($friend['profile_picture']); ?>" alt="Photo de profil" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <p class="text-gray-400">Aucune photo</p>
                                <?php endif; ?>
                            </div>
                            <a href="profile.php?user_id=<?php echo $friend['id']; ?>" class="text-blue-600 font-semibold hover:underline">
                                @<?php echo htmlspecialchars($friend['username']); ?>
                            </a>
                        </div>
                        <a href="messages.php?receiver_id=<?php echo $friend['id']; ?>" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                            Envoyer un message
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Boutons d'action pour l'utilisateur -->
    <?php if ($is_own_profile): ?>
        <div class="mt-6 flex justify-center space-x-4">
            <a href="chat.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-blue-700">Messagerie</a>
        </div>
    <?php else: ?>
        <div class="mt-6 text-center">
            <a href="profile.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-blue-700">Retourner à mon profil</a>
        </div>
    <?php endif; ?>

    <!-- Section des demandes d'amis en attente -->
    <?php if ($is_own_profile && !empty($pending_requests)): ?>
        <section class="mb-10">
            <h3 class="text-xl sm:text-2xl font-bold mb-4">Demandes d'amis en attente</h3>
            <ul class="space-y-4">
                <?php foreach ($pending_requests as $request): ?>
                    <li class="flex items-center justify-between p-4 border border-gray-300 rounded-md">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gray-200 rounded-full overflow-hidden">
                                <?php if ($request['profile_picture']): ?>
                                    <img src="<?php echo htmlspecialchars($request['profile_picture']); ?>" alt="Photo de profil" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <p class="text-gray-400">Aucune photo</p>
                                <?php endif; ?>
                            </div>
                            <p class="text-gray-900 font-semibold"><?php echo htmlspecialchars($request['username']); ?></p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="profile.php?accept_friend_id=<?php echo $request['id']; ?>" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Accepter</a>
                            <a href="profile.php?reject_friend_id=<?php echo $request['id']; ?>" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">Refuser</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <!-- Section des publications -->
    <section class="mt-10">
        <h3 class="text-xl sm:text-2xl font-bold mb-4">Publications de <?php echo htmlspecialchars($user['username']); ?></h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <?php if (!empty($user_posts)): ?>
                <?php foreach ($user_posts as $post): ?>
                    <div class="bg-gray-100 p-4 rounded-lg shadow">
                        <p class="text-gray-900 mb-2"><?php echo htmlspecialchars($post['content']); ?></p>
                        <p class="text-sm text-gray-500">Publié le: <?php echo htmlspecialchars(date('d M Y', strtotime($post['created_at']))); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500">Aucune publication pour l'instant.</p>
            <?php endif; ?>
        </div>
    </section>
</main>



<script>
const notificationButton = document.querySelector('#notification-btn');
const notificationDropdown = document.querySelector('#notification-dropdown');
const notificationCounter = document.querySelector('#notification-counter');

// Charger les notifications
async function fetchNotifications() {
    try {
        const response = await fetch('fetch_notifications.php');
        const notifications = await response.json();

        if (notifications.error) {
            console.error(notifications.error);
            return;
        }

        // Mettre à jour le compteur
        if (notifications.length > 0) {
            notificationCounter.textContent = notifications.length;
            notificationCounter.classList.remove('hidden');
        } else {
            notificationCounter.classList.add('hidden');
        }

        // Afficher les notifications dans le dropdown
        notificationDropdown.innerHTML = notifications.map(notification => `
            <a href="${notification.link}" data-id="${notification.id}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <div class="flex items-center space-x-2">
                    <img src="${notification.profile_picture || 'default.png'}" class="w-8 h-8 rounded-full" alt="Photo">
                    <div>
                        <p class="font-medium">${notification.username || 'Système'}</p>
                        <p>${notification.content}</p>
                        <span class="text-xs text-gray-500">${new Date(notification.created_at).toLocaleString()}</span>
                    </div>
                </div>
            </a>
        `).join('');

        // Marquer comme lues lors du clic
        document.querySelectorAll('#notification-dropdown a').forEach(link => {
            link.addEventListener('click', async (e) => {
                const notificationId = link.dataset.id;
                await fetch('mark_notification_as_read.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `notification_id=${notificationId}`
                });
            });
        });
    } catch (error) {
        console.error('Erreur lors du chargement des notifications:', error);
    }
}

// Afficher/masquer le dropdown
notificationButton.addEventListener('click', () => {
    notificationDropdown.classList.toggle('hidden');
    fetchNotifications();
});

// Charger les notifications toutes les 5 secondes
setInterval(fetchNotifications, 1000);

</script>