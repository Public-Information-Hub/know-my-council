# Source Ingestion and Adapters

## 1. Purpose

KnowMyCouncil will gather data from a mix of council-published sources and supporting artefacts. The platform should prefer automated ingestion routes wherever a council exposes them, while still allowing manual capture for backfills, exceptions, and awkward source formats.

This document defines the intended modelling shape for source-driven ingestion. It is meant to sit alongside:

- [canonical-entity-model.md](canonical-entity-model.md)
- [migration-plan-phase-1.md](migration-plan-phase-1.md)
- [../../architecture.md](../../architecture.md)

## 2. Core idea

The ingestion system should distinguish three things that are easy to conflate:

1. The source itself
2. The reusable ingestion definition
3. The individual import execution

That separation matters because the same council may expose:

- a JSON API for current records
- a CSV export for spend
- an HTML table for a register page
- a PDF or document archive for supporting evidence

Those are different source shapes, but they should all land in the same canonical model and provenance trail.

## 3. Modelling layers

### Source

A source is the concrete external origin of data: a URL, endpoint, page, file location, or document feed that can be fetched or observed.

Intended metadata for a source includes:

- council or organisation scope
- source kind, such as API, CSV download, HTML table, or document
- source URL or discovery location
- expected refresh cadence
- authentication or access requirements, if any
- last seen / last successful fetch information
- whether the source is preferred for automatic ingestion or only used as a manual fallback

The current schema has a dedicated source registry table. Source-shaped metadata should remain explicit in the registry, import configuration, and `source_files`.

### Ingestion definition

An ingestion definition is the reusable configuration for a dataset family and parsing strategy.

This is the role currently played by `imports`:

- `dataset_id` groups the source family
- `import_key` identifies the reusable import definition
- `import_type` names the broad ingest family
- `connector_key` identifies the adapter used to fetch or read the source
- `parser_version` and `normalisation_profile` make transformation behaviour explicit
- `is_active` lets maintainers retire a definition without deleting history

The definition should stay stable across runs. Changing the parser or connector behaviour should be treated as a versioned change, not an invisible mutation.

### Import run

An import run is a single execution against a specific input set.

This is the role currently played by `import_runs`:

- it records when the run started and finished
- it captures run state, warnings, and summary counts
- it ties the execution to a `dataset_version`
- it gives idempotency a durable home

If the same source is re-fetched later, that should create a new run, not overwrite the earlier history.

## 4. Source artefacts

Raw artefacts belong in object storage, with PostgreSQL storing metadata and relationships only.

The current `source_files` table is the provenance anchor for those artefacts:

- `storage_provider`, `storage_bucket`, and `storage_key` locate the raw object
- `sha256` helps identify duplicate content
- `capture_method` records how the artefact was obtained
- `source_url` records the original external location when there is one
- `published_at` and `captured_at` preserve temporal context
- `is_raw_unmodified` records whether the stored artefact is the original raw form

For automatic ingestion, every fetched artefact should produce a `source_files` row, even when the parsed data is later discarded or rejected. That keeps failures explainable.

## 5. Supported source shapes

The platform should support these source shapes without changing the canonical data model:

- JSON or REST-style APIs
- CSV or TSV downloads
- HTML tables or paginated web pages
- XLSX and other tabular exports where practical
- PDF or document-backed publication feeds

Each adapter should do the same three things:

1. fetch or read the source
2. preserve the raw artefact
3. emit normalised rows with provenance attached

The parser and normaliser may differ per source shape, but the canonical destination should stay consistent.

## 6. Automatic ingestion behaviour

Automatic ingestion should be the default for sources that support it.

Recommended operating shape:

- discover or refresh known sources on a schedule
- fetch with explicit idempotency keys or content hashes where possible
- store the raw artefact before normalisation
- parse into staging or canonical rows only after basic validation
- record failures durably without dropping the raw input
- keep retries safe and bounded

This aligns with the batch-and-retry approach already adopted for imports and queued work.

Manual ingestion remains important as a fallback for:

- one-off backfills
- sources that require human download steps
- documents that need ad hoc capture
- cases where the automated route is not yet reliable enough

Operators can register or update source entries with the `kmc:ingestion-source:upsert` command before wiring a source into a scheduled fetch path.
The hourly dispatcher is `kmc:ingestion-source:dispatch-due`, which is also wired into Laravel's scheduler for regular runs.

## 7. Practical implementation order

When implementing automatic ingestion, the likely order is:

1. Define the source registry model and the minimal metadata needed for discovery.
2. Extend or version import definitions so they can reference specific adapters.
3. Add adapters for the highest-value council sources first, starting with the most stable machine-readable feeds.
4. Keep the manual CSV path as a verified fallback while automated routes are being built out.
5. Move successful automated sources out of ad hoc handling and into the scheduled ingestion set.

## 8. Current status

Current Phase 1 support already gives us a useful base:

- `datasets` groups repeated releases or captures
- `imports` stores reusable ingestion definitions
- `ingestion_sources` records concrete source endpoints and feeds
- `import_runs` stores execution history and idempotency
- `source_files` stores raw artefact metadata and provenance
- `spend_records` and `contracts` can receive normalised data from any adapter shape

What is still missing is the scheduler/orchestrator that will drive automatic refreshes at scale.
