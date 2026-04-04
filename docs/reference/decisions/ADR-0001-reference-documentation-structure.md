# ADR-0001: Reference Documentation Structure

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil is intended to be a long-lived civic transparency project. The repository needs a place to accumulate stable architectural and domain context over time.

Without a deliberate structure, important decisions and modelling guidance tend to be scattered across issues, PRs, and ad-hoc documents. That makes the project harder to maintain, harder to review, and riskier for AI-assisted contribution.

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
- Maintainers can ask for changes that affect core modelling to update reference docs and/or add ADRs.
- The docs surface an explicit separation between canonical reference, dated decisions, and contextual timeline notes.

