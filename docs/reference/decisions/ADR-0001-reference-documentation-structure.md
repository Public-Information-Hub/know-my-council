# ADR-0001: Reference Documentation Structure

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil needs a durable place to capture architectural, domain, and modelling guidance so it does not fragment across issues, PRs, and ad-hoc documents.

## Decision

Create a reference documentation system under `docs/reference/` with the following sections:

- `architecture/` for architectural reference docs
- `data-model/` for canonical modelling guidance
- `domain/` for domain term definitions
- `decisions/` for ADRs
- `timelines/` for short project context notes

Add a top-level `docs/reference/README.md` describing how to use the system and how to avoid turning it into a junk drawer.

## Consequences

- Contributors have a clear place to capture stable reference material and decision rationale.
- Maintainers can require architecture/modelling changes to update reference docs and/or add ADRs.
- Canonical reference, dated decisions, and contextual timeline notes are explicitly separated.

## Links

- `docs/reference/README.md`
