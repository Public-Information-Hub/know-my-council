# ADR-0038: AI Usage and Limitations Policy

Status: accepted
Date: 2026-04-04

## Context

AI tools can support summarisation and pattern discovery, but they are not reliable sources of truth. In a civic transparency context, AI misuse can cause serious harm:

- invented facts or sources presented as evidence
- overconfident conclusions or accusations
- biased framing presented as neutral analysis

The platform must treat AI output as assistive, not authoritative.

## Decision

Allow AI-assisted tooling only within explicit boundaries, with labelling and human review requirements.

Acceptable uses (examples):

- summarising a specific source artefact *with a link to the artefact*
- extracting structured fields from documents as a draft for human review
- clustering/duplicate detection suggestions (not automatic merges) (ADR-0029)
- anomaly detection flags that are explicitly labelled as “requires review”

Prohibited uses:

- generating accusations or allegations about individuals or organisations
- presenting AI output as evidence or as verified fact
- publishing conclusions without underlying evidence links
- “auto-publishing” AI-derived narratives to public pages without review

Requirements:

- AI-generated or AI-assisted content must be explicitly labelled as such (content type: analysis) (ADR-0037).
- Any AI output that affects public presentation must be human-reviewed before publication (ADR-0027).
- AI outputs must link to the exact inputs used (artefacts, record sets, prompt/config where appropriate) to support reproducibility and audit (ADR-0007, ADR-0025).

## Consequences

- AI can help scale triage and discovery without becoming a source of truth.
- The platform must store enough context to audit AI-assisted outputs (inputs, versions, and reviewer actions).
- Moderation must treat AI-generated allegations as high-risk content and restrict it by default (ADR-0020, ADR-0024).

