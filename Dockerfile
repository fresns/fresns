FROM php:8.2-fpm as Api

ADD ./docker/services/php/www.conf /usr/local/etc/php-fpm.d/www.conf

ARG USER_ID=1000
RUN usermod -u $USER_ID www-data
RUN usermod -G staff www-data

RUN mkdir -p /var/www/html

ADD . /var/www/html

RUN chmod -R 775 /var/www/html/storage
RUN chmod -R 775 /var/www/html/bootstrap/cache

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    build-essential \
    curl \
    libzip-dev \
    libpq-dev \
    git \
    jpegoptim optipng pngquant gifsicle \
    locales \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Graphics Draw
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Multibyte String
RUN apt-get update && apt-get install -y libonig-dev && docker-php-ext-install mbstring

# Miscellaneous
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install exif
RUN docker-php-ext-install zip
RUN docker-php-ext-install -j$(nproc) fileinfo opcache
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Enable PHP extensions
RUN docker-php-ext-enable bcmath pdo_pgsql pdo_mysql zip


RUN chown -R . /var/www/html

# Install Composer dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Install Cron
RUN apt-get update && apt-get install -y cron
RUN echo "* * * * * root php /var/www/html/artisan schedule:run >> /var/log/cron.log 2>&1" >> /etc/crontab
RUN touch /var/log/cron.log

CMD bash -c "cron && php-fpm"
