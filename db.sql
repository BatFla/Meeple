-- Création du schéma
CREATE SCHEMA IF NOT EXISTS social_network;

-- Création de la table `users` avec les colonnes mises à jour
CREATE TABLE social_network.users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Création de la table `posts`
CREATE TABLE social_network.posts (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES social_network.users(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Création de la table `messages`
CREATE TABLE social_network.messages (
    id SERIAL PRIMARY KEY,
    sender_id INT NOT NULL REFERENCES social_network.users(id) ON DELETE CASCADE,
    receiver_id INT NOT NULL REFERENCES social_network.users(id) ON DELETE CASCADE,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE social_network.friends (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES social_network.users(id) ON DELETE CASCADE,
    friend_id INT NOT NULL REFERENCES social_network.users(id) ON DELETE CASCADE,
    status VARCHAR(50) DEFAULT 'pending', -- pending, accepted, rejected
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
