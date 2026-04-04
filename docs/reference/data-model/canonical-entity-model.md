# Canonical Entity Model

## 1. Overview

This document defines the intended canonical data model for KnowMyCouncil as a UK-wide civic transparency platform built on PostgreSQL, using Laravel-style naming conventions where helpful (`snake_case`, plural table names, explicit pivot tables).

The model is designed around a few core principles already established in the ADRs:

- stable internal IDs are the anchor for identity
- time is explicit rather than implied
- provenance is first-class
- raw, normalised, and derived data are separate layers
- factual records, evidence, assertions, analysis, and editorial content must remain distinct

### Philosophy of the model

The platform needs to survive messy real-world data:

- councils merge, split, rename, and change boundaries
- identifiers change or are reused
- datasets are inconsistent and republished
- contributors submit evidence, corrections, and investigations
- analysis and editorial layers may sit on top of the data platform, but must not contaminate the factual layer

The model therefore uses an entity + relationship approach:

- stable concept tables for long-lived entities (`councils`, `organisations`, `geographies`)
- version tables for time-bounded representations (`council_versions`, `geography_versions`, `dataset_versions`)
- relationship tables for lineage, mappings, and many-to-many links
- cross-cutting workflow tables for review, moderation, audit, and presentation control

### Temporal and provenance requirements

The canonical model must be able to answer:

- what was published at the time?
- what does the platform currently believe is the best normalised interpretation?
- what evidence supports that interpretation?
- who changed it, when, and why?

That means:

- time-bounded records use `valid_from` and `valid_to`
- ingestable records keep `dataset_version_id`, `import_run_id`, and evidence/source links where relevant
- raw files live outside PostgreSQL, but PostgreSQL stores their metadata and relationships
- derived read/search outputs are rebuildable and do not become canonical truth

### Why the layers stay separate

The platform must model the following separately:

- factual platform records: canonical structured data the platform currently considers true enough to publish
- evidence: source-backed material that supports or challenges a record
- assertions: user claims not yet verified by evidence
- analysis: human or AI-assisted interpretation built on top of evidence and facts
- editorial/journalism: narrative content with authorship, attribution, and review, but not authoritative factual status

If these are mixed, the platform loses neutrality, traceability, and legal defensibility.

## 2. Core Entity Groups

### Governance and Geography

#### `councils`

- Purpose: Stable identity for a council or equivalent local authority body in UK local government scope.
- Key fields: `id`, `canonical_slug`, `jurisdiction_code`, `country_code`, `authority_kind`, `current_state`, `created_at`.
- Relationships: has many `council_versions`, `council_identifiers`, `authority_lineage`, `spend_records`, `contracts`, `investigations`, `foi_requests`.
- Important constraints: `id` is immutable; names and external codes never act as primary keys; `jurisdiction_code` must support future UK-wide expansion even if early datasets are England-heavy.

#### `council_versions`

- Purpose: Time-bounded representation of a council as it existed or was published in a period.
- Key fields: `id`, `council_id`, `display_name`, `short_name`, `status`, `valid_from`, `valid_to`, `primary_geography_version_id`, `source_evidence_id`, `public_state`.
- Relationships: belongs to `councils`; has many `council_identifiers`; may be referenced by `spend_records`, `contracts`, `foi_requests`, `dataset_versions`.
- Important constraints: versions for the same `council_id` must not overlap for the same semantic role; `valid_to` may be null for the current version; version rows are appended rather than overwritten.

#### `council_identifiers`

- Purpose: Store external identifiers and codes used for a council over time.
- Key fields: `id`, `council_id`, `council_version_id`, `identifier_scheme`, `identifier_value`, `valid_from`, `valid_to`, `source_file_id`, `mapping_confidence`.
- Relationships: belongs to `councils`; optionally belongs to `council_versions`; links back to `source_files`.
- Important constraints: uniqueness should be scoped by scheme, value, and validity window rather than globally; identifiers may be missing, duplicated in source data, or superseded.

#### `geographies`

- Purpose: Stable geography concept used for administrative or analytical areas.
- Key fields: `id`, `canonical_slug`, `geography_kind`, `country_code`, `created_at`.
- Relationships: has many `geography_versions`; referenced by `geography_mappings`, `dataset_versions`, and optionally `council_versions`.
- Important constraints: geography identity is distinct from council identity; a geography may outlive or differ from the authority that uses it.

#### `geography_versions`

- Purpose: Time-bounded version of a geography, including boundary or area definition metadata.
- Key fields: `id`, `geography_id`, `display_name`, `geography_kind`, `valid_from`, `valid_to`, `boundary_source_file_id`, `geometry_storage_ref`, `external_scheme`, `external_value`.
- Relationships: belongs to `geographies`; may be the `primary_geography_version_id` for `council_versions`; participates in `geography_mappings`.
- Important constraints: do not assume continuity from one version to the next; boundary change is a real event even when the council concept continues.

#### `geography_mappings`

- Purpose: Explicit mapping between geography versions for comparison and normalisation.
- Key fields: `id`, `from_geography_version_id`, `to_geography_version_id`, `mapping_basis`, `basis_reference_date`, `coverage_ratio`, `mapping_confidence`, `source_evidence_id`, `valid_from`, `valid_to`.
- Relationships: belongs to `geography_versions` twice; may link to `evidence`.
- Important constraints: mappings are directional and basis-specific; mappings are derived, not raw truth; joins between old and current areas must use explicit mapping rows rather than assumptions.

