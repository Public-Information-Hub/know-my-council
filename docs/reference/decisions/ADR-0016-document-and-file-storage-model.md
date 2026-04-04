# ADR-0016: Document and File Storage Model

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil will store a large volume of files over time, including:

- raw council datasets (CSV/XLSX exports, PDFs, HTML captures)
- FOI/EIR responses and attachments
- supporting documents used as evidence

For a civic transparency platform, file handling is not incidental. We need:

- long-term retention of source artefacts where feasible
- traceability from derived outputs back to the original files (provenance)
- a way to link files to import runs, datasets, and canonical records

Storing large binaries in the primary database is a poor fit for performance, cost, and operational complexity.

## Decision

Adopt an object-storage-first model for files, aligned with the provenance approach (ADR-0007):

- Use **MinIO (S3-compatible)** for local development object storage, with an S3-compatible interface as the long-term portability target.
- Store **raw files unmodified** as immutable artefacts:
  - do not “clean up” or rewrite source files in place
  - if a file is republished or corrected, store it as a new artefact
- Use a **logical key structure** that supports organisation by context (without locking into a single bucket layout upfront), for example:
  - grouping by authority, dataset/release, and import run
  - grouping by artefact type (dataset, document, FOI/EIR response)
- Reference stored objects via **database records** (artefact metadata), rather than embedding files in the database:
  - object key (and bucket name where relevant)
  - content type and size
  - capture time and capture source (URL, upload, email, etc.)
  - content hash where practical (for deduplication and integrity checking)
  - links to import runs and downstream records where applicable

Avoid storing large files in PostgreSQL. PostgreSQL should store metadata and relationships, not the binary payload.

### Considerations

- **Versioning:** treat new publications or corrections as new artefacts; retain prior artefacts for auditability. Object versioning can be a future operational choice, but the logical model must not depend on it.
- **Naming conventions:** object keys should be deterministic, safe, and stable, but must not assume user-friendly names are unique. Use internal IDs and import run identifiers rather than free-text names where possible.
- **Linking to import runs:** artefacts produced or consumed by ingestion should be linked to the relevant import run records (ADR-0006), enabling replay and debugging.
- **Linking to provenance:** all canonical and derived records should ultimately be traceable to artefacts via the provenance model (ADR-0007).
- **Scalability:** object storage is the primary scaling lever for file volume; the database holds the graph of relationships and provenance.

## Consequences

Benefits:

- Strong traceability and auditability: “show the file this came from” remains possible.
- Scales better for large binaries and many artefacts than database storage.
- Supports reprocessing and rebuilds from retained raw artefacts.
- Keeps the system portable across S3-compatible providers.

Trade-offs:

- Requires storage lifecycle management (retention, backups, cost control) as the project grows.
- Deduplication and integrity checking need deliberate handling (hashing, indexing) to avoid unnecessary duplication.
- Access control and privacy handling (especially for FOI/EIR and personal data) must be designed carefully; storage makes it easier to retain sensitive material, which increases the need for review and governance.

