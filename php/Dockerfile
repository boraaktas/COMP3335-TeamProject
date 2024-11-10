# Use PHP 8.3 official image with Apache
FROM php:8.3-apache

# Install necessary PHP extensions for Percona
RUN docker-php-ext-install mysqli

# Set the working directory for Apache to serve files
WORKDIR /var/www/html
