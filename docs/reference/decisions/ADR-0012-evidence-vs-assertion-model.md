# ADR-0012: Evidence vs Assertion Model

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil will eventually contain both:

- source-backed records (what a council published, what a document contains)
- community-reported leads and interpretations (suspicions, investigations, corrections)

If the platform mixes evidence and assertion without a clear boundary, it risks misinformation, reputational harm, and loss of trust.

## Decision

Adopt an explicit “evidence vs assertion” model:

- **Evidence** is a claim or record that can be traced to a stored source artefact (or a clearly linked external primary source) with provenance.
- **Assertion** is a user-submitted claim, interpretation, or lead that is not yet supported by stored source artefacts.

Operational rules (future-facing):

- Evidence and assertions must be stored and displayed differently.
- Assertions must not be presented in the UI as verified facts.
- Assertions should have structured fields to encourage clarity:
  - what is being claimed
  - what evidence would support or refute it
  - any links provided (even if not yet captured as artefacts)
- Evidence should be immutable at the artefact layer (ADR-0007) and should retain as-published context (ADR-0005).

## Consequences

- Public pages must prioritise evidence-backed records and clearly label unverified assertions.
- Moderation workflows must focus on:
  - preventing defamatory or unsupported claims being presented as fact
  - encouraging contributors to attach primary sources
  - escalating sensitive content (privacy, security, potential wrongdoing allegations)
- Data modelling must support “uncertain/unknown” states without forcing a false join or overconfident mapping.

