#!/bin/bash

set -eo pipefail

# If command starts with an option (`-f` or `--some-option`), prepend main command
if [ "${1#-}" != "$1" ]; then
    set -- php-fpm "$@"
fi

# Logging functions
entrypoint_log() {
    local type="$1"
    shift
    printf '%s [%s] [Entrypoint]: %s\n' "$(date '+%Y-%m-%d %T %z')" "$type" "$*"
}
entrypoint_note() {
    entrypoint_log Note "$@"
}
entrypoint_warn() {
    entrypoint_log Warn "$@" >&2
}
entrypoint_error() {
    entrypoint_log ERROR "$@" >&2
    exit 1
}

# usage: file_env VAR [DEFAULT]
#    ie: file_env 'XYZ_DB_PASSWORD' 'example'
#
# Will allow for "$XYZ_DB_PASSWORD_FILE" to fill in the value of
# "$XYZ_DB_PASSWORD" from a file, especially for Docker's secrets feature
# Read more: https://docs.docker.com/engine/swarm/secrets/
file_env() {
    local var="$1"
    local fileVar="${var}_FILE"
    local def="${2:-}"
    if [ "${!var:-}" ] && [ "${!fileVar:-}" ]; then
        echo >&2 "error: both $var and $fileVar are set (but are exclusive)"
        exit 1
    fi
    local val="$def"
    if [ "${!var:-}" ]; then
        val="${!var}"
    elif [ "${!fileVar:-}" ]; then
        val="$(<"${!fileVar}")"
    fi
    export "$var"="$val"
    unset "$fileVar"
}

