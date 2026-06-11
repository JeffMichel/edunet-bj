# Guide de Déploiement sur Render — EduNet BJ

Ce guide explique comment mettre en ligne gratuitement l'application **EduNet BJ** sur **Render** en utilisant un conteneur Docker unifié (qui héberge à la fois le Front-end et le Back-end PHP sur le même nom de domaine, éliminant les problèmes de CORS).

---

## Prérequis
1. Un compte [GitHub](https://github.com/) ou GitLab.
2. Un compte [Render](https://render.com/).
3. Une base de données MySQL hébergée gratuitement en ligne (voir Étape 1).

---

## Étape 1 : Créer une Base de Données MySQL gratuite en ligne

Render ne propose pas de base de données MySQL dans son offre gratuite. Vous devez utiliser un hébergeur tiers gratuit. Deux choix recommandés :

### Option A : Clever Cloud (Très simple, recommandé)
1. Créez un compte gratuit sur [Clever Cloud](https://console.clever-cloud.com/).
2. Cliquez sur **Create...** -> **an add-on** -> Choisissez **MySQL Free (Shared)**.
3. Nommez-le (ex: `edunet-db`) et choisissez une région proche de vous.
4. Une fois créé, allez dans les détails de l'add-on pour récupérer vos identifiants :
   - **Host** (Serveur)
   - **Database name** (Nom de la base)
   - **User** (Utilisateur)
   - **Password** (Mot de passe)
   - **Port** (généralement `3306`)

### Option B : Aiven.io
1. Créez un compte gratuit sur [Aiven](https://aiven.io/).
2. Créez un nouveau service **MySQL** en choisissant le plan gratuit (Hobbyist).
3. Attendez quelques minutes que le service démarre, puis récupérez l'URI de connexion ou les paramètres individuels.

Une fois votre base de données en ligne créée, connectez-vous avec un outil comme DBeaver, TablePlus ou via phpMyAdmin local, et exécutez le script SQL d'initialisation :
👉 `database/schema.sql` (qui va créer toutes les tables nécessaires).

---

## Étape 2 : Envoyer le projet sur GitHub

1. Créez un nouveau dépôt privé ou public sur GitHub nommé `edunet-bj`.
2. Initialisez Git dans votre projet local et poussez le code :
   ```bash
   git init
   git add .
   git commit -m "Initial commit for Render"
   git branch -M main
   git remote add origin https://github.com/VOTRE_NOM/edunet-bj.git
   git push -u origin main
   ```

---

## Étape 3 : Créer le service sur Render

1. Connectez-vous sur votre tableau de bord **Render**.
2. Cliquez sur **New +** -> **Web Service**.
3. Associez votre compte GitHub et sélectionnez votre dépôt `edunet-bj`.
4. Configurez les paramètres suivants :
   - **Name** : `edunet-bj`
   - **Region** : Choisissez la même région que celle de votre base de données (ex: `Frankfurt (EU)`).
   - **Branch** : `main`
   - **Runtime** : **Docker** (Render va automatiquement détecter le fichier `Dockerfile` présent à la racine du projet et s'en servir pour compiler l'image).
   - **Instance Type** : **Free** (0$/mois).

---

## Étape 4 : Configurer les Variables d'Environnement sur Render

Pendant la création du service ou dans l'onglet **Environment** de votre Web Service sur Render, ajoutez les variables d'environnement suivantes correspondantes à votre base de données Clever Cloud (ou Aiven) :

| Clé | Valeur |
|---|---|
| `DB_HOST` | *(Hôte de votre base en ligne)* |
| `DB_PORT` | `3306` |
| `DB_NAME` | *(Nom de votre base en ligne)* |
| `DB_USER` | *(Utilisateur de votre base en ligne)* |
| `DB_PASSWORD` | *(Mot de passe de votre base en ligne)* |
| `JWT_SECRET` | *(Mettez une clé aléatoire sécurisée et secrète)* |
| `UPLOAD_DIR` | `uploads/` |
| `MAX_FILE_SIZE` | `5242880` |

---

## Étape 5 : Accéder à l'application

Une fois le déploiement terminé (Render affiche `Your service is live`), cliquez sur l'URL générée par Render (ex : `https://edunet-bj.onrender.com`).

- L'adresse principale affichera la page d'accueil (Front-end).
- Toutes les requêtes API seront automatiquement acheminées vers `/api` sur la même URL (grâce au résolveur automatique d'URL dynamique inclus dans `api.js`).
- Connectez-vous avec les identifiants administrateur par défaut :
  - **Email** : `admin@edunetbj.bj`
  - **Mot de passe** : `Admin@2026`
