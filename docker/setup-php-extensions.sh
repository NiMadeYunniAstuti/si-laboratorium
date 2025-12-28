#!/bin/bash

# Install required PHP extensions
docker-php-ext-install pdo_mysql mysqli zip opcache mbstring curl bcmath xml

# Install GD extension with JPEG support
docker-php-ext-configure gd --with-freetype --with-jpeg
docker-php-ext-install gd

# Enable Apache modules
a2enmod rewrite
a2enmod headers

# Restart Apache
apache2ctl graceful