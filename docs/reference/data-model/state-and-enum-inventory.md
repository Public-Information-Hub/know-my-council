# State and Enum Inventory

## 1. Purpose

This document is the canonical inventory of workflow states and controlled vocabularies used across KnowMyCouncil.

It exists to prevent vocabulary drift during implementation. In this project, naming consistency is part of correctness because:

- review and publication are safety gates, not just UI decoration
- moderation and dispute handling must be reconstructable later
- provenance and audit trails must join cleanly across systems
- contributors and AI-assisted contributors must not introduce new ad-hoc values

If a new value is needed, it should be added here first and then implemented consistently.

## 2. Modelling Guidance (specific to this platform)

This platform has two kinds of “enums”:

- **policy-facing vocabularies** (publication state, visibility, trust levels): likely to evolve and are referenced across many tables
- **metadata-rich vocabularies** (content types, roles, permissions): have attributes and behaviours attached

Recommended mechanisms:

- **PostgreSQL enums**
  - Use sparingly.
  - Only use when the value set is low-churn and internal/technical (not policy-facing).
  - Avoid for publication/moderation/identity policy vocabularies because changing PostgreSQL enums is operationally awkward and encourages schema churn.

- **Check constraints on text columns**
  - Default for shared workflow/policy vocabularies.
  - Keeps migrations simple and reduces coupling while still preventing invalid values.
  - Works well with Laravel model casts and validation.

- **Lookup/reference tables**
  - Use when the value set carries metadata or configurable behaviour (for example `content_types`, `roles`, `permissions`).
  - Prefer lookup tables for anything that needs labels, defaults, or rule flags.

- **Application-level constant sets**
  - Use for action families and other code-owned groupings where the database should not encode every string.
  - Must still map onto a constrained database field where cross-system integrity matters (for example `action_family` in `audit_logs`).

General rule:

- policy vocabularies: check-constrained text
- metadata-rich vocabularies: lookup tables
- purely internal/technical: consider PostgreSQL enum, but default to check constraint unless you have a concrete reason

## 3. Canonical Inventories

Values in this section are the initial canonical set. Keep them lean and expand only when a real workflow requires it.

### Content / Publication

#### Content types (`content_types.content_key`)

These represent content classification for the publication layer. They must not blur the boundary between authoritative facts and non-authoritative interpretation.

| Value | Meaning | Authoritative | Notes |
|---|---|---:|---|
| `factual_record` | Canonical factual record intended for public rendering. | Yes | Not every factual row needs a content wrapper; use only when a publishable wrapper is required. |
| `evidence` | Evidence unit linked to source artefacts. | Supporting only | Evidence is authoritative as supporting material, not as conclusion. |
| `assertion` | User-submitted unverified claim/lead. | No | Must never be presented as verified fact. |
| `analysis` | Derived analysis (human or AI-assisted). | No | Must link to inputs and be review-gated. |
| `editorial` | Editorial/journalism narrative layer. | No | Must be attributed and separated from facts. |
| `investigation` | Investigation container content item. | No | Not a finding by default; links to evidence and assertions. |
| `foi_request` | FOI/EIR request content item. | No | Request existence is not evidence of a claim. |

#### Publication/workflow states (`public_state`)

This is the shared state vocabulary from ADR-0027.

| Value | Meaning | Allowed on |
|---|---|---|
| `draft` | Private; not submitted for review. | Draftable items (FOI drafts, editorial drafts, etc.). |
| `submitted` | Submitted into workflow, not yet under review. | Submissions and publishable content. |
| `under_review` | Actively being reviewed/triaged. | Submissions, content items, moderation cases where applicable. |
| `approved` | Approved for publication but not yet published. | Publishable content and records that use staged publication. |
| `published` | Publicly visible (subject to visibility rules). | Public-facing content and records. |
| `disputed` | Published but actively disputed/challenged. | Published content and records. |
| `rejected` | Not accepted for publication; retained with rationale. | Submissions and publishable content. |
| `archived` | No longer active; retained for auditability. | Most stateful items. |

#### Review states and outcomes

This platform uses review for two different purposes:

- **integrity review** (evidence/corrections): does it meet provenance and correctness expectations?
- **publication safety review**: is it safe and correctly labelled to publish?

Keep the vocabulary small. Prefer a `decision` field rather than inventing many “review states”.

Recommended `decision` values (for `reviews.decision` and `content_reviews.decision`):

| Value | Meaning |
|---|---|
| `approve` | Approved as submitted. |
| `request_changes` | Needs changes before approval. |
| `needs_info` | Missing required context or provenance. |
| `reject` | Rejected (retain record and rationale). |
| `restrict` | Approved only for restricted visibility, or requires restriction pending safety/privacy handling. |

#### Visibility states (`visibility`)

From ADR-0024.

| Value | Meaning |
|---|---|
| `public` | Safe for anonymous viewing. |
| `restricted` | Visible to authenticated users with capability and/or the submitter. |
| `private` | Visible only to the submitter and authorised maintainers. |

### Moderation

#### Moderation case types (`moderation_cases.case_type`)

