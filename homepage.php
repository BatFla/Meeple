<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id']; // On s'assure d'utiliser l'ID de l'utilisateur connecté

try {
    $conn = new PDO('mysql:host=linkspw179.mysql.db;dbname=linkspw179;charset=utf8', 'linkspw179', 'Albagumy205');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $nom_table_users = 'linkspw179.users';
    $nom_table_posts = 'linkspw179.posts';
    $nom_table_friends = 'linkspw179.friends';
    $nom_table_comments = 'linkspw179.comments';
    $nom_table_likes = 'linkspw179.likes';

    // Récupérer les publications et leurs informations associées
    $posts_stmt = $conn->prepare(
        "SELECT p.id, p.content, p.created_at, p.user_id, u.username, u.profile_picture,
            (SELECT COUNT(*) FROM $nom_table_likes WHERE post_id = p.id AND action = 'like') AS likes,
            (SELECT COUNT(*) FROM $nom_table_likes WHERE post_id = p.id AND action = 'dislike') AS dislikes
         FROM $nom_table_posts p
         JOIN $nom_table_users u ON u.id = p.user_id
         ORDER BY p.created_at DESC"
    );
    $posts_stmt->execute();
    $posts = $posts_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nombre d'amis
    $friends_stmt = $conn->prepare("SELECT COUNT(*) AS friend_count FROM $nom_table_friends WHERE (user_id = ? OR friend_id = ?) AND status = 'accepted'");
    $friends_stmt->execute([$user_id, $user_id]);
    $friend_count = $friends_stmt->fetch(PDO::FETCH_ASSOC)['friend_count'];

    // Liste des amis
    $friend_list_stmt = $conn->prepare(
        "SELECT u.id, u.username, u.profile_picture 
         FROM $nom_table_friends f 
         JOIN $nom_table_users u 
         ON (u.id = f.friend_id OR u.id = f.user_id) 
         WHERE u.id != ? 
         AND (f.user_id = ? OR f.friend_id = ?) 
         AND f.status = 'accepted'"
    );
    $friend_list_stmt->execute([$user_id, $user_id, $user_id]);
    $friend_list = $friend_list_stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Accueil - LinkSphere</title>
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

<main class="p-4 sm:p-8 lg:w-1/2 w-full mx-auto bg-white shadow-lg rounded-lg mt-10 relative max-w-[95%]">
    <!-- Titre principal -->
    <section class="mb-6">
        <h1 class="text-3xl lg:text-4xl font-bold text-center text-gray-800">Fil d'actualité</h1>
    </section>

    <!-- Formulaire de création de post -->
    <section class="bg-white w-full sm:w-3/4 lg:w-1/2 p-4 sm:p-6 rounded-lg shadow-md mb-6 mx-auto">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-700 mb-4 text-center">Exprimez-vous</h2>
        <form action="create_post.php" method="POST" class="flex flex-col space-y-4">
            <textarea 
                name="content" 
                id="content" 
                rows="3" 
                placeholder="Quoi de neuf ?" 
                class="w-full p-3 sm:p-4 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" 
                required></textarea>
            <button 
                type="submit" 
                class="w-full bg-blue-600 text-white py-2 rounded-lg shadow-md hover:bg-blue-700 transition">
                Publier
            </button>
        </form>
    </section>

    <!-- Liste des publications -->
    <section class="space-y-4">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <div class="bg-white p-4 sm:p-5 rounded-lg shadow-md hover:shadow-lg transition max-w-full" id="post-<?php echo $post['id']; ?>">
                    <div class="flex flex-col sm:flex-row items-start space-y-4 sm:space-y-0 sm:space-x-4">
                        <!-- Photo de profil -->
                        <div class="flex-shrink-0">
                            <a href="profile.php?user_id=<?php echo $post['user_id']; ?>">
                                <img 
                                    src="<?php echo htmlspecialchars($post['profile_picture'] ?? 'default.png'); ?>" 
                                    alt="Photo de profil" 
                                    class="w-12 h-12 rounded-full object-cover"
                                >
                            </a>
                        </div>

                        <!-- Contenu principal -->
                        <div class="flex-1">
                            <div class="flex justify-between items-center mb-2">
                                <!-- Nom d'utilisateur et date -->
                                <div>
                                    <p class="font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($post['username']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars(date('d M Y, H:i', strtotime($post['created_at']))); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Contenu du post -->
                            <p class="text-gray-700 mb-4">
                                <?php echo htmlspecialchars($post['content']); ?>
                            </p>

                            <!-- Actions -->
                            <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                                <button 
                                    class="flex items-center space-x-1 hover:text-green-600 like-btn" 
                                    data-post-id="<?php echo $post['id']; ?>" 
                                    data-action="like">
                                    <span>&#128077;</span>
                                    <span id="likes-<?php echo $post['id']; ?>"><?php echo $post['likes']; ?></span>
                                </button>

                                <button 
                                    class="flex items-center space-x-1 hover:text-red-600 dislike-btn" 
                                    data-post-id="<?php echo $post['id']; ?>" 
                                    data-action="dislike">
                                    <span>&#128078;</span>
                                    <span id="dislikes-<?php echo $post['id']; ?>"><?php echo $post['dislikes']; ?></span>
                                </button>

                                <button 
                                    class="flex items-center space-x-1 hover:text-yellow-500 report-btn" 
                                    data-post-id="<?php echo $post['id']; ?>">
                                    <span>&#9888;</span>
                                    <span>Signaler</span>
                                </button>

                                <?php if ($post['user_id'] === $_SESSION['user_id']): ?>
                                    <button 
                                        class="flex items-center space-x-1 hover:text-gray-800 delete-btn" 
                                        data-post-id="<?php echo $post['id']; ?>">
                                        <span>&#10060;</span>
                                        <span>Supprimer</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-500 text-center">Aucune publication pour l'instant.</p>
        <?php endif; ?>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const likeButtons = document.querySelectorAll('.like-btn');
        const dislikeButtons = document.querySelectorAll('.dislike-btn');
        const deleteButtons = document.querySelectorAll('.delete-btn');

        // Gestion des likes
        likeButtons.forEach(button => {
            button.addEventListener('click', async () => {
                const postId = button.getAttribute('data-post-id');
                try {
                    const response = await fetch('like_post.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `post_id=${postId}&action=like`
                    });
                    const result = await response.json();

                    if (result.success) {
                        document.getElementById(`likes-${postId}`).textContent = result.likes;
                        document.getElementById(`dislikes-${postId}`).textContent = result.dislikes;
                    } else {
                        alert('Une erreur est survenue.');
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                }
            });
        });

        // Gestion des dislikes
        dislikeButtons.forEach(button => {
            button.addEventListener('click', async () => {
                const postId = button.getAttribute('data-post-id');
                try {
                    const response = await fetch('like_post.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `post_id=${postId}&action=dislike`
                    });
                    const result = await response.json();

                    if (result.success) {
                        document.getElementById(`likes-${postId}`).textContent = result.likes;
                        document.getElementById(`dislikes-${postId}`).textContent = result.dislikes;
                    } else {
                        alert('Une erreur est survenue.');
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                }
            });
        });

        // Gestion des suppressions
        deleteButtons.forEach(button => {
            button.addEventListener('click', async () => {
                const postId = button.getAttribute('data-post-id');
                const postElement = document.getElementById(`post-${postId}`);
                
                if (confirm('Êtes-vous sûr de vouloir supprimer ce post ?')) {
                    postElement.remove();
                    try {
                        const response = await fetch('delete_post.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `post_id=${postId}`
                        });
                        const result = await response.json();
                        if (!result.success) {
                            alert(result.message || 'Une erreur est survenue lors de la suppression.');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                    }
                }
            });
        });
    });
</script>
</body>