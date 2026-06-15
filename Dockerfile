FROM php:8.2-apache

# Install system dependencies and PostgreSQL PDO extension
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy custom Apache virtual host configuration
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY apache-vhost.conf /etc/apache2/sites-enabled/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy all project files
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Set permissions for storage and bootstrap cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose port 80
EXPOSE 80

CMD ["apache2-foreground"]
