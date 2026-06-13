# EduNet BJ — Plateforme Scolaire Numérique Sécurisée 🇧🇯

> **Projet de fin d'études — Développement Web Full-Stack**
> Plateforme de gestion et de communication scolaire destinée aux établissements du secondaire en République du Bénin.

---

## 📋 Table des Matières

1. [Présentation du Projet](#1-présentation-du-projet)
2. [Contexte et Problématique](#2-contexte-et-problématique)
3. [Objectifs](#3-objectifs)
4. [Stack Technique](#4-stack-technique)
5. [Architecture du Système](#5-architecture-du-système)
6. [Structure du Projet](#6-structure-du-projet)
7. [Modèle de Données](#7-modèle-de-données)
8. [API REST — Documentation Complète](#8-api-rest--documentation-complète)
9. [Fonctionnalités par Rôle](#9-fonctionnalités-par-rôle)
10. [Sécurité et Authentification](#10-sécurité-et-authentification)
11. [Interface Utilisateur](#11-interface-utilisateur)
12. [Déploiement et Infrastructure](#12-déploiement-et-infrastructure)
13. [Guide d'Installation Locale](#13-guide-dinstallation-locale)
14. [Tests et Validation](#14-tests-et-validation)
15. [Perspectives et Améliorations Futures](#15-perspectives-et-améliorations-futures)
16. [Conclusion](#16-conclusion)

---

## 1. Présentation du Projet

**EduNet BJ** est une application web complète de gestion scolaire numérique, développée de bout en bout avec des technologies modernes et accessibles. La plateforme est conçue pour répondre aux besoins réels des établissements d'enseignement secondaire (collèges et lycées) en République du Bénin, en proposant un espace centralisé, sécurisé et multitirant pour les quatre acteurs de la communauté scolaire : l'**administrateur**, les **enseignants**, les **élèves (apprenants)** et le **censeur (responsable de la discipline)**.

| Propriété | Détail |
| :--- | :--- |
| **Type** | Application Web Full-Stack (Client-Serveur) |
| **Langue de l'interface** | Français uniquement |
| **Cible** | Lycées et Collèges du Bénin |
| **Rôles utilisateurs** | Administrateur, Enseignant, Élève, Censeur |
| **Dépôt GitHub** | [github.com/JeffMichel/edunet-bj](https://github.com/JeffMichel/edunet-bj) |
| **URL de Production** | [https://edunet-bj.onrender.com](https://edunet-bj.onrender.com) |

---

## 2. Contexte et Problématique

Dans le contexte de la digitalisation croissante de l'éducation en Afrique de l'Ouest, les établissements scolaires béninois font face à plusieurs défis majeurs dans leur gestion quotidienne :

- **Fragmentation de la communication :** Les échanges entre parents, élèves, enseignants et administration se font encore majoritairement de manière informelle (appels téléphoniques, cahiers de correspondance, affiches).
- **Manque de traçabilité :** La gestion des notes, des devoirs rendus, de l'emploi du temps et des incidents disciplinaires manque de centralisation et de transparence.
- **Absence d'outil numérique adapté au contexte local :** Les solutions existantes sont souvent coûteuses, complexes, conçues pour des marchés occidentaux et ne répondent pas aux contraintes locales (connectivité, langue française, gestion administrative spécifique).

**EduNet BJ** répond à ces défis en proposant une solution simple, légère, 100 % en français, hébergeable à faible coût et entièrement maîtrisée techniquement.

---

## 3. Objectifs

### Objectif Général
Développer une plateforme web sécurisée permettant la gestion numérique complète d'un établissement scolaire secondaire, accessible depuis n'importe quel navigateur web moderne.

### Objectifs Spécifiques
1. Implémenter un système d'authentification sécurisé basé sur les numéros matricule (sans email, sans inscription publique).
2. Centraliser la gestion des cours, devoirs, notes et emplois du temps dans une interface unifiée.
3. Fournir des tableaux de bord adaptés à chaque rôle utilisateur avec des droits d'accès strictement compartimentés.
4. Assurer la sécurité des données avec une authentification JWT et un chiffrement Bcrypt des mots de passe.
5. Déployer l'application sur une infrastructure cloud (Render.com + Aiven MySQL) accessible 24h/24.

---

## 4. Stack Technique

La décision d'utiliser une stack "pure" (sans frameworks lourds) est délibérée : elle garantit la maîtrise complète du code, facilite la compréhension, réduit les dépendances et simplifie la maintenance.

### Back-End

| Technologie | Version | Rôle |
| :--- | :--- | :--- |
| **PHP** | 8.1+ | Langage serveur principal — API REST complète |
| **MySQL** | 8.0 | Système de Gestion de Base de Données Relationnelle |
| **PDO** (PHP Data Objects) | Natif PHP | Couche d'abstraction pour les requêtes SQL sécurisées |
| **Apache** | 2.4 | Serveur HTTP (via `.htaccess` pour le routage) |
| **Docker** | 20+ | Conteneurisation pour le déploiement cloud |

> **Choix architectural :** Le back-end est structuré comme une **API REST** pure, séparé du front-end. Cela permet une architecture découplée, maintenable, et potentiellement réutilisable par d'autres clients (application mobile future, etc.).

### Front-End

| Technologie | Version | Rôle |
| :--- | :--- | :--- |
| **HTML5** | Sémantique | Structure des pages web |
| **Tailwind CSS** | Compilé local | Framework CSS utilitaire pour le design responsive |
| **JavaScript Vanilla** | ES6+ | Logique d'interface, appels API via `Fetch API` |
| **Chart.js** | 3.x | Rendu de graphiques pour les tableaux de bord statistiques |
| **Lucide Icons** | Local | Bibliothèque d'icônes SVG (version locale, sans CDN) |
| **Poppins** | Google Fonts | Typographie principale de l'interface |

### Infrastructure et DevOps

| Outil | Rôle |
| :--- | :--- |
| **Render.com** | Hébergement de l'application (service Web Docker) |
| **Aiven MySQL** | Base de données de production managée dans le cloud (Frankfurt, EU) |
| **GitHub** | Gestion de versions et hébergement du code source |
| **GitHub Actions** | Pipeline CI/CD pour le déploiement automatique |
| **WampServer** | Environnement de développement local (Windows) |

---

## 5. Architecture du Système

### Diagramme Client-Serveur

```
┌────────────────────────────────────────┐
│           NAVIGATEUR WEB               │
│          (Client HTML/CSS/JS)          │
│                                        │
│  ┌─────────────────────────────────┐   │
│  │  api.js (Wrapper Fetch API)     │   │
│  │  auth.js (Gestion JWT client)   │   │
│  │  utils.js (Fonctions UI)        │   │
│  └──────────────┬──────────────────┘   │
└─────────────────┼──────────────────────┘
                  │ HTTPS (JSON)
                  ▼
┌────────────────────────────────────────┐
│           SERVEUR APACHE (Docker)      │
│                                        │
│  ┌─────────────────────────────────┐   │
│  │  api/index.php (Router)         │   │
│  │  api/helpers/ (JWT, Auth, ...)  │   │
│  │  api/routes/ (10 modules)       │   │
│  └──────────────┬──────────────────┘   │
└─────────────────┼──────────────────────┘
                  │ PDO + SSL
                  ▼
┌────────────────────────────────────────┐
│         BASE DE DONNÉES MySQL          │
│   (Aiven Cloud / MySQL WAMP local)     │
│                                        │
│  users │ courses │ assignments │ grades│
│  messages │ schedule │ announcements  │
│  reports │ refresh_tokens │ mat_counter│
└────────────────────────────────────────┘
```

### Flux d'Authentification JWT

```
Utilisateur          Client (JS)             Serveur (PHP)           Base de données
    │                    │                        │                        │
    │── Saisit matricule─►│                        │                        │
    │   et mot de passe  │─── POST /auth/login ──►│                        │
    │                    │                        │── SELECT * FROM users ─►│
    │                    │                        │◄──── Utilisateur ───────│
    │                    │                        │── Vérifie Bcrypt ──────►│
    │                    │◄── JWT + Refresh Token ─│                        │
    │                    │── Stockage localStorage │                        │
    │◄── Redirection vers│                        │                        │
    │    son dashboard   │                        │                        │
    │                    │                        │                        │
    │                    │  [15 min plus tard]    │                        │
    │                    │─── POST /auth/refresh ─►│                        │
    │                    │◄─── Nouveau JWT ────────│                        │
    │                    │   (silencieux, auto)   │                        │
```

### Détection Automatique de l'Environnement

Le système détecte automatiquement son environnement d'exécution :

```
Démarrage du serveur PHP
          │
          ▼
  .env.local existe ?
    │           │
   OUI         NON
    │           │
    ▼           ▼
LOCAL (WAMP) Variables système Render
MySQL local   présentes ?
              │          │
             OUI        NON
              │          │
              ▼          ▼
           PROD        .env local
          (Aiven)    (Render .env)
```

---

## 6. Structure du Projet

```
EDUNET/
│
├── .github/
│   └── workflows/
│       └── deploy.yml            ← Pipeline CI/CD GitHub Actions → Render
│
├── api/                          ← BACK-END PHP COMPLET
│   ├── config/
│   │   ├── config.php            ← Chargement .env, constantes globales, diagnostic Render
│   │   └── database.php          ← Singleton PDO + SSL Aiven automatique
│   │
│   ├── helpers/
│   │   ├── jwt.php               ← JWT manuel : jwt_generate() et jwt_verify() en HMAC-SHA256
│   │   ├── response.php          ← Réponses JSON normalisées : json_success() et json_error()
│   │   ├── auth.php              ← Middleware : require_auth(), require_role()
│   │   └── upload.php            ← Validation, sécurisation et enregistrement des fichiers PDF
│   │
│   ├── routes/
│   │   ├── auth.php              ← POST /auth/login, /logout, /refresh, /change-password
│   │   ├── admin.php             ← CRUD comptes + statistiques + génération matricule
│   │   ├── users.php             ← GET profil, PUT mise à jour, upload avatar
│   │   ├── courses.php           ← CRUD cours + upload PDF (enseignant)
│   │   ├── assignments.php       ← CRUD devoirs + soumissions PDF (élève)
│   │   ├── grades.php            ← CRUD notes par matière et trimestre
│   │   ├── messages.php          ← Messagerie de classe (lecture, envoi)
│   │   ├── schedule.php          ← Emploi du temps (lecture, gestion admin)
│   │   ├── announcements.php     ← Annonces de classe (enseignant/admin)
│   │   └── reports.php           ← Signalements disciplinaires (censeur)
│   │
│   ├── uploads/
│   │   ├── courses/              ← PDFs des cours uploadés
│   │   ├── assignments/          ← Rendus de devoirs des élèves
│   │   └── avatars/              ← Photos de profil
│   │
│   ├── .htaccess                 ← RewriteRule : tout vers index.php (sauf fichiers physiques)
│   ├── index.php                 ← Point d'entrée unique, router principal, headers CORS
│   ├── update_admin.php          ← Script de migration autonome des identifiants admin
│   └── seed_realistic_users.php  ← Script de génération de 76 utilisateurs de démonstration
│
├── client/                       ← FRONT-END HTML/CSS/JS
│   ├── assets/
│   │   ├── css/style.css         ← Thème global, variables CSS, animations, responsive
│   │   ├── js/
│   │   │   ├── api.js            ← Wrapper Fetch : détecte URL, injecte JWT, rafraîchit auto
│   │   │   ├── auth.js           ← Guard de session, vérif premier_connexion, redirections
│   │   │   └── utils.js          ← Sidebar mobile, notifications toast, formatage dates
│   │   └── img/                  ← Images locales (logo, illustrations)
│   │
│   ├── index.html                ← Page d'accueil publique
│   └── pages/
│       ├── login.html            ← Connexion par matricule
│       ├── change-password.html  ← Changement de MDP obligatoire (1ère connexion)
│       │
│       ├── admin/
│       │   ├── dashboard.html    ← Stats globales (Chart.js), résumé activité
│       │   ├── users.html        ← Tableau de gestion, création, suspension, reset MDP
│       │   └── settings.html     ← Paramètres de l'école
│       │
│       ├── eleve/
│       │   ├── dashboard.html    ← Résumé personnel de l'élève
│       │   ├── courses.html      ← Bibliothèque de cours PDF
│       │   ├── assignments.html  ← Devoirs à rendre + Historique rendus
│       │   ├── grades.html       ← Bulletin de notes par matière/trimestre
│       │   ├── schedule.html     ← Emploi du temps de la classe
│       │   ├── messages.html     ← Messagerie de classe
│       │   └── announcements.html← Annonces reçues
│       │
│       ├── enseignant/
│       │   ├── dashboard.html    ← Vue d'ensemble du professeur
│       │   ├── courses.html      ← Mes cours (upload PDF, gestion)
│       │   ├── assignments.html  ← Mes devoirs, rendus d'élèves, notation
│       │   ├── grades.html       ← Saisie de notes par classe et trimestre
│       │   ├── schedule.html     ← Mon emploi du temps
│       │   ├── messages.html     ← Messagerie
│       │   └── announcements.html← Publier une annonce
│       │
│       └── censeur/
│           ├── dashboard.html    ← Statistiques disciplinaires
│           └── reports.html      ← Gestion des signalements (ouvert/traité/fermé)
│
├── database/
│   └── schema.sql                ← CREATE TABLE complet + INSERT admin par défaut
│
├── .env                          ← Variables de production (Aiven, JWT secret)
├── .env.local                    ← Variables locales WAMP (ignoré par Git)
├── .gitignore                    ← Exclut .env, .env.local, uploads/, logs
├── Dockerfile                    ← Image php:8.1-apache, mod_rewrite, entrypoint
└── entrypoint.sh                 ← Exécute update_admin.php puis lance apache2-foreground
```

---

## 7. Modèle de Données

La base de données est composée de **10 tables** relationnelles normalisées.

### Schéma Relationnel (résumé)

```
users (id, matricule, nom, prenom, password, role, classe, matiere, avatar, statut, premier_connexion)
  │
  ├──► refresh_tokens (id, user_id, token, expires_at)
  ├──► courses (id, titre, fichier_url, enseignant_id, classe, matiere)
  ├──► assignments (id, titre, date_limite, enseignant_id, classe, matiere)
  │      └──► submissions (id, assignment_id, eleve_id, fichier_url, note)
  ├──► grades (id, eleve_id, enseignant_id, matiere, note, trimestre)
  ├──► messages (id, expediteur_id, classe, contenu)
  ├──► announcements (id, auteur_id, titre, contenu, classe_cible)
  ├──► schedule (id, enseignant_id, classe, matiere, jour, heure_debut, heure_fin, salle)
  ├──► reports (id, signaleur_id, cible_id, motif, statut)
  └──► matricule_counter (id, annee, dernier_numero)
```

### Description Détaillée des Tables Principales

#### Table `users` — Utilisateurs
| Colonne | Type | Description |
| :--- | :--- | :--- |
| `id` | INT AUTO_INCREMENT | Clé primaire |
| `matricule` | VARCHAR(20) UNIQUE | Identifiant unique de connexion (ex: `BJ-2026-0001`) |
| `nom` | VARCHAR(100) | Nom de famille |
| `prenom` | VARCHAR(100) | Prénom |
| `password` | VARCHAR(255) | Hash Bcrypt du mot de passe |
| `role` | ENUM | `eleve`, `enseignant`, `censeur`, `admin` |
| `classe` | VARCHAR(50) | Classe de l'élève (ex: `3ème A`) |
| `matiere` | VARCHAR(100) | Matière de l'enseignant (ex: `Mathématiques`) |
| `avatar` | VARCHAR(255) | Chemin de la photo de profil |
| `statut` | ENUM | `actif` ou `suspendu` |
| `premier_connexion` | TINYINT(1) | `1` = doit changer son MDP, `0` = normal |

#### Table `matricule_counter` — Séquence Auto
| Colonne | Type | Description |
| :--- | :--- | :--- |
| `annee` | INT | Année académique (ex: `2026`) |
| `dernier_numero` | INT | Dernier numéro attribué (incrémenté à chaque création) |

> **Fonctionnement :** Lors de la création d'un compte via `POST /admin/users`, le système lit `dernier_numero`, l'incrémente, construit le matricule `BJ-{annee}-{numéro padded 4}`, puis met à jour le compteur en une transaction atomique.

---

## 8. API REST — Documentation Complète

Toutes les routes passent par le point d'entrée unique `api/index.php`. Le format de réponse est systématiquement du **JSON**.

**URL de base :**
- Locale : `http://localhost/EDUNET/api/`
- Production : `https://edunet-bj.onrender.com/api/`

**Format des réponses :**
```json
// Succès
{ "success": true, "message": "Description", "data": { ... } }

// Erreur
{ "success": false, "message": "Message d'erreur", "data": null }
```

### 🔓 Routes Publiques (sans authentification)

| Méthode | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/auth/login` | Connexion par matricule + mot de passe. Retourne un JWT et un Refresh Token. |
| `POST` | `/auth/refresh` | Renouvellement silencieux du JWT via le Refresh Token. |
| `POST` | `/auth/logout` | Invalidation du Refresh Token en base de données. |

### 🔒 Routes Protégées (JWT requis dans l'en-tête `Authorization: Bearer {token}`)

#### Profil & Authentification
| Méthode | Endpoint | Rôle requis | Description |
| :--- | :--- | :--- | :--- |
| `POST` | `/auth/change-password` | Tout rôle | Changement de mot de passe (obligatoire à la 1ère connexion) |
| `GET` | `/users/profile` | Tout rôle | Récupération du profil de l'utilisateur connecté |
| `PUT` | `/users/profile` | Tout rôle | Mise à jour du profil et de la photo de profil |

#### Cours & Devoirs
| Méthode | Endpoint | Rôle requis | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/courses` | Élève / Enseignant | Liste des cours de la classe ou de l'enseignant |
| `POST` | `/courses` | Enseignant | Création et upload d'un cours (PDF) |
| `DELETE` | `/courses/{id}` | Enseignant | Suppression d'un cours |
| `GET` | `/assignments` | Élève / Enseignant | Liste des devoirs |
| `POST` | `/assignments` | Enseignant | Création d'un devoir avec date limite |
| `POST` | `/assignments/{id}/submit` | Élève | Soumission d'un devoir (PDF) |
| `PUT` | `/assignments/{id}/grade` | Enseignant | Attribution d'une note à un rendu |

#### Notes & Emploi du Temps
| Méthode | Endpoint | Rôle requis | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/grades` | Élève / Enseignant | Notes de l'élève ou par classe |
| `POST` | `/grades` | Enseignant | Saisie d'une note |
| `PUT` | `/grades/{id}` | Enseignant | Modification d'une note |
| `GET` | `/schedule` | Tous | Emploi du temps (filtré par rôle) |

#### Communication
| Méthode | Endpoint | Rôle requis | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/messages` | Tout rôle | Messages de sa classe |
| `POST` | `/messages` | Tout rôle | Envoi d'un message à la classe |
| `GET` | `/announcements` | Tout rôle | Annonces de sa classe |
| `POST` | `/announcements` | Enseignant / Admin | Publication d'une annonce |

#### Discipline & Administration
| Méthode | Endpoint | Rôle requis | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/reports` | Censeur | Liste des signalements |
| `PUT` | `/reports/{id}` | Censeur | Mise à jour du statut d'un signalement |
| `GET` | `/admin/users` | Admin | Liste complète des utilisateurs |
| `POST` | `/admin/users` | Admin | Création d'un compte (génération matricule auto) |
| `PUT` | `/admin/users/{id}` | Admin | Modification / Suspension d'un compte |
| `DELETE` | `/admin/users/{id}` | Admin | Suppression d'un compte |
| `GET` | `/admin/stats` | Admin | Statistiques globales de l'établissement |

---

## 9. Fonctionnalités par Rôle

### 🔑 Administrateur Général

L'administrateur est le seul acteur capable de créer des comptes dans le système. Cela élimine toute possibilité d'inscription frauduleuse.

**Tableau de bord :**
- Vue des statistiques globales : nombre d'élèves par classe, d'enseignants par matière, taux de devoirs rendus, distribution des signalements (via Chart.js).

**Gestion des utilisateurs :**
- Création de comptes avec génération automatique d'un matricule unique au format `BJ-AAAA-XXXX`.
- Attribution automatique d'un mot de passe temporaire `000000` (l'utilisateur sera redirigé vers une page de changement de MDP obligatoire lors de sa première connexion).
- Suspension ou réactivation instantanée d'un compte.
- Réinitialisation du mot de passe d'un compte.

### 👨‍🏫 Enseignant (Professeur)

- **Gestion des cours :** Upload de cours au format PDF, visibles uniquement par les élèves de la classe ciblée.
- **Gestion des devoirs :** Création de sujets avec date et heure limite. Consultation des rendus PDF soumis par les élèves.
- **Notation :** Attribution d'une note numérique à chaque rendu d'élève avec commentaire optionnel.
- **Saisie des notes :** Formulaire de saisie des bulletins par trimestre et par classe.
- **Communication :** Envoi de messages à sa classe, publication d'annonces.
- **Emploi du temps :** Vue de son calendrier de cours hebdomadaire.

### 👨‍🎓 Élève (Apprenant)

- **Cours :** Accès à la liste des cours disponibles pour sa classe, téléchargement des PDFs.
- **Devoirs :** Visualisation des devoirs en cours avec compteur de délai restant. Soumission d'un rendu au format PDF.
- **Notes :** Consultation de son bulletin par matière et par trimestre.
- **Emploi du temps :** Visualisation de son calendrier de cours.
- **Messagerie :** Participation aux échanges de sa classe.
- **Annonces :** Réception des annonces de ses enseignants et de l'administration.

### 👮‍♂️ Censeur (Responsable de la Discipline)

- **Tableau de bord disciplinaire :** Vue synthétique des incidents en cours, traités et fermés.
- **Gestion des signalements :** Consultation des rapports d'incidents déposés, avec suivi de leur statut (`Ouvert` → `En traitement` → `Fermé`).

---

## 10. Sécurité et Authentification

La sécurité est un axe fondamental de l'architecture d'EduNet BJ. Plusieurs couches de protection ont été implémentées :

### 1. Authentification par Numéro Matricule
- L'identifiant de connexion est le **numéro matricule** attribué par l'administration, et non un email auto-déclaré.
- Cela rend toute tentative d'usurpation d'identité beaucoup plus difficile (le matricule n'est pas devinable).

### 2. Hachage Bcrypt des Mots de Passe
- Aucun mot de passe n'est stocké en clair en base de données.
- L'algorithme `PASSWORD_BCRYPT` de PHP est utilisé avec un coût de travail par défaut de 12 (paramétrable). Ce chiffrement est irréversible : même un administrateur base de données ne peut pas récupérer un mot de passe.

### 3. JSON Web Tokens (JWT) Manuels
- Le JWT est généré **manuellement** (sans bibliothèque externe) en HMAC-SHA256.
- Structure : `Header.Payload.Signature` encodé en Base64URL.
- **Durée de vie du jeton d'accès :** 15 minutes (900 secondes).
- **Refresh Token :** Jeton aléatoire de 80 caractères hexadécimaux, stocké en base de données avec une expiration de 7 jours. Il est **invalidé à chaque utilisation** et remplacé par un nouveau (rotation du refresh token).

### 4. Contrôle d'Accès Basé sur les Rôles (RBAC)
- Chaque route protégée appelle `require_auth()` pour vérifier la validité du JWT.
- `require_role('admin')` ou `require_role(['enseignant', 'admin'])` vérifie le rôle extrait du jeton avant toute opération de données.
- Un élève ne peut jamais accéder à un endpoint d'enseignant ou d'admin, et vice-versa.

### 5. Matricule Admin Hors-Séquence
- L'administrateur principal possède le matricule `ADMIN-EDN-2026`, délibérément hors du format séquentiel `BJ-AAAA-XXXX`. Cela évite toute collision de matricule et rend ce compte non-devinable par un attaquant connaissant le format standard.

### 6. Protection des Uploads
- Les fichiers uploadés sont validés (type MIME, extension, taille maximale de 5 Mo).
- Les noms de fichiers sont renommés avec un hash unique pour éviter l'écrasement et les attaques par traversée de répertoire.

### 7. Connexion DB en SSL
- En environnement de production (Aiven), la connexion PDO est configurée avec SSL activé (`MYSQL_ATTR_SSL_VERIFY_SERVER_CERT = false` pour Aiven qui gère ses propres certificats).

---

## 11. Interface Utilisateur

### Design System
L'interface repose sur une charte graphique cohérente et moderne :
- **Palette de couleurs :** Tons de bleu marine profond (`#1a1f5e`) pour la crédibilité et la confiance, avec des accents dynamiques en bleu vif.
- **Typographie :** Police **Poppins** (Google Fonts) pour une lisibilité optimale.
- **Layout :** Architecture en deux colonnes (sidebar fixe + zone de contenu principale) sur desktop, menu tiroir sur mobile.
- **Composants :** Cartes statistiques animées, tableaux paginés, formulaires validés côté client, notifications toast en temps réel.

### Responsivité
Toutes les pages sont entièrement **responsive** grâce à Tailwind CSS. Un menu hamburger géré par `utils.js` assure la navigation sur appareils mobiles et tablettes.

### Flux de Navigation (Login)
```
Page d'accueil (index.html)
          │
          ▼
  Formulaire de connexion (login.html)
  [Saisie : Matricule + Mot de passe]
          │
          ▼
  API : POST /auth/login
  ◄── JWT + Refresh Token + Role ──►
          │
          ├── premier_connexion = 1 ?
          │         │
          │        OUI ──► change-password.html (Changement MDP obligatoire)
          │
          └── Role ?
              ├── admin    ──► /pages/admin/dashboard.html
              ├── enseignant ─► /pages/enseignant/dashboard.html
              ├── eleve    ──► /pages/eleve/dashboard.html
              └── censeur  ──► /pages/censeur/dashboard.html
```

---

## 12. Déploiement et Infrastructure

### Environnements

| Environnement | URL | Base de données | Configuration |
| :--- | :--- | :--- | :--- |
| **Développement** | `http://localhost/EDUNET/` | MySQL local (WAMP) | `.env.local` |
| **Production** | `https://edunet-bj.onrender.com` | Aiven MySQL (Frankfurt) | Variables Render |

### Architecture Docker (Production)

```dockerfile
FROM php:8.1-apache
# Extensions : PDO, PDO MySQL
# mod_rewrite activé
# Client : /var/www/html/
# API    : /var/www/html/api/
# Uploads: /var/www/html/api/uploads/ (chown www-data)
ENTRYPOINT ["entrypoint.sh"]  # Migration DB + apache2-foreground
```

### Pipeline CI/CD (GitHub Actions)

```yaml
on: push (branche main)
  └── job: deploy
        └── step: curl RENDER_DEPLOY_HOOK
```
Chaque `git push` sur `main` déclenche automatiquement le redéploiement sur Render via un webhook sécurisé (stocké dans les secrets GitHub).

### Migration Automatique à Chaque Démarrage

Le script `entrypoint.sh` exécute systématiquement `update_admin.php` avant de démarrer Apache. Ce script se connecte à la base Aiven et s'assure que les identifiants administrateur sont correctement configurés — sans aucune intervention manuelle.

---

## 13. Guide d'Installation Locale

### Prérequis
- **WampServer** (ou équivalent : XAMPP, Laragon) avec PHP 8.1+
- Un navigateur moderne (Chrome, Firefox, Edge)

### Étapes d'Installation

**1. Cloner le dépôt**
```bash
git clone https://github.com/JeffMichel/edunet-bj.git
# Puis copier dans C:\wamp64\www\EDUNET\
```

**2. Créer la base de données**

Ouvrir PhpMyAdmin (`http://localhost/phpmyadmin`) et exécuter :
```sql
CREATE DATABASE edunet_bj CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Puis importer le fichier `database/schema.sql`.

**3. Créer le fichier `.env.local`** à la racine du projet :
```ini
DB_HOST=localhost
DB_PORT=3306
DB_NAME=edunet_bj
DB_USER=root
DB_PASSWORD=

JWT_SECRET=une_cle_secrete_complexe_et_longue_minimum_32_caracteres
JWT_EXPIRES_IN=900
REFRESH_TOKEN_EXPIRES_IN=604800

UPLOAD_DIR=uploads/
MAX_FILE_SIZE=5242880
APP_URL=http://localhost
```

**4. Peupler la base de données avec les données de démonstration**

Ouvrir dans un navigateur (les scripts s'exécutent séquentiellement) :
```
http://localhost/EDUNET/api/seed_realistic_users.php
```

**5. Accéder à l'application**
```
http://localhost/EDUNET/client/pages/login.html
```

---

## 14. Tests et Validation

### Comptes de Démonstration

#### 🔑 Administrateur
| Champ | Valeur |
| :--- | :--- |
| **Matricule** | `ADMIN-EDN-2026` |
| **Mot de passe** | `EduN3t@BJ#R00t!2026Adm` |
| **Accès** | Tableau de bord + Gestion des comptes + Statistiques |

#### 👨‍🏫 Enseignants (16 comptes — Mot de passe temporaire : `000000`)

| Matricule | Nom & Prénom | Matière |
| :--- | :--- | :--- |
| `BJ-2026-0002` | Paul D ALMEIDA | Mathématiques |
| `BJ-2026-0003` | Alain TCHABI | Mathématiques |
| `BJ-2026-0004` | Aline HOUENOU | Français |
| `BJ-2026-0005` | Koffi MENSAH | Français |
| `BJ-2026-0006` | Senami ZINSOU | Anglais |
| `BJ-2026-0007` | Jean DOSSA | Anglais |
| `BJ-2026-0008` | Marc ADJANOHOUN | Physique-Chimie |
| `BJ-2026-0009` | Fifame LOKO | SVT |
| `BJ-2026-0010` | Kossi SOGLO | Histoire-Géo |
| `BJ-2026-0011` | Pelagie VODONOU | Philosophie |
| `BJ-2026-0012` | Eric BIO | Informatique |
| `BJ-2026-0013` à `BJ-2026-0017` | *(autres enseignants)* | Matières variées |

> ⚠️ Un enseignant avec `premier_connexion = 1` sera redirigé vers la page de changement de mot de passe à sa première connexion.

#### 👨‍🎓 Élèves (60 comptes — Mot de passe temporaire : `000000`)

| Plage de Matricules | Classe | Nombre |
| :--- | :--- | :--- |
| `BJ-2026-0018` à `BJ-2026-0022` | 6ème A | 5 élèves |
| `BJ-2026-0023` à `BJ-2026-0027` | 6ème B | 5 élèves |
| `BJ-2026-0028` à `BJ-2026-0032` | 5ème A | 5 élèves |
| `BJ-2026-0033` à `BJ-2026-0037` | 5ème B | 5 élèves |
| `BJ-2026-0038` à `BJ-2026-0042` | 4ème A | 5 élèves |
| `BJ-2026-0043` à `BJ-2026-0047` | 3ème A | 5 élèves |
| `BJ-2026-0048` à `BJ-2026-0052` | 2nde C | 5 élèves |
| `BJ-2026-0053` à `BJ-2026-0057` | 2nde D | 5 élèves |
| `BJ-2026-0058` à `BJ-2026-0062` | 1ère C | 5 élèves |
| `BJ-2026-0063` à `BJ-2026-0067` | 1ère D | 5 élèves |
| `BJ-2026-0068` à `BJ-2026-0072` | Terminale C | 5 élèves |
| `BJ-2026-0073` à `BJ-2026-0077` | Terminale D | 5 élèves |

### Scénarios de Test Recommandés

1. **Test Admin :** Connexion → Création d'un nouvel enseignant → Vérifier le matricule généré (`BJ-2026-0078`) → Suspendre le compte → Réactiver.
2. **Test Enseignant :** Connexion avec `BJ-2026-0002` → Changer le MDP → Uploader un cours PDF → Créer un devoir avec date limite → Consulter les rendus d'élèves.
3. **Test Élève :** Connexion avec `BJ-2026-0018` → Changer le MDP → Consulter les cours → Rendre un devoir → Consulter ses notes.
4. **Test Censeur :** Connexion → Consulter les signalements → Marquer un incident comme "Traité".
5. **Test Sécurité :** Essayer d'accéder à `/admin/users` sans token JWT → Vérifier le rejet `401`. Essayer avec un token d'élève → Vérifier le rejet `403`.

---

## 15. Perspectives et Améliorations Futures

Bien que fonctionnelle et complète, la plateforme laisse place à plusieurs évolutions envisagées :

| Amélioration | Description |
| :--- | :--- |
| **Application Mobile** | Développement d'une application Android/iOS via React Native consommant l'API existante sans modification du back-end. |
| **Notifications en Temps Réel** | Intégration de WebSockets (ou Server-Sent Events) pour les alertes instantanées (nouveau devoir, nouveau message). |
| **Paiement des Frais Scolaires** | Module de gestion et de suivi des frais d'inscription et de scolarité intégré à l'interface admin. |
| **Gestion des Parents** | Ajout d'un 5ème rôle `parent` permettant la consultation du bulletin et de l'emploi du temps de l'enfant. |
| **Rapports PDF Automatiques** | Génération automatique de bulletins de notes au format PDF (via une bibliothèque comme mPDF ou FPDF). |
| **Mode Hors-Ligne (PWA)** | Transformation en Progressive Web App avec cache local pour fonctionner sans connexion internet stable. |
| **Multi-Établissements** | Extension de l'architecture pour gérer plusieurs écoles depuis une interface d'administration centralisée (mode SaaS). |

---

## 16. Conclusion

**EduNet BJ** illustre la faisabilité de développer une solution logicielle complète, sécurisée et prête pour la production en utilisant des technologies fondamentales maîtrisées (PHP, MySQL, HTML/CSS/JS) sans dépendances à des frameworks lourds. 

Le projet démontre des compétences complètes en :
- **Conception d'architecture logicielle** (API REST découplée, pattern Client-Serveur)
- **Sécurité applicative** (JWT, Bcrypt, RBAC, protection des uploads)
- **Modélisation de données** (10 tables relationnelles normalisées, gestion des contraintes)
- **Développement Front-End** (interfaces modernes, responsives, gestion de session JWT côté client)
- **DevOps et Infrastructure** (Docker, Render.com, GitHub Actions CI/CD, double environnement)

La plateforme est **déployée et opérationnelle** à l'adresse [https://edunet-bj.onrender.com](https://edunet-bj.onrender.com) et le code source est intégralement disponible sur [GitHub](https://github.com/JeffMichel/edunet-bj).

---

*Développé dans le cadre d'un projet de fin d'études — République du Bénin, 2026.*
