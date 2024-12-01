# Use the PHP Apache image
FROM php:8.3-apache

# Install PHP extensions
RUN docker-php-ext-install mysqli

# Set the working directory
WORKDIR /var/www/

# Copy your application files
COPY generate_dummy_data.php /var/www/generate_dummy_data.php

# Expose port 80 (optional for documentation)
EXPOSE 80

# Run the PHP script to create dummy data
CMD php /var/www/generate_dummy_data.php && apache2-foreground