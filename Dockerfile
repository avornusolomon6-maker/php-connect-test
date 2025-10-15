# Use official PHP image
FROM php:8.2-apache

# Install PostgreSQL extension for PHP
RUN docker-php-ext-install pgsql pdo pdo_pgsql

# Copy project files into the container
COPY . /var/www/html/

# Expose port 80 for web access
EXPOSE 80
