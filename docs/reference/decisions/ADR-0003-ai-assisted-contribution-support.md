# ADR-0003: AI-Assisted Contribution Support

Status: accepted
Date: 2026-04-04

## Context

Many contributors will use AI assistance for code, documentation, and project materials. AI can improve productivity, but it can also introduce:

- invented facts and sources
- over-claiming of implementation status
- bloated or mixed-concern changes that are hard to review
- accidental shifts in governance language or evidence standards

For a civic transparency project, these failure modes undermine trust and maintainability.

## Decision

Allow AI-assisted contributions, but set explicit repository standards:

- `AGENTS.md` provides canonical behavioural instructions for AI agents and AI-assisted contributors.
- Human contributors remain responsible for submissions.
- Evidence-sensitive changes require additional care and must not invent facts, sources, or institutional details.
- Pull request templates and contributing guidance explicitly ask for verification and disclosure where AI was materially used.

## Consequences

- The repository is more robust to low-quality AI-generated changes.
- Maintainers have a shared baseline for review expectations.
- Contributors can use AI tools without the project becoming “AI-first” or losing evidence discipline.

