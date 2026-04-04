# Architecture (initial scaffold)

KnowMyCouncil is intended to be a read-heavy public transparency platform.

For a more durable architecture reference (intended direction), see:

- [reference/architecture/platform-shape.md](reference/architecture/platform-shape.md)

In practice this means:

- The majority of traffic is expected to be anonymous reads (pages and API queries).
- Write activity exists, but tends to be bursty and operational (imports, re-indexing, data corrections).
- The system should favour stable URLs, caching, and predictable query patterns.

## Separation: ingestion/truth vs read/query

The architecture is designed around a clean separation:

1. Ingestion and "truth" layer
- Fetch, parse, validate, and normalise datasets.
- Preserve provenance (where a row came from, when it was imported, and any transformation steps).
- Keep auditability and re-runs in mind.

2. Read/query layer
- Serve the public site and API endpoints with fast, predictable reads.
- Support search and faceting.
- Prefer denormalised read models where necessary.

This separation helps avoid coupling public query performance to the operational complexity of imports.

## Role of each service

### PostgreSQL

Primary system of record.

Expected uses (future):

- Canonical entities (councils, suppliers, contracts)
- Import runs and audit trails
- Materialised/denormalised read models

### Redis

Operational fast-path.

Used for:

- Cache
- Session storage (future admin)
- Queue backend for background jobs

### Meilisearch

Search index optimised for fast, user-facing search.

Used for:

- Full-text search and ranking
- Quick filtering/facets (when appropriate)

### MinIO (S3-compatible)

Local development object storage.

Used for:

- Storing downloaded source files
- Storing parsed artefacts
- Serving or generating document assets

MinIO is used locally because it mirrors an S3-style storage API without requiring a cloud account.

## Why Laravel + Nuxt

- Laravel provides a stable and productive backend foundation (routing, queues, storage, mail, and a clear structure for future admin tooling).
- Nuxt provides an excellent base for a fast public-facing site with a clear path to SSR/SEO-friendly pages.

## Future expectations

This scaffold expects additional layers to be introduced later:

- Importers/crawlers to fetch spend data, contract notices, and supporting documents.
- A structured import pipeline with retry and observability.
- Read models optimised for public queries.
- Search indexing flows (PostgreSQL -> Meilisearch), likely via queued jobs.
