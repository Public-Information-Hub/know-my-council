# ADR-0040: Language and Presentation Standards

Status: accepted
Date: 2026-04-04

## Context

Language is part of system behaviour. Neutrality can be lost through phrasing even if the underlying data is accurate. A civic transparency platform must avoid emotional or accusatory framing that implies wrongdoing without evidence.

We need enforceable standards that apply to UI copy, public summaries, analysis output, and contributor-facing templates.

## Decision

Adopt neutral language requirements and prohibit accusatory framing in core platform presentation.

Rules:

- Use descriptive, source-linked phrasing.
- Avoid implying intent, corruption, incompetence, or malice unless the platform is quoting a source that explicitly states it (and even then, label it clearly as a quotation/opinion).
- Prefer uncertainty-aware language when the data is incomplete, stale, disputed, or mapped with low confidence (ADR-0015, ADR-0027, ADR-0028).
- Use consistent terms for content types and states (ADR-0037, ADR-0027).

Examples (non-exhaustive):

Acceptable:

- “This contract appears in the council’s published contract register (captured on YYYY-MM-DD).”
- “This supplier name was observed in the council’s spend dataset for 2025-01.”
- “Mapping confidence: low (name-only match; requires review).”
- “Disputed: a challenge has been raised; review is in progress.”

Unacceptable in core platform UI:

- “The council hid this information.”
- “This supplier is corrupt.”
- “This contract is suspicious.”
- “They wasted money.”

Such language may appear only in clearly separated editorial content (ADR-0039) and must still be evidence-led, attributed, and moderated.

## Consequences

- Copy and UI changes become part of correctness review, not a cosmetic afterthought.
- Analysis features (including AI-assisted) must default to neutral phrasing and include evidence links (ADR-0038).
- The platform will sometimes appear less sensational than other sources. This is intentional and supports long-term trust.

