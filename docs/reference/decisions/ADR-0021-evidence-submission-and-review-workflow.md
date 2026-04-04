# ADR-0021: Evidence Submission and Review Workflow

Status: accepted
Date: 2026-04-04

## Context

Evidence is the foundation of trust. The platform must support evidence submission without letting unreviewed content appear as verified fact. The workflow must also be practical for volunteer maintainers and scalable over time.

This ADR focuses on workflow states and responsibilities, not implementation UI details.

## Decision

Adopt a structured evidence submission model with explicit review states.

Submission types (conceptual):

- **Source artefact:** a file/capture/URL reference stored with provenance (ADR-0007, ADR-0016).
- **Evidence claim:** a structured claim linked to one or more source artefacts (for example: “this spend line item exists in this dataset”, “this contract is listed in this register”).
- **Normalisation suggestion:** a proposal to map raw observations to canonical entities (ADR-0005, ADR-0008).

Review states (conceptual):

- `submitted` (not yet reviewed)
- `needs-info` (missing provenance, unclear claim, incomplete metadata)
- `in-review`
- `accepted` (meets evidence/provenance standards)
- `rejected` (does not meet standards; keep record and rationale)
- `restricted` (contains sensitive content; limited visibility) (ADR-0024)

Verification expectations:

- accepted items must link to stored artefacts or captured primary sources
- uncertainty must be explicitly represented rather than forced into a confident mapping
- derived interpretations must be clearly separated from the evidence layer

## Consequences

- The platform needs consistent data structures to link claims to artefacts and import runs (ADR-0006/ADR-0007).
- Moderation and editorial roles require clear separation (ADR-0013, ADR-0020).
- Public pages should preferentially show `accepted` evidence, with other states visible only in appropriate contributor contexts.

