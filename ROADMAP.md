# Roadmap

This roadmap is milestone-based rather than date-based. It reflects intended direction, not a promise of delivery.

## Phase 0: Foundation (current)

- Local development foundation (Laravel + Nuxt + infra services)
- Basic health/version endpoints and operational smoke tests
- Evidence and contribution principles documented

## Phase 1: Canonical data model

- Council and supplier identity boundaries and identifiers
- Provenance primitives (import runs, source files, lineage)
- Normalisation rules and correction mechanisms

## Phase 2: Ingestion pipelines

- Import orchestration and idempotent job execution
- Raw source retention in object storage (MinIO locally)
- Staging and validation prior to canonical updates

## Phase 3: Read models and public pages

- Fast read models for common transparency queries
- Council pages (profile, datasets, latest imports, spend summaries)
- Supplier pages (aggregated spend, relationships, council links)

## Phase 4: Search and comparison

- Meilisearch index schemas
- Incremental indexing pipelines
- Cross-council comparison and filtering

## Phase 5: Evidence and community workflows

- Source submission and review workflow (planning document today)
- Editorial/moderation direction (planning document today)
- Contributor reputation and trust signals (planning document today)

## Phase 6: FOI / EIR support (planning)

- Identify missing information and transparency gaps
- Help draft FOI/EIR requests and track responses
- Store released documents and extract structured information

See also:

- [docs/next-steps.md](docs/next-steps.md)
- [docs/community-and-editorial-model.md](docs/community-and-editorial-model.md)
- [docs/foi-and-eir-direction.md](docs/foi-and-eir-direction.md)

