# ADR-0020: Moderation and Content Governance Model

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil will host public-facing content about public bodies, suppliers, spending, and investigations. This attracts risk:

- misinformation and overconfident claims
- harassment or targeting
- privacy issues (personal data in documents)
- politically charged disputes

Moderation must protect users and the project’s credibility while remaining evidence-led and non-partisan.

## Decision

Adopt a moderation model with clear boundaries, escalation, and separation of powers.

- **Moderation scope:** moderation covers conduct, safety, and the evidence vs assertion boundary (ADR-0012). Moderation is not “deciding what is true”; it is enforcing standards for what can be published and how it must be labelled.
- **Escalation paths:** define escalation for:
  - privacy/personal data concerns
  - harassment threats or repeated bad-faith behaviour
  - sensitive allegations or reputational risk content
  - security reports (refer to `SECURITY.md`)
- **Authority boundaries:**
  - moderators can hide/remove/lock content, and restrict accounts
  - editors can manage evidence review and normalisation decisions (ADR-0013 / ADR-0021)
  - a small set of maintainers control capability assignment and policy changes
- **Transparency:** moderation actions should have recorded reasons and be audit logged (ADR-0025). Where safe, provide user-visible explanations (without doxxing or exposing sensitive details).

## Consequences

- Tooling must support moderation actions that are reversible and traceable.
- Content types must support states like “hidden”, “restricted”, and “pending review” rather than hard deletion (aligned with ADR-0014).
- The project should document moderation guidance and escalation without pretending to be a large organisation.

