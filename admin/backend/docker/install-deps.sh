#!/bin/sh
set -e

# Установка системных зависимостей
apk update && apk add --no-cache \
    git \
    openssl-dev \
    freetype-dev \
    jpeg-dev \
    libpng-dev \
    libxml2-dev \
    mariadb-dev \
    zip \
    unzip \
    bzip2-dev \
    libzip-dev \
    openldap-dev \
    fontconfig \
    oniguruma-dev \
    icu-dev \
    iputils

# Установка PHP-расширений
docker-php-ext-configure ldap
docker-php-ext-configure gd --with-freetype --with-jpeg
docker-php-ext-install -j"$(nproc)" gd ldap pcntl pdo_mysql mysqli bcmath zip soap intl bz2