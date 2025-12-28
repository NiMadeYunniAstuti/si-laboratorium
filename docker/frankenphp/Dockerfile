FROM dunglas/frankenphp:latest

# Install system dependencies
RUN apk add --no-cache \
    libzip-dev \
    png-dev \
    jpeg-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    wget

# Install PHP extensions
RUN install-php-extensions \
    pdo_mysql \
    mysqli \
    gd \
    zip \
    opcache \
    mbstring \
    intl \
    bcmath \
    soap \
    xml \
    tokenizer

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /app
RUN chmod -R 755 /app

# Create logs directory
RUN mkdir -p /app/logs && chown www-data:www-data /app/logs

# Expose ports
EXPOSE 80 443

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Start FrankenPHP
CMD ["php", "server.php"]