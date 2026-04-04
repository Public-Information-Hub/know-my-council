# ADR-0025: Audit Logging and Action Traceability

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil needs to be defensible and trustworthy. When data changes, evidence is accepted/rejected, or moderation actions occur, we must be able to answer:

- who did what?
- when did it happen?
- what changed?
- why (where recorded)?

Auditability also protects maintainers and contributors by providing traceability when disputes arise or mistakes are made.

## Decision

Adopt audit logging as a core platform requirement for state-changing actions.

At minimum, log:

- authentication and permission changes (role/capability assignments) (ADR-0018)
- creation and state transitions for evidence submissions and assertions (ADR-0012, ADR-0021)
- normalisation/correction changes and supersessions (ADR-0014)
- moderation actions (hide/restrict/remove/lock/suspend) with reason codes and notes (ADR-0020)
- FOI/EIR request lifecycle transitions and artefact attachments (ADR-0023, ADR-0016)
- ingestion/import runs and projection/index rebuild triggers (ADR-0006, ADR-0010, ADR-0009)

Audit logs should record:

- actor (account)
- action type
- target entity reference
- timestamp
- before/after summary where practical
- correlation to an import run or workflow item where relevant

This ADR defines what must be auditable; it does not mandate a specific logging implementation.

## Consequences

- The platform can support credible dispute resolution and correction history.
- Maintainers can safely delegate capabilities because actions remain traceable.
- Logging adds operational overhead and storage, but it is required for a civic transparency system where trust and accountability are central.

