# EduNet BJ — README Principal pour Agent IA (Antigravity)

> **Instructions pour l'agent** : Tu vas construire de zéro une application web complète appelée **EduNet BJ** en **PHP pur uniquement**. Lis ce document **entièrement** avant de générer le moindre fichier. Respecte scrupuleusement la stack, l'architecture, la charte graphique et toutes les fonctionnalités décrites ci-dessous. Ne génère aucune fonctionnalité non listée. Le projet comporte deux parties : un **back-end PHP pur (API REST)** et un **front-end HTML/CSS/JS** qui consomme cette API.
>
> **Modifications critiques à respecter absolument** :
> - ❌ Aucune page d'inscription publique — tous les comptes sont créés uniquement par l'administrateur
> - ✅ Authentification par **numéro matricule** (pas d'email)
> - ✅ Matricule généré automatiquement par le système au format `BJ-AAAA-XXXX`
> - ✅ Mot de passe par défaut `000000` à la création de tout compte
> - ✅ Changement de mot de passe obligatoire à la **première connexion** pour tous les rôles sauf admin

---

## 1. Présentation du projet

**EduNet BJ** est une plateforme scolaire numérique sécurisée destinée aux établissements du secondaire en République du Bénin. Elle centralise la communication entre élèves, enseignants, censeur et administration dans un espace unique, structuré et sécurisé.

- **Type** : Application web responsive
- **Langue** : Français uniquement
- **Cible** : Lycées et collèges du Bénin
- **Rôles** : Élève, Enseignant, Censeur, Administrateur

---

## 2. Stack technique — À respecter absolument

### Back-end
- **Langage** : PHP 8.1+ (pur, sans framework)
- **Base de données** : MySQL 8
- **Format de réponse** : JSON uniquement
- **Authentification** : JWT implémenté manuellement avec HMAC-SHA256
- **Hachage mots de passe** : `password_hash()` / `password_verify()` avec `PASSWORD_BCRYPT`
- **Upload de fichiers** : `$_FILES` natif PHP
- **CORS** : headers manuels
- **Routing** : router maison basé sur `$_SERVER['REQUEST_URI']` et `$_SERVER['REQUEST_METHOD']`

### Front-end
- **HTML5** sémantique
- **CSS** : Tailwind CSS (fichier CSS compilé en local — pas de CDN en production)
- **JavaScript** : Vanilla JS ES6+ (Fetch API pour les appels à l'API back-end PHP)
- **Graphiques** : Chart.js
- **Icônes** : Lucide Icons (version locale)
- **Police** : Poppins (Google Fonts)

---

## 3. Structure des dossiers à générer

```
edunet-bj/
├── api/
│   ├── config/
│   │   ├── database.php          ← Connexion PDO MySQL
│   │   └── config.php            ← Variables globales (JWT_SECRET, etc.)
│   ├── helpers/
│   │   ├── jwt.php               ← Génération et vérification JWT manuel
│   │   ├── response.php          ← Fonctions json_success() et json_error()
│   │   ├── auth.php              ← Middleware vérification JWT
│   │   └── upload.php            ← Gestion upload fichiers
│   ├── routes/
│   │   ├── auth.php              ← Routes authentification
│   │   ├── users.php             ← Routes utilisateurs
│   │   ├── courses.php           ← Routes cours
│   │   ├── assignments.php       ← Routes devoirs
│   │   ├── announcements.php     ← Routes annonces
│   │   ├── messages.php          ← Routes messagerie
│   │   ├── schedule.php          ← Routes emploi du temps
│   │   ├── grades.php            ← Routes notes
│   │   ├── reports.php           ← Routes signalements
│   │   └── admin.php             ← Routes administration
│   ├── uploads/
│   │   ├── courses/              ← PDF des cours
│   │   ├── assignments/          ← Fichiers devoirs soumis
│   │   └── avatars/              ← Photos de profil
│   ├── .htaccess                 ← Réécriture URL Apache
│   └── index.php                 ← Point d'entrée unique (router principal)
│
├── client/
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css         ← Variables CSS + Tailwind base
│   │   ├── js/
│   │   │   ├── api.js            ← Wrapper Fetch API réutilisable
│   │   │   ├── auth.js           ← Gestion JWT côté client
│   │   │   └── utils.js          ← Fonctions utilitaires
│   │   └── img/
│   └── pages/
│       ├── login.html
│       ├── register.html
│       ├── eleve/
│       │   ├── dashboard.html
│       │   ├── courses.html
│       │   ├── assignments.html
│       │   └── announcements.html
│       ├── enseignant/
│       │   ├── dashboard.html
│       │   ├── courses.html
│       │   ├── assignments.html
│       │   └── announcements.html
│       ├── censeur/
│       │   ├── dashboard.html
│       │   └── reports.html
│       └── admin/
│           ├── dashboard.html
│           ├── users.html
│           └── settings.html
│
├── database/
│   └── schema.sql                ← Script SQL complet création tables
│
└── .env                          ← Variables d'environnement
```

---

## 4. Fichier `.env` à générer

```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=edunet_bj
DB_USER=root
DB_PASSWORD=

JWT_SECRET=edunet_bj_secret_key_change_this_in_production
JWT_EXPIRES_IN=900
REFRESH_TOKEN_EXPIRES_IN=604800

UPLOAD_DIR=uploads/
MAX_FILE_SIZE=5242880
APP_URL=http://localhost
```

---

## 5. Base de données MySQL — Schéma complet

Génère le fichier `database/schema.sql` :

```sql
CREATE DATABASE IF NOT EXISTS edunet_bj CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edunet_bj;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  matricule VARCHAR(20) UNIQUE NOT NULL,          -- Format BJ-AAAA-XXXX, généré auto
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,                 -- Bcrypt de '000000' par défaut
  role ENUM('eleve', 'enseignant', 'censeur', 'admin') NOT NULL,
  classe VARCHAR(50) NULL,
  matiere VARCHAR(100) NULL,
  avatar VARCHAR(255) NULL,
  statut ENUM('actif', 'suspendu') DEFAULT 'actif',
  premier_connexion TINYINT(1) DEFAULT 1,         -- 1 = doit changer son mdp
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

-- Compte admin par défaut (matricule: BJ-2026-0001 / mot de passe: EduNet@BJ_Adm1n!2026#S3cur3 — premier_connexion=0 car admin ne change pas)
INSERT INTO users (matricule, nom, prenom, password, role, statut, premier_connexion)
VALUES ('BJ-2026-0001', 'Admin', 'EduNet', '$2y$10$mZEzkZlRzXBb8GAC9ymAcOZucAUeuYUdnZLCvGcoTJcpE6.xjG4Dm', 'admin', 'actif', 0);
```

---

## 6. Point d'entrée — `api/index.php`

Ce fichier est le router principal. Il doit :
1. Charger les variables `.env` manuellement
2. Définir les headers CORS pour toutes les requêtes
3. Parser le body JSON des requêtes (`file_get_contents('php://input')`)
4. Router vers le bon fichier selon l'URI et la méthode HTTP
5. Retourner 404 si la route n'existe pas

```php
<?php
// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Charger config
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/jwt.php';
require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/upload.php';

// Parser l'URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/api', '', $uri);
$method = $_SERVER['REQUEST_METHOD'];

// Router
if (preg_match('/^\/auth/', $uri)) {
    require_once __DIR__ . '/routes/auth.php';
} elseif (preg_match('/^\/users/', $uri)) {
    require_once __DIR__ . '/routes/users.php';
} elseif (preg_match('/^\/courses/', $uri)) {
    require_once __DIR__ . '/routes/courses.php';
} elseif (preg_match('/^\/assignments/', $uri)) {
    require_once __DIR__ . '/routes/assignments.php';
} elseif (preg_match('/^\/announcements/', $uri)) {
    require_once __DIR__ . '/routes/announcements.php';
} elseif (preg_match('/^\/messages/', $uri)) {
    require_once __DIR__ . '/routes/messages.php';
} elseif (preg_match('/^\/schedule/', $uri)) {
    require_once __DIR__ . '/routes/schedule.php';
} elseif (preg_match('/^\/grades/', $uri)) {
    require_once __DIR__ . '/routes/grades.php';
} elseif (preg_match('/^\/reports/', $uri)) {
    require_once __DIR__ . '/routes/reports.php';
} elseif (preg_match('/^\/admin/', $uri)) {
    require_once __DIR__ . '/routes/admin.php';
} else {
    json_error('Route non trouvée', 404);
}
```

---

## 7. JWT Manuel — `api/helpers/jwt.php`

Implémenter JWT sans librairie externe :

```php
<?php
function jwt_generate($payload) {
    $header = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload['iat'] = time();
    $payload['exp'] = time() + JWT_EXPIRES_IN;
    $payload_encoded = base64url_encode(json_encode($payload));
    $signature = base64url_encode(hash_hmac('sha256', "$header.$payload_encoded", JWT_SECRET, true));
    return "$header.$payload_encoded.$signature";
}

function jwt_verify($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    [$header, $payload, $signature] = $parts;
    $expected = base64url_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    if (!hash_equals($expected, $signature)) return false;
    $data = json_decode(base64url_decode($payload), true);
    if ($data['exp'] < time()) return false;
    return $data;
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}
```

---

## 8. Fichier `.htaccess`

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

---

## 9. API Endpoints à implémenter

### Auth (`/api/auth`)
| Méthode | Route | Description | Accès |
|---|---|---|---|
| POST | `/auth/login` | Connexion avec matricule + mot de passe — retourne JWT + refresh token + flag `premier_connexion` | Public |
| POST | `/auth/change-password` | Changer le mot de passe (obligatoire si `premier_connexion=1`) | Authentifié |
| POST | `/auth/refresh` | Renouveler l'access token | Public |
| POST | `/auth/logout` | Invalider le refresh token | Authentifié |

> **Règle `premier_connexion`** : Si la réponse du login contient `"premier_connexion": true`, le front-end doit **immédiatement rediriger** vers la page de changement de mot de passe. Toute autre navigation est bloquée jusqu'à ce que le mot de passe soit changé. Une fois changé, `premier_connexion` passe à `0` en base et l'utilisateur est redirigé vers son dashboard.

### Utilisateurs (`/api/users`)
| Méthode | Route | Description | Accès |
|---|---|---|---|
| GET | `/users/me` | Profil de l'utilisateur connecté | Tous |
| PUT | `/users/me` | Modifier son profil | Tous |
| PUT | `/users/me/password` | Changer son mot de passe | Tous |

### Annonces (`/api/announcements`)
| Méthode | Route | Description | Accès |
|---|---|---|---|
| GET | `/announcements` | Liste des annonces filtrées par classe | Élève, Enseignant |
| POST | `/announcements` | Créer une annonce | Enseignant, Admin |
| PUT | `/announcements/:id` | Modifier une annonce | Enseignant, Admin |
| DELETE | `/announcements/:id` | Supprimer une annonce | Enseignant, Admin |

### Cours (`/api/courses`)
| Méthode | Route | Description | Accès |
|---|---|---|---|
| GET | `/courses` | Liste des cours par classe | Élève, Enseignant |
| GET | `/courses/:id` | Détail d'un cours | Élève, Enseignant |
| POST | `/courses` | Publier un cours avec fichier PDF | Enseignant |
| DELETE | `/courses/:id` | Supprimer un cours | Enseignant |

### Devoirs (`/api/assignments`)
| Méthode | Route | Description | Accès |
|---|---|---|---|
| GET | `/assignments` | Liste des devoirs par classe | Élève, Enseignant |
| POST | `/assignments` | Créer un devoir | Enseignant |
| POST | `/assignments/:id/submit` | Soumettre un devoir (upload fichier) | Élève |
| PUT | `/assignments/:id/submissions/:sid` | Noter une soumission | Enseignant |
| GET | `/assignments/:id/submissions` | Voir toutes les soumissions | Enseignant |

### Messagerie (`/api/messages`)
| Méthode | Route | Description | Accès |
|---|---|---|---|
| GET | `/messages/:classe` | Messages d'une classe | Élève, Enseignant |
| POST | `/messages` | Envoyer un message dans une classe | Élève, Enseignant |

### Emploi du temps (`/api/schedule`)
| Méthode | Route | Description | Accès |
|---|---|---|---|
| GET | `/schedule/:classe` | Emploi du temps d'une classe | Tous |
| POST | `/schedule` | Ajouter un créneau | Admin, Enseignant |
| PUT | `/schedule/:id` | Modifier un créneau | Admin |
| DELETE | `/schedule/:id` | Supprimer un créneau | Admin |

### Notes (`/api/grades`)
| Méthode | Route | Description | Accès |
|---|---|---|---|
| GET | `/grades/me` | Mes notes | Élève |
| GET | `/grades/:eleve_id` | Notes d'un élève spécifique | Enseignant |
| POST | `/grades` | Ajouter une note | Enseignant |
| PUT | `/grades/:id` | Modifier une note | Enseignant |

### Signalements (`/api/reports`)
| Méthode | Route | Description | Accès |
|---|---|---|---|
| GET | `/reports` | Liste des signalements | Censeur, Admin |
| POST | `/reports` | Créer un signalement | Tous |
| PUT | `/reports/:id` | Traiter un signalement | Censeur |

### Administration (`/api/admin`)
| Méthode | Route | Description | Accès |
|---|---|---|---|
| GET | `/admin/users` | Liste tous les utilisateurs avec leur matricule | Admin |
| POST | `/admin/users` | Créer un compte (élève, enseignant ou censeur) — génère automatiquement le matricule `BJ-AAAA-XXXX` et définit le mot de passe par défaut `000000` | Admin |
| PUT | `/admin/users/:id/status` | Activer / suspendre un compte | Admin |
| PUT | `/admin/users/:id/reset-password` | Réinitialiser le mot de passe à `000000` et repasser `premier_connexion=1` | Admin |
| DELETE | `/admin/users/:id` | Supprimer un compte (le matricule ne peut jamais être réattribué) | Admin |
| GET | `/admin/stats` | Statistiques globales | Admin |

> **Règle matricule** : Le système génère le matricule en combinant `BJ` + année courante + numéro séquentiel sur 4 chiffres (ex: `BJ-2026-0042`). Ce numéro est unique, stocké en `UNIQUE` en base, et **jamais réutilisé** même après suppression du compte (gérer via une table `matricules_archives` ou compteur auto-incrémenté distinct).

---

## 10. Format des réponses JSON

Toutes les réponses de l'API doivent suivre ce format :

```json
// Succès
{
  "success": true,
  "message": "Connexion réussie",
  "data": { }
}

// Erreur
{
  "success": false,
  "message": "Email ou mot de passe incorrect",
  "data": null
}
```

Codes HTTP à respecter : `200`, `201`, `400`, `401`, `403`, `404`, `500`.

---

## 11. Logique matricule et première connexion

### Génération du matricule (`BJ-AAAA-XXXX`)

Ajouter une table `matricule_counter` pour garantir l'unicité même après suppression :

```sql
CREATE TABLE matricule_counter (
  annee YEAR NOT NULL PRIMARY KEY,
  dernier_numero INT DEFAULT 0
);
```

Lors de la création d'un compte via `POST /admin/users`, le back-end PHP doit :
1. Lire l'année courante (`date('Y')`)
2. Faire un `INSERT ... ON DUPLICATE KEY UPDATE dernier_numero = dernier_numero + 1` dans `matricule_counter`
3. Récupérer le nouveau `dernier_numero`
4. Composer le matricule : `sprintf('BJ-%d-%04d', $annee, $numero)` → ex: `BJ-2026-0042`
5. Stocker ce matricule dans `users.matricule`
6. Le matricule est **permanent et immuable** — même après suppression du compte, le compteur ne recule jamais

### Mot de passe par défaut

À la création de tout compte (élève, enseignant, censeur) :
```php
$password_defaut = password_hash('000000', PASSWORD_BCRYPT);
// premier_connexion = 1
```
La réponse de l'API doit retourner clairement le matricule et indiquer que le mot de passe par défaut est `000000`.

### Flux première connexion

```
Login (matricule + 000000)
        ↓
API retourne JWT avec { premier_connexion: true }
        ↓
Front-end détecte le flag → redirige vers /pages/change-password.html
        ↓
Utilisateur saisit nouveau mot de passe (min 6 caractères, différent de 000000)
        ↓
POST /auth/change-password → met à jour password + premier_connexion = 0
        ↓
Redirige vers le dashboard selon le rôle
```

**Règles de validation du nouveau mot de passe :**
- Minimum 6 caractères
- Ne peut pas être `000000`
- Confirmation identique au nouveau mot de passe

---

## 12. Règles back-end importantes

1. Toutes les réponses sont en JSON avec `Content-Type: application/json`
2. Vérifier le JWT sur toutes les routes protégées via `api/helpers/auth.php`
3. Vérifier les rôles avant chaque action sensible
4. Valider toutes les entrées utilisateur avant insertion en base
5. Utiliser PDO avec des requêtes préparées — jamais de concaténation SQL
6. Uploads : vérifier le type MIME (PDF uniquement pour cours/devoirs, image pour avatar) et la taille avant stockage
7. Nommer les fichiers uploadés avec un nom unique (UUID + timestamp)
8. Variables sensibles dans `.env` uniquement — jamais dans le code
9. Commenter les fonctions importantes en français

---

## 13. Charte graphique — À respecter sur toutes les pages

### Couleurs

```css
/* Primaire */
--color-primary:        #1C3F94;
--color-primary-light:  #2952C4;
--color-primary-dark:   #112569;

/* Secondaire */
--color-secondary:      #E8621A;
--color-secondary-light:#F07840;
--color-secondary-dark: #C04E10;

/* Neutres mode clair */
--color-bg:             #F8F9FC;
--color-surface:        #FFFFFF;
--color-border:         #E4E8F0;
--color-text:           #0F1C3F;
--color-text-muted:     #64748B;

/* Sémantique */
--color-success:        #10B981;
--color-warning:        #F59E0B;
--color-error:          #EF4444;
```

### Typographie
- Police : **Poppins** (Google Fonts — weights: 400, 500, 600, 700)
- Titres de page : Poppins 700, 28px
- Titres de section : Poppins 600, 20px
- Texte courant : Poppins 400, 14px, line-height 1.6
- Labels formulaire : Poppins 500, 12px

### Logo
- Texte **"EduNet"** en Poppins 700 couleur `#1C3F94` + **"BJ"** en Poppins 700 couleur `#E8621A`

### Composants UI

**Boutons**
```css
/* Primaire */
background: #1C3F94; color: white; border-radius: 8px;
padding: 12px 24px; font: 600 14px Poppins;
hover: background #2952C4;

/* Secondaire */
background: #E8621A; color: white; mêmes dimensions;
hover: background #F07840;

/* Ghost */
background: transparent; border: 1px solid #1C3F94; color: #1C3F94;
```

**Cartes**
```css
background: #FFFFFF;
border: 1px solid #E4E8F0;
border-radius: 12px;
padding: 20px;
box-shadow: 0 2px 8px rgba(28,63,148,0.06);
```

**Navigation latérale**
```css
background: #112569;
item inactif : color rgba(255,255,255,0.55);
item actif   : background #E8621A; color white; border-radius 8px;
```

---

## 14. Pages front-end à construire

Chaque page vérifie le rôle JWT avant d'afficher le contenu. Si non authentifié ou mauvais rôle → rediriger vers `/pages/login.html`.

### Pages publiques
- `client/index.html` — Landing page minimaliste : nom du projet **EduNet BJ**, slogan, et un seul bouton central **"Se connecter"** qui redirige vers `login.html`. **Aucune carte "espace élève / enseignant / censeur / admin"**, aucun lien direct vers les espaces, aucune description des fonctionnalités par rôle.
- `client/pages/login.html` — Formulaire de connexion avec **matricule** + mot de passe (pas d'email, pas de lien "s'inscrire")
- `client/pages/change-password.html` — Page de changement de mot de passe obligatoire à la première connexion (accessible uniquement si `premier_connexion=true` dans le JWT)

### Espace Élève
- `pages/eleve/dashboard.html` — Résumé : dernières annonces, devoirs à rendre, cours récents
- `pages/eleve/courses.html` — Liste des cours avec téléchargement PDF
- `pages/eleve/assignments.html` — Liste des devoirs, soumission de fichier
- `pages/eleve/announcements.html` — Fil des annonces de la classe

### Espace Enseignant
- `pages/enseignant/dashboard.html` — Stats classe, derniers devoirs soumis
- `pages/enseignant/courses.html` — Gérer et publier des cours PDF
- `pages/enseignant/assignments.html` — Créer devoirs, noter les soumissions
- `pages/enseignant/announcements.html` — Publier et gérer les annonces

### Espace Censeur
- `pages/censeur/dashboard.html` — Vue globale des signalements
- `pages/censeur/reports.html` — Consulter et traiter les signalements

### Espace Admin
- `pages/admin/dashboard.html` — Stats globales avec Chart.js (utilisateurs, cours, devoirs)
- `pages/admin/users.html` — Gérer tous les comptes : créer élève/enseignant/censeur (matricule généré automatiquement affiché à la création), suspendre, réinitialiser mot de passe
- `pages/admin/settings.html` — Configuration classes, matières, années scolaires

### Règles front-end
1. Toutes les pages sont **responsives** (min-width: 320px) et fonctionnent sur mobile et desktop
2. Le fichier `client/assets/js/api.js` est un **wrapper Fetch réutilisable** qui gère automatiquement le header `Authorization: Bearer <token>`
3. Le token JWT est stocké dans `localStorage` côté client
4. Protection XSS : échapper toutes les données affichées dynamiquement
5. Graphiques dans les dashboards : utiliser **Chart.js**
6. **Guard `premier_connexion`** : dans `auth.js`, après chaque login et après chaque chargement de page protégée, si le JWT décodé contient `premier_connexion: true`, rediriger immédiatement vers `change-password.html` sans exception
7. La page `login.html` ne contient **aucun lien vers une inscription** — seul champ de connexion : matricule + mot de passe

---

## 15. Ordre de génération recommandé

Génère les fichiers dans cet ordre pour éviter les dépendances manquantes :

**Back-end**
1. `.env` + `api/config/config.php` + `api/config/database.php`
2. `api/helpers/jwt.php` + `api/helpers/response.php` + `api/helpers/auth.php` + `api/helpers/upload.php`
3. `database/schema.sql`
4. `api/index.php`
5. `api/routes/auth.php` (login + change-password + refresh + logout — pas de register)
6. `api/routes/users.php`
7. `api/routes/courses.php`
8. `api/routes/assignments.php`
9. `api/routes/announcements.php`
10. `api/routes/messages.php`
11. `api/routes/schedule.php`
12. `api/routes/grades.php`
13. `api/routes/reports.php`
14. `api/routes/admin.php` (création comptes + génération matricule + reset password)
15. `api/.htaccess`

**Front-end**
16. `client/assets/css/style.css` (variables CSS + Tailwind base)
17. `client/assets/js/api.js` (wrapper Fetch API réutilisable)
18. `client/assets/js/auth.js` (avec guard `premier_connexion`) + `client/assets/js/utils.js`
19. `client/pages/login.html` (matricule + mot de passe, sans lien inscription)
20. `client/pages/change-password.html` (changement obligatoire à la 1ère connexion)
21. `client/index.html` (landing page)
22. Pages espace élève (dashboard → courses → assignments → announcements)
23. Pages espace enseignant (dashboard → courses → assignments → announcements)
24. Pages espace censeur (dashboard → reports)
25. Pages espace admin (dashboard → users → settings)

---

## 16. Test rapide après génération

```bash
# 1. Créer la base de données
mysql -u root -p < database/schema.sql

# 2. Lancer le serveur PHP intégré
cd api && php -S localhost:8000

# 3. Tester le login admin (matricule BJ-2026-0001)
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{"matricule":"BJ-2026-0001","password":"EduNet@BJ_Adm1n!2026#S3cur3"}'

# 4. Créer un compte élève via l'admin (doit retourner le matricule généré)
curl -X POST http://localhost:8000/admin/users \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token_admin>" \
  -d '{"nom":"Agossou","prenom":"Kossi","role":"eleve","classe":"3eme A"}'
# Réponse attendue: { "matricule": "BJ-2026-0002", "mot_de_passe_defaut": "000000" }

# 5. Tester le login du nouvel élève (doit retourner premier_connexion: true)
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{"matricule":"BJ-2026-0002","password":"000000"}'
```

---

*EduNet BJ — Connecter · Apprendre · Progresser*
*République du Bénin · 2026*
