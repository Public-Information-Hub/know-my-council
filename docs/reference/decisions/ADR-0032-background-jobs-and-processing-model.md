# ADR-0032: Background Jobs and Processing Model

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil relies on operational work that should not run in request/response paths:

- imports and reprocessing (ADR-0006)
- read model projections (ADR-0010)
- search indexing (ADR-0009)
- document processing (text extraction, metadata parsing) (future)

These tasks can be long-running and failure-prone. The platform needs predictable retry behaviour and idempotency.

## Decision

Adopt queue-backed background jobs as the default model for non-interactive processing.

Principles:

- **Queue usage:** run imports, projections, indexing, and heavy processing via queued jobs.
- **Idempotency:** jobs must be safe to retry without duplicating canonical entities or derived outputs (aligned with ADR-0006).
- **Retries:** use bounded retries with backoff; on repeated failure, record a durable failure state linked to the import run/work item.
- **Long-running jobs:** support chunking (split large imports/indexing into smaller jobs) and resumability via checkpoints where practical.
- **Isolation:** failures should be scoped (one council/dataset/import run failure should not block unrelated work).

This ADR sets behaviour expectations; the concrete queue backend and worker topology can evolve.

## Consequences

- Job design must carry correlation IDs (import run, dataset release, projection run) for auditability and monitoring (ADR-0025, ADR-0034).
- Contributors must be able to replay failed jobs deterministically using stored artefacts (ADR-0016).
- Queue-driven processing increases system complexity, but supports the platform’s ingestion and read-heavy architecture.

