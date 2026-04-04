# ADR-0014: Data Correction and Versioning Strategy

Status: accepted
Date: 2026-04-04

## Context

Council datasets are often corrected, republished, or clarified over time. The platform will also make normalisation decisions (for example identity mappings) that can be wrong and later corrected.

If the system overwrites records destructively, it becomes impossible to audit what changed, when, and why. That undermines trust and makes disputes hard to resolve.

## Decision

Adopt a non-destructive correction strategy with auditability:

- **No destructive overwrites of raw artefacts or raw observations.**
  - If a council republishes a file, store it as a new artefact with its own capture metadata.
- **Version history where meaning changes:**
  - for canonical/normalised entities and mappings, record changes with timestamps and (where practical) “why” notes and provenance.
- **Correction vs replacement:**
  - corrections update normalised mappings or canonical records while retaining prior versions
  - replacements are explicit (for example “supersedes import run X” or “supersedes mapping Y”)
- **Audit trails are first-class:**
  - it must be possible to explain what changed and reproduce the state at a point in time (at least in principle)

Derived read models and search indices are rebuildable outputs and should be regenerated after corrections.

## Consequences

- Storage usage increases, but the platform remains defensible and reviewable.
- Tooling must support:
  - “what changed since last month?”
  - “why does this record look different now?”
  - “which source supports the corrected mapping?”
- Public UI should, over time, be able to show correction history in a calm way (without implying wrongdoing).

