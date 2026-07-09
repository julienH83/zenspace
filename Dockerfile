# Image du serveur web : PHP 8.2 avec Apache.
FROM php:8.2-apache

# Installation des dépendances système nécessaires aux extensions PHP.
RUN apt-get update && apt-get install -y \
        libzip-dev \
        zip \
        unzip \
        git \
        libssl-dev \
        pkg-config \
    && rm -rf /var/lib/apt/lists/*

# Extension PDO MySQL : accès à la base relationnelle.
RUN docker-php-ext-install pdo pdo_mysql

# Extensions MongoDB (base non relationnelle) et Redis (cache / rate-limit).
RUN pecl install mongodb redis \
    && docker-php-ext-enable mongodb redis

# Modules Apache : réécriture d'URL (jolies URLs) + en-têtes de sécurité.
RUN a2enmod rewrite headers

# Configuration PHP de développement (OPcache revérifie le code à chaque requête,
# affichage des erreurs activé). Évite les surprises liées au cache lors des modifs.
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-dev.ini

# Composer (gestionnaire de dépendances PHP) pour la librairie MongoDB.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# La racine web pointe sur le dossier public/ (bonne pratique de sécurité :
# seul ce dossier est exposé, le reste du code reste inaccessible).
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
