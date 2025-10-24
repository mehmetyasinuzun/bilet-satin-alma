# Multi-stage build for optimized image
FROM php:8.2-apache as base

# Metadata
LABEL maintainer="mehmetyasinuzun"
LABEL description="Bilet Satın Alma Platformu - Cross-platform Docker Container"
LABEL version="1.0"

# Set environment variables
ENV DEBIAN_FRONTEND=noninteractive \
    TZ=Europe/Istanbul \
    APACHE_DOCUMENT_ROOT=/var/www/html \
    PHP_MEMORY_LIMIT=256M \
    PHP_UPLOAD_MAX_FILESIZE=10M \
    PHP_POST_MAX_SIZE=10M

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    sqlite3 \
    libsqlite3-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_sqlite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Configure PHP
RUN { \
    echo 'memory_limit = ${PHP_MEMORY_LIMIT}'; \
    echo 'upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE}'; \
    echo 'post_max_size = ${PHP_POST_MAX_SIZE}'; \
    echo 'max_execution_time = 300'; \
    echo 'date.timezone = ${TZ}'; \
    echo 'display_errors = Off'; \
    echo 'log_errors = On'; \
    echo 'error_log = /var/log/apache2/php_errors.log'; \
    } > /usr/local/etc/php/conf.d/custom.ini

# Configure Apache
RUN a2enmod rewrite headers expires && \
    sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Create optimized Apache VirtualHost configuration
RUN { \
    echo '<VirtualHost *:80>'; \
    echo '    ServerAdmin admin@localhost'; \
    echo '    DocumentRoot /var/www/html'; \
    echo '    '; \
    echo '    <Directory /var/www/html>'; \
    echo '        Options -Indexes +FollowSymLinks'; \
    echo '        AllowOverride All'; \
    echo '        Require all granted'; \
    echo '    </Directory>'; \
    echo '    '; \
    echo '    ErrorLog ${APACHE_LOG_DIR}/error.log'; \
    echo '    CustomLog ${APACHE_LOG_DIR}/access.log combined'; \
    echo '</VirtualHost>'; \
    } > /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy application files with proper ownership
COPY --chown=www-data:www-data . /var/www/html/

# Create data directory for SQLite database
RUN mkdir -p /var/www/html/data && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 777 /var/www/html/data && \
    chmod 644 /var/www/html/*.php

# Copy and configure entrypoint script
COPY --chmod=755 docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Expose HTTP port
EXPOSE 80

# Use custom entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Default command
CMD ["apache2-foreground"]