# Setup php
if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ]; then
    entrypoint_note 'Entrypoint script for OpenBlog started'

    # ----------------------------------------

    entrypoint_note 'Load various environment variables'
    manualEnvs=(
        APP_SECRET
        GOOGLE_RECAPTCHA_SITE_KEY
        GOOGLE_RECAPTCHA_SECRET
    )
    envs=(
        PHP_MAX_EXECUTION_TIME
        PHP_MEMORY_LIMIT
        PHP_POST_MAX_SIZE
        PHP_UPLOAD_MAX_FILESIZE
        APP_ENV
        TRUSTED_PROXIES
        TRUSTED_HOSTS
        MAILER_DSN
        DATABASE_URL
        "${manualEnvs[@]}"
    )

    # Set empty environment variable or get content from "/run/secrets/<something>"
    for e in "${envs[@]}"; do
        file_env "$e"
    done

    # Set default environment variable values
    : "${PHP_MAX_EXECUTION_TIME:=120}"
    # 'memory_limit' has to be larger than 'post_max_size' and 'upload_max_filesize'
    : "${PHP_MEMORY_LIMIT:=256M}"
    # Important for upload limit. 'post_max_size' has to be larger than 'upload_max_filesize'
    : "${PHP_POST_MAX_SIZE:=100M}"
    : "${PHP_UPLOAD_MAX_FILESIZE:=100M}"

    ###> symfony/framework-bundle ###
    : "${APP_ENV:=prod}"
    : "${APP_SECRET:=}"
    : "${TRUSTED_PROXIES:=}"
    : "${TRUSTED_HOSTS:=}"
    ###< symfony/framework-bundle ###

    ###> symfony/mailer ###
    : "${MAILER_DSN:=}"
    ###< symfony/mailer ###

    ###> doctrine/doctrine-bundle ###
    # Format described at https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
    # For an SQLite database, use: "sqlite:///%kernel.project_dir%/var/data.db"
    # For a PostgreSQL database, use: "postgresql://db_user:db_password@127.0.0.1:5432/db_name?serverVersion=11&charset=utf8"
    # IMPORTANT: You MUST configure your server version, either here or in config/packages/doctrine.yaml
    : "${DATABASE_URL:=}"
    ###< doctrine/doctrine-bundle ###

    ###> google/recaptcha ###
    # To use Google Recaptcha, you must register a site on Recaptcha's admin panel:
    # https://www.google.com/recaptcha/admin
    # https://developers.google.com/recaptcha/docs/faq#id-like-to-run-automated-tests-with-recaptcha.-what-should-i-do
    : "${GOOGLE_RECAPTCHA_SITE_KEY:=}"
    : "${GOOGLE_RECAPTCHA_SECRET:=}"
    ###< google/recaptcha ###

    missing_manual_settings=
    for e in "${manualEnvs[@]}"; do
        if [ -z "${!e}" ]; then
            missing_manual_settings=1
            case $e in
            APP_SECRET)
                : "${!e:='!change-me!'}"
                entrypoint_warn "$e=${!e}"
                ;;
            GOOGLE_RECAPTCHA_SITE_KEY|GOOGLE_RECAPTCHA_SECRET)
                entrypoint_warn "Keys for Google Recaptcha not set"
                ;;
            *)
                ;;
            esac
        fi
    done
    if [ "$missing_manual_settings" = 1 ]; then
        entrypoint_warn "You haven't set all the important values. Above you can copy-paste the generated ones, but make sure to use them."
    fi
    unset missing_manual_settings

    # ----------------------------------------

    entrypoint_note 'Load/Create optimized PHP configs'
    PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-production"
    if [ "$APP_ENV" != 'prod' ]; then
        PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-development"
    fi
    ln -sf "$PHP_INI_RECOMMENDED" "$PHP_INI_DIR/php.ini"

    {
        echo 'opcache.revalidate_freq=0'
        if [ "$APP_ENV" = "prod" ]; then
            echo 'opcache.validate_timestamps = 0'
        fi
        echo "opcache.max_accelerated_files = $(find -L /app -type f -print | grep -c php)"
        echo 'opcache.memory_consumption = 192'
        echo 'opcache.interned_strings_buffer = 16'
        echo 'opcache.fast_shutdown = 1'
    } >"$PHP_INI_DIR/conf.d/opcache.ini"
    {
        echo 'apc.enable_cli = 1'
        echo 'date.timezone = UTC'
        echo 'session.auto_start = Off'
        echo 'short_open_tag = Off'
        echo "max_execution_time = $PHP_MAX_EXECUTION_TIME"
        echo "memory_limit = $PHP_MEMORY_LIMIT"
    } >"$PHP_INI_DIR/conf.d/misc.ini"
    {
        echo "post_max_size = $PHP_POST_MAX_SIZE"
        echo "upload_max_filesize = $PHP_UPLOAD_MAX_FILESIZE"
    } >"$PHP_INI_DIR/conf.d/upload-limit.ini"

    # ----------------------------------------

    if [ "$APP_ENV" != 'prod' ] && [ -f /certs/localCA.crt ]; then
        entrypoint_note 'Update CA certificates.'
        ln -sf /certs/localCA.crt /usr/local/share/ca-certificates/localCA.crt
        update-ca-certificates
    fi

    # ----------------------------------------

    if [ "$APP_ENV" != 'prod' ]; then
        entrypoint_note 'Installing libraries according to non-production environment ...'
        composer install --prefer-dist --no-interaction --no-plugins --no-scripts --no-progress --no-suggest
    fi

    # ----------------------------------------

    entrypoint_note 'Waiting for db to be ready'

    if [ -z "${DATABASE_URL}" ]; then
        entrypoint_error "DATABASE_URL has to be set"
    fi

    # https://unix.stackexchange.com/questions/83926/how-to-download-a-file-using-just-bash-and-nothing-else-no-curl-wget-perl-et
    # shellcheck disable=SC2034
    read -r protocol server path <<<"${DATABASE_URL//// }"
    read -r user server <<<"${server//@/ }"
    DB_DRIVER=${protocol//:/} # Remove the : at the end
    DB_HOST=${server//:*}
    DB_PORT=${server//*:}
    DB_USER=${user//:*}
    DB_PASSWORD=${user//*:}

    if [[ x"${HOST}" == x"${PORT}" ]]; then
        if [ "$DB_DRIVER" = "mysql" ]; then
            DB_PORT=3306
        fi
    fi

    ATTEMPTS_LEFT_TO_REACH_DATABASE=60
    if [ "$DB_DRIVER" = "mysql" ]; then
        until [ $ATTEMPTS_LEFT_TO_REACH_DATABASE = 0 ] || mysql --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USER" --password="$DB_PASSWORD" -e "SELECT 1" >/dev/null 2>&1; do
            sleep 1
            ATTEMPTS_LEFT_TO_REACH_DATABASE=$((ATTEMPTS_LEFT_TO_REACH_DATABASE - 1))
            entrypoint_warn "Still waiting for db to be ready... Or maybe the db is not reachable. $ATTEMPTS_LEFT_TO_REACH_DATABASE attempts left"
        done
    else
        entrypoint_error 'Database not supported! Use MySQL'
    fi

    if [ $ATTEMPTS_LEFT_TO_REACH_DATABASE = 0 ]; then
        entrypoint_error 'The db is not up or not reachable'
    else
        entrypoint_note 'The db is now ready and reachable'
    fi

    # ----------------------------------------

    entrypoint_note 'Fix directory/file permissions'
    chown www-data:www-data -R .
    find . -type d -exec chmod 755 {} \;
    find . -type f -exec chmod 644 {} \;
    chmod +x bin/*
fi

exec docker-php-entrypoint "$@"