#### `authority_lineage`

- Purpose: Capture merge, split, rename, succession, and abolition relationships between councils.
- Key fields: `id`, `predecessor_council_id`, `successor_council_id`, `relationship_type`, `effective_from`, `effective_to`, `source_evidence_id`, `mapping_confidence`.
- Relationships: belongs to `councils` as predecessor and successor; may link to `evidence`.
- Important constraints: many-to-many lineage must be supported; lineage is append-only and evidence-backed; lack of lineage evidence must remain representable.

### Organisations

#### `organisations`

- Purpose: Stable canonical identity for suppliers and other organisations appearing across datasets and evidence.
- Key fields: `id`, `canonical_name`, `organisation_kind`, `jurisdiction_code`, `current_state`, `created_at`.
- Relationships: has many `organisation_aliases`, `organisation_identifiers`, `organisation_relationships`, `organisation_resolution_events`; referenced by `spend_records`, `contract_suppliers`, `investigation_items`, `foi_requests`.
- Important constraints: not keyed by free-text name or external company number alone; organisation identity can exist before full resolution is complete.

#### `organisation_aliases`

- Purpose: Store as-published names, trading names, legal names, and other aliases over time.
- Key fields: `id`, `organisation_id`, `alias`, `alias_type`, `valid_from`, `valid_to`, `dataset_version_id`, `source_file_id`, `mapping_confidence`.
- Relationships: belongs to `organisations`; optionally belongs to `dataset_versions`; links to `source_files`.
- Important constraints: aliases are observations, not identity proof; multiple similar aliases may exist across unrelated organisations.

#### `organisation_identifiers`

- Purpose: External identifiers for organisations, such as company or charity numbers, with temporal validity.
- Key fields: `id`, `organisation_id`, `identifier_scheme`, `identifier_value`, `valid_from`, `valid_to`, `source_file_id`, `mapping_confidence`.
- Relationships: belongs to `organisations`; links to `source_files`.
- Important constraints: identifier assignment is time-aware; identifiers can be wrong or missing in source data and must remain challengeable.

#### `organisation_relationships`

- Purpose: Represent parent, subsidiary, trading, operating, or other organisation-to-organisation links.
- Key fields: `id`, `subject_organisation_id`, `related_organisation_id`, `relationship_type`, `valid_from`, `valid_to`, `source_evidence_id`, `relationship_note`.
- Relationships: belongs to `organisations` twice; may link to `evidence`.
- Important constraints: relationships are evidence-backed and time-scoped; do not use them to silently collapse distinct entities.

#### `organisation_resolution_events`

- Purpose: Record merges, splits, and manual resolution decisions affecting organisation identity.
- Key fields: `id`, `event_type`, `source_organisation_id`, `target_organisation_id`, `resolved_by_user_id`, `resolution_basis`, `supersedes_event_id`, `resolved_at`.
- Relationships: belongs to `organisations` as source and target; belongs to `users`; referenced by `audit_logs`.
- Important constraints: resolution decisions must be reversible and auditable; automatic suggestions must not directly create published merges without review.

### Financial and Contracts

#### `service_categories`

- Purpose: Normalised service or expenditure taxonomy used to classify spend and contracts.
- Key fields: `id`, `taxonomy_key`, `code`, `display_name`, `parent_id`, `valid_from`, `valid_to`.
- Relationships: self-referential parent/child; referenced by `spend_records` and `contracts`.
- Important constraints: taxonomy changes should be version-aware; category assignment is normalised data, not raw truth.

#### `payments`

- Purpose: Optional payment/invoice-level grouping when a dataset distinguishes a payment event from individual spend lines.
- Key fields: `id`, `council_id`, `council_version_id`, `dataset_version_id`, `import_run_id`, `reporting_period_id`, `payment_reference`, `payment_date`, `gross_amount`, `currency`.
- Relationships: belongs to `councils`, `council_versions`, `dataset_versions`, `import_runs`, `reporting_periods`; has many `spend_records`.
- Important constraints: this table is optional per dataset family; raw payment references must be retained even when the payment is unresolved to a canonical organisation.

#### `spend_records`

- Purpose: Canonical spend line item representing as-published financial outflow with normalised links where available.
- Key fields: `id`, `council_id`, `council_version_id`, `organisation_id`, `payment_id`, `dataset_version_id`, `import_run_id`, `reporting_period_id`, `service_category_id`, `dataset_geography_version_id`, `supplier_name_observed`, `description_observed`, `transaction_date`, `amount`, `currency`, `mapping_confidence`, `public_state`.
- Relationships: belongs to `councils`, `council_versions`, `organisations` (nullable), `payments` (nullable), `dataset_versions`, `import_runs`, `reporting_periods`, `service_categories`, `geography_versions`; may link to `evidence`, `investigation_items`, `correction_proposals`.
- Important constraints: keep observed raw text even when `organisation_id` is present; do not require supplier resolution; council/time/geography relationships must reflect the reporting basis, not current assumptions.

#### `contracts`

