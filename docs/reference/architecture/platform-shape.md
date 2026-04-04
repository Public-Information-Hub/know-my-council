# Platform Shape (intended direction)

KnowMyCouncil is intended to be a read-heavy public transparency platform focused on English councils.

“Read-heavy” means most traffic is expected to be anonymous browsing, searching, and API queries. Writes exist, but are typically operational and bursty (imports, re-indexing, corrections, administrative changes).

This document describes the intended platform shape at a high level. It does not claim that all components are implemented today.

For the introductory architecture overview in `docs/`, see:

- [../../architecture.md](../../architecture.md)

## Major components

- **Backend:** Laravel for the API, administrative tooling (future), ingestion coordination (future), queues, and integrations.
- **Frontend:** Nuxt 3 for a public-facing site with a path to SSR/SEO-friendly pages and clean routing.
- **Primary database:** PostgreSQL for canonical records, auditability, and durable storage.
- **Operational cache/queue:** Redis for cache/session/queue backends (depending on configuration and maturity).
- **Search:** Meilisearch as a query-optimised index for user-facing search, faceting, and fast lookup experiences.
- **Object storage:** MinIO in local development as an S3-compatible object store for source artefacts and documents.

## Truth/ingestion layer vs read/query layer

The platform is expected to evolve with a deliberate separation between:

1. **Ingestion and “truth” layer**
  - Fetch and store source artefacts (files, HTML, PDFs, etc.) with provenance.
  - Parse and normalise into canonical entities.
  - Capture import runs, errors, and traceability (what happened, when, and why).

2. **Read/query layer**
  - Serve stable public pages and APIs with predictable reads.
  - Use denormalised read models where needed for performance.
  - Feed and query Meilisearch for user-facing search experiences.

This separation reduces the risk that operational complexity (imports, retries, parsing variance) leaks into public query performance or user-facing data guarantees.

## Service responsibilities (short)

These are the intended responsibilities at a high level:

- **PostgreSQL:** primary system of record for canonical entities, provenance/audit, and (later) read-optimised projections.
- **Redis:** cache and queue backend for operational work (imports, indexing, document processing).
- **Meilisearch:** user-facing search index for fast search/ranking and (where appropriate) filtering/facets.
- **Object storage (MinIO locally):** source artefacts and document storage with provenance.

Search and derived views must remain reproducible and traceable back to inputs.

## Open-source and public-interest considerations

The architecture should remain:

- **auditable:** clear provenance, reproducible transforms, visible reasoning
- **safe for volunteers:** manageable dependency surface area and predictable maintenance costs
- **credible:** no over-claiming of accuracy or completeness; uncertainty is allowed and should be explicit
- **non-partisan:** engineering should support evidence-led conclusions, not narrative
