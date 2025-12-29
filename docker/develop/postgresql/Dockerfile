ARG PG_VERSION=18
ARG PGDATA="${PGDATA:-/var/lib/postgresql/data}"
ARG UID=1000
ARG GID=1000

# Build postgres image that we're upgrading from
FROM debian:trixie-slim AS pg17

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        gnupg \
        postgresql-common \
        apt-transport-https \
        lsb-release \
        wget \
        ca-certificates \
    && /usr/share/postgresql-common/pgdg/apt.postgresql.org.sh -y \
    && echo "deb https://packagecloud.io/timescale/timescaledb/debian/ $(lsb_release -c -s) main" | tee /etc/apt/sources.list.d/timescaledb.list \
    && wget --quiet -O - https://packagecloud.io/timescale/timescaledb/gpgkey | gpg --dearmor -o /etc/apt/trusted.gpg.d/timescaledb.gpg \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        timescaledb-2-postgresql-17 \
        postgresql-client-17 \
    && rm -rf /var/lib/apt/lists/*

# Build postgres image that we're upgrading to
FROM debian:trixie-slim AS pg

ARG PG_VERSION

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        gnupg \
        postgresql-common \
        apt-transport-https \
        lsb-release \
        wget \
        ca-certificates \
    && /usr/share/postgresql-common/pgdg/apt.postgresql.org.sh -y \
    && echo "deb https://packagecloud.io/timescale/timescaledb/debian/ $(lsb_release -c -s) main" | tee /etc/apt/sources.list.d/timescaledb.list \
    && wget --quiet -O - https://packagecloud.io/timescale/timescaledb/gpgkey | gpg --dearmor -o /etc/apt/trusted.gpg.d/timescaledb.gpg \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        timescaledb-2-postgresql-${PG_VERSION} \
        postgresql-client-${PG_VERSION} \
    && rm -rf /var/lib/apt/lists/*

# Build pgautoupgrade image that will perform the upgrade
FROM docker.io/pgautoupgrade/pgautoupgrade:${PG_VERSION}-debian AS pgautoupgrade

ARG PG_VERSION

RUN --mount=type=bind,from=pg,source=/usr/lib,target=/mnt/pg${PG_VERSION}_lib \
    cp -rn /mnt/pg${PG_VERSION}_lib/* /usr/lib/

RUN --mount=type=bind,from=pg,source=/usr/local,target=/mnt/pg${PG_VERSION}_local \
    cp -rn /mnt/pg${PG_VERSION}_local/* /usr/local/

RUN --mount=type=bind,from=pg,source=/etc/alternatives,target=/mnt/pg${PG_VERSION}_etc_alternatives \
    cp -rn /mnt/pg${PG_VERSION}_etc_alternatives/postgresql-${PG_VERSION}-* /etc/alternatives/ || true

COPY --from=pg /usr/lib/postgresql/${PG_VERSION} /usr/lib/postgresql/${PG_VERSION}
COPY --from=pg /usr/share/postgresql/${PG_VERSION} /usr/share/postgresql/${PG_VERSION}

COPY --from=pg17 /usr/lib/postgresql/17 /usr/lib/postgresql/17
COPY --from=pg17 /usr/share/postgresql/17 /usr/share/postgresql/17

# Create symlinks for each version (except latest)
# Install runtime dependencies, clean up build tools & caches in single layer
RUN rm -rf /usr/local-pg17 \
    && ln -s /usr/lib/postgresql/17 /usr/local-pg17 \
    # Install runtime dependencies
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        gnupg \
        apt-transport-https \
        lsb-release \
        wget \
        ca-certificates \
    && echo "deb https://packagecloud.io/timescale/timescaledb/debian/ $(lsb_release -c -s) main" | tee /etc/apt/sources.list.d/timescaledb.list \
    && wget --quiet -O - https://packagecloud.io/timescale/timescaledb/gpgkey | gpg --dearmor -o /etc/apt/trusted.gpg.d/timescaledb.gpg \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        timescaledb-tools \
        # For pg_stat_statements extension
        libdbd-pg-perl \
        libdbi-perl \
    # Generate only the required locale
    && locale-gen en_US.UTF-8 \
    # Remove build-only packages
    && apt-get purge -y --auto-remove \
        gnupg \
        apt-transport-https \
        lsb-release \
        wget \
    # Clean up apt caches
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    # Remove documentation and man pages
    && rm -rf /usr/share/doc/* \
    && rm -rf /usr/share/man/* \
    && rm -rf /usr/share/info/* \
    && rm -rf /usr/share/lintian/* \
    && rm -rf /usr/share/linda/* \
    # Remove unused locales (keep only en_US)
    && find /usr/share/locale -mindepth 1 -maxdepth 1 ! -name 'en_US*' ! -name 'en' ! -name 'locale.alias' -exec rm -rf {} + 2>/dev/null || true \
    # Remove apt lists and caches
    && rm -rf /var/cache/apt/* \
    && rm -rf /var/log/* \
    && rm -rf /tmp/* \
    && rm -rf /var/tmp/*

# Build the final image that will be used to run the container
# (Just the pgautoupgrade image but squashed to save space)
FROM scratch
ARG PG_VERSION
ARG PGDATA
ARG UID
ARG GID

COPY --from=pgautoupgrade / /
COPY ./refresh-collation.sh /usr/local/bin/refresh-collation.sh
COPY ./entrypoint.sh /usr/local/bin/custom-entrypoint.sh

RUN chmod +x /usr/local/bin/refresh-collation.sh /usr/local/bin/custom-entrypoint.sh \
    # Change the user and group of the postgres user to the provided UID and GID
    && usermod -u ${UID} postgres \
    && groupmod -g ${GID} postgres \
    && find / -xdev -group 999 -exec chgrp -h postgres {} \; \
    && find / -xdev -user 999 -exec chown -h postgres {} \;

ENV \
    PGTARGET=${PG_VERSION} \
    PGDATA=${PGDATA} \
    PATH="$PATH:/usr/lib/postgresql/${PG_VERSION}/bin" \
    LC_ALL="en_US.UTF-8"
WORKDIR /var/lib/postgresql
ENTRYPOINT ["/usr/local/bin/custom-entrypoint.sh"]
CMD ["postgres"]
