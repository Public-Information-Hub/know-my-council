# ADR-0024: Public vs Internal Data Separation

Status: accepted
Date: 2026-04-04

## Context

Not all platform data should be public:

- personal data may appear in documents or correspondence
- moderation notes and reports should not be exposed by default
- some submissions may be sensitive until reviewed
- private drafts (FOI requests, evidence submissions) need restricted visibility

Without explicit separation, the platform risks leaking sensitive information or discouraging participation.

## Decision

Adopt explicit data visibility classification and access boundaries.

Conceptual visibility classes:

- **Public:** safe for anonymous viewing.
- **Restricted:** visible to authenticated users with specific capabilities (for example moderators/editors) and/or the submitting user.
- **Private:** visible only to the submitting user and authorised maintainers (for example drafts).

Rules:

- public pages and APIs may only expose fields explicitly designated public.
- restricted/private data must never be accidentally included in public search indexing (ADR-0009) or derived read models (ADR-0010) without deliberate design.
- moderation actions and rationales should have internal fields, with carefully chosen public-facing explanations where appropriate (ADR-0020).

## Consequences

- Data modelling must treat visibility as a first-class attribute, not an afterthought.
- Search indexing and read model generation must enforce visibility filtering by design.
- This reduces leakage risk, but adds complexity to querying and projections.

