# ADR-0041: Auditing and Forensic Traceability Model

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil is not a generic content site. It is a public-interest civic transparency platform where records, claims, moderation decisions, and publication choices may be challenged later by contributors, public bodies, journalists, or affected organisations.

Ordinary application logging is not enough for that job.

Application logs help operate the system, but they do not reliably answer questions such as:

- who changed this record?
- what state did it move from and to?
- which source file, FOI response, review, or moderation case was involved?
- was this action taken by a user, an import, a job worker, an API client, or an AI-assisted workflow?
- what did the record look like before the change?

For this platform, traceability must support:

- user accountability, including pseudonymous but internally accountable users
- moderation and review traceability
- data corrections, disputes, and takedowns
- import/job/system actions
- provenance-linked publishing decisions
- public trust and defensibility when records are challenged

`updated_at` is not audit. Application logs are not an adequate audit substitute.

## Decision

Adopt a forensic traceability model with four distinct layers that work together but are not treated as the same thing.

### 1. Action audit

Maintain a general audit trail for state-changing actions.

Each audit record should capture at minimum:

- actor identity
- actor type
- action type
- target entity reference
- timestamp
- correlation/workflow context
- enough before/after context to understand the action

Actor type must support more than human users. It should be able to represent:

- authenticated user
- system process
- import run
- background job
- API client/token
- AI-assisted or system-generated action where relevant

Action audit answers: who or what acted, against what, when, and in which broader workflow context.

### 2. State transition history

Maintain separate append-only state transition history for workflow-bearing records.

This is distinct from action audit. It records:

- subject
- from state
- to state
- actor
- timestamp
- reason code/note where relevant

State transition history is required for:

- publication states
- moderation cases
- FOI lifecycle stages
- review/submission workflows
- dispute and takedown handling

State transitions answer: how did the workflow status evolve over time?

### 3. Record change history

Maintain record-level version or snapshot history for important mutable entities where the exact prior and new state may later need to be reconstructed.

Not every table needs full snapshot history. It is required for key entities where corrections, moderation, identity resolution, or publication decisions may materially change meaning.

Likely candidates include:

- normalised mappings and entity resolution decisions
- permissions/role assignments
- moderation cases and moderation outcomes
- published content metadata
- correction proposals and accepted corrections

Record history answers: what data changed, and what did the record look like before and after the change?

### 4. Provenance linkage

Maintain provenance as a separate but linked layer.

Publishable and disputed records must remain traceable to source context such as:

- source files
- dataset versions
- import runs
- evidence records
- FOI responses
- review decisions
- moderation cases where relevant

Provenance is not the same as audit:

- provenance explains where a fact or claim came from
- audit explains what actions were taken on platform records

### Additional rules

- Prefer append-only design for audit, import, and workflow history records.
- Raw imported data must not rely on in-place edits for history; republished or corrected source data should create new artefacts or new version rows.
- Publication and moderation decisions must be reconstructable later from stored history, not inferred from the current row state alone.
- Ordinary application logs remain useful for operations and debugging, but they are separate from the forensic model and must not be treated as the canonical audit trail.

## Consequences

- The platform becomes stronger in disputes, corrections, moderation review, and public accountability because key decisions and changes are reconstructable.
- This supports pseudonymous participation with real internal accountability, which is necessary for sensitive actions.
- Storage and implementation complexity increase, especially for entities that need full change history rather than simple `updated_at` timestamps.
- The implementation must be disciplined about which entities get full snapshot/version history and which only need action audit plus state transitions.
- Future implementers need to preserve the distinction between:
  - audit logs
  - state transitions
  - entity history/versioning
  - provenance records

That separation is the core design decision. Treating them as interchangeable would weaken traceability rather than strengthen it.

