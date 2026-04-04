# Migration Plan (Phase 1)

## 1. Purpose

This document defines the Phase 1 database implementation cut for KnowMyCouncil.

Phase 1 is not the full schema. It is a controlled first slice intended to deliver:

- a working ingestion pipeline
- core factual data for councils, organisations, spend, and contracts
- provenance and traceability (source files, dataset versions, import runs)
- basic evidence linking foundations (without the full community workflow)
- minimal viable auditability for state-changing actions

Phase 1 explicitly defers advanced community, moderation, editorial, FOI, and reputation features.

References:

- `docs/reference/data-model/canonical-entity-model.md`
- `docs/reference/data-model/state-and-enum-inventory.md`

## 2. Inclusion Criteria

A table belongs in Phase 1 if it is required for at least one of:

- ingesting and normalising core factual records (spend/contracts)
- preserving raw artefact provenance and replayable imports
- establishing canonical identity anchors (councils, organisations)
- linking factual records back to dataset versions and import runs
- minimal auditability of state-changing actions (forensics baseline)

Anything primarily related to:

- community contribution workflows beyond basic internal operation
- editorial/journalism, analysis outputs, or investigations
- full moderation, dispute handling, and takedown process
- complex permissions and trust progression

is deferred to Phase 2+.

## 3. Tables to Include in Phase 1

This section lists the Phase 1 tables. Column-level detail is intentionally omitted; Phase 1 should implement the smallest viable column set that supports the relationships and constraints described in the canonical model.

### Core domain

- `councils`
  - Purpose: stable identity anchors for councils.
  - Phase 1 notes: include jurisdiction-aware fields; do not key on external codes.

- `council_versions`
  - Purpose: time-bounded names/status for councils.
  - Phase 1 notes: required to avoid overwriting identity when names/codes change.

- `geographies` (optional; likely Phase 2+)
  - Purpose: provide a future-safe anchor for “what area does this dataset cover?” without committing to full boundary modelling on day one.
  - Phase 1 notes: only include if ingestion genuinely needs a geography identity anchor early; otherwise store geography basis metadata on `dataset_versions` and defer geography tables until Phase 2+.

- `organisations`
  - Purpose: canonical supplier (and other org) identity anchors.
  - Phase 1 notes: allow unresolved cases; do not force resolution before ingesting spend.

- `organisation_identifiers`
  - Purpose: store external identifiers where available (time-aware).

- `organisation_aliases`
  - Purpose: preserve as-published supplier names and aliases with provenance.

### Financial

- `spend_records`
  - Purpose: canonical spend line items linked to council, dataset version, and import run, with observed raw fields retained.
  - Phase 1 notes: keep `organisation_id` nullable; preserve observed supplier name string.

- `contracts`
  - Purpose: canonical contract records linked to council, dataset version, and import run.
  - Phase 1 notes: supplier resolution can be partial; keep publishability separate from ingestion.

- `contract_suppliers`
  - Purpose: join table between contracts and organisations with observed supplier names.
  - Phase 1 notes: do not require `organisation_id` for ingestion.

### Data / provenance

- `datasets`
  - Purpose: dataset family anchors (not files).

- `dataset_versions`
  - Purpose: dataset releases/captures with reporting period and geography basis metadata.
  - Phase 1 notes: this is required to support freshness labelling and avoid unsafe joins.

- `imports`
  - Purpose: ingestion definitions/config anchors.

- `import_runs`
  - Purpose: record each execution, status, and summary metrics; required for replay and traceability.
  - Phase 1 notes: treat import runs as append-only history.

- `source_files`
  - Purpose: metadata for raw stored artefacts (object storage), including hashes and capture dates.
  - Phase 1 notes: do not store binaries in PostgreSQL; store metadata + references only.

Optional but recommended in Phase 1 if it is needed immediately for linking:

- `reporting_periods`
  - Purpose: shared period dimension for dataset versions and records.
  - Phase 1 notes: if skipped, Phase 1 must still represent reporting periods consistently (but this usually ends up as a table anyway).

### Audit (minimum viable)

- `audit_logs`
  - Purpose: append-only action audit for state-changing operations.
  - Phase 1 notes: include actor and context modelling fields so imports and background jobs are auditable from day one.

- `state_transitions`
  - Purpose: append-only workflow/publication state history for stateful records.
  - Phase 1 notes: must support multiple state fields (at minimum `public_state` and `import_runs.run_state`) so import lifecycle changes are not forced into publication-state vocabulary. Even with limited workflow in Phase 1, state transitions prevent “mystery changes”.

