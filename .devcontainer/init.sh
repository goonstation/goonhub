#!/usr/bin/env sh

if [ ! -d "vendor" ]; then
  docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs --no-autoloader --no-scripts
fi

if [ ! -f ".env" ]; then
  cp .env.example .env
fi
