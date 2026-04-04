# ADR-0027: Content and Record State Model

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil must only publish reviewed content. It must also represent uncertainty, disputes, and superseded/corrected records without destructive overwrites (ADR-0014).

Without a consistent state model, contributors and tooling will:

- accidentally publish unreviewed content
- treat “disputed” as “false” (or ignore it)
- overwrite history instead of recording changes

## Decision

Adopt a shared state vocabulary across major user-facing artefacts:

- submissions (evidence/assertions/FOI/investigation items)
- data records (canonical entities and derived/public records)
- investigations

States (canonical vocabulary):

- `draft`: private, not submitted for review.
- `submitted`: submitted into a workflow, not yet under review.
- `under_review`: actively being reviewed/triaged; may request more info.
- `approved`: meets requirements for publication, but not yet published (allows scheduled or dependency-aware publishing).
- `published`: publicly visible output (subject to visibility rules in ADR-0024).
- `disputed`: a published item has an active dispute or credible challenge; not removed by default.
- `rejected`: did not meet requirements for publication (keep record and rationale).
- `archived`: no longer active (for example superseded, obsolete, or closed) but retained for auditability.

### Transitions (why they exist)

Allowed high-level transitions (conceptual):

- `draft` -> `submitted`: contributor requests review.
- `submitted` -> `under_review`: reviewer/editor picks up item.
- `under_review` -> `approved`: evidence/review requirements met.
- `approved` -> `published`: item becomes publicly visible.
- `under_review` -> `rejected`: item fails requirements; rationale recorded.
- `published` -> `disputed`: dispute raised with supporting information.
- `disputed` -> `published`: dispute resolved without unpublishing (add clarifications/corrections).
- `published` or `disputed` -> `archived`: superseded/replaced/closed while retained (aligned with ADR-0014).

Not all artefacts will support every transition, but the vocabulary is shared so contributors and tooling remain consistent.

### Relationship to existing workflow states

Where earlier ADRs define more granular states (for example `needs-info` in ADR-0021), those are sub-states within `under_review`. This ADR defines the cross-platform canonical state vocabulary.

## Consequences

- Public publishing becomes explicitly gated: only `published` content appears publicly, and only after review.
- “Disputed” becomes a first-class state that supports transparency without overreacting to bad-faith claims.
- Corrections and replacements can be represented without overwriting history, supporting audit and trust (ADR-0014, ADR-0025).

