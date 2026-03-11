FROM php:8.2-apache

# Activer les modules Apache nécessaires (mod_rewrite, mod_headers)
RUN a2enmod rewrite headers

# Utiliser la configuration PHP de production pour cacher les erreurs fatales
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Installer les extensions PHP nécessaires (ex: pdo_mysql pour AWS RDS)
RUN docker-php-ext-install pdo pdo_mysql

# Copier les fichiers du projet dans le dossier web d'Apache
COPY . /var/www/html/

# Donner les bons droits au dossier web
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/

# Modifier la configuration Apache pour autoriser les .htaccess et l'accès
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
CMD ["apache2-foreground"]