Keep this taxonomy practical and tied to implementable workflows.

| Value | Meaning |
|---|---|
| `conduct` | Code of conduct enforcement and user safety. |
| `privacy` | Personal data, redactions, sensitive documents. |
| `legal_risk` | Defamation/takedown/legal risk handling (process, not legal advice). |
| `misinformation` | Evidence vs assertion boundary breach, misleading presentation. |
| `harassment` | Targeting, threats, repeated bad-faith behaviour. |
| `takedown` | Formal takedown/dispute process case. |
| `security` | Security-adjacent moderation (route to `SECURITY.md` where applicable). |

#### Moderation case statuses (`moderation_cases.case_state`)

| Value | Meaning |
|---|---|
| `open` | Created, not yet triaged. |
| `triage` | Classification and initial risk assessment. |
| `in_review` | Active review/investigation in progress. |
| `action_taken` | Action applied (restriction, removal, warning, etc.). |
| `no_action` | Reviewed and no action required. |
| `closed` | Case concluded and closed. |

#### Flag types (`flags.flag_type`)

| Value | Meaning |
|---|---|
| `factual_error` | Suspected incorrect factual record or summary. |
| `identity_mislink` | Wrong join/mapping (supplier/council/contract mismatch). |
| `missing_provenance` | Record lacks source linkage or provenance is unclear. |
| `privacy` | Personal data or redaction issue. |
| `harassment` | Abuse or targeting. |
| `legal_risk` | Potential defamation/takedown risk. |
| `spam` | Spam/noise. |
| `other` | Requires manual triage; must include free-text reason. |

#### Flag statuses (`flags.public_state`)

Flags are not verdicts; they are workflow triggers.

| Value | Meaning |
|---|---|
| `submitted` | Flag raised and awaiting triage. |
| `under_review` | Flag is being reviewed/triaged. |
| `approved` | Flag accepted as valid and action is being taken (often linked to a moderation case). |
| `rejected` | Flag dismissed as unsupported/bad-faith/noise. |
| `archived` | Superseded/closed. |

### Identity and Trust

#### Identity verification levels (`users.verification_level`)

This is the internal verification signal layer (not community trust).

| Value | Meaning |
|---|---|
| `unverified` | No verified internal signal beyond basic authentication. |
| `verified` | Has a verified internal signal (for example verified contact channel). |

#### Trust levels (`users.trust_level`)

This is the community/evidence-based trust level used for capability gating. Keep it minimal and tie it to capability assignment.

| Value | Meaning |
|---|---|
| `baseline` | Normal contributor; no elevated trust. |
| `trusted` | Elevated trust based on evidence discipline and review behaviour. |

Note:

- ADR-0026 describes “unverified/verified/trusted” as a conceptual progression. Implementation should keep verification and trust separate (`verification_level` + `trust_level`) to avoid conflating internal identity verification with community-earned trust.

#### Team member roles (`team_members.membership_role`)

| Value | Meaning |
|---|---|
| `member` | Standard team member. |
| `lead` | Team lead/coordinator (does not bypass platform permissions). |
| `editor` | Editorial/review-focused team member (still permission-gated). |

### FOI / EIR

#### FOI request states (`foi_requests.public_state`)

This uses the shared publication/workflow states. Lifecycle detail is tracked in `foi_request_events`.

Primary states:

- `draft`, `submitted`, `under_review`, `published`, `disputed`, `rejected`, `archived`

#### FOI response types (`foi_responses.response_type`)

| Value | Meaning |
|---|---|
| `full` | Request fulfilled in full (as far as response indicates). |
| `partial` | Partially fulfilled. |
| `refusal` | Refused/withheld. |
| `clarification_required` | Authority requested clarification. |
| `no_response` | No response received within expected window (recorded as status). |
| `other` | Non-standard response; requires summary. |

#### FOI event types (`foi_request_events.event_type`)

Keep these as events, not states.

| Value | Meaning |
|---|---|
| `drafted` | Draft created/updated (may be private). |
| `submitted` | Sent to authority. |
| `acknowledged` | Authority acknowledgement received. |
| `clarification_requested` | Authority requests clarification. |
| `clarification_sent` | Clarification sent. |
| `response_received` | Response received (attachments stored as artefacts). |
| `fulfilled` | Marked fulfilled (full or partial). |
| `refused` | Marked refused. |
| `closed` | Closed with final outcome. |
| `note_added` | Internal/admin note added to lifecycle. |

### Audit / Traceability

#### Actor types (`audit_logs.actor_type`, `state_transitions.actor_type`)

| Value | Meaning |
|---|---|
| `user` | Human account performed the action. |
| `system` | System-initiated action (maintenance, automated policy). |
| `import` | Import run performed the action. |
| `job` | Background worker/job performed the action. |
| `api` | Action via API client/token. |
| `ai_process` | AI-assisted or AI-driven system process (still human-reviewed for publication). |

#### Context types (`audit_logs.workflow_type` / `context_json.context_type`)

Prefer a small set of contexts to support reconstruction.

