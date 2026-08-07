FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    curl \
    libpq-dev \
<<<<<<< Updated upstream
    gnupg \
    chromium

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs
=======
    nodejs \
    npm
>>>>>>> Stashed changes

WORKDIR /var/www

RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY ./docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY ./docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY . .

ENV PUPPETEER_SKIP_DOWNLOAD=true

RUN composer install --no-dev --optimize-autoloader --no-interaction \
<<<<<<< Updated upstream
    && npm ci --omit=dev \
    && chown -R www-data:www-data storage bootstrap/cache

# Chrome for Testing (matches the puppeteer version from npm ci). Installed
# outside /var/www because the bind mount shadows that path at runtime.
# The apt "chromium" package above stays: it provides the shared libraries
# this binary needs. Debian's own chromium build crashes under puppeteer
# (crashpad handler spawned without --database), so it is not used directly.
ENV PUPPETEER_CACHE_DIR=/opt/puppeteer-cache

RUN npx puppeteer browsers install chrome \
    && ln -sf "$(find /opt/puppeteer-cache -type f -name chrome -path '*chrome-linux64*')" /usr/local/bin/chrome-for-testing \
    && chmod -R a+rX /opt/puppeteer-cache
=======
    && chown -R www-data:www-data storage bootstrap/cache
>>>>>>> Stashed changes
