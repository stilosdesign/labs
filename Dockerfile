FROM php:7.4-fpm

# Update sources and install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    zip \
    unzip \
    inkscape \
    openssl \
    libssl-dev \
    libpng-dev \
    libzip-dev \
    libgd-dev \
    libonig-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Swoole
RUN cd /tmp && wget https://pecl.php.net/get/swoole-4.5.2.tgz && \
    tar zxvf swoole-4.5.2.tgz && \
    cd swoole-4.5.2  && \
    phpize  && \
    ./configure  --enable-openssl && \
    make && make install

RUN touch /usr/local/etc/php/conf.d/swoole.ini && \
    echo 'extension=swoole.so' > /usr/local/etc/php/conf.d/swoole.ini

# Install the PHP extentions (zip, gd)
RUN docker-php-ext-install zip

RUN docker-php-ext-configure gd --with-freetype=/usr --with-jpeg=/usr \
    && docker-php-ext-install gd

RUN mkdir -p /app/data

WORKDIR /app

# Copy sources to folder api in container
COPY ./api /app

# Run Composer commands
RUN composer install && \
    composer update && \
    composer dumpautoload

EXPOSE 5000

CMD ["/usr/local/bin/php", "/app/index.php"]