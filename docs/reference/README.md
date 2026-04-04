# Reference Documentation System

This folder is a long-lived reference and decision system for KnowMyCouncil.

It exists so the project can accumulate technical, domain, and architectural context over time without scattering it across issues, chat logs, or ad-hoc notes. It is intended to be useful to maintainers, contributors, AI-assisted contributors, and external reviewers.

## What belongs here

This is not a dumping ground. Add documents here when they are:

- stable reference material that future work should align with
- domain definitions and modelling guidance that should be consistently applied
- decision records explaining *why* we chose a particular approach
- short timeline notes capturing meaningful context (not every small change)

If something is temporary, speculative, or best handled as discussion, keep it in an issue or a PR instead.

## Sections

- `architecture/`: stable architectural reference docs (intended platform shape, integration boundaries).
- `data-model/`: modelling guidance for canonical identifiers, temporal validity, provenance boundaries, and comparability.
- `domain/`: definitions of domain concepts in project terms (what we mean by “authority”, “supplier”, etc.).
- `decisions/`: lightweight ADR-style decision records (dated, explicit, and reviewable).
- `timelines/`: short chronological context notes (used sparingly; not a changelog).

## Where to put things (quick guide)

- A term definition or naming guidance: `domain/`
- Modelling invariants and cross-domain rules (IDs, time, provenance): `data-model/`
- System boundaries and integration shape: `architecture/`
- “We chose X over Y” with consequences: `decisions/` (ADR)
- “Here is what happened in early setup and why it matters”: `timelines/`

## Canonical vs chronological

- Reference docs (`architecture/`, `data-model/`, `domain/`) should aim to be canonical and current. Update them when the project’s intended direction changes.
- Decision records (`decisions/`) are dated. They explain the reasoning at the time and should not be rewritten after acceptance; supersede them with a new ADR if needed.
- Timeline notes (`timelines/`) are context. They help future contributors understand *why* the project looks the way it does.

## How to contribute to reference docs

- Prefer small PRs: add one new reference doc, or update one existing one.
- Keep assertions grounded. Do not describe planned functionality as implemented.
- Use en_GB English.
- Link to supporting material where helpful (issues, PRs, external standards) but do not assume they will always remain available; capture the important reasoning in the doc.

## Keeping this scalable

This system is intended to grow without becoming noisy.

Guidelines:

- Prefer a few “root” docs per section that other docs can link to.
- Create new files when there is a genuinely distinct, stable topic (not just a paragraph).
- Avoid repeating the same explanations across many docs. Link instead.
- If guidance is frequently changing, it may belong in issues/PR discussion, not in reference docs.

## How to avoid a junk drawer

Do not add:

- meeting notes or personal scratchpads
- “vision” prose without concrete modelling or decision content
- speculative roadmaps (use `ROADMAP.md` instead)
- implementation instructions (use `docs/local-development.md` or task-specific docs instead)
