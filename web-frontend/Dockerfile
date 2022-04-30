# Use the official PHP image.
# https://hub.docker.com/_/php
FROM php:8.0-apache

# Configure PHP for Cloud Run.
# Precompile PHP code with opcache.
RUN docker-php-ext-install -j "$(nproc)" opcache
RUN set -ex; \
  { \
    echo "; Cloud Run enforces memory & timeouts"; \
    echo "memory_limit = -1"; \
    echo "max_execution_time = 0"; \
    echo "; File upload at Cloud Run network limit"; \
    echo "upload_max_filesize = 32M"; \
    echo "post_max_size = 32M"; \
    echo "; Configure Opcache for Containers"; \
    echo "opcache.enable = On"; \
    echo "opcache.validate_timestamps = Off"; \
    echo "; Configure Opcache Memory (Application-specific)"; \
    echo "opcache.memory_consumption = 32"; \
  } > "$PHP_INI_DIR/conf.d/cloud-run.ini"

# Copy in custom code from the host machine.
WORKDIR /var/www/html
COPY . ./

# Edit Apache config to allow the .htaccess file to work
RUN cat apache-config.txt >> /etc/apache2/apache2.conf

# Use the PORT environment variable in Apache configuration files.
# https://cloud.google.com/run/docs/reference/container-contract#port
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Configure PHP for development.
# Switch to the production php.ini for production operations.
# RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
# https://github.com/docker-library/docs/blob/master/php/README.md#configuration
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Copy Composer and Install the PHP dependencies
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN apt-get update && apt-get install -y git
RUN composer require google/cloud-storage

# Install GD support to be able to run JpGraph
# Install dependencies for GD and install GD with support for jpeg, png webp and freetype
# Info about installing GD in PHP https://www.php.net/manual/en/image.installation.php
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \

# As of PHP 7.4 we don't need to add --with-png
RUN docker-php-ext-configure gd --with-webp --with-freetype
RUN docker-php-ext-install gd