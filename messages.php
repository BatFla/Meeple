<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    $conn = new PDO('mysql:host=linkspw179.mysql.db;dbname=linkspw179;charset=utf8', 'linkspw179', 'Albagumy205');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;

    // Vérifier si l'utilisateur cible existe
    $user_stmt = $conn->prepare("SELECT username, profile_picture FROM linkspw179.users WHERE id = ?");
    $user_stmt->execute([$receiver_id]);
    $receiver = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$receiver) {
        echo "<p class='text-red-600 text-center'>Utilisateur introuvable.</p>";
        exit;
    }

    // Récupérer les messages
    $messages_stmt = $conn->prepare(
        "SELECT m.message, m.created_at, m.sender_id, u.username, u.profile_picture 
         FROM linkspw179.messages m 
         JOIN linkspw179.users u ON m.sender_id = u.id 
         WHERE (m.sender_id = ? AND m.receiver_id = ?) 
         OR (m.sender_id = ? AND m.receiver_id = ?) 
         ORDER BY m.created_at ASC"
    );
    $messages_stmt->execute([$_SESSION['user_id'], $receiver_id, $receiver_id, $_SESSION['user_id']]);
    $messages = $messages_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Envoi d'un message
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
        $message = trim($_POST['message']);
        if (!empty($message)) {
            $send_stmt = $conn->prepare("INSERT INTO linkspw179.messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $send_stmt->execute([$_SESSION['user_id'], $receiver_id, $message]);
            header("Location: messages.php?receiver_id=$receiver_id");
            exit;
        }
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
    <title>Messages - LinkSphere</title>
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



<main class="flex justify-center p-4 sm:p-8 bg-gray-100 min-h-screen">
    <div class="w-full max-w-3xl bg-white shadow-md rounded-lg p-4 sm:p-8">
        <!-- En-tête de la conversation -->
        <div class="flex items-center mb-6">
            <div class="w-16 h-16 bg-gray-200 rounded-full overflow-hidden">
                <?php if ($receiver['profile_picture']): ?>
                    <img src="<?php echo htmlspecialchars($receiver['profile_picture']); ?>" alt="Photo de profil" class="w-full h-full object-cover">
                <?php else: ?>
                    <p class="text-gray-400 text-center">Aucune photo</p>
                <?php endif; ?>
            </div>
            <h2 class="text-lg sm:text-2xl font-bold ml-4"><?php echo htmlspecialchars($receiver['username']); ?></h2>
        </div>

        <div class="h-[600px] flex flex-col bg-white p-4 rounded-lg shadow-md border border-gray-300">
    <!-- Zone des messages -->
    <div id="messages-container" class="flex-1 overflow-y-auto mb-2">
        <!-- Les messages seront insérés ici dynamiquement -->
    </div>

    <!-- Formulaire d'envoi -->
    <form method="POST" action="" class="flex items-center space-x-2 border-t border-gray-300 pt-2">
        <input 
            type="text" 
            name="message" 
            placeholder="Écrire un message..." 
            class="flex-1 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base"
            required
        />
        <button 
            type="submit" 
            class="p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </button>
    </form>
</div>

    </div>
</main>

</body>
</html>

<script>
    const receiverId = <?php echo $receiver_id; ?>;
    const messagesContainer = document.querySelector('#messages-container');

    async function fetchMessages() {
    try {
        const response = await fetch(`fetch_messages.php?receiver_id=${receiverId}`);
        const messages = await response.json();

        // Effacer les messages actuels
        messagesContainer.innerHTML = '';

        // Ajouter les nouveaux messages
        messages.forEach((message) => {
            const isSender = parseInt(message.sender_id) === <?php echo $_SESSION['user_id']; ?>;
            const alignment = isSender ? 'justify-end' : 'justify-start';
            const bubbleStyle = isSender
                ? 'bg-blue-500 text-white self-end rounded-br-none'
                : 'bg-gray-200 text-gray-900 self-start rounded-bl-none';

            const messageDiv = document.createElement('div');
            messageDiv.className = `flex ${alignment} mb-4`;

            messageDiv.innerHTML = `
                <div class="${bubbleStyle} px-4 py-2 rounded-lg max-w-[80%] sm:max-w-[70%] lg:max-w-[60%]">
                    <p class="text-sm md:text-base">${message.message}</p>
                    <span class="text-xs md:text-sm text-gray-400 block mt-1">${new Date(message.created_at).toLocaleString()}</span>
                </div>
            `;

            messagesContainer.appendChild(messageDiv);
        });

        // Faire défiler jusqu'au bas
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    } catch (error) {
        console.error('Erreur lors du chargement des messages :', error);
    }
}

// Charger les messages toutes les 1 seconde
setInterval(fetchMessages, 1000);

// Charger les messages au premier chargement
fetchMessages();




</script>