- Purpose: Canonical contract or procurement award record.
- Key fields: `id`, `council_id`, `council_version_id`, `dataset_version_id`, `import_run_id`, `reporting_period_id`, `service_category_id`, `dataset_geography_version_id`, `title`, `description_observed`, `contract_reference`, `award_date`, `start_date`, `end_date`, `total_value_amount`, `currency`, `public_state`.
- Relationships: belongs to `councils`, `council_versions`, `dataset_versions`, `import_runs`, `reporting_periods`, `service_categories`, `geography_versions`; has many `contract_suppliers`; may link to `evidence`, `investigation_items`, `foi_requests`.
- Important constraints: a contract record may exist before supplier resolution is complete; dates and values may be partial and must allow uncertainty.

#### `contract_suppliers`

- Purpose: Join table between contracts and organisations with as-published supplier observations.
- Key fields: `id`, `contract_id`, `organisation_id`, `supplier_name_observed`, `role_type`, `share_fraction`, `mapping_confidence`.
- Relationships: belongs to `contracts`; belongs to `organisations` (nullable until resolved).
- Important constraints: do not force a canonical organisation if only the name string is known; many suppliers per contract must be supported.

### Data and Provenance

#### `reporting_periods`

- Purpose: Shared reporting period dimension for dataset releases and factual records.
- Key fields: `id`, `label`, `period_kind`, `period_start`, `period_end`.
- Relationships: referenced by `dataset_versions`, `payments`, `spend_records`, `contracts`.
- Important constraints: reporting period describes what the data covers, not when it was published or imported.

#### `datasets`

- Purpose: Stable dataset family or publication source definition.
- Key fields: `id`, `dataset_key`, `publisher_name`, `publisher_kind`, `dataset_family`, `jurisdiction_scope`, `default_council_id`, `expected_refresh_cadence`, `licence_summary`.
- Relationships: has many `dataset_versions`; has many `imports`; optionally belongs to `councils`.
- Important constraints: a dataset family is not a file; it groups repeated releases or captures of the same conceptual source.

#### `dataset_versions`

- Purpose: Specific published release, capture, or edition of a dataset.
- Key fields: `id`, `dataset_id`, `version_label`, `edition_date`, `published_at`, `captured_at`, `reporting_period_id`, `geography_basis_type`, `geography_version_id`, `code_scheme`, `mapping_confidence`, `freshness_state`, `public_state`.
- Relationships: belongs to `datasets`, `reporting_periods`, `geography_versions`; referenced by `source_files`, `import_runs`, `spend_records`, `contracts`, `organisation_aliases`.
- Important constraints: dataset metadata belongs here, not only in import config; geography basis and code scheme must be explicit to prevent bad joins.

#### `imports`

- Purpose: Reusable ingestion definition for a dataset family and parser/connector combination.
- Key fields: `id`, `dataset_id`, `import_key`, `import_type`, `connector_key`, `parser_version`, `normalisation_profile`, `is_active`.
- Relationships: belongs to `datasets`; has many `import_runs`.
- Important constraints: import definition is stable configuration, not runtime history; changing parser behaviour should create a new versioned config or a clearly logged update.

#### `import_runs`

- Purpose: Record one execution of an import over one or more dataset inputs.
- Key fields: `id`, `import_id`, `dataset_version_id`, `started_at`, `finished_at`, `run_state`, `idempotency_key`, `triggered_by_user_id`, `rows_seen`, `rows_inserted`, `rows_updated`, `warning_count`, `error_summary`, `parent_import_run_id`.
- Relationships: belongs to `imports`, `dataset_versions`, `users` (nullable), and optionally parent `import_runs`; referenced by factual records, `source_file_links`, `projection_runs`, `audit_logs`.
- Important constraints: reruns must be idempotent at the normalised layer; failed runs remain recorded; summary counts should never be the only provenance.

#### `source_files`

- Purpose: Metadata row for a raw stored artefact in object storage.
- Key fields: `id`, `storage_provider`, `storage_bucket`, `storage_key`, `sha256`, `content_type`, `byte_size`, `capture_method`, `source_url`, `published_at`, `captured_at`, `is_raw_unmodified`, `visibility`.
- Relationships: linked to `dataset_versions`, `import_runs`, `evidence`, `foi_responses`, and `geography_versions` through `source_file_links` or explicit foreign keys.
- Important constraints: PostgreSQL stores metadata and relationships, not the binary; raw artefacts are immutable; republished files create new rows rather than overwriting.

#### `source_file_links`

- Purpose: Generic link from a source file to the records or workflows it supports.
- Key fields: `id`, `source_file_id`, `linked_type`, `linked_id`, `link_role`, `created_at`.
- Relationships: belongs to `source_files`; polymorphically links to `dataset_versions`, `import_runs`, `evidence`, `foi_responses`, `geography_versions`, and other evidence-bearing records.
- Important constraints: only cross-cutting provenance links should use polymorphic references; core domain tables should prefer explicit foreign keys where practical.

#### `projection_runs`

- Purpose: Metadata for rebuilds of derived read models, search documents, or analytical materialisations.
- Key fields: `id`, `projection_type`, `source_dataset_version_id`, `source_import_run_id`, `started_at`, `finished_at`, `run_state`, `triggered_by_user_id`, `output_version`.
- Relationships: belongs to `dataset_versions`, `import_runs`, and `users`; referenced by `audit_logs` and derived system components.
- Important constraints: this table records derived lineage only; projection output must never be mistaken for canonical truth.

### Evidence and Contributions

#### `submissions`

