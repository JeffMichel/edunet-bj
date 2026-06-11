DROP DATABASE IF EXISTS edunet_bj;
CREATE DATABASE edunet_bj CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edunet_bj;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('eleve', 'enseignant', 'censeur', 'admin') NOT NULL,
  classe VARCHAR(50) NULL,
  matiere VARCHAR(100) NULL,
  avatar VARCHAR(255) NULL,
  statut ENUM('en_attente', 'actif', 'suspendu') DEFAULT 'en_attente',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE refresh_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token TEXT NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(200) NOT NULL,
  contenu TEXT NOT NULL,
  auteur_id INT NOT NULL,
  classe_cible VARCHAR(50) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (auteur_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(200) NOT NULL,
  description TEXT NULL,
  fichier_url VARCHAR(255) NULL,
  enseignant_id INT NOT NULL,
  classe VARCHAR(50) NOT NULL,
  matiere VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (enseignant_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  fichier_url VARCHAR(255) NULL,
  enseignant_id INT NOT NULL,
  classe VARCHAR(50) NOT NULL,
  matiere VARCHAR(100) NOT NULL,
  date_limite DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (enseignant_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE assignment_submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  assignment_id INT NOT NULL,
  eleve_id INT NOT NULL,
  fichier_url VARCHAR(255) NOT NULL,
  note DECIMAL(5,2) NULL,
  commentaire TEXT NULL,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
  FOREIGN KEY (eleve_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  expediteur_id INT NOT NULL,
  classe VARCHAR(50) NOT NULL,
  contenu TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (expediteur_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE schedule (
  id INT AUTO_INCREMENT PRIMARY KEY,
  classe VARCHAR(50) NOT NULL,
  matiere VARCHAR(100) NOT NULL,
  enseignant_id INT NOT NULL,
  jour ENUM('lundi','mardi','mercredi','jeudi','vendredi','samedi') NOT NULL,
  heure_debut TIME NOT NULL,
  heure_fin TIME NOT NULL,
  salle VARCHAR(50) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (enseignant_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE grades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  eleve_id INT NOT NULL,
  enseignant_id INT NOT NULL,
  matiere VARCHAR(100) NOT NULL,
  note DECIMAL(5,2) NOT NULL,
  appreciation TEXT NULL,
  trimestre ENUM('1','2','3') NOT NULL,
  annee_scolaire VARCHAR(10) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (eleve_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (enseignant_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  signaleur_id INT NOT NULL,
  cible_id INT NOT NULL,
  motif TEXT NOT NULL,
  statut ENUM('ouvert','traite','ferme') DEFAULT 'ouvert',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (signaleur_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (cible_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Compte admin par défaut (mot de passe: Admin@2026)
INSERT INTO users (nom, prenom, email, password, role, statut)
VALUES ('Admin', 'EduNet', 'admin@edunetbj.bj', '$2y$10$D70PYz5bqd/gFbzb7MMxOO.J.ppmzuxb7PV.eCh7yt/Fcg95vNU5K', 'admin', 'actif');
