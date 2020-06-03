#!/usr/bin/env bash

set -eux

# Get all php requirements
# shellcheck disable=SC2086
apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
	icu-dev
docker-php-ext-install -j "$(nproc)" \
	intl \
	opcache \
	pdo_mysql \
	>/dev/null

# Find packages to keep, so we can safely delete dev packages
RUN_DEPS="$(
    scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions |
        tr ',' '\n' |
        sort -u |
        awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }'
)"
# shellcheck disable=SC2086
apk add --virtual .phpexts-rundeps $RUN_DEPS

# Remove building tools for smaller container size
apk del .build-deps

# Install composer
cd /usr/local/bin
/build/install-composer.sh
mv composer.phar composer

# Install Symfony tool
apk add --no-cache git
wget https://get.symfony.com/cli/installer -O - | bash
mv /root/.symfony/bin/symfony /usr/local/bin/symfony

# Copy the final app to /app
cd /build/src
mkdir /app
mv ./bin /app
mv ./config /app
mv ./public /app
mv ./src /app
mv ./templates /app
mv ./translations /app
if [[ -d ./vendor ]]; then
	mv ./vendor /app
fi
mv ./.env /app
mv ./composer.json /app
if [[ -f ./composer.lock ]]; then
	mv ./composer.lock /app
fi

# Get all vendors
cd /app
if [[ "$ENV" == "dev" ]]; then
    composer install --prefer-dist --no-interaction --no-plugins --no-scripts --no-suggest --optimize-autoloader
else
    composer install --prefer-dist --no-dev --no-interaction --no-plugins --no-scripts --no-suggest --optimize-autoloader
fi

# Prepare Symfony
chmod 755 ./bin/console
./bin/console cache:warmup --env="$ENV"

# Fix permission
chown www-data:www-data -R .
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod 755 bin/*

# Cleanup
rm -r /build
