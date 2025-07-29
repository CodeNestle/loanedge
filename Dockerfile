# Use official PHP Apache image
FROM php:8.1-apache

# Enable mod_rewrite
RUN a2enmod rewrite

# Install MySQLi extension
RUN docker-php-ext-install mysqli

# Copy app files to Apache server
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Configure Apache if your app is inside "public"
# If your index.php is inside a folder, like public/, update DocumentRoot
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

EXPOSE 80