- Purpose: Workflow envelope for user-submitted evidence, assertions, corrections, FOI items, or investigation items.
- Key fields: `id`, `submission_type`, `submitted_by_user_id`, `team_id`, `content_item_id`, `public_state`, `visibility`, `submitted_at`, `requires_review`, `target_summary`.
- Relationships: belongs to `users`, `teams`, and optionally `content_items`; has many `reviews`; may be linked to `evidence`, `assertions`, `correction_proposals`, `foi_requests`, `investigations`.
- Important constraints: submission state is separate from the canonical truth of the target record; every high-impact submission must remain reviewable and traceable.

#### `evidence`

- Purpose: Curated evidence unit that links a claim or observation to one or more source artefacts.
- Key fields: `id`, `content_item_id`, `evidence_type`, `title`, `summary`, `primary_source_file_id`, `external_primary_url`, `source_excerpt`, `submitted_by_user_id`, `public_state`, `visibility`.
- Relationships: belongs to `content_items`, `source_files` (nullable if external primary source not yet captured), and `users`; has many `evidence_links`; may be linked from `reviews`, `investigations`, `foi_responses`.
- Important constraints: evidence must point to a stored artefact or clearly identified primary source; evidence is supporting material, not a conclusion.

#### `evidence_links`

- Purpose: Link evidence to the records or content it supports, challenges, or contextualises.
- Key fields: `id`, `evidence_id`, `linked_type`, `linked_id`, `link_kind`, `created_by_user_id`.
- Relationships: belongs to `evidence`; polymorphically links to domain records, `assertions`, `analysis_records`, `editorial_articles`, `investigations`, `foi_requests`, `correction_proposals`.
- Important constraints: link kind matters (`supports`, `refutes`, `context_for`, `supersedes`); evidence linkage does not automatically publish or bless the target.

#### `reviews`

- Purpose: Integrity review record for a submission or correction workflow.
- Key fields: `id`, `reviewable_type`, `reviewable_id`, `reviewer_user_id`, `review_kind`, `decision`, `decision_reason`, `reviewed_at`, `follow_up_state`.
- Relationships: belongs to `users`; polymorphically links to `submissions`, `evidence`, `correction_proposals`, and other reviewable items.
- Important constraints: review history is append-only; a negative review does not erase the submitted item; review decisions should feed `state_transitions`.

#### `correction_proposals`

- Purpose: Proposed change to a canonical factual record or mapping without destructive overwrite.
- Key fields: `id`, `target_type`, `target_id`, `submission_id`, `proposed_by_user_id`, `proposal_kind`, `proposed_patch_json`, `rationale`, `evidence_threshold`, `public_state`.
- Relationships: belongs to `submissions` and `users`; polymorphically targets factual records, mappings, or resolution events; may link to `evidence`.
- Important constraints: proposals are not applied directly; accepted proposals should create new canonical versions or corrected mappings, not rewrite raw truth.

### Investigations and FOI

#### `investigations`

- Purpose: Structured investigation thread for a public-interest question, anomaly, or lead.
- Key fields: `id`, `content_item_id`, `slug`, `title`, `summary`, `lead_user_id`, `team_id`, `public_state`, `visibility`, `opened_at`, `closed_at`, `outcome_type`.
- Relationships: belongs to `content_items`, `users`, and `teams`; has many `investigation_items`; may link to `foi_requests`, `assertions`, `evidence`, `editorial_articles`.
- Important constraints: investigations are not findings by default; they remain distinct from evidence and factual records.

#### `investigation_items`

- Purpose: Polymorphic link table attaching records, evidence, assertions, FOI items, and notes to an investigation.
- Key fields: `id`, `investigation_id`, `item_type`, `item_id`, `item_role`, `added_by_user_id`, `created_at`.
- Relationships: belongs to `investigations` and `users`; polymorphically links to many item types.
- Important constraints: item role should preserve whether the linked item is evidence, assertion, question, or outcome; investigation pages must not flatten those distinctions.

#### `foi_requests`

- Purpose: FOI/EIR request record with lifecycle, ownership, and linkage to transparency gaps.
- Key fields: `id`, `content_item_id`, `council_id`, `investigation_id`, `requested_by_user_id`, `team_id`, `legal_route`, `title`, `request_text`, `missing_data_summary`, `public_state`, `visibility`, `submitted_at`, `due_at`.
- Relationships: belongs to `content_items`, `councils`, `investigations`, `users`, `teams`; has many `foi_request_events` and `foi_responses`.
- Important constraints: drafts may be private; published FOI records require review and appropriate verification level; request existence is not the same as evidence of the requested fact.

#### `foi_request_events`

- Purpose: Append-only lifecycle history for an FOI/EIR request.
- Key fields: `id`, `foi_request_id`, `event_type`, `event_at`, `actor_user_id`, `note`, `source_file_id`, `visibility`.
- Relationships: belongs to `foi_requests`, `users`, and optionally `source_files`.
- Important constraints: events should model acknowledgement, clarification, refusal, fulfilment, and closure explicitly; do not overwrite lifecycle history.

#### `foi_responses`

- Purpose: Store response metadata and link released artefacts or datasets back to the request.
- Key fields: `id`, `foi_request_id`, `response_date`, `response_type`, `summary`, `primary_source_file_id`, `released_dataset_version_id`, `contains_personal_data`, `public_state`, `visibility`.
- Relationships: belongs to `foi_requests`, `source_files`, and optionally `dataset_versions`; may link to `evidence`.
- Important constraints: released documents remain source artefacts with their own provenance; privacy and redaction concerns must affect visibility and indexing.

