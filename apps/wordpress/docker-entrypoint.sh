#!/bin/sh
set -eu

UPLOADS_DIRECTORY=/var/www/persistent-content/uploads

mkdir -p "$UPLOADS_DIRECTORY"
chown www-data:www-data "$UPLOADS_DIRECTORY"

exec docker-entrypoint.sh "$@"
