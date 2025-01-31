// Gestion des likes et dislikes
document.querySelectorAll('.like-btn, .dislike-btn').forEach((btn) => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        const postId = btn.dataset.postId;
        const action = btn.dataset.action;

        fetch('like_handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `post_id=${postId}&action=${action}`,
        })
            .then((response) => response.json())
            .then((data) => {
                document.querySelector(`.like-btn[data-post-id="${postId}"]`).innerText = `Like (${data.likes})`;
                document.querySelector(`.dislike-btn[data-post-id="${postId}"]`).innerText = `Dislike (${data.dislikes})`;
            });
    });
});
