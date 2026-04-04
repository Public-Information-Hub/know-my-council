# Guidance for AI agents and AI-assisted contributors (canonical)

KnowMyCouncil is a public-interest civic transparency project focused on English councils. The work in this repository should be evidence-led, non-partisan, source-driven, and easy to review.

This file is the canonical instruction set for AI agents and for contributors using AI assistance.

## Mission and standards

- This project exists to improve public accountability through transparency.
- Clarity, provenance, and auditability matter more than volume of output.
- Prefer small, reviewable changes over broad refactors.
- Use en_GB English in documentation, comments, and UI text.

## General behavioural rules

AI agents and AI-assisted contributors must:

- **Not invent facts**: do not fabricate council names, datasets, contract details, supplier identities, figures, dates, or outcomes.
- **Not invent sources**: do not fabricate URLs, citations, documents, or “official” statements.
- **Not invent governance**: do not invent maintainers, contacts, decisions, endorsements, or external review.
- **Not invent functionality**: do not describe planned features as if they already exist.
- **Avoid silent major changes**: do not introduce major architecture changes without discussion and explicit rationale.
- **Avoid casual dependencies**: do not add new dependencies without a clear need and explicit discussion of trade-offs.
- **Avoid unfocused refactors**: do not generate broad, low-signal rewrites just to “clean up”.
- **Preserve placeholders**: do not remove placeholders unless replacing them with real, verified values.
- **Do not add fluffy institutional wording**: avoid corporate polish and inflated claims about maturity, governance, or impact.
- **Do not casually rewrite sensitive docs**: be conservative with governance, evidence, disclaimer, and security text.
- **Keep docs useful**: avoid vague fluff and marketing language.
- **Be explicit about verification**: do not claim tests passed, checks ran, or services worked unless you actually verified it.

## Civic, data, and evidence-sensitive work

When work touches public data, evidence, investigations, FOI/EIR, or reputational claims:

- Never fabricate council data, supplier records, contract concerns, FOI outcomes, or source links.
- Clearly distinguish:
  - raw source material
  - normalised records
  - derived outputs and interpretation
- Preserve provenance where relevant (where it came from, when obtained, how transformed).
- Flag uncertainty rather than smoothing it over.
- Avoid legal-advice language. Do not present guidance as legal interpretation.
- Avoid defamatory framing. Stick to what sources say and what is reproducible.

## Documentation and governance text

- Keep governance text honest: early-stage, best-effort, and explicit about what is not yet formalised.
- Do not “polish” documents into generic corporate language.
- Do not claim external legal review, editorial processes, or trust systems unless they exist.
- Keep non-partisan language calm and credible.

## Code changes

- Avoid unnecessary complexity and abstraction.
- Maintain consistency with the existing structure in `backend/` and `frontend/`.
- Prefer minimal changes that are easy to test and revert.
- Add or update tests when it is practical and meaningful.
- If verification is incomplete (e.g. Docker not running, missing extensions), say so plainly.
- Do not bundle unrelated changes into one PR without a clear reason.

## Output expectations (for PRs and change proposals)

When submitting work, include:

- what changed
- why it changed
- what you verified (commands run, outputs checked)
- assumptions made
- anything that still needs manual follow-up

If AI assistance was used materially, disclose it and confirm you reviewed the output.

## Related guidance

- Human-facing AI guidance: [docs/ai-contributing.md](docs/ai-contributing.md)
- Maintainer and contributor workflows: [docs/ai-workflows.md](docs/ai-workflows.md)
- Prompt templates: [docs/ai-task-templates.md](docs/ai-task-templates.md)
- Contribution workflow: [CONTRIBUTING.md](CONTRIBUTING.md)
- Evidence principles: [docs/data-and-evidence-principles.md](docs/data-and-evidence-principles.md)
