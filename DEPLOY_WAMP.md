# Déploiement d'EduNet BJ avec WampServer (Apache & MySQL)

Ce guide explique comment faire tourner l'application **EduNet BJ** sous **WampServer** (au lieu de lancer les serveurs intégrés en ligne de commande PHP).

Grâce au résolveur d'URL dynamique implémenté dans l'application, vous disposez de deux méthodes simples pour héberger la plateforme.

---

## Étape 1 : Importer la Base de Données

1. Démarrez **WampServer** (l'icône dans la barre des tâches doit devenir **verte**).
2. Ouvrez **phpMyAdmin** en allant sur `http://localhost/phpmyadmin` (par défaut, l'utilisateur est `root` sans mot de passe).
3. Créez une nouvelle base de données nommée `edunet_bj`.
4. Sélectionnez la base de données `edunet_bj`, allez dans l'onglet **Importer**.
5. Choisissez le fichier de schéma SQL situé dans votre projet à l'emplacement :
   `C:\Users\Jefferson Michel\Desktop\EDUNET\database\schema.sql`
6. Cliquez sur **Importer** en bas de la page.
7. La base est maintenant prête avec l'administrateur par défaut inséré.

---

## Étape 2 : Activer le module de réécriture d'URL d'Apache (mod_rewrite)

L'API utilise un fichier `.htaccess` pour rediriger les requêtes virtuelles vers `index.php`. Pour cela, le module `mod_rewrite` d'Apache doit être actif :

1. Cliquez gauche sur l'icône verte de **WampServer** dans la barre des tâches.
2. Allez dans **Apache** -> **Modules Apache**.
3. Faites défiler la liste et assurez-vous que **rewrite_module** est coché.
4. Si ce n'est pas le cas, cliquez dessus pour l'activer. WampServer va redémarrer Apache automatiquement.

---

## Méthode A : Déploiement Direct (Recommandé - Très simple)

Cette méthode ne nécessite aucune modification de fichiers système de Windows ou d'Apache. Il suffit de copier le projet dans le dossier public de WampServer.

1. Copiez le dossier du projet `EDUNET` de votre bureau et collez-le dans le dossier `www` de WampServer :
   - Chemin source : `C:\Users\Jefferson Michel\Desktop\EDUNET`
   - Chemin de destination : `C:\wamp64\www\EDUNET`
   
2. Ouvrez votre navigateur et accédez directement à la page d'accueil via l'URL :
   **`http://localhost/EDUNET/client/index.html`**
   
3. Le résolveur d'URL détecte automatiquement que le site tourne dans le sous-dossier `/EDUNET` de Wamp et redirigera correctement les appels API vers `http://localhost/EDUNET/api`.

---

## Méthode B : Déploiement avec Virtual Hosts (Pour des adresses propres)

Cette méthode est utile si vous voulez avoir des URL locales de production comme `http://client.edunetbj.local` et `http://api.edunetbj.local`.

### 1. Configurer les Virtual Hosts
Ouvrez le fichier de configuration des Virtual Hosts d'Apache. Il se trouve généralement ici :
`C:\wamp64\bin\apache\apache[version]\conf\extra\httpd-vhosts.conf`
*(Remplacez `[version]` par la version installée d'Apache)*
   
Ajoutez ce bloc à la fin :
```apache
<VirtualHost *:80>
    ServerName api.edunetbj.local
    DocumentRoot "C:/Users/Jefferson Michel/Desktop/EDUNET/api"
    <Directory "C:/Users/Jefferson Michel/Desktop/EDUNET/api">
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

<VirtualHost *:80>
    ServerName client.edunetbj.local
    DocumentRoot "C:/Users/Jefferson Michel/Desktop/EDUNET/client"
    <Directory "C:/Users/Jefferson Michel/Desktop/EDUNET/client">
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 2. Modifier le fichier hosts de Windows
Exécutez le **Bloc-notes** en tant qu'administrateur, ouvrez le fichier `C:\Windows\System32\drivers\etc\hosts`, et ajoutez à la fin :
```text
127.0.0.1 api.edunetbj.local
127.0.0.1 client.edunetbj.local
```

### 3. Redémarrer les services
Faites un clic gauche sur l'icône WampServer -> **Redémarrer tous les services**.
Vous pouvez ensuite accéder au site via **`http://client.edunetbj.local`**.

---

## Identifiants de test administrateur
*   **Matricule** : `ADMIN-EDN-2026`
*   **Mot de passe** : `EduN3t@BJ#R00t!2026Adm`