### Users, Identity, Teams, and Trust

#### `users`

- Purpose: Core user account with pseudonymous public identity.
- Key fields: `id`, `handle`, `display_name`, `public_bio`, `account_state`, `verification_level`, `trust_level`, `last_seen_at`.
- Relationships: has many `user_identities`, `identity_verifications`, `submissions`, `reviews`, `audit_logs`, `team_members`.
- Important constraints: public identity is separate from private verified identity; `handle` should be stable enough for attribution but remains pseudonymous.

#### `user_identities`

- Purpose: Private identity/contact records associated with a user.
- Key fields: `id`, `user_id`, `identity_type`, `identity_value_encrypted`, `identity_value_hash`, `visibility`, `is_primary`.
- Relationships: belongs to `users`; has many `identity_verifications`.
- Important constraints: always treated as restricted/private data; encrypted or hashed storage is required for sensitive values; public APIs must never expose this table directly.

#### `identity_verifications`

- Purpose: Record verification events and evidence for internal identity/accountability signals.
- Key fields: `id`, `user_identity_id`, `verification_method`, `verification_level_granted`, `verified_by_user_id`, `verified_at`, `expires_at`, `internal_note`.
- Relationships: belongs to `user_identities`; belongs to `users` as verifier.
- Important constraints: verification history must be append-only; verification does not equal trust and must remain distinct from community reputation.

#### `roles`

- Purpose: Named role bundles for contributor clarity and operational convenience.
- Key fields: `id`, `role_key`, `display_name`, `description`, `is_system_role`.
- Relationships: many-to-many with `permissions` through `role_permissions`; many-to-many with `users` through `user_roles`.
- Important constraints: roles are bundles, not the source of truth for access control; role proliferation should be controlled.

#### `permissions`

- Purpose: Atomic capabilities used for access control.
- Key fields: `id`, `permission_key`, `description`, `minimum_verification_level`, `minimum_trust_level`.
- Relationships: many-to-many with `roles` through `role_permissions`; many-to-many with `users` through `user_permissions`.
- Important constraints: permissions must separate submission, approval, moderation, publication, and identity management duties.

#### `role_permissions`

- Purpose: Join table between roles and permissions.
- Key fields: `id`, `role_id`, `permission_id`.
- Relationships: belongs to `roles` and `permissions`.
- Important constraints: uniqueness on (`role_id`, `permission_id`); changes must be audit logged.

#### `user_roles`

- Purpose: Assigned role bundles for a user, optionally scoped in future to a team or workflow context.
- Key fields: `id`, `user_id`, `role_id`, `assigned_by_user_id`, `assigned_at`, `expires_at`.
- Relationships: belongs to `users` and `roles`.
- Important constraints: role assignment history must be auditable; expired assignments must not be deleted silently.

#### `user_permissions`

- Purpose: Direct capability grants or revocations for exceptions to role bundles.
- Key fields: `id`, `user_id`, `permission_id`, `grant_state`, `assigned_by_user_id`, `assigned_at`, `expires_at`.
- Relationships: belongs to `users` and `permissions`.
- Important constraints: use sparingly; direct grants are operational exceptions and should remain reviewable.

#### `user_trust_scores`

- Purpose: Structured trust signals used to support contributor progression decisions.
- Key fields: `id`, `user_id`, `score_type`, `score_value`, `basis_summary`, `computed_at`, `is_manual_override`.
- Relationships: belongs to `users`.
- Important constraints: trust signals support human judgement and capability assignment; they must not become a popularity contest or the sole source of permission decisions.

#### `teams`

- Purpose: Collaboration unit for editorial, investigative, or maintenance work.
- Key fields: `id`, `team_key`, `display_name`, `team_type`, `visibility`, `owner_user_id`.
- Relationships: belongs to `users` as owner; has many `team_members`; may own `submissions`, `investigations`, `foi_requests`, `content_items`.
- Important constraints: teams are optional collaboration scope, not a substitute for role/capability control.

#### `team_members`

- Purpose: Membership record for users in teams.
- Key fields: `id`, `team_id`, `user_id`, `membership_role`, `joined_at`, `left_at`, `membership_state`.
- Relationships: belongs to `teams` and `users`.
- Important constraints: team membership is time-aware; team role does not bypass platform-wide permission checks.

### Moderation, State, and Auditability

#### `audit_logs`

- Purpose: Append-only record of state-changing actions across the platform.
- Key fields: `id`, `actor_user_id`, `action_type`, `target_type`, `target_id`, `before_json`, `after_json`, `context_json`, `correlation_id`, `created_at`.
- Relationships: belongs to `users`; polymorphically targets many records; may link conceptually to `import_runs`, `projection_runs`, or `moderation_cases`.
- Important constraints: append-only; sensitive internal context may exist and must respect visibility rules; audit rows are not a replacement for domain history tables.

#### `state_transitions`

- Purpose: Generic state history for records using the shared publication/workflow vocabulary.
- Key fields: `id`, `subject_type`, `subject_id`, `from_state`, `to_state`, `reason_code`, `reason_note`, `acted_by_user_id`, `changed_at`.
- Relationships: belongs to `users`; polymorphically targets `content_items`, `submissions`, `investigations`, `foi_requests`, `correction_proposals`, and other stateful records.
- Important constraints: current state may be denormalised on the subject record, but transition history is authoritative for audit.

