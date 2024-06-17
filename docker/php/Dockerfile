FROM php:8.2-fpm

RUN apt update \
    && apt install -y supervisor zlib1g-dev g++ git libicu-dev zip libxslt-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libpq-dev zip librabbitmq-dev \
    && docker-php-ext-install intl opcache pdo pdo_mysql pdo_pgsql pgsql xsl \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && pecl install amqp \
    && docker-php-ext-enable amqp \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && pecl install redis \
    && docker-php-ext-enable redis \ 
    && docker-php-ext-configure gd --enable-gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install gd \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug
WORKDIR /var/www/conference

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