## 4. Tables Deferred to Phase 2+

Do not implement these in Phase 1:

- Investigations: `investigations`, `investigation_items`
- FOI/EIR: `foi_requests`, `foi_request_events`, `foi_responses`
- Moderation/disputes: `moderation_cases`, `moderation_case_items`, expanded `flags` workflow
- Full evidence and review workflow: `submissions`, `reviews`, `evidence`, `evidence_links`, `correction_proposals`
- Teams and advanced permissions: `teams`, `team_members`, `roles`, `permissions`, join tables, trust scoring
- Editorial/analysis content system: `content_types`, `content_items`, `content_reviews`, `assertions`, `analysis_records`, `editorial_articles`
- Full entity history tables (snapshots): `*_histories` tables are mostly Phase 2+, except where Phase 1 needs an explicit event table to avoid destructive updates

Note:

- Phase 1 should still preserve the ability to add these without back-migrating core concepts.

## 5. Minimal States and Enums for Phase 1

Phase 1 should implement only the minimal vocabulary required for ingestion, traceability, and basic publication gating.

### `public_state` (minimal subset)

Use a minimal subset of the canonical state vocabulary:

- `draft` (for internal drafts if any)
- `under_review` (optional; only if Phase 1 includes an approval gate)
- `published` (for public-facing factual records)
- `archived` (for superseded/retained records, if needed)

If Phase 1 does not include review gating yet, `under_review` can be deferred. Do not invent new state names.

### `actor_type`

At minimum:

- `user`
- `system`
- `import`
- `job`

`api` and `ai_process` can be deferred unless Phase 1 includes API tokens or AI-assisted processes.

### `import_status` (`import_runs.run_state`)

At minimum:

- `queued`
- `running`
- `succeeded`
- `failed`

### `mapping_confidence`

At minimum:

- `high`
- `medium`
- `low`
- `unknown`

This is required in Phase 1 to prevent accidental overconfidence in identity joins.

## 6. Simplifications Allowed in Phase 1

Phase 1 is allowed to simplify, but must not compromise traceability.

Allowed simplifications:

- limited review workflow: optional “approval” gate for publication if required, but no complex moderation/dispute lifecycle yet
- no complex moderation flows and no takedown/dispute system (defer to Phase 2+)
- no full trust system: no role/capability assignment model beyond basic operational access
- limited content types: focus on factual data ingestion and provenance; do not implement editorial/analysis content layers yet

Not allowed in Phase 1:

- publishing factual data without preserved dataset version + import run linkage
- overwriting raw source files or raw observations in place (republishes become new artefacts)

## 7. Migration Sequencing (rough order)

1. Base entities
   - `councils`
   - `council_versions`
   - `geographies` (minimal, if included)
   - `organisations`
   - `organisation_aliases`
   - `organisation_identifiers`

2. Dataset and ingestion foundations
   - `datasets`
   - `dataset_versions`
   - `imports`
   - `import_runs`
   - `source_files`
   - `reporting_periods` (if included)

3. Financial records
   - `contracts`
   - `contract_suppliers`
   - `spend_records`

4. Indexes and integrity constraints
   - foreign keys for domain relationships
   - check constraints for Phase 1 vocabularies
   - unique constraints where safe (avoid assuming global uniqueness for external codes)

5. Audit baseline
   - `audit_logs`
   - `state_transitions`

## 8. Risks and Follow-ups

Intentional incompleteness:

- Phase 1 does not deliver investigations, FOI workflows, moderation cases, editorial content, or the full evidence review pipeline.
- Phase 1 does not deliver full entity snapshot history tables for every mutable entity.

Key risks if Phase 1 is rushed:

- accidental publication of low-confidence joins (mitigate with `mapping_confidence` and nullable links)
- provenance drift (mitigate by enforcing dataset version + import run linkage early)
- losing the ability to explain changes later (mitigate by implementing audit + state transitions early)

Phase 2 priorities (recommended):

- Evidence and review workflow tables (`evidence`, `submissions`, `reviews`, `evidence_links`, `correction_proposals`) with review-first publication.
- Moderation and dispute handling (`moderation_cases`, flags workflow, takedown/dispute process).
- FOI/EIR request lifecycle (`foi_requests`, events, responses, artefact linkage).
- Content publication layer (`content_items`, `content_types`) for assertions, analysis, and editorial content, without contaminating factual records.
- Selective entity history tables for high-risk mutable entities (identity resolution, moderation outcomes, publication metadata).
