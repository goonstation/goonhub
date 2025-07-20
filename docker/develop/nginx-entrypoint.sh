#!/bin/bash
set -e

DOMAIN=$(echo "$APP_URL" | sed -E 's/https?:\/\///gi')
export APP_DOMAIN="$DOMAIN"

API_DOMAIN=$(echo "$API_URL" | sed -E 's/https?:\/\///gi')
export API_DOMAIN="$API_DOMAIN"

CERT_DIR="/etc/ssl/certs"
KEY_DIR="/etc/ssl/private"

ROOT_KEY_PATH="$KEY_DIR/$DOMAIN-root.key"
ROOT_CERT_PATH="$CERT_DIR/$DOMAIN-root.crt"
SERVER_KEY_PATH="$KEY_DIR/$DOMAIN.key"
SERVER_CSR_PATH="$CERT_DIR/$DOMAIN.csr"
SERVER_CERT_PATH="$CERT_DIR/$DOMAIN.crt"

SHARED_DIR="/certs"
SHARED_CERT_PATH="$SHARED_DIR/$DOMAIN.crt"
SHARED_KEY_PATH="$SHARED_DIR/$DOMAIN.key"

mkdir -p "$CERT_DIR" "$KEY_DIR" "$SHARED_DIR"

if [ ! -f "$SERVER_CERT_PATH" ] || [ ! -f "$SERVER_KEY_PATH" ]; then
    if [ -f "$SHARED_CERT_PATH" ] && [ -f "$SHARED_KEY_PATH" ]; then
        echo "Using existing certificate and key."

        cp "$SHARED_CERT_PATH" "$SERVER_CERT_PATH"
        cp "$SHARED_KEY_PATH" "$SERVER_KEY_PATH"

        chmod 600 "$SERVER_KEY_PATH"
        chmod 644 "$SERVER_CERT_PATH"

        OWNER=$(whoami)
        GROUP=$(groups | cut -d' ' -f1)
        chown "$OWNER:$GROUP" "$SERVER_CERT_PATH" "$SERVER_KEY_PATH"
    else
        echo "Generating self-signed certificate..."

        openssl req -x509 -nodes \
            -newkey RSA:2048 \
            -keyout "$ROOT_KEY_PATH" \
            -days 3650 \
            -out "$ROOT_CERT_PATH" \
            -subj "/CN=$DOMAIN" \
            -addext "subjectAltName=DNS:$DOMAIN,DNS:*.$DOMAIN,IP:127.0.1.1"

        openssl req -nodes \
            -newkey rsa:2048 \
            -keyout "$SERVER_KEY_PATH" \
            -out "$SERVER_CSR_PATH" \
            -subj "/CN=$DOMAIN" \
            -addext "subjectAltName=DNS:$DOMAIN,DNS:*.$DOMAIN,IP:127.0.1.1"

        openssl x509 -req \
            -CA "$ROOT_CERT_PATH" \
            -CAkey "$ROOT_KEY_PATH" \
            -in "$SERVER_CSR_PATH" \
            -out "$SERVER_CERT_PATH" \
            -days 3650 \
            -CAcreateserial \
            -extfile <(printf "subjectAltName=DNS:$DOMAIN,DNS:*.$DOMAIN,IP:127.0.1.1\nauthorityKeyIdentifier = keyid,issuer\nbasicConstraints = CA:FALSE\nkeyUsage = digitalSignature, keyEncipherment\nextendedKeyUsage=serverAuth")

        chmod 600 "$ROOT_KEY_PATH" "$SERVER_KEY_PATH"
        chmod 644 "$ROOT_CERT_PATH" "$SERVER_CERT_PATH"

        cp "$ROOT_CERT_PATH" "$ROOT_KEY_PATH" "$SERVER_CERT_PATH" "$SERVER_KEY_PATH" "$SHARED_DIR"
        chown "$WWWUSER:$WWWGROUP" "$SHARED_DIR"/*
    fi
else
    echo "Certificate already exists, skipping generation."
fi

/usr/bin/envsubst '$APP_DOMAIN $API_DOMAIN $REVERB_HOST' < /etc/nginx/templates/site.template > /etc/nginx/conf.d/site.conf
/usr/bin/openresty -g 'daemon off;'
