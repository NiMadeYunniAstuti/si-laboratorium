FROM php:8.2-apache-bullseye

# Copy badproxy configuration to fix checksum issues
COPY ./badproxy /etc/apt/apt.conf.d/99fixbadproxy

# Add ServerName to prevent Apache warnings and restart issues
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# Install system dependencies for PHP extensions
RUN apt-get update && \
    apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli zip opcache mbstring curl bcmath xml

# Install GD extension with JPEG support
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Enable Apache modules
RUN a2enmod rewrite
RUN a2enmod headers

# Copy Apache configuration
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]