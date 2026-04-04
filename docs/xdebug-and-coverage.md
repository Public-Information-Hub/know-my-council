# Xdebug and coverage (backend)

KnowMyCouncil runs Laravel on the host machine in local development. This document explains how to use:

- **Xdebug** for interactive step debugging (requests and tests)
- **PCOV** (preferred) or **Xdebug** for generating PHPUnit coverage

Nothing in this document is intended for production use.

## When to use what

- Use **Xdebug** when you want to set breakpoints and step through code.
- Use **PCOV** when you want fast code coverage reports.

Coverage is slower with Xdebug than PCOV.

## Install Xdebug (host-run PHP)

The exact steps depend on how PHP is installed on your machine. Common approaches:

### Homebrew PHP + PECL (macOS)

```bash
pecl install xdebug
php -m | rg -i xdebug
```

Then add a minimal config (example; adjust as needed):

```ini
; php.ini or conf.d/xdebug.ini
xdebug.mode=debug,develop
xdebug.start_with_request=yes
xdebug.client_host=127.0.0.1
xdebug.client_port=9003
```

Restart any running PHP processes after enabling it.

## Confirm Xdebug is working

```bash
php --ri xdebug | rg -n \"xdebug.mode|xdebug.client_host|xdebug.client_port\" || true
```

## Using Xdebug with web requests

Start your IDE listener (VS Code/PhpStorm).

Run the backend:

```bash
make backend-dev
```

Then hit an endpoint such as:

- `http://127.0.0.1:8000/api/health`

### Path mapping notes

Because PHP runs on the host and the repository is local, path mapping is usually straightforward:

- your IDE project root should be the git repository root
- server paths will match local paths

If you run PHP inside a container in the future, you will need explicit path mappings.

## Using Xdebug with tests

To debug a single test, you can run PHPUnit with Xdebug enabled and your IDE listening.

Example (forces Xdebug debug mode for the command):

```bash
XDEBUG_MODE=debug php backend/vendor/bin/phpunit --filter ExampleTest
```

## Generating backend coverage

Coverage output is written to:

- `backend/coverage/html/` (HTML)
- `backend/coverage/clover.xml` (Clover XML)

From the repository root:

```bash
make backend-coverage
```

This uses:

- PCOV if installed, otherwise
- Xdebug if installed, otherwise it prints installation guidance

Open the HTML report:

```bash
make backend-coverage-open
```

