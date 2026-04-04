# ADR-0036: Neutrality and Bias Control Principles

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil is a civic transparency platform. It will be used by members of the public, journalists, researchers, and potentially by councils themselves. Trust depends on political neutrality and disciplined separation between evidence and interpretation.

Bias risk shows up in system design choices:

- wording and framing on public pages
- what gets highlighted or ranked
- what is treated as “fact” vs “claim”
- how uncertainty and disputes are presented

## Decision

Adopt neutrality requirements as system-level constraints, not as optional guidelines.

Neutrality requirements:

- **Evidence-led:** publish factual records only when they are source-backed and review-approved (ADR-0021, ADR-0027).
- **Separation of layers:** keep evidence/data distinct from analysis and editorial content (ADR-0037, ADR-0039).
- **No partisan framing:** the platform does not advocate for parties, candidates, or ideological positions. It focuses on verifiable records, provenance, and reproducible methods.
- **Uncertainty is explicit:** uncertain mappings and disputes are represented explicitly rather than “resolved” through tone or implication (ADR-0012, ADR-0027).

Enforcement expectations (system-level):

- UI must label content types and states clearly (evidence vs assertion vs analysis; published vs disputed).
- Rankings and summaries must disclose basis (coverage period, freshness, mapping confidence) to avoid misleading narratives (ADR-0015, ADR-0028).
- Moderation must enforce evidence-vs-assertion boundaries and remove/limit content that presents allegation as fact (ADR-0020, ADR-0038).

## Consequences

- Neutrality becomes a product requirement that informs design, review, and moderation decisions.
- The platform will sometimes look “less decisive” than advocacy content because it preserves uncertainty and provenance. This is a deliberate trade-off for trust.
- Contributors must treat framing and language as part of correctness, not just style (see ADR-0040).

