FROM php:5.6-apache

# install php extensions
RUN apt-get update \
 && apt-get install -y git zlib1g-dev vim libmcrypt-dev \
 && docker-php-ext-install zip mysqli pdo pdo_mysql mcrypt

# enable apache2 mods
RUN a2enmod rewrite

# install php.ini
COPY dockerfile-assets/php.ini /usr/local/etc/php

# install apache2 site conf
COPY dockerfile-assets/apache2-site.conf /etc/apache2/sites-available/site.conf
RUN a2ensite site.conf

# install composer
RUN curl -sS https://getcomposer.org/installer \
  | php -- --install-dir=/usr/local/bin --filename=composer

ADD . /var/www/html

WORKDIR /var/www/html
