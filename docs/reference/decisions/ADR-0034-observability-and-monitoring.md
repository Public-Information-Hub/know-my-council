# ADR-0034: Observability and Monitoring

Status: accepted
Date: 2026-04-04

## Context

The platform will run ingestion, projections, indexing, and public serving. Failures are inevitable, especially with inconsistent council data. Without observability, maintainers cannot:

- see what failed and why
- distinguish data issues from software bugs
- detect performance regressions
- provide credible operational transparency to contributors

## Decision

Adopt observability as a core platform requirement with four pillars:

1. **Structured logging**
  - application logs include correlation identifiers (import run IDs, job IDs, request IDs where available)
  - log levels are used consistently; warnings and errors are actionable

2. **Error tracking**
  - unhandled exceptions and recurring failures are captured with context
  - ingestion and job failures are linked to the relevant run/work item

3. **Performance visibility**
  - track request latency for key public endpoints
  - track queue latency and job runtimes for ingestion/projection/indexing

4. **Ingestion monitoring**
  - import runs record counts, warnings, and outcomes (ADR-0006)
  - stale data and missing coverage are visible to maintainers and (where appropriate) to the public (ADR-0028)

This ADR defines observability expectations without committing to specific vendors or tooling.

## Consequences

- Contributors can diagnose problems without guesswork and avoid “silent failure” modes.
- Operational dashboards (even basic ones) become part of maintainership.
- Observability adds overhead but is required for a civic platform that aims to be dependable and auditable.
