# ADR-0030: Dispute, Takedown, and Legal Risk Handling

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil will publish information that may be challenged. Challenges may be legitimate (errors, outdated data, misattribution) or bad-faith (attempts to suppress evidence).

The platform must reduce legal and reputational risk while remaining evidence-led and transparent. It must also protect against defamation risk by ensuring unverified allegations are not presented as fact (ADR-0012).

This ADR is about platform handling and process. It is not legal advice.

## Decision

Adopt a structured dispute and takedown workflow with temporary restriction and evidence review.

Dispute raising:

- Provide a mechanism to raise a dispute against a published item, requiring:
  - what is disputed
  - why it is disputed
  - what evidence supports the challenge (or what evidence is missing)
  - contactability at least at a verified level for high-risk disputes (ADR-0026)

Handling approach (conceptual):

- **Triage:** classify dispute type (factual error, identity mislink, privacy issue, legal risk, harassment).
- **Temporary restriction when warranted:** if privacy or credible legal risk is raised, restrict visibility pending review (ADR-0024) rather than hard deletion.
- **Evidence-led review:** review the underlying source artefacts and provenance links (ADR-0007, ADR-0016).
- **Outcome states:** apply outcomes using the record state model (ADR-0027):
  - add clarification/correction and return to `published`
  - mark as `disputed` where uncertainty remains
  - `archived` if superseded
  - `rejected` for disputes that are unsupported or bad-faith (with internal notes)

Defamation risk handling:

- assertions and investigations must be labelled and moderated to avoid presenting allegations as verified facts (ADR-0012, ADR-0020).
- require higher trust/verification for publishing sensitive allegations or named accusations, with conservative defaults.

## Consequences

- The platform can respond quickly to high-risk claims (privacy/legal) without losing the audit trail.
- Moderation requires disciplined audit logging and reason recording (ADR-0025).
- This increases process overhead but protects long-term public trust and reduces the risk of irresponsible publication.

