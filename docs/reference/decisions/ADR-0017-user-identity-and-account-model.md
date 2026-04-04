# ADR-0017: User Identity and Account Model

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil is a public-interest civic platform. Most readers will be anonymous. Some participation will require accountability and moderation to protect against misinformation, harassment, and low-quality submissions.

Contributors may be anonymous or pseudonymous. The platform must not assume real-world identity, but must still support:

- accountability for actions
- reversible permissions
- traceability for moderation and dispute resolution

## Decision

Adopt a dual-mode model: anonymous read by default; accounts required for actions that change platform state.

- **Anonymous access:** public pages and public APIs are accessible without an account where practical.
- **Account required for state-changing actions:** submitting evidence, assertions, corrections, FOI/EIR items, investigations, and moderation actions require an authenticated account.
- **Pseudonymous by default:** the platform supports pseudonyms/usernames; it does not require real-name identity as a baseline.
- **Accountability model:** accountability is enforced through:
  - verified contact channel for the account (implementation detail may vary)
  - audit logging of actions (ADR-0025)
  - moderation capability to restrict, suspend, or revoke privileges (ADR-0020)
- **Separation of identity and roles:** identity is not permission. Roles/permissions are handled separately (ADR-0018).

## Consequences

- The platform can remain broadly accessible to the public while protecting high-impact workflows behind authentication.
- Pseudonymity supports community participation, but requires strong moderation, audit logging, and careful public/internal separation (ADR-0024).
- Any future “identity verification” is an optional layer and must be justified; it is not assumed by default.

