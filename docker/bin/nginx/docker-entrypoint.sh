#!/usr/bin/env bash

set -eu

# Prepare nginx
# https://github.com/docker-library/docs/issues/496#issuecomment-287927576
envsubst "$(printf '${%s} ' $(bash -c "compgen -A variable"))" </etc/nginx/nginx.template >/etc/nginx/nginx.conf
if [[ $USE_HTTPS == "true" ]]; then
    if [[ ! -f "/data/certs/website.crt" || ! -f "/data/certs/website.key" ]]; then
        # Create certificates
        openssl req -new -newkey rsa:4096 -x509 -sha256 -days 365 -nodes -out website.crt -keyout website.key -subj "/C=/ST=/L=/O=/OU=/CN="

        if [[ ! -d /data/certs ]]; then
            mkdir -p /data/certs
        fi
        mv website.crt /data/certs
        mv website.key /data/certs

        # Delete files if already exist (Docker saving files)
        if [[ -f "/etc/ssl/certs/website.crt" ]]; then
            rm /etc/ssl/certs/website.crt
        fi
        if [[ -f "/etc/ssl/certs/website.key" ]]; then
            rm /etc/ssl/certs/website.key
        fi
    fi

    # Link certificates
    if [[ -f /etc/ssl/certs/website.crt ]]; then
        rm /etc/ssl/certs/website.crt
    fi
    if [[ -f /etc/ssl/certs/website.key ]]; then
        rm /etc/ssl/certs/website.key
    fi
    ln -s /data/certs/website.crt /etc/ssl/certs/website.crt
    ln -s /data/certs/website.key /etc/ssl/certs/website.key

    # Enable HTTPS
    if [[ ! -f /etc/nginx/conf.d/default-ssl.conf ]]; then
        envsubst "$(printf '${%s} ' $(bash -c "compgen -A variable"))" </etc/nginx/conf.d/default-ssl.template >/etc/nginx/conf.d/default-ssl.conf
    fi
else
    # Enable HTTP
    envsubst "$(printf '${%s} ' $(bash -c "compgen -A variable"))" </etc/nginx/conf.d/default.template >/etc/nginx/conf.d/default.conf
fi

# Empty all php files (to reduce size). Only the file's existence is important
find . -type f -name "*.php" -exec sh -c 'i="$1"; >"$i"' _ {} \;

exec "$@"
