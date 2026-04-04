# ADR-0007: Source of Truth and Provenance Model

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil is a transparency platform. Users (and councils) must be able to challenge outputs. That requires traceability: the system must be able to show how a number, contract, or document reference was produced from primary sources.

Provenance is also operationally important: import failures, reprocessing, and corrections are only safe when inputs and transformations are recorded.

## Decision

Adopt a provenance model where:

- **Raw artefacts are retained** as first-class objects (files, captures, downloads), stored in object storage (MinIO locally; S3-compatible in future deployments).
- **Every ingestable record can be linked back** to:
  - the source artefact(s) it came from
  - the import run that processed it
  - key capture metadata (where/when/how obtained)
- **Import runs** record:
  - inputs (artefact references, URLs, hashes where practical)
  - start/end timestamps and status
  - warnings/errors with enough detail to reproduce and debug
- **Derived outputs** (read models, search documents) are traceable:
  - they must reference upstream canonical records, which in turn reference provenance
  - where aggregation occurs, the basis should be reproducible (inputs and query logic)
- **Missing provenance is explicit:**
  - if we cannot link a record to a source artefact, it should be marked as such and treated as lower trust until resolved

The truth of “what was published” is anchored in stored artefacts and raw observations, not in derived projections.

## Consequences

- Storage and retention become core infrastructure concerns, not an afterthought.
- Data correction workflows must improve provenance links rather than rewriting history.
- Public pages should ultimately be able to show “why we think this is true” via source links, capture times, and (where relevant) transformation notes.
- This increases modelling and operational complexity, but it is required for credibility in a civic transparency context.

