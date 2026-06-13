DROP DATABASE IF EXISTS edunet_bj;
CREATE DATABASE edunet_bj CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edunet_bj;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  matricule VARCHAR(20) UNIQUE NOT NULL,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('eleve', 'enseignant', 'censeur', 'admin') NOT NULL,
  classe VARCHAR(50) NULL,
  matiere VARCHAR(100) NULL,
  avatar VARCHAR(255) NULL,
  statut ENUM('actif', 'suspendu') DEFAULT 'actif',
  premier_connexion TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE matricule_counter (
  annee YEAR NOT NULL PRIMARY KEY,
  dernier_numero INT DEFAULT 0
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

-- Compte admin par défaut
-- Matricule : ADMIN-EDN-2026
-- Mot de passe : EduN3t@BJ#R00t!2026Adm
INSERT INTO users (matricule, nom, prenom, password, role, statut, premier_connexion)
VALUES ('ADMIN-EDN-2026', 'Admin', 'EduNet', '$2y$10$HpBdcKQa/M98WWBAAnCcuOo2Tj.iSnzkMwRI6eZO46NhZ18PM980K', 'admin', 'actif', 0);

-- Initialiser le compteur de matricules pour l'année 2026 à 0 (puisque l'admin utilise un format séparé)
INSERT INTO matricule_counter (annee, dernier_numero)
VALUES (2026, 0);

