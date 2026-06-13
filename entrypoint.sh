#!/bin/sh
# Mettre à jour les identifiants de l'admin en base de données au démarrage
php /var/www/html/api/update_admin.php

# Lancer Apache en premier plan (comportement par défaut)
exec apache2-foreground
