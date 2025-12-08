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

# -------------------- Application Setup (Unchanged from before) --------------------

# STEP 3: Set the Working Directory in the container
WORKDIR /app

# STEP 4: Copy the application code into the container
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

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]