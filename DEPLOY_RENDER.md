# Guide de Déploiement sur Render — EduNet BJ

Ce guide explique comment mettre en ligne **EduNet BJ** sur **Render** en utilisant Docker (héberge le Front-end et le Back-end PHP sur le même domaine, sans problèmes CORS).

---

## Prérequis

1. Un compte [GitHub](https://github.com/) — ✅ repo `JeffMichel/edunet-bj` déjà créé
2. Un compte [Render](https://render.com/)
3. Base de données Aiven MySQL — ✅ **déjà configurée et opérationnelle**

---

## ✅ Base de données Aiven — DÉJÀ CONFIGURÉE

La base de données MySQL est hébergée sur **Aiven.io** et toutes les tables sont créées.

| Paramètre | Valeur |
|-----------|--------|
| **Host** | `mysql-21fe4fef-michelnono34-06f0.d.aivencloud.com` |
| **Port** | `14402` |
| **Database** | `defaultdb` |
| **User** | `avnadmin` |
| **Password** | *(voir `.env` local)* |

**Compte Admin par défaut :**
- **Matricule** : `ADMIN-EDN-2026`
- **Mot de passe** : `EduN3t@BJ#R00t!2026Adm`

> ⚠️ Tous les comptes utilisateurs (élèves, enseignants, censeur) sont créés **uniquement par l'administrateur** depuis l'interface Admin. Il n'y a pas d'inscription publique.

---

## Étape 1 : Envoyer le projet sur GitHub

Le code est déjà sur GitHub. Pour pousser les mises à jour :

```bash
git add .
git commit -m "Mise à jour"
git push origin main
```

---

## Étape 2 : Créer le service sur Render

1. Connectez-vous sur [render.com](https://render.com/)
2. Cliquez sur **New +** → **Web Service**
3. Connectez votre compte GitHub → sélectionnez `edunet-bj`
4. Configurez :
   - **Name** : `edunet-bj`
   - **Region** : `Frankfurt (EU Central)` *(même région qu'Aiven)*
   - **Branch** : `main`
   - **Runtime** : **Docker** *(détecté automatiquement via le `Dockerfile`)*
   - **Instance Type** : **Free**

---

## Étape 3 : Variables d'environnement sur Render

Dans l'onglet **Environment** de votre service Render, ajoutez :

| Clé | Valeur |
|-----|--------|
| `DB_HOST` | `mysql-21fe4fef-michelnono34-06f0.d.aivencloud.com` |
| `DB_PORT` | `14402` |
| `DB_NAME` | `defaultdb` |
| `DB_USER` | `avnadmin` |
| `DB_PASSWORD` | `*(votre_mot_de_passe_db_aiven)*` |
| `JWT_SECRET` | `edunet_bj_secret_key_change_this_in_production` |
| `JWT_EXPIRES_IN` | `900` |
| `REFRESH_TOKEN_EXPIRES_IN` | `604800` |
| `UPLOAD_DIR` | `uploads/` |
| `MAX_FILE_SIZE` | `5242880` |

> ⚠️ **Important** : Ne pas commit le fichier `.env` sur GitHub (il est dans `.gitignore`). Toujours configurer les variables directement sur Render.

---

## Étape 4 : Déploiement automatique

Render va automatiquement :
1. Cloner le repo depuis GitHub
2. Construire l'image Docker (PHP 8.1 + Apache)
3. Démarrer le conteneur avec les variables d'environnement

Le déploiement prend **3 à 5 minutes**. Render affichera `Your service is live` quand c'est prêt.

---

## Étape 5 : Accéder à l'application

Une fois déployé, l'URL sera : `https://edunet-bj.onrender.com` (ou similaire)

- **Page d'accueil** : `https://edunet-bj.onrender.com`
- **Connexion** : `https://edunet-bj.onrender.com/pages/login.html`
- **API** : `https://edunet-bj.onrender.com/api/`

**Connexion Admin :**
- Matricule : `ADMIN-EDN-2026`
- Mot de passe : `EduN3t@BJ#R00t!2026Adm`

---

## Flux de connexion

```
Connexion (matricule + MDP)
        ↓
  premier_connexion = 1 ?
       ↙         ↘
     OUI          NON
      ↓             ↓
 Page changement  Dashboard
  de MDP obligatoire selon rôle
      ↓
  MDP changé
      ↓
 Dashboard selon rôle
(élève / enseignant / censeur / admin)
```

---

## Dépannage

| Problème | Solution |
|----------|----------|
| `Erreur de connexion à la base de données` | Vérifier les variables d'environnement sur Render |
| `Impossible de contacter le serveur d'API` | L'API est sur le même domaine `/api`, vérifier le Dockerfile |
| `Session expirée` | Le token JWT expire après 15 min, le refresh token gère automatiquement |
| `Service Render en veille` | Le plan gratuit se met en veille après 15 min d'inactivité, le premier chargement peut prendre 30-60 sec |
