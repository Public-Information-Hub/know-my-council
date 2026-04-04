# ADR-0039: Editorial and Journalism Layer

Status: accepted
Date: 2026-04-04

## Context

Good journalism and civic explanation can help the public understand complex procurement, finance, and governance information. However, editorial content can also introduce bias or be mistaken for authoritative platform records if not separated.

KnowMyCouncil must remain a trusted evidence platform first. Editorial outputs must not be able to overwrite or masquerade as canonical data.

## Decision

Treat editorial/journalism content as a distinct layer, separated from the core data platform.

Rules:

- Editorial content is a separate content type (ADR-0037) and is never the source of truth for factual records (ADR-0005).
- Editorial pieces must be:
  - attributed (author identity, date)
  - linked to supporting evidence artefacts and records
  - clear about what is evidence vs interpretation
- Editorial content is non-authoritative:
  - it may provide context and analysis
  - it must not present unverified assertions as fact (ADR-0012, ADR-0020)
- Publication is gated:
  - editorial content must follow review and publication states (ADR-0027)
  - sensitive claims require additional scrutiny and may be restricted pending review (ADR-0024)

This ADR defines separation and publication constraints; it does not define the editorial workflow UI.

## Consequences

- The platform can support journalism contributions without diluting data integrity.
- Editorial content remains auditable (links to evidence, clear authorship), reducing reputational and legal risk.
- Moderation must be able to apply consistent rules across editorial and non-editorial content, with explicit escalation paths (ADR-0020, ADR-0030).

