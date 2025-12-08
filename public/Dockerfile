# STEP 1: Use the official PHP image with Apache
# Using php:8.2-apache is a good choice for a modern, production-ready environment.
FROM php:8.2-apache

# STEP 2: Install Required System Dependencies and PHP Extensions (THE FIX!)
# 'mysqli' is the crucial part that fixes your error.
# 'pdo_mysql' is good practice to include as well.
# 'zip' is often needed for composer.
RUN docker-php-ext-install mysqli pdo_mysql zip

# STEP 3: Set the Working Directory in the container
WORKDIR /app

# STEP 4: Copy the application code into the container
COPY . /app

# STEP 5: Install Composer (if you manage dependencies with it)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# STEP 6: Configure Apache Document Root
# Your application uses a `public/` folder (`/app/public/index.php`),
# so we must point the web server's document root to it.
RUN mv /var/www/html /var/www/html_old \
    && ln -s /app/public /var/www/html

# STEP 7: Enable Apache's rewrite module
# This is required for clean URLs and routing in most modern PHP frameworks.
RUN a2enmod rewrite

# The base image already runs Apache on port 80, which Railway will handle.