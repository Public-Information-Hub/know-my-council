# ADR-0010: Read Model Strategy

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil is expected to be read-heavy with many anonymous users. Public pages will need fast, predictable responses for:

- council summary pages
- supplier pages
- spend/contract summaries and breakdowns
- rankings and comparisons

Directly querying canonical/normalised tables for every request is likely to become expensive and complex as the dataset grows. Many queries are naturally “report-like” aggregations that benefit from precomputation.

## Decision

Adopt a read model approach based on explicit projections:

- Treat read models as **derived** data that is rebuilt from canonical/normalised layers.
- Use **projection jobs** (queued tasks) to build and refresh read models and summary tables.
- Accept **eventual consistency**:
  - ingestion writes to raw/canonical layers
  - projections update asynchronously
  - the public site may lag behind ingestion by a bounded delay
- Keep read models separated from canonical entities to avoid corrupting the source of truth.

Examples of intended read models (not yet implemented):

- “top suppliers for a council over period X”
- monthly spend summaries and category breakdowns
- contract counts and values by supplier/authority
- document availability and coverage summaries

## Consequences

- The project must define projection boundaries and refresh triggers (per import run, scheduled rebuilds, manual rebuilds).
- Projections must be reproducible and traceable back to canonical data (and therefore to provenance).
- Public endpoints should prefer read models for performance and predictable query patterns.
- This increases system complexity (jobs and projections), but it is aligned with the platform’s read-heavy nature and improves reliability for public users.

