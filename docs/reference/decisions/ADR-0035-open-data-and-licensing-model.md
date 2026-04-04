# ADR-0035: Open Data and Licensing Model

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil aims to improve public access to information. Open data principles support reuse, scrutiny, and independent analysis.

However, “open” is constrained by:

- upstream licences and terms (councils publish under varying terms)
- privacy and personal data handling
- user-submitted materials (contributors may not own copyright)

The platform must not imply that all stored data is freely reusable if it is not.

## Decision

Adopt a licensing model that separates code, documentation, upstream data, and user submissions.

1. **Platform code**
  - The codebase remains under the repository licence (MIT) (see `LICENSE`).

2. **Upstream council datasets and documents**
  - Treat upstream artefacts as governed by their original licence/terms.
  - Record licence/terms metadata at dataset/artefact level where known (aligned with ADR-0015 and ADR-0016).
  - Do not relicense upstream artefacts by default.

3. **Derived datasets and exports produced by the platform**
  - Prefer open licensing for platform-generated exports where rights allow, but only after confirming:
    - the export does not include restricted/private information (ADR-0024)
    - the upstream licences permit the intended reuse
  - If licensing is unclear, mark exports as “licence unknown” or restrict them rather than implying open reuse.

4. **User-submitted content**
  - Users must only submit files/text they have the right to submit and publish.
  - Before enabling broad public submission features, the platform should have clear terms that specify the licence grant required for publication (policy requirement; not assumed to exist today).

## Consequences

- The project can support open reuse without making blanket claims that create legal risk.
- Dataset and artefact records must carry licensing metadata and surface it in APIs/exports (aligned with ADR-0031).
- Some outputs may be restricted or withheld until licensing and privacy conditions are satisfied, which is preferable to accidental misuse.
