# ADR-0037: Content Type Classification Model

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil will hold multiple kinds of content. If they are mixed or presented with the same visual weight, users will misinterpret opinions as facts and assertions as verified records.

This is a civic platform where incorrect interpretation can cause harm. Content types must be explicit, enforced, and visible in the UI and API.

## Decision

Adopt a content type classification model with clear boundaries and presentation requirements.

Canonical content types:

1. **Factual data (records)**
  - Structured records representing “what the system currently believes is true” in the normalised/canonical layer.
  - Must be source-backed via provenance links to evidence (ADR-0007, ADR-0005).

2. **Evidence**
  - Source artefacts and evidence claims (files/captures, FOI responses, datasets) with provenance.
  - Evidence is not “conclusion”; it is the supporting material.

3. **User assertions**
  - Unverified claims, leads, or interpretations submitted by users.
  - Must never be presented as verified facts (ADR-0012).

4. **Analysis (human or AI-assisted)**
  - Derived interpretation or computation that goes beyond stating what a source contains.
  - Must be reproducible (method disclosed) and clearly labelled as analysis, not evidence.

5. **Editorial / journalism**
  - Narrative explanations, contextual reporting, or commentary intended for public understanding.
  - Must be clearly separated, attributed, and non-authoritative with respect to the underlying evidence (ADR-0039).

Presentation and API requirements:

- Content type must be visible to users and carried in API responses for public-facing content.
- Evidence links must be readily available for factual records.
- Assertions and analysis must carry warnings/disclaimers appropriate to their type and state.
- Search and read models must not collapse types into a single “result” without labelling (ADR-0009, ADR-0010).

## Consequences

- Data modelling and UI must treat content type as a first-class attribute (aligned with ADR-0024).
- Review workflows must be type-aware (evidence claims vs assertions vs editorial).
- Contributors cannot “launder” an assertion into a fact via analysis. The evidence boundary remains explicit.
