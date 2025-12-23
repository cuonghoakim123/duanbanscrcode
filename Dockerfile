FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Create uploads directory and set permissions
RUN mkdir -p /var/www/html/uploads/templates \
    && mkdir -p /var/www/html/uploads/users \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 755 /var/www/html/uploads

# Set permissions for Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Copy .env.example to .env if .env doesn't exist
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
