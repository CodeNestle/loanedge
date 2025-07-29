FROM php:8.1-apache

# Enable rewrite for Laravel/Lumen-style routing
RUN a2enmod rewrite

# Install MySQLi for DB
RUN docker-php-ext-install mysqli

# Install Node.js & npm
RUN apt-get update && \
    apt-get install -y curl gnupg && \
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

# Set workdir
WORKDIR /var/www/html

# Copy everything
COPY . .

# Install Tailwind + build
RUN npm install && npx tailwindcss -i ./input.css -o ./public/output.css --minify

# Set Apache to use public as document root
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

EXPOSE 80
