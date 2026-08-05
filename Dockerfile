FROM php:8.3-fpm

# 1. Instalar dependencias del sistema y bibliotecas cliente para Oracle
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    libaio-dev \
    && (apt-get install -y libaio1t64 || apt-get install -y libaio1) \
    && rm -rf /var/lib/apt/lists/*

# Crear symlink para libaio (compatible con Debian Bookworm/Ubuntu 24.04)
RUN ln -s /usr/lib/x86_64-linux-gnu/libaio.so.1t64 /usr/lib/x86_64-linux-gnu/libaio.so.1 || true

# Configurar Git en el contenedor para evitar advertencias de ownership sobre volúmenes
RUN git config --global --add safe.directory /var/www

# 2. Configurar Oracle Instant Client
WORKDIR /opt/oracle
COPY instantclient-basiclite-linux.x64-21.6.0.0.0dbru.zip instantclient-basic.zip
COPY instantclient-sdk-linux.x64-21.6.0.0.0dbru.zip instantclient-sdk.zip

RUN unzip -o instantclient-basic.zip \
    && unzip -o instantclient-sdk.zip \
    && rm -f instantclient-basic.zip instantclient-sdk.zip \
    && mv instantclient_21_6 instantclient

ENV ORACLE_HOME=/opt/oracle/instantclient
ENV LD_LIBRARY_PATH=/opt/oracle/instantclient:/usr/lib/x86_64-linux-gnu
RUN echo /opt/oracle/instantclient > /etc/ld.so.conf.d/oracle-instantclient.conf && ldconfig

# 3. Compilar extensiones OCI8 y PDO_OCI + extensiones requeridas por Laravel 12
RUN echo 'instantclient,/opt/oracle/instantclient' | pecl install oci8-3.3.0 \
    && docker-php-ext-configure pdo_oci --with-pdo-oci=instantclient,/opt/oracle/instantclient \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_oci zip pdo_mysql gd bcmath \
    && docker-php-ext-enable oci8

# 4. Instalar Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

EXPOSE 9000

CMD ["php-fpm"]