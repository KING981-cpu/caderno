FROM php:8.2-apache

# Install necessary extensions for MySQL/MariaDB
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache for clean URLs
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    chmod +x /usr/local/bin/composer

# Create application directories first (before COPY)
RUN mkdir -p /var/www/html && \
    mkdir -p /var/log/caderno

# Copy application files
COPY . /var/www/html/

# Install PHP dependencies with Composer
WORKDIR /var/www/html
RUN composer install --no-interaction --no-dev --optimize-autoloader 2>&1 | grep -v "^$" || true

# Set the document root to public/ (after COPY so path exists)
RUN sed -i 's|DocumentRoot /var/www/html$|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's|DocumentRoot /var/www/html$|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/default-ssl.conf

# Add rewrite rules for clean URLs
RUN echo '<Directory /var/www/html/public>' >> /etc/apache2/apache2.conf && \
    echo '    AllowOverride All' >> /etc/apache2/apache2.conf && \
    echo '    Options Indexes FollowSymLinks' >> /etc/apache2/apache2.conf && \
    echo '    Require all granted' >> /etc/apache2/apache2.conf && \
    echo '</Directory>' >> /etc/apache2/apache2.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chown www-data:www-data /var/log/caderno

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

WORKDIR /var/www/html