# ADR-0026: Identity Verification and Trust Levels

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil allows pseudonymous participation (ADR-0017) but must protect against misuse and support accountability for sensitive actions. A civic transparency platform will attract bad actors as well as well-meaning contributors.

We need a model that:

- keeps public participation accessible (pseudonyms allowed)
- increases accountability as impact increases (publishing, moderation, permission changes)
- does not assume real-world identity is always safe or necessary

## Decision

Adopt two identity layers and three trust levels.

Identity layers:

- **Public identity (pseudonymous):** the username/profile shown to the public.
- **Internal verified identity (private):** verification signals held internally (for example verified contact channel; other checks may be added later). This information is not public by default and is protected as restricted/private data (ADR-0024).

Trust levels (conceptual):

- **Unverified:** account exists but has no verified identity signals beyond basic authentication.
- **Verified:** account has a verified internal signal (for example a verified contact channel). This enables higher accountability actions.
- **Trusted:** account has earned elevated trust through evidence-backed contribution and review discipline (ADR-0019) and has been granted additional capabilities (ADR-0018).

Verification requirements (conceptual, capability-aligned):

- **Unverified can:** read, submit low-risk items (evidence submissions/notes) that remain non-published until reviewed.
- **Verified required for:** actions that create public-facing impact or elevated risk, such as:
  - creating/publishing investigations intended for public viewing
  - publishing FOI/EIR requests/responses beyond private drafts
  - filing disputes/takedown requests (to reduce abuse)
- **Trusted required for:** high-impact actions, such as:
  - approving/publishing evidence into public records (ADR-0021, ADR-0027)
  - applying normalised mappings or entity merges
  - performing moderation actions (ADR-0020)
  - changing roles/capabilities (ADR-0018)

This ADR defines the policy boundary; exact verification mechanics are implementation details to be chosen later.

## Consequences

- The platform can support pseudonymous participation while scaling accountability for higher-impact actions.
- Sensitive internal identity signals must be stored and handled as restricted/private data (ADR-0024) with audit logging (ADR-0025).
- Maintainership must remain conservative when granting “trusted” capabilities and should rely on evidence-backed behaviour, not popularity (ADR-0019).

