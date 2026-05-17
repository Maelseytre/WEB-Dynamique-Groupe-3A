CREATE DATABASE IF NOT EXISTS omnesevent CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE omnesevent;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  prenom VARCHAR(80) NOT NULL,
  nom VARCHAR(80) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('participant','organisateur','admin') NOT NULL DEFAULT 'participant',
  association VARCHAR(120) DEFAULT NULL,
  status ENUM('pending','active','suspended') NOT NULL DEFAULT 'active',
  telephone VARCHAR(40) DEFAULT NULL,
  promo VARCHAR(40) DEFAULT NULL,
  campus VARCHAR(80) DEFAULT NULL,
  bio TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  organizer_id INT NOT NULL,
  titre VARCHAR(160) NOT NULL,
  categorie VARCHAR(40) NOT NULL,
  association VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  date_debut DATE NOT NULL,
  date_fin DATE DEFAULT NULL,
  heure_debut TIME NOT NULL,
  heure_fin TIME DEFAULT NULL,
  lieu VARCHAR(190) NOT NULL,
  adresse VARCHAR(255) DEFAULT NULL,
  capacite INT NOT NULL,
  prix DECIMAL(8,2) NOT NULL DEFAULT 0,
  affiche VARCHAR(255) DEFAULT NULL,
  waitlist_enabled TINYINT(1) NOT NULL DEFAULT 1,
  manual_validation TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('draft','published','cancelled') NOT NULL DEFAULT 'published',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  user_id INT NOT NULL,
  nb_places INT NOT NULL DEFAULT 1,
  commentaire TEXT DEFAULT NULL,
  ticket_code VARCHAR(40) NOT NULL UNIQUE,
  status ENUM('confirmed','waitlist','cancelled') NOT NULL DEFAULT 'confirmed',
  presence_validated TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_event (event_id, user_id),
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
