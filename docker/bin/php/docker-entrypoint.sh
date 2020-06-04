#!/usr/bin/env bash

set -eu

# Setup php
if [[ "$APP_ENV" == "dev" ]]; then
    cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
else
    cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
fi

# Add recommended options
{
    echo "opcache.revalidate_freq=0"
    if [[ "$APP_ENV" != "dev" ]]; then
        echo "opcache.validate_timestamps = 0"
    fi
    echo "opcache.max_accelerated_files = $(find /app -type f -print | grep -c php)"
    echo "opcache.memory_consumption = 192"
    echo "opcache.interned_strings_buffer = 16"
    echo "opcache.fast_shutdown = 1"
} >"$PHP_INI_DIR/conf.d/opcache.ini"
{
    echo "max_execution_time = 120"
} >"$PHP_INI_DIR/conf.d/misc.ini"

# Add custom upload limit
UPLOAD_LIMIT=${UPLOAD_LIMIT:="10M"}
{
	# TODO: Maximum Upload size is defined by the smallest of the 3 following variables
	echo "memory_limit = 256M"
	echo "upload_max_filesize = $UPLOAD_LIMIT"
	# TODO: "post_max_size" should be greater than "upload_max_filesize".
	echo "post_max_size = $UPLOAD_LIMIT"
} >"$PHP_INI_DIR/conf.d/upload-limit.ini"

# Link storage/
if [[ ! -d /data ]]; then
	mkdir /data
fi
ln -sf /data ./var/data

# Fix permission
chown -R www-data:www-data /data
find /data -type d -exec chmod 755 {} \;
find /data -type f -exec chmod 644 {} \;

exec "$@"
