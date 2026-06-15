FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist

# Create storage directories and set permissions
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && chmod -R 777 storage bootstrap/cache

# Configure Apache to serve Laravel from public directory
RUN echo '<Directory /var/www/html/public>' > /etc/apache2/sites-available/laravel.conf && \
    echo '    Options Indexes FollowSymLinks' >> /etc/apache2/sites-available/laravel.conf && \
    echo '    AllowOverride All' >> /etc/apache2/sites-available/laravel.conf && \
    echo '    Require all granted' >> /etc/apache2/sites-available/laravel.conf && \
    echo '</Directory>' >> /etc/apache2/sites-available/laravel.conf && \
    echo 'DocumentRoot /var/www/html/public' >> /etc/apache2/sites-available/laravel.conf

RUN a2ensite laravel && a2dissite 000-default

# Copy Apache config for Laravel
RUN cp /etc/apache2/sites-available/laravel.conf /etc/apache2/sites-enabled/

# Expose port
EXPOSE 8000

# Start Apache
CMD ["apache2ctl", "-D", "FOREGROUND"]