#### `comments`

- Purpose: User discussion and review notes attached to a workflow item or published content.
- Key fields: `id`, `commentable_type`, `commentable_id`, `author_user_id`, `parent_comment_id`, `body`, `visibility`, `public_state`, `is_internal_note`.
- Relationships: belongs to `users`; self-referential parent/child; polymorphically targets many records.
- Important constraints: internal moderation/editorial notes must not leak into public views; comments themselves can be moderated and stateful.

#### `flags`

- Purpose: Raise disputes, safety concerns, quality issues, or legal/privacy challenges.
- Key fields: `id`, `flaggable_type`, `flaggable_id`, `raised_by_user_id`, `flag_type`, `reason`, `public_state`, `linked_moderation_case_id`, `raised_at`.
- Relationships: belongs to `users`; polymorphically targets many records; may belong to `moderation_cases`.
- Important constraints: a flag is not a verdict; flag handling should trigger moderation or review workflows rather than direct deletion.

#### `moderation_cases`

- Purpose: Structured case for moderation, dispute, takedown, or legal-risk handling.
- Key fields: `id`, `case_type`, `severity`, `opened_by_user_id`, `assigned_to_user_id`, `case_state`, `visibility`, `summary`, `resolution_note`, `opened_at`, `closed_at`.
- Relationships: belongs to `users` as opener and assignee; has many `moderation_case_items`; may link to `flags`, `comments`, `audit_logs`.
- Important constraints: case notes often contain restricted/internal information; moderation resolution must remain traceable and reversible where possible.

#### `moderation_case_items`

- Purpose: Join table linking a moderation case to one or more affected records.
- Key fields: `id`, `moderation_case_id`, `linked_type`, `linked_id`, `link_role`.
- Relationships: belongs to `moderation_cases`; polymorphically links to content, submissions, users, files, or records.
- Important constraints: many targets per case must be supported; moderation scope must remain explicit instead of implicit.

### Content Classification and Neutrality

This section uses a deliberately separate publication model. It does not collapse factual and editorial layers into one generic store. Instead:

- specialised domain tables continue to hold canonical facts
- specialised content tables hold assertions, analysis, and editorial material
- a shared `content_items` envelope provides review, visibility, and presentation controls only where a content-style publication wrapper is actually needed

### Recommended publication boundary

Use two patterns, not one:

- Pattern A: keep the record in its domain table and store `public_state`/`visibility` on that table.
- Pattern B: use a dedicated content entity plus `content_items` for publication/review control.

For this project, the default should be:

- Factual data records (`spend_records`, `contracts`, `council_versions`, organisation resolution history): Pattern A.
- Evidence records: dedicated `evidence` table plus `content_items` because evidence is both provenance-bearing and publishable.
- User assertions: Pattern B.
- Analysis records: Pattern B.
- Editorial/journalism content: Pattern B.

Do not create a `content_items` row for every factual row by default. A spend line or contract record should remain a domain record first. Only add a content wrapper when there is a genuine publication workflow or presentation need that is separate from the factual row itself.

#### `content_types`

- Purpose: Controlled vocabulary for publishable content classification.
- Key fields: `id`, `content_key`, `display_name`, `is_authoritative`, `default_visibility`, `requires_review_before_publication`, `allows_ai_assist`.
- Relationships: has many `content_items`.
- Important constraints: the seed set should include at least `factual_record`, `evidence`, `assertion`, `analysis`, and `editorial`; authoritative status is explicit, not inferred.

#### `content_items`

- Purpose: Publication/review envelope for any publishable item across the platform.
- Key fields: `id`, `content_type_id`, `subject_type`, `subject_id`, `title`, `summary`, `submitted_by_user_id`, `owned_by_team_id`, `public_state`, `visibility`, `published_at`, `is_ai_assisted`.
- Relationships: belongs to `content_types`, `users`, and `teams`; polymorphically points to a subject row such as `evidence`, `assertions`, `analysis_records`, `editorial_articles`, `investigations`, `foi_requests`, or a factual domain record intended for direct public rendering.
- Important constraints: `content_items` controls presentation and workflow, not domain truth; it should not become a mirror of every canonical row in the database; publication requires state transitions and review appropriate to the type.

#### `content_reviews`

- Purpose: Review record focused on publication safety, neutrality, AI disclosure, and presentation correctness.
- Key fields: `id`, `content_item_id`, `reviewer_user_id`, `review_kind`, `decision`, `decision_note`, `reviewed_at`.
- Relationships: belongs to `content_items` and `users`.
- Important constraints: this is distinct from `reviews`; it governs whether an item is safe and correctly labelled for publication, not whether a data correction is technically right.

#### `assertions`

- Purpose: Store unverified user claims, suspicions, or leads that are not yet evidence-backed.
- Key fields: `id`, `content_item_id`, `submitted_by_user_id`, `assertion_text`, `assertion_scope`, `risk_level`, `expected_evidence_type`, `current_evidence_status`.
- Relationships: belongs to `content_items` and `users`; may link to `evidence` through `evidence_links`; may appear in `investigation_items` and `submissions`.
- Important constraints: never authoritative; public visibility is optional and review-gated; assertions must remain distinguishable from both facts and analysis.

