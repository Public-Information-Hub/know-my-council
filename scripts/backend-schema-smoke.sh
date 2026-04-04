#!/usr/bin/env bash
set -euo pipefail

# Schema smoke test: runs migrations against PostgreSQL to catch DDL issues early.
#
# This intentionally uses a separate database by default because `migrate:fresh`
# drops all tables. Do not point this at your normal dev database.

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKEND_DIR="${ROOT_DIR}/backend"

cd "${BACKEND_DIR}"

export APP_ENV="${APP_ENV:-testing}"
export APP_KEY="${APP_KEY:-base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=}"

export DB_CONNECTION="${DB_CONNECTION:-pgsql}"
export DB_HOST="${DB_HOST:-127.0.0.1}"
export DB_PORT="${DB_PORT:-5432}"
export DB_USERNAME="${DB_USERNAME:-knowmycouncil}"
export DB_PASSWORD="${DB_PASSWORD:-knowmycouncil}"
export DB_DATABASE="${DB_DATABASE:-knowmycouncil_schema_smoke}"

# Avoid requiring Redis/mail/etc for this check.
export CACHE_STORE="${CACHE_STORE:-array}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"
export SESSION_DRIVER="${SESSION_DRIVER:-array}"
export MAIL_MAILER="${MAIL_MAILER:-array}"

if [[ "${DB_CONNECTION}" != "pgsql" ]]; then
  echo "Schema smoke test requires DB_CONNECTION=pgsql (got: ${DB_CONNECTION})." >&2
  exit 2
fi

if [[ "${DB_DATABASE}" == "knowmycouncil" ]]; then
  echo "Refusing to run schema smoke against DB_DATABASE=knowmycouncil." >&2
  echo "Set DB_DATABASE to a dedicated smoke database (e.g. knowmycouncil_schema_smoke)." >&2
  exit 2
fi

php artisan migrate:fresh --force --no-interaction

echo "Schema smoke succeeded against ${DB_HOST}:${DB_PORT}/${DB_DATABASE}."

