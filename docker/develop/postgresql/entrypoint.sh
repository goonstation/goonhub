#!/usr/bin/env bash

find /var/lib/postgresql -group 999 -exec chgrp -h postgres {} \;
find /var/lib/postgresql -user 999 -exec chown -h postgres {} \;

timescaledb-tune -yes -quiet -color false

/usr/local/bin/docker-entrypoint.sh "$@"