#### `analysis_records`

- Purpose: Structured analytical outputs derived from factual records and evidence.
- Key fields: `id`, `content_item_id`, `analysis_type`, `method_summary`, `input_snapshot_ref`, `conclusion_summary`, `confidence_note`, `is_ai_assisted`, `ai_model_label`, `ai_prompt_ref`.
- Relationships: belongs to `content_items`; may link to `evidence`, `investigations`, `datasets`, and factual records through `evidence_links` or explicit subject references.
- Important constraints: never authoritative by itself; must link to input evidence or record sets; AI-assisted analysis must be labelled and human-reviewed before publication.

#### `editorial_articles`

- Purpose: Narrative editorial or journalism content built on top of evidence and factual records.
- Key fields: `id`, `content_item_id`, `headline`, `deck`, `body_markdown`, `author_user_id`, `editor_user_id`, `article_kind`, `published_at`.
- Relationships: belongs to `content_items`; belongs to `users` as author and editor; may link to `evidence`, `investigations`, `contracts`, `spend_records`, `organisations`, or `councils`.
- Important constraints: non-authoritative; must never be the source of truth for canonical domain data; attribution and evidence linkage are required.

#### Content type expectations

- Factual data:
  - Authoritative: yes, when published and evidence-backed.
  - Public: yes, once `published` and visibility is `public`.
  - Review required: yes for publication and significant correction.
  - Source linkage: via `dataset_versions`, `import_runs`, `evidence_links`, and `source_files`.
  - Recommended storage pattern: remain in domain tables with `public_state`; do not default to `content_items`.

- Evidence:
  - Authoritative: authoritative as supporting material, not as interpretation.
  - Public: yes if reviewed and safe to publish.
  - Review required: yes.
  - Source linkage: mandatory.
  - Recommended storage pattern: `evidence` plus `content_items`.

- User assertions:
  - Authoritative: no.
  - Public: optional, review-gated, and clearly labelled.
  - Review required: yes.
  - Source linkage: optional at submission time, but expected to mature into evidence or remain non-authoritative.
  - Recommended storage pattern: `assertions` plus `content_items`.

- Analysis:
  - Authoritative: no.
  - Public: yes after review and labelling.
  - Review required: yes, including AI review where relevant.
  - Source linkage: mandatory to evidence and/or factual inputs.
  - Recommended storage pattern: `analysis_records` plus `content_items`.

- Editorial/journalism:
  - Authoritative: no.
  - Public: yes after review.
  - Review required: yes.
  - Source linkage: mandatory to evidence or published factual records.
  - Recommended storage pattern: `editorial_articles` plus `content_items`.

## 3. Cross-cutting Rules

### Temporal modelling

- Stable concept tables (`councils`, `organisations`, `geographies`) do not overwrite identity when names or codes change.
- Version tables use `valid_from` and `valid_to`.
- Validity windows for the same semantic role should not overlap unless the model explicitly supports concurrent validity.
- Historic vs current comparison must be expressed through explicit mappings, not current-state joins.

### Provenance linking

- Canonical factual records should carry `dataset_version_id` and `import_run_id` where they come from ingestion.
- Raw artefact relationships live in `source_files` and `source_file_links`.
- Evidence is the human/compliance-readable bridge from source artefacts to claims and records.
- Derived runs should be traced through `projection_runs`, never confused with raw or canonical truth.

### Internal immutable IDs

- All core tables use immutable internal IDs.
- External identifiers are attributes, not primary keys.
- Public URLs should prefer stable internal IDs or slugs derived from them, not external codes.

Recommended default for this project:

- Prefer `UUID` stored in PostgreSQL’s native `uuid` type.
- If the implementation team wants time-ordered generation, prefer UUIDv7 generation while still using the native `uuid` column type.

UUID vs ULID for this project:

- `UUID`:
  - strongest PostgreSQL fit because the database has a native `uuid` type
  - simpler indexing, storage, and operational tooling
  - well-supported in Laravel and adjacent tooling
  - less human-readable than ULID, but human readability is not the main requirement for core keys

- `ULID`:
  - better human readability and lexical sortability
  - fits Laravel well
  - weaker PostgreSQL fit because it is typically stored as `char(26)`/text rather than a native type
  - adds avoidable storage and operational awkwardness for a model with many high-volume tables

Recommendation:

- Use `UUID` as the project default.
- Do not optimise for visually readable IDs in the canonical schema; use slugs, references, and admin tooling for human-facing workflows instead.

### Enum, check constraint, and lookup table strategy

Use three different mechanisms on purpose:

- **PostgreSQL enums:** use sparingly, only for low-churn technical states that are unlikely to need metadata or frequent policy changes.
- **Check constraints on text columns:** default choice for shared workflow/state vocabularies where the allowed set is small but may still evolve over time.
- **Lookup/reference tables:** use where the value set carries metadata, public meaning, or configurable behaviour.

Concrete guidance for this project:

- Workflow/publication states such as `draft`, `submitted`, `under_review`, `approved`, `published`, `disputed`, `rejected`, `archived`:
  - store as text with check constraints
  - record history in `state_transitions`

- Visibility states such as `public`, `restricted`, `private`:
  - store as text with check constraints

- Verification levels and trust levels:
  - store as text with check constraints on `users`, `permissions`, and related tables
  - keep the vocabulary small and policy-driven unless a later implementation genuinely needs metadata-rich levels

