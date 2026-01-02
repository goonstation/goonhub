ARG PHP_VERSION=8.4
ARG COMPOSER_VERSION=2.8
ARG NODE_VERSION=latest
ARG BUN_VERSION=latest

FROM node:${NODE_VERSION} AS node

FROM oven/bun:${BUN_VERSION} AS bun

FROM composer:${COMPOSER_VERSION} AS vendor

FROM php:${PHP_VERSION}-cli-bookworm AS base

ARG WWWUSER=1000
ARG WWWGROUP=1000
ARG TZ=UTC

ENV DEBIAN_FRONTEND=noninteractive \
    TERM=xterm-color \
    TZ=${TZ} \
    USER=sail \
    ROOT=/var/www/html \
    COMPOSER_FUND=0 \
    COMPOSER_MAX_PARALLEL_HTTP=24

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
    locales \
    # For SSH signing
    openssh-client \
    # Custom packages
    nano zip unzip gnupg software-properties-common rsync \
    ffmpeg automake build-essential libasound2-dev libpulse-dev lame \
    jpegoptim optipng pngquant gifsicle webp libavif-bin \
    # Local dev packages
    gnupg2 sqlite3 libcap2-bin libpng-dev dnsutils librsvg2-bin fswatch \
    # Stuff for building with Byond
    gcc-multilib lib32stdc++6 zlib1g-dev:i386 libssl-dev:i386 pkg-config:i386 libstdc++6 libstdc++6:i386 libcurl4:i386 \
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
    memcached \
    igbinary \
    ldap \
    swoole-6.1.4 \
    uv \
    vips \
    excimer \
    xdebug \
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

# Set the locale
RUN sed -i '/en_US.UTF-8/s/^# //g' /etc/locale.gen && \
    locale-gen
ENV LANG=en_US.UTF-8 \ LANGUAGE=en_US \ LC_ALL=en_US.UTF-8

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

# Github "gh"
# RUN mkdir -p -m 755 /etc/apt/keyrings \
#     && out=$(mktemp) && wget -nv -O$out https://cli.github.com/packages/githubcli-archive-keyring.gpg \
#     && cat $out | tee /etc/apt/keyrings/githubcli-archive-keyring.gpg > /dev/null \
#     && chmod go+r /etc/apt/keyrings/githubcli-archive-keyring.gpg \
#     && echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main" | tee /etc/apt/sources.list.d/github-cli.list > /dev/null \
#     && apt update \
#     && apt install gh -y \
#     && gh alias set -s run-watch 'gh workflow run $1 && sleep 3 && gh run list --json databaseId --workflow $1 -L 1 -q ".[0].databaseId" | xargs -I {} gh run watch {}'

##########################################

COPY --link --chown=${WWWUSER}:${WWWGROUP} --from=vendor /usr/bin/composer /usr/bin/composer
COPY --link --chown=${WWWUSER}:${WWWGROUP} --from=node /usr/local/bin /usr/local/bin
COPY --link --chown=${WWWUSER}:${WWWGROUP} --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --link --chown=${WWWUSER}:${WWWGROUP} --from=node /opt /opt
COPY --link --chown=${WWWUSER}:${WWWGROUP} --from=bun /usr/local/bin/bun /usr/local/bin/
COPY --link --chown=${WWWUSER}:${WWWGROUP} --from=bun /usr/local/bin/bunx /usr/local/bin/

###########################################

USER ${USER}

COPY --link --chown=${WWWUSER}:${WWWGROUP} supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY --link --chown=${WWWUSER}:${WWWGROUP} php.ini ${PHP_INI_DIR}/conf.d/99-sail.ini
COPY --link --chown=${WWWUSER}:${WWWGROUP} --chmod=755 start-container /usr/local/bin/start-container

EXPOSE 80/tcp

ENTRYPOINT ["start-container"]
