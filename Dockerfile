# Use official PHP image with Apache
FROM php:8.2-apache

# Install system dependencies required for PostgreSQL extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Copy all project files into the container
COPY . /var/www/html/

# Expose port 80 for Render
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]