- Content types:
  - use the `content_types` lookup table
  - this table already carries metadata such as `is_authoritative`, `default_visibility`, and `allows_ai_assist`

- Roles and permissions:
  - use lookup/reference tables because they are operational configuration, not mere enum labels

Project recommendation:

- Do not use PostgreSQL enums for policy-facing vocabularies such as workflow state, visibility, trust level, verification level, or content type.
- Prefer check-constrained text for small shared vocabularies and lookup tables where the value set has metadata or will be administered.

### Publication and review states

- Stateful records use the shared vocabulary from ADR-0027: `draft`, `submitted`, `under_review`, `approved`, `published`, `disputed`, `rejected`, `archived`.
- `state_transitions` stores history; the current state may be denormalised on the table for query efficiency.
- Review-first publication means no public content or public-facing factual change skips review.

### Content classification

- `content_type_id` and `content_items` are the primary mechanism for keeping assertions, analysis, editorial, and evidence visibly separate.
- Core factual tables do not store editorial prose or opinion fields.
- AI-assisted outputs live in `analysis_records` with explicit labelling, not in factual domain tables.

### Public vs internal visibility

- Visibility is a first-class field on content-bearing and workflow-bearing tables.
- Use `public`, `restricted`, and `private` visibility classes consistently.
- Search indexing, read models, and APIs must filter on visibility and state, not assume everything in PostgreSQL is safe to expose.

### Pseudonymous public users with private accountability

- `users` holds public pseudonymous identity.
- `user_identities` and `identity_verifications` hold private accountability signals.
- Permissions should be evaluated against both capabilities and verification/trust levels.

### Neutrality-safe separation of facts from interpretation

- Facts live in domain tables.
- Evidence links facts to primary sources.
- Assertions remain non-authoritative until supported and reviewed.
- Analysis and editorial content may interpret facts, but never overwrite them or masquerade as them.

### Use explicit foreign keys first

- Core identity and factual relationships should use strict foreign keys whenever the target type is known at design time.
- Do not replace a known domain relationship with a polymorphic pair just because it is convenient in Laravel.

Use strict foreign keys for records such as:

- `spend_records.council_id`, `spend_records.council_version_id`, `spend_records.organisation_id`
- `contracts.council_id`, `contracts.dataset_version_id`
- `contract_suppliers.contract_id`, `contract_suppliers.organisation_id`
- `foi_requests.council_id`
- `dataset_versions.dataset_id`

Use polymorphic references only for genuinely cross-cutting workflow and annotation features, where the whole point of the table is to attach the same behaviour to many unrelated subject types:

- `evidence_links`
- `comments`
- `flags`
- `state_transitions`
- `audit_logs`
- `moderation_case_items`
- `investigation_items`
- `content_items`

Contributor rule of thumb:

- if the table describes core truth about a domain entity, use strict foreign keys
- if the table annotates, reviews, flags, comments on, or publishes many different subject types, polymorphic links are acceptable

Avoid using polymorphic relations to hide weak domain modelling or to postpone important schema decisions.

## 4. Implementation Decision Points

These decisions should be locked before writing migrations:

- **ID type:** use native PostgreSQL `uuid` columns by default; only switch to ULID if there is a concrete implementation need that outweighs PostgreSQL simplicity.
- **State vocabulary storage:** use check-constrained text for workflow/publication/visibility/trust/verification states; avoid PostgreSQL enums for policy-facing vocabularies.
- **Lookup-table boundary:** keep metadata-rich vocabularies in tables (`content_types`, `roles`, `permissions`, taxonomies), not in enums.
- **FK vs polymorphic boundary:** use strict foreign keys for domain truth; reserve polymorphic references for cross-cutting workflow/annotation tables.
- **Publication-layer boundary:** do not wrap every factual row in `content_items`; keep factual records in domain tables and use `content_items` for evidence, assertions, analysis, editorial, and other genuinely publishable content entities.

## 5. Important Notes and Modelling Cautions

### UK-wide taxonomy validation

- The model is intentionally jurisdiction-aware.
- Before writing migrations, implementation should validate authority and geography taxonomy choices against devolved-nation realities and publishing practices.
- This is a schema validation task, not a reason to weaken the temporal or provenance model.

- Do not destructively overwrite raw artefacts or raw observations. If a council republishes a file or a correction changes a normalised interpretation, add a new version or correction event.
- Do not treat derived outputs as canonical fact. Read models, search documents, AI-assisted analysis, and ranking outputs are rebuildable projections.
- Review states matter because this platform is public-facing. Unreviewed submissions, assertions, and sensitive material must not leak into public pages or APIs.
- Content separation matters because neutrality is a system design problem, not only an editorial style problem. Assertions, analysis, and editorial content can be valuable, but they must not sit in the same conceptual bucket as canonical facts.
- AI outputs must never be treated as raw fact or as evidence. They are assistive analysis and must remain labelled, reviewable, and linked to source inputs.
- Joins between historic and current entities must be explicit and time-aware. A current council or organisation identity must not be backfilled into historic records without an explicit mapping basis and provenance.
- The current product focus is council transparency, but the model is intentionally UK-wide in scope. Before implementation, the authority classification and geography taxonomy should be checked against devolved nation-specific publishing patterns so the schema does not accidentally hard-code England-only assumptions.
