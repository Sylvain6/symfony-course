# Image de développement du projet blog EPSI.
# FrankenPHP = serveur web (Caddy) + PHP 8.4 dans un seul processus.
FROM dunglas/frankenphp:php8.4

# Extensions PHP dont le projet a besoin :
#   pdo_mysql  -> Doctrine parle à MySQL
#   intl       -> Symfony (traductions, slugger) et transliterator_transliterate()
#   zip        -> Composer décompresse les paquets
#   opcache    -> compilation PHP mise en cache
RUN install-php-extensions pdo_mysql intl zip opcache

# Composer 2, récupéré depuis son image officielle
COPY --from=composer/composer:2-bin /composer /usr/bin/composer

# Réglages PHP du projet (fuseau horaire, affichage des erreurs, limites)
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /app
