# Next steps

This repository is intentionally only the initial scaffold. The next sensible build phases are:

The account/auth surface is now in place at a basic product level: registration, login, email verification, password reset, profile management, and email-based 2FA. The remaining work there is refinement and policy hardening rather than a blank start.

## 1) Canonical data model

- Define canonical identifiers and entity boundaries (council, supplier, contract, spend line, document).
- Establish naming and normalisation rules (e.g. supplier canonicalisation and aliases).
- Add provenance and audit primitives (import run, source file, row lineage).

## 2) Ingestion pipeline

- Introduce an import runner (queued jobs + retry strategy + idempotency).
- Add a source registry and adapter layer for council APIs, CSV exports, HTML tables, and document feeds.
- Store raw source files in object storage (MinIO locally) and record metadata in PostgreSQL.
- Parse and validate into staging tables before updating canonical tables.
- Prefer automated refresh routes for stable council sources, with manual ingestion kept as a fallback.
- Bootstrap the council registry from the official ONS / ArcGIS local authority dataset, then keep the internal council lookup route aligned with that registry.
- Surface ingestion counts, recent runs, and source freshness in the admin area so operators can see what needs attention quickly.

## 3) Read models

- Design denormalised read models for the public site (fast queries, predictable pagination).
- Consider materialised views or explicit read tables for common pages.
- Add caching where it is obviously beneficial (per-page fragments, hot endpoints).

## 4) Search indexing

- Decide what becomes searchable (suppliers, contracts, spend descriptions, documents).
- Define index schemas and update strategies (incremental updates from canonical tables).
- Use queued jobs to keep Meilisearch in sync.

## 5) Public council/supplier pages

- Council profile pages: identity, datasets available, latest imports, spend summaries.
- Supplier profile pages: aggregated spend, contracts, related councils, and search facets.
- Build with stable URLs and SEO in mind (Nuxt SSR when appropriate).
