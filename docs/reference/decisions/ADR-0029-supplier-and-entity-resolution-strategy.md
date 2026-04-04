# ADR-0029: Supplier and Entity Resolution Strategy

Status: accepted
Date: 2026-04-04

## Context

Entity resolution (for example supplier identity) is a major risk area for misinformation. Council datasets frequently contain free-text supplier names, inconsistent spellings, abbreviations, and reused names across unrelated entities.

Incorrect joins can create misleading narratives (for example attributing spend to the wrong organisation). For a civic platform, we must prefer auditability and conservative matching over aggressive deduplication.

## Decision

Adopt a conservative, evidence-backed entity resolution approach with explicit lineage and reversibility.

Rules:

- Preserve **raw observations** (as-published supplier name strings, identifiers) and never treat them as canonical identity (ADR-0005).
- Use **immutable internal IDs** for entities (ADR-0008).
- Treat matches as **mappings** with provenance and the ability to represent uncertainty (ADR-0007, ADR-0012).
- Support explicit operations:
  - **merge** (two internal entities are determined to represent the same real-world entity)
  - **split** (a previously merged entity is corrected into multiple entities)
  - **alias/aka** (store alternative names as observed, with provenance)
- Track **lineage** for merges/splits so the history is auditable and reversible (aligned with ADR-0014).
- Avoid “auto-merge” without strong evidence. Automated suggestions may exist later, but publication of merges requires review.

This ADR defines the strategy and safety constraints. It does not define a matching algorithm.

## Consequences

- The platform may show duplicate-looking suppliers until evidence supports resolution; this is safer than incorrect consolidation.
- Review tooling must support merge/split workflows and show provenance for identity decisions.
- Public pages should clearly distinguish as-published names from normalised identities and disclose uncertainty where present.

