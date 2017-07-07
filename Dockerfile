FROM php:7.0-apache

# install PHP extensions (PDO mysql)
RUN docker-php-ext-install pdo pdo_mysql

# enable mod_rewrite
RUN a2enmod rewrite

# apache configuration
COPY apache-virtual-host.conf /etc/apache2/sites-available/000-default.conf

COPY nette/composer.json /app/

WORKDIR /app

COPY nette/ /app

RUN chmod 777 log temp && \
sed -i "s/DocumentRoot \/var\/www\/html/DocumentRoot \/app\/www/g" /etc/apache2/sites-available/000-default.conf