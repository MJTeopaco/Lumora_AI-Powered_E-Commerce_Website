# STEP 1: Use the official PHP image with Apache
FROM php:8.2-apache

# STEP 2: Install system dependencies required for PHP extensions
RUN apt-get update && \
    apt-get install -y libzip-dev git unzip && \
    rm -rf /var/lib/apt/lists/*

# STEP 3: Install Required PHP Extensions
RUN docker-php-ext-install mysqli pdo_mysql zip

# STEP 4: Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# STEP 5: Set the Working Directory
WORKDIR /var/www/html

# STEP 6: Copy composer files first 
COPY composer.json composer.lock ./

# STEP 7: Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# STEP 8: Copy the rest of the application code
COPY . .

# STEP 9: Run post-install scripts if any
RUN composer dump-autoload --optimize

# STEP 10: Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# STEP 11: Configure Apache for the public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# STEP 12: Enable Apache's rewrite module (essential for clean URLs)
RUN a2enmod rewrite

# STEP 13: Configure AllowOverride for .htaccess
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/lumora.conf && \
    a2enconf lumora

# STEP 14: Configure Apache to listen on port 8080 (Railway requirement)
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:8080>/g' /etc/apache2/sites-available/*.conf

# STEP 15: Expose the port
EXPOSE 8080

# STEP 16: Start Apache
CMD ["apache2-foreground"]