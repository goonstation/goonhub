ARG PHP_VERSION=8.4
ARG COMPOSER_VERSION=2.8
ARG NODE_VERSION=latest
ARG BUN_VERSION=latest

FROM node:${NODE_VERSION} AS node

FROM oven/bun:${BUN_VERSION} AS bun

FROM composer:${COMPOSER_VERSION} AS vendor

FROM php:${PHP_VERSION}-cli-bookworm AS base

ARG WWWUSER=42080
ARG WWWGROUP=42080
ARG TZ=UTC
ARG SENTRY_RELEASE

ENV DEBIAN_FRONTEND=noninteractive \
    TERM=xterm-color \
    OCTANE_SERVER=swoole \
    TZ=${TZ} \
    USER=octane \
    ROOT=/var/www/html \
    COMPOSER_FUND=0 \
    COMPOSER_MAX_PARALLEL_HTTP=24 \
    SENTRY_RELEASE=${SENTRY_RELEASE}

WORKDIR ${ROOT}

SHELL ["/bin/bash", "-eou", "pipefail", "-c"]

RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime \
    && echo ${TZ} > /etc/timezone

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN dpkg --add-architecture i386; \
    apt-get update; \
    apt-get upgrade -yqq; \
    apt-get install -yqq --no-install-recommends --show-progress \
    apt-utils \
    curl \
    wget \
    vim \
    git \
    ncdu \
    procps \
    ca-certificates \
    supervisor \
    libsodium-dev \
    libbrotli-dev \
    # Custom packages
    nano unzip gnupg software-properties-common rsync \
    ffmpeg automake build-essential libasound2-dev libpulse-dev lame \
    jpegoptim optipng pngquant gifsicle webp libavif-bin \
    # Stuff for building with Byond
    gcc-multilib lib32stdc++6 zlib1g-dev:i386 libssl-dev:i386 pkg-config:i386 libstdc++6 libstdc++6:i386 \
    # Install PHP extensions
    && install-php-extensions \
    bz2 \
    pcntl \
    mbstring \
    bcmath \
    sockets \
    pgsql \
    pdo_pgsql \
    opcache \
    exif \
    pdo_mysql \
    zip \
    intl \
    gd \
    redis \
    rdkafka \
    memcached \
    igbinary \
    ldap \
    swoole \
    uv \
    vips \
    excimer \
    # Clean up
    && apt-get -y autoremove \
    && apt-get clean \
    && docker-php-source delete \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && rm /var/log/lastlog /var/log/faillog \
    # Supercronic
    && arch="$(uname -m)" \
    && case "$arch" in \
    armhf) _cronic_fname='supercronic-linux-arm' ;; \
    aarch64) _cronic_fname='supercronic-linux-arm64' ;; \
    x86_64) _cronic_fname='supercronic-linux-amd64' ;; \
    x86) _cronic_fname='supercronic-linux-386' ;; \
    *) echo >&2 "error: unsupported architecture: $arch"; exit 1 ;; \
    esac \
    && wget -q "https://github.com/aptible/supercronic/releases/download/v0.2.29/${_cronic_fname}" -O /usr/bin/supercronic \
    && chmod +x /usr/bin/supercronic \
    && mkdir -p /etc/supercronic \
    && echo "*/1 * * * * php ${ROOT}/artisan schedule:run --no-interaction" > /etc/supercronic/laravel

# Create user
RUN groupadd --force -g ${WWWGROUP} ${USER} \
    && useradd -ms /bin/bash --no-log-init --no-user-group -g ${WWWGROUP} -u ${WWWUSER} ${USER} \
    && chown -R ${WWWUSER}:${WWWGROUP} ${ROOT} /var/{log,run} \
    && chmod -R a+rw ${ROOT} /var/{log,run} \
    && cp ${PHP_INI_DIR}/php.ini-production ${PHP_INI_DIR}/php.ini

###########################################
# Custom root packages
##########################################

# Youtube-DLP
ADD --chown=${WWWUSER}:${WWWGROUP} --chmod=0755 https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp /usr/local/bin/

# Dectalk
RUN cd /usr/local/src && \
    curl -fsSL https://github.com/dectalk/dectalk/releases/download/2023-10-30/ubuntu-latest.tar.gz -o dectalk.tar.gz && \
    mkdir dectalk && \
    tar -xzf dectalk.tar.gz -C dectalk/ && \
    rm dectalk.tar.gz && \
    chmod -R +x dectalk && \
    ln -s /usr/local/src/dectalk/say /usr/local/bin/dectalk

##########################################

COPY --link --chown=${WWWUSER}:${WWWGROUP} --from=vendor /usr/bin/composer /usr/bin/composer
COPY --link --chown=${WWWUSER}:${WWWGROUP} --from=node /usr/local/bin /usr/local/bin
COPY --link --chown=${WWWUSER}:${WWWGROUP} --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --link --chown=${WWWUSER}:${WWWGROUP} --from=node /opt /opt
COPY --link --chown=${WWWUSER}:${WWWGROUP} --from=bun /usr/local/bin/bun /usr/local/bin/
COPY --link --chown=${WWWUSER}:${WWWGROUP} --from=bun /usr/local/bin/bunx /usr/local/bin/
COPY --link --chown=${WWWUSER}:${WWWGROUP} . .

###########################################

USER ${USER}

ENV NODE_ENV=production

RUN chmod -R a+rw storage \
    && composer install \
    --classmap-authoritative \
    --no-interaction \
    --no-ansi \
    --no-dev \
    && composer clear-cache \
    && bun install --frozen-lockfile \
    && bun run build \
    && rm -rf node_modules

COPY --link --chown=${WWWUSER}:${WWWGROUP} docker/deploy/supervisord.conf /etc/
COPY --link --chown=${WWWUSER}:${WWWGROUP} docker/deploy/supervisord.app.conf /etc/supervisor/conf.d/
COPY --link --chown=${WWWUSER}:${WWWGROUP} docker/deploy/php.ini ${PHP_INI_DIR}/conf.d/99-octane.ini
COPY --link --chown=${WWWUSER}:${WWWGROUP} --chmod=755 docker/deploy/start-container /usr/local/bin/start-container
COPY --link --chown=${WWWUSER}:${WWWGROUP} --chmod=755 docker/deploy/healthcheck /usr/local/bin/healthcheck

EXPOSE 8000 8080

VOLUME ["public"]

ENTRYPOINT ["start-container"]

HEALTHCHECK --start-period=5s --interval=2s --timeout=5s --retries=8 CMD healthcheck || exit 1
