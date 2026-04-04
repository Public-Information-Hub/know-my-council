#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/../backend"

php artisan test
