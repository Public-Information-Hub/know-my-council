# KnowMyCouncil

KnowMyCouncil is a production-minded starting point for a read-heavy public transparency platform focused on English local councils.

This repository is intentionally an initial scaffold only. It provides a clean local development foundation and a structure that can scale into ingestion pipelines, canonical "truth" storage, and optimised read/query models.

## Stack

- Backend: Laravel (API + future admin)
- Frontend: Nuxt 3 (public site + future admin UI)
- Database: PostgreSQL
- Cache/session/queue: Redis
- Search: Meilisearch
- Object storage: MinIO (S3-compatible)
- Local mail testing: Mailpit
- Local infrastructure: Docker Compose (services only; app code runs on the host)

## Repository structure

- `backend/` Laravel application
- `frontend/` Nuxt 3 application
- `infra/` Docker Compose for local dev services
- `docs/` Project documentation
- `scripts/` Convenience scripts

## Quickstart (local development)

### 1) Start infrastructure services

From the repo root:

```bash
make infra-up
```

This starts:

- PostgreSQL on `localhost:5432`
- Redis on `localhost:6379`
- Meilisearch on `http://127.0.0.1:7700`
- MinIO on `http://127.0.0.1:9000` (console `http://127.0.0.1:9001`)
- Mailpit UI on `http://127.0.0.1:8025` (SMTP `127.0.0.1:1025`)

MinIO is initialised with a bucket called `knowmycouncil`.

### 2) Configure environment files

Backend:

```bash
cp backend/.env.example backend/.env
```

Frontend:

```bash
cp frontend/.env.example frontend/.env
```

### 3) Run the backend (Laravel)

```bash
make backend-dev
```

In a separate terminal, run a queue worker:

```bash
make backend-queue
```

Migrations:

```bash
make backend-migrate
```

Useful endpoints:

- `GET http://127.0.0.1:8000/api/health`
- `GET http://127.0.0.1:8000/api/version`

### 4) Run the frontend (Nuxt 3)

```bash
make frontend-dev
```

Frontend pages:

- `http://127.0.0.1:3000/` landing page
- `http://127.0.0.1:3000/status` status page (calls Laravel API)
- `http://127.0.0.1:3000/admin` admin placeholder

### 5) Useful URLs

```bash
make urls
```

## Key environment variables

### Backend (`backend/.env`)

- `DB_CONNECTION=pgsql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=5432`
- `DB_DATABASE=knowmycouncil`
- `DB_USERNAME=knowmycouncil`
- `DB_PASSWORD=knowmycouncil`

- `CACHE_STORE=redis`
- `SESSION_DRIVER=redis`
- `QUEUE_CONNECTION=redis`

- `SCOUT_DRIVER=meilisearch`
- `MEILISEARCH_HOST=http://127.0.0.1:7700`
- `MEILISEARCH_KEY=local-meili-master-key`

- `FILESYSTEM_DISK=minio`
- `MINIO_ENDPOINT=http://127.0.0.1:9000`
- `MINIO_ACCESS_KEY_ID=minio`
- `MINIO_SECRET_ACCESS_KEY=miniosecret`
- `MINIO_BUCKET=knowmycouncil`

- `MAIL_MAILER=smtp`
- `MAIL_HOST=127.0.0.1`
- `MAIL_PORT=1025`

### Frontend (`frontend/.env`)

- `NUXT_PUBLIC_API_BASE_URL=http://127.0.0.1:8000/api`

## Direction (high level)

KnowMyCouncil is expected to be read-heavy:

- Many public page views and API reads
- Less frequent but potentially expensive ingestion/import workloads

The codebase is organised to support a clean separation between:

- Ingestion and "truth" storage (imports, raw data, audit trails)
- Read/query layer (denormalised views, search indices, public-facing endpoints)

See [docs/architecture.md](docs/architecture.md) and [docs/local-development.md](docs/local-development.md).

## Notes

- Docker Compose is used for local services only. Laravel and Nuxt run on the host.
- Use en_GB English for docs, comments, and UI placeholders.
