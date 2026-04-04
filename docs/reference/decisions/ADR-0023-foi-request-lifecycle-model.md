# ADR-0023: FOI Request Lifecycle Model

Status: accepted
Date: 2026-04-04

## Context

FOI/EIR support is part of the platform’s evidence acquisition direction (ADR-0011). FOI/EIR activity has time-sensitive stages, variable outcomes, and can include sensitive information.

To avoid confusion and misinformation, FOI/EIR items need explicit lifecycle modelling and clear linkage to platform entities and evidence.

## Decision

Model FOI/EIR requests with explicit lifecycle stages and visibility controls.

Lifecycle stages (conceptual):

- `draft` (private by default; user-owned)
- `submitted` (sent to authority; capture submission metadata)
- `acknowledged` (optional state if acknowledgement exists)
- `response-received` (response exists; artefacts stored) (ADR-0016)
- `needs-clarification` (authority asks for clarification)
- `partially-fulfilled` / `fulfilled`
- `refused` / `no-response`
- `closed` (final state, with outcome)

Linkage requirements:

- link a request to an authority (temporal identity aware) (ADR-0004, ADR-0008)
- link to the transparency gap it is intended to address (if recorded)
- link any response artefacts and derived datasets back to the request (ADR-0007, ADR-0016)

Visibility considerations:

- drafts and sensitive details should be restricted
- published requests/responses must respect redactions and personal data handling
- the platform must clearly indicate that a “request exists” is not the same as “the claim is verified”

## Consequences

- FOI/EIR becomes a structured evidence acquisition workflow rather than an informal comment thread.
- The platform can report coverage and outcomes without presenting unverified expectations as facts.
- Moderation and restricted-data handling are required for responsible FOI/EIR support (ADR-0020, ADR-0024).

