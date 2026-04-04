# ADR-0005: Raw vs Normalised vs Derived Data

Status: accepted
Date: 2026-04-04

## Context

Council data is inconsistent. The same concept can appear under different names and formats across councils and years. Some datasets are corrected or republished. Some “facts” (for example supplier identity) require cautious interpretation.

For a civic transparency platform, trust depends on auditability:

- we must preserve what was published (and when)
- we must be able to reprocess and reproduce outputs
- we must avoid quietly overwriting history to “tidy up” the data

## Decision

Adopt an explicit separation of data layers:

1. **Raw source data (raw)**
  - The original artefacts and extracted fields as-observed.
  - Immutable once stored (append-only corrections are new records).
  - Always linked to a source artefact and capture metadata.

2. **Normalised structured data (canonical/normalised)**
  - Structured entities used for consistent interpretation across councils and time.
  - Contains stable internal IDs and explicit temporal validity where needed.
  - May include mappings from raw observations to canonical entities, with provenance and (where relevant) uncertainty.

3. **Derived data (read models / projections)**
  - Query-optimised, denormalised, or aggregated representations built from normalised data (and sometimes raw metadata).
  - Rebuildable and disposable (can be regenerated from upstream layers).
  - Used to serve read-heavy pages and APIs efficiently.

Derived outputs must never be treated as the system of record.

## Consequences

- Ingestion work must preserve raw observations even when normalisation exists (for example, preserve supplier name strings as published).
- Corrections should be handled by adding or updating normalised mappings and regenerating derived projections, not by overwriting raw records.
- User-facing features can offer both:
  - “as published” views (truth/audit)
  - “normalised/derived” views (comparability), clearly labelled
- Query strategy:
  - public endpoints should primarily read from derived read models for performance
  - investigative and audit tools may query raw/canonical layers with appropriate guardrails
- This approach increases storage and modelling work, but reduces the risk of misleading comparisons and makes the platform defensible when challenged.
