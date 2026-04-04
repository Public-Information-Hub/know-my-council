# Local development

KnowMyCouncil local development runs application code on the host machine:

- Laravel runs via `php artisan serve`
- Nuxt runs via `npm run dev`

Infrastructure services run in Docker:

- PostgreSQL
- Redis
- Meilisearch
- MinIO
- Mailpit

This keeps the dev loop fast while still matching production-style dependencies.

## Prerequisites

- PHP (compatible with the Laravel version in `backend/`)
- Composer
- Node.js + npm
- Docker Desktop (or Docker Engine + Compose)

## Start/stop infra

Start infra:

```bash
make infra-up
```

Stop infra:

```bash
make infra-down
```

Tail logs:

```bash
make infra-logs
```

## Backend workflow (Laravel)

Create a local env file:

```bash
cp backend/.env.example backend/.env
```

Run migrations:

```bash
make backend-migrate
```

Run the API:

```bash
make backend-dev
```

Run a queue worker (separate terminal):

```bash
make backend-queue
```

Smoke test commands:

- `php artisan kmc:queue-smoke-test`
- `php artisan kmc:storage-smoke-test`

API endpoints:

- `GET /api/health`
- `GET /api/version`

## Frontend workflow (Nuxt 3)

Create a local env file:

```bash
cp frontend/.env.example frontend/.env
```

Run Nuxt:

```bash
make frontend-dev
```

The status page calls the Laravel API configured in `NUXT_PUBLIC_API_BASE_URL`.

Typecheck and lint:

```bash
cd frontend
npm run typecheck
npm run lint
```

## Common issues

- If ports are already in use (5432/6379/7700/9000/9001/8025/1025/8000/3000), stop the conflicting service or adjust ports in `infra/docker-compose.yml` and your `.env` files.
- If storage tests fail, confirm infra is running and MinIO has initialised the `knowmycouncil` bucket.
