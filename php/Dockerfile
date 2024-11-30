# php/Dockerfile
FROM php:8.3-apache

# Install dependencies
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /var/www/

# Copy application files
COPY generate_dummy_data.php /var/www/generate_dummy_data.php
COPY wait-for-it.sh /var/www/wait-for-it.sh

# Set executable permissions on wait-for-it.sh within the container
RUN chmod +x wait-for-it.sh

# Expose port 80
EXPOSE 80

# Command to run your PHP script and start Apache
CMD ["bash", "-c", "./wait-for-it.sh percona:3306 -- php generate_dummy_data.php && apache2-foreground"]