| Value | Meaning |
|---|---|
| `ingestion` | Imports, parsing, normalisation. |
| `projection` | Read models / projections rebuild. |
| `indexing` | Search indexing. |
| `publication` | Publishing actions and content gating. |
| `review` | Review workflow actions. |
| `moderation` | Moderation case actions. |
| `correction` | Correction proposals and application. |
| `foi` | FOI/EIR workflow actions. |
| `investigation` | Investigation workflow actions. |
| `identity` | Verification/permissions actions. |

#### Action families (`audit_logs.action_type` high-level families)

Do not attempt to predefine every action string. Instead, standardise families and require action types to begin with one of these family prefixes.

| Family prefix | Meaning |
|---|---|
| `identity.` | Identity verification, roles, permissions. |
| `ingestion.` | Imports, source files, dataset versions. |
| `data.` | Canonical record changes (facts/mappings). |
| `publication.` | Publish/unpublish, visibility changes. |
| `review.` | Review actions and decisions. |
| `moderation.` | Moderation actions and outcomes. |
| `foi.` | FOI/EIR lifecycle actions. |
| `investigation.` | Investigation creation, linking, outcomes. |
| `system.` | Maintenance, migrations, internal ops actions. |

#### State fields (`state_transitions.state_field`)

`state_transitions.state_field` identifies which state column changed on the target entity.

Phase 1 allowed values:

| Value | Meaning |
|---|---|
| `public_state` | Publication/workflow state for publishable records. |
| `run_state` | Import run lifecycle for `import_runs.run_state`. |

Implementation approach: check constraint (Phase 1), expected to expand as more stateful workflows are introduced.

### Data / Imports

#### Import run statuses (`import_runs.run_state`)

| Value | Meaning |
|---|---|
| `queued` | Scheduled but not started. |
| `running` | In progress. |
| `succeeded` | Completed successfully. |
| `failed` | Failed (retained with errors). |
| `cancelled` | Cancelled by operator/system. |

#### Mapping confidence (`mapping_confidence`)

Used for joins and normalisation decisions where uncertainty must be explicit.

| Value | Meaning |
|---|---|
| `high` | Strong evidence supports mapping. |
| `medium` | Reasonable basis, some ambiguity. |
| `low` | Weak basis; requires review. |
| `unknown` | Not assessed or not applicable. |

#### Dataset freshness (`dataset_versions.freshness_state`)

| Value | Meaning |
|---|---|
| `current` | Within expected update range. |
| `stale` | Outside expected update range. |
| `unknown` | Cadence/reporting period unclear. |

## 4. Recommended Implementation Approach

This section recommends how to implement each inventory to prevent drift while keeping migrations maintainable.

- Content types: lookup table (`content_types`)
  - Reason: carries metadata and defaults (`is_authoritative`, `default_visibility`, flags).

- Publication/workflow states: check constraint on text columns
  - Reason: policy-facing vocabulary likely to evolve; also shared across many tables.

- Review decisions: check constraint on text columns
  - Reason: small controlled set; referenced across multiple review tables.

- Visibility states: check constraint on text columns
  - Reason: policy-facing, shared, referenced in API and indexing filters.

- Moderation case types/statuses: check constraints on text columns
  - Reason: policy-facing and likely to evolve with operational learning; keep metadata in code/docs rather than enums.

- Flag types/statuses: check constraints on text columns
  - Reason: small controlled set; easy to extend intentionally.

- Identity verification and trust levels: check constraints on text columns
  - Reason: policy-facing and must remain easy to evolve; separate verification from trust.

- Team member roles: check constraints on text columns
  - Reason: small set; avoid overengineering early.

- FOI response types and FOI event types: check constraints on text columns
  - Reason: vocabulary evolves as real FOI usage emerges; avoid enum churn.

- Actor types and context types: check constraints on text columns
  - Reason: cross-cutting integrity requirement; must be consistent for forensic reconstruction.

- Action families: application constant set plus check constraint on `action_family` field (or family prefix enforcement)
  - Reason: the exact `action_type` strings are code-owned; the family set should remain constrained.

- Import run statuses, mapping confidence, dataset freshness: check constraints on text columns
  - Reason: shared across ingestion code and reporting; small and stable enough for constraints.

Recommendation:

- Avoid PostgreSQL enums for these vocabularies unless a later implementation has a concrete operational need that cannot be met with constraints or lookup tables.

## 5. Cautions

- Values likely to evolve before implementation:
  - moderation case types and statuses
  - FOI response types and event types
  - action families and context types (as new workflows appear)

- Values that should remain tightly controlled and stable:
  - publication/workflow states (`public_state`)
  - visibility states
  - actor types
  - mapping confidence levels

- Avoid inconsistent naming:
  - use `snake_case` values everywhere
  - do not introduce synonyms (`in_review` vs `under_review`); pick one (this project uses `under_review`)
  - do not encode meaning in free-text strings; use structured fields + `reason_note` where needed

- Handling future additions safely:
  - add new values here first
  - update documentation and constraints together
  - prefer adding values over renaming existing ones; renames break history and forensic reconstruction
  - if a value becomes obsolete, keep it valid but treat it as deprecated in docs and UI
