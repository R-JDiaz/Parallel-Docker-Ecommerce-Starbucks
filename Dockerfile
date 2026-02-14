FROM php:8.3-apache

# Set working directory
WORKDIR /var/www/html

# Enable Apache mod_rewrite
RUN a2enmod rewrite && docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy custom Apache config
COPY ./apache/default.conf /etc/apache2/sites-available/000-default.conf

# Copy application files
COPY . .


# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

