#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/../backend"

mkdir -p coverage/html

if php -m | rg -qi '^pcov$'; then
  echo "Using PCOV for coverage"
  php -d pcov.enabled=1 -d pcov.directory=app vendor/bin/phpunit \
    --coverage-text \
    --coverage-clover coverage/clover.xml \
    --coverage-html coverage/html
  exit 0
fi

if php -m | rg -qi '^xdebug$'; then
  echo "Using Xdebug for coverage"
  XDEBUG_MODE=coverage php vendor/bin/phpunit \
    --coverage-text \
    --coverage-clover coverage/clover.xml \
    --coverage-html coverage/html
  exit 0
fi

cat <<'MSG'
No PHP coverage driver detected.

To generate coverage you need either:
- PCOV (recommended for coverage speed), or
- Xdebug (recommended for step debugging; can also do coverage).

Mac (Homebrew PHP) options:
- pecl install pcov
- pecl install xdebug

After installing, confirm with:
- php -m | rg -i 'pcov|xdebug'

Then re-run:
- make backend-coverage
MSG

exit 1
