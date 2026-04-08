# Docker Server Deployment

This repository is intended to run on Docker for the server setup.

The current production shape is:

- Caddy as the public reverse proxy
- Laravel backend in a container
- Nuxt frontend in a container
- PostgreSQL, Redis, Meilisearch, and MinIO in containers
- a background queue worker and scheduler in separate containers

## Deployment files

- `deploy/docker-compose.prod.yml`
- `deploy/Caddyfile`
- `deploy/.env.example`

## High-level flow

1. Point `knowmycouncil.uk` and `www.knowmycouncil.uk` at the server IP.
2. Install Docker and the Compose plugin on the server.
3. Clone the repository.
4. Copy `deploy/.env.example` to `deploy/.env` and fill in any remaining production values.
5. Run `docker compose -f deploy/docker-compose.prod.yml up -d --build`.
6. Run the Laravel migrations once the database is healthy.

## Operational notes

- The scheduler container runs `php artisan schedule:work`.
- The queue worker container runs `php artisan queue:work`.
- MinIO is initialised with a `knowmycouncil` bucket.
- Caddy will request and renew TLS automatically once the domain points at the server and ports 80/443 are open.
- The public site is protected with HTTP Basic Auth while the build is in progress.
- Set `KMC_BASIC_AUTH_USER` and `KMC_BASIC_AUTH_HASH` in `deploy/.env` for the live credentials.
- If you want the homepage map to use Google Maps, set `NUXT_PUBLIC_GOOGLE_MAPS_API_KEY` and optionally `NUXT_PUBLIC_GOOGLE_MAPS_MAP_ID` in `deploy/.env`.

## Host hardening

- SSH is restricted to key-based root access only.
- Password and keyboard-interactive SSH authentication are disabled.
- X11 forwarding is disabled on the host.
- UFW is enabled with inbound access limited to `22`, `80`, and `443`.
- Fail2ban is enabled with an SSH jail.
- Unattended security upgrades are enabled on the server.
- Only Caddy publishes public ports from the Compose stack; the app services stay on the internal Docker network.
