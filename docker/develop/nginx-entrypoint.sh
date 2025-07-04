#!/bin/bash
set -e

DOMAIN=$(echo "$APP_URL" | sed -E 's/https?:\/\///gi')
export APP_DOMAIN="$DOMAIN"

API_DOMAIN=$(echo "$API_URL" | sed -E 's/https?:\/\///gi')
export API_DOMAIN="$API_DOMAIN"

CERT_DIR="/etc/ssl/certs"
KEY_DIR="/etc/ssl/private"
CERT_PATH="$CERT_DIR/$DOMAIN.crt"
KEY_PATH="$KEY_DIR/$DOMAIN.key"

SHARED_DIR="/certs"
SHARED_CERT_PATH="$SHARED_DIR/$DOMAIN.crt"
SHARED_KEY_PATH="$SHARED_DIR/$DOMAIN.key"

mkdir -p "$CERT_DIR" "$KEY_DIR" "$SHARED_DIR"

if [ ! -f "$CERT_PATH" ] || [ ! -f "$KEY_PATH" ]; then
    if [ -f "$SHARED_CERT_PATH" ] && [ -f "$SHARED_KEY_PATH" ]; then
        echo "Using existing certificate and key."

        cp "$SHARED_CERT_PATH" "$CERT_PATH"
        cp "$SHARED_KEY_PATH" "$KEY_PATH"

        chmod 600 "$KEY_PATH"
        chmod 644 "$CERT_PATH"

        OWNER=$(whoami)
        GROUP=$(groups | cut -d' ' -f1)
        chown "$OWNER:$GROUP" "$CERT_PATH" "$KEY_PATH"
    else
        echo "Generating self-signed certificate..."

        openssl req -x509 -newkey rsa:4096 -sha256 -days 3650 \
            -nodes -keyout "$KEY_PATH" -out "$CERT_PATH" \
            -subj "/CN=$DOMAIN" \
            -addext "subjectAltName=DNS:$DOMAIN,DNS:*.${DOMAIN},IP:127.0.1.1"

        chmod 600 "$KEY_PATH"
        chmod 644 "$CERT_PATH"

        cp "$CERT_PATH" "$KEY_PATH" "$SHARED_DIR"
        chown "$WWWUSER:$WWWGROUP" "$SHARED_DIR"/*
    fi
else
    echo "Certificate already exists, skipping generation."
fi

/usr/bin/envsubst '$APP_DOMAIN $API_DOMAIN $REVERB_HOST' < /etc/nginx/templates/site.template > /etc/nginx/conf.d/site.conf
/usr/bin/openresty -g 'daemon off;'
