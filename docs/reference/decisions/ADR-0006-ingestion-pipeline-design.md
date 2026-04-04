# ADR-0006: Ingestion Pipeline Design

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil will ingest data from many councils and sources. Publication practices vary widely:

- formats differ (CSV, XLSX, HTML tables, PDFs)
- columns and definitions drift over time
- files may be republished with corrections without clear versioning
- URLs change and content can disappear

The ingestion pipeline must therefore be resilient, repeatable, and auditable. It must also be realistic for a volunteer-maintained open-source project.

## Decision

Adopt a batch-oriented ingestion philosophy with explicit idempotency and reprocessing support:

- **Batch first:** treat each ingest as a batch import run over a defined input set (for example a monthly spend file, or a periodic contract register export).
- **Idempotent operations:** rerunning the same import with the same inputs should not create duplicate canonical entities or derived projections.
- **Reprocessing is normal:** the pipeline must support rebuilding normalised and derived layers from stored raw artefacts (for fixes, improved parsers, or schema evolution).
- **Import runs are first-class:** record an import run for each execution with status, timestamps, inputs, and summary metrics (counts, warnings, failures).
- **Failure handling:** prefer “fail scoped” behaviour:
  - a single broken file should not block other council imports
  - errors should be captured and replayable (same input artefact, same parser version)
- **Inconsistency tolerance:** parsers should preserve raw observations and explicitly model missing/unknown fields rather than inventing defaults.

Incremental ingestion (for example, “only changes since last run”) may be introduced later where sources support it, but must not remove the ability to do full rebuilds.

## Consequences

- The system must store raw artefacts durably and reference them from import runs.
- Pipeline code should be written to allow safe retries and backfills (including across schema changes).
- Operational tooling should focus on:
  - visibility of what ran and what failed
  - repeatable re-runs
  - controlled regeneration of derived read models and search indices
- This favours correctness and auditability over “near real-time” updates, which is appropriate for civic transparency data where the publication cadence is often monthly/quarterly/annual.

