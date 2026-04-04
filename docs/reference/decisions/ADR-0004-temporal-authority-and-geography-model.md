# ADR-0004: Temporal Authority and Geography Model

Status: accepted
Date: 2026-04-04

## Context

English local authorities reorganise over time. Names, codes, and boundaries change. Datasets are published inconsistently and often reflect the publisher’s organisational view at the time.

A transparency platform that overwrites identities or uses external codes as primary keys will:

- misrepresent historic records
- produce misleading comparisons
- lose provenance and auditability

## Decision

Adopt a modelling approach that:

- uses stable internal identifiers for authority concepts
- models time-bounded authority “versions” (names/codes/boundary associations as-of a period)
- treats external identifiers as attributes with provenance and, where needed, temporal validity
- represents reorganisations with explicit lineage relationships (merge/split/rename) that can be evidence-backed and time-scoped
- separates “as published” truth from any normalised/derived comparison views

The initial canonical guidance lives in:

- `docs/reference/data-model/temporal-authority-and-geography-modelling.md`

## Consequences

- Schema work must support temporal validity and lineage, not just current-state records.
- Public UI and APIs will need to disclose which basis is used for comparisons (including uncertainty).
- Import pipelines should preserve as-published identifiers and names as observations, even when normalisation exists.

