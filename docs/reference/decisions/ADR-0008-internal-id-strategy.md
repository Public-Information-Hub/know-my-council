# ADR-0008: Internal ID Strategy

Status: accepted
Date: 2026-04-04

## Context

Council datasets contain unstable identifiers:

- names vary by publisher and over time
- external codes can change, be corrected, or be missing
- supplier references are often free-text

If we treat names or external codes as primary keys, we will overwrite history, mis-link records, and make the platform hard to audit. We also need temporal modelling to handle reorganisations and boundary change without breaking continuity.

## Decision

Adopt immutable internal identifiers as the primary identity mechanism:

- Use an internal, immutable ID for core concepts (authorities, suppliers, contracts, documents) that is never reused.
- Treat names and external codes as **attributes** and **observations** linked to internal identities, with provenance.
- Model external identifiers as first-class links that can vary over time:
  - allow multiple identifiers per entity
  - allow validity periods and provenance for identifier assignments
- Where sources use ONS/LAD-style codes (or similar), store them as external identifiers with temporal validity rather than assuming they are stable keys.

This decision is consistent with the temporal authority/geography modelling approach (ADR-0004).

## Consequences

- Normalisation work must include mapping from raw observations (names/codes) to internal IDs, with evidence and the ability to represent uncertainty.
- Imports should store as-published identifiers and names even when a mapping exists.
- Future public pages and APIs can provide stable URLs based on internal IDs while still displaying as-published identifiers for auditability.
- This increases up-front modelling work, but reduces long-term breakage and makes corrections possible without rewriting history.

