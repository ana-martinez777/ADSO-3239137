# Laravel API on PHP 8.4 with Apache. Build context: plan-mejoramiento/
# libfaketime is only activated by the "api-past" service used by the seeder.
FROM composer:2 AS composer

FROM php:8.4-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip curl libfaketime \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers \
    && ln -s "$(dpkg -L libfaketime | grep 'libfaketime.so.1$' | head -n 1)" /usr/local/lib/libfaketime.so.1 \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install dependencies first so this layer is cached between builds.
COPY api/composer.json api/composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY api/ ./
RUN composer dump-autoload --no-dev --optimize --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache

COPY infra/docker/api/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY infra/docker/api/entrypoint.sh /usr/local/bin/sports-gear-entrypoint
# Strip Windows line endings in case the script was checked out with CRLF.
RUN sed -i 's/\r$//' /usr/local/bin/sports-gear-entrypoint \
    && chmod +x /usr/local/bin/sports-gear-entrypoint

EXPOSE 80
ENTRYPOINT ["sports-gear-entrypoint"]
CMD ["apache2-foreground"]
