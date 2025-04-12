FROM php:8.1-apache

# Install the necessary extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    zip \
    && docker-php-ext-install pdo pdo_mysql zip

# Enable mod_rewrite for Yii2 (pretty URLs)
RUN a2enmod rewrite

# Apache settings: allow .htaccess
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Install Compose
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Specify the default working directory
WORKDIR /var/www/html

