# ADR-0022: Investigations and Public Issue Tracking

Status: accepted
Date: 2026-04-04

## Context

People will use KnowMyCouncil not only to read published data, but to investigate anomalies and raise leads. Investigations can be valuable civic work, but can also drift into speculation and reputational harm if not anchored to evidence.

We need a model that supports investigation as a structured workflow linked to evidence, corrections, and FOI/EIR activity.

## Decision

Define an “investigation” as a structured, evidence-oriented thread with clear boundaries.

- An **investigation** is a container for:
  - a question or concern to be examined
  - linked evidence and source artefacts (ADR-0021)
  - linked assertions/notes that remain explicitly unverified until supported (ADR-0012)
  - related FOI/EIR items (ADR-0023)
  - outcomes (for example: “resolved”, “needs more evidence”, “disproved”, “ongoing”)
- Investigations must not be treated as findings by default. They are workflows that may or may not produce verified conclusions.
- Public visibility rules apply:
  - investigations can exist publicly, but sensitive allegations may require restriction or moderation (ADR-0020, ADR-0024)

## Consequences

- The platform needs to support linking investigations to authorities, suppliers, contracts, datasets, and documents without forcing premature identity joins (ADR-0008, ADR-0015).
- Moderation must be able to enforce evidence-vs-assertion labelling within investigations.
- Investigation outcomes should be evidence-backed and reproducible, not narrative.

