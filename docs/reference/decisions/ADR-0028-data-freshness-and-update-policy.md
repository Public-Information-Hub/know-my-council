# ADR-0028: Data Freshness and Update Policy

Status: accepted
Date: 2026-04-04

## Context

Council datasets are published on varying cadences and are often late, missing, or corrected. A transparency platform must avoid implying that data is current when it is not.

“Stale” data is not necessarily wrong, but it changes how it should be interpreted and compared.

## Decision

Adopt an explicit data freshness policy based on dataset metadata and import history.

Key rules:

- Each dataset (or dataset release) records its **reporting period** and **capture/publish dates** (ADR-0015).
- Freshness is assessed relative to:
  - expected update cadence (if known) or historical cadence (if inferred later)
  - the end of the reporting period
  - the most recent successful import run (ADR-0006)
- The platform should explicitly mark datasets as:
  - `current` (within expected range)
  - `stale` (outside expected range)
  - `unknown` (cadence/reporting period unclear)
- Re-ingestion strategy:
  - support periodic re-ingestion for known cadences
  - support manual backfills and reprocessing from stored artefacts
  - treat republished/corrected source files as new artefacts and rerun normalisation/projections (ADR-0014, ADR-0016)

This ADR sets the policy boundary and labelling expectations. It does not define fixed cadences for specific councils.

## Consequences

- Public pages can show “last updated” and “coverage period” without implying real-time completeness.
- Ingestion tooling can prioritise stale datasets and track gaps as explicit work items (aligned with ADR-0011).
- Comparisons and rankings should consider freshness/coverage metadata to avoid misleading conclusions.

