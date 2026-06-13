FROM php:8.1-apache

# Installer les extensions PHP requises (PDO MySQL)
RUN docker-php-ext-install pdo pdo_mysql

# Activer le module de réécriture Apache (mod_rewrite)
RUN a2enmod rewrite

# Copier le client au root de l'hébergement
COPY client/ /var/www/html/

# Copier l'API dans le sous-dossier /api
COPY api/ /var/www/html/api/

# Configurer les permissions pour les dossiers d'uploads
RUN mkdir -p /var/www/html/api/uploads/courses \
             /var/www/html/api/uploads/assignments \
             /var/www/html/api/uploads/avatars \
    && chown -R www-data:www-data /var/www/html/api/uploads

# Ajuster la configuration Apache pour autoriser les .htaccess
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Exposer le port par défaut d'Apache
EXPOSE 80

# Configurer l'entrypoint pour lancer les scripts de démarrage
COPY entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["entrypoint.sh"]
