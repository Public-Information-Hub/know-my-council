#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKEND_DIR="${ROOT_DIR}/backend"

cd "${BACKEND_DIR}"

export APP_KEY="${APP_KEY:-base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=}"

cleanup() {
  if [[ "${KMC_CREATED_TEST_ENV:-0}" == "1" ]]; then
    rm -f .env.testing
  fi
}

trap cleanup EXIT

if [[ ! -f .env.testing ]]; then
  cp .env.example .env.testing
  KMC_CREATED_TEST_ENV=1
fi

php artisan test
