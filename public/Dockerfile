# STEP 1: Use the official PHP image with Apache
FROM php:8.2-apache

# THE FIX: Install system dependencies required for PHP extensions.
# This installs the 'libzip' development package needed to compile the 'zip' PHP extension.
RUN apt-get update && \
    apt-get install -y libzip-dev && \
    rm -rf /var/lib/apt/lists/*

# STEP 2: Install Required PHP Extensions
# This step will now succeed because the system dependency is present.
RUN docker-php-ext-install mysqli pdo_mysql zip

# -------------------- Application Setup --------------------

# STEP 3: Set the Working Directory in the container
WORKDIR /app

# STEP 4: Copy the application code into the container
# NOTE: Ensure your composer.json and composer.lock are in the root 
# directory of your repository for this COPY command to work correctly.
COPY . /app

# STEP 5: Install Composer dependencies
# Copy Composer binary first
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# Run installation
RUN composer install --no-dev --optimize-autoloader

# STEP 6: Configure Apache Document Root 
# Points Apache to your public/ directory.
RUN mv /var/www/html /var/www/html_old \
    && ln -s /app/public /var/www/html

# STEP 7: Enable Apache's rewrite module (essential for clean URLs)
RUN a2enmod rewrite

# -------------------- CRITICAL FIX FOR 502 ERROR --------------------

# STEP 8: Configure Apache to listen on port 8080
# The default PHP-Apache container listens on port 80.
# We modify the configuration files to listen on 8080, as required by Railway.
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:8080>/g' /etc/apache2/sites-enabled/000-default.conf

# STEP 9: Expose the port (good practice)
EXPOSE 8080

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]