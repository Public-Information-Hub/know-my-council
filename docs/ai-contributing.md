# AI-assisted contributing

AI-assisted contribution is welcome in KnowMyCouncil.

However, this is a public-interest civic transparency project. Trust depends on accuracy, evidence discipline, and reviewability. Using AI tools does not reduce the contributor's responsibility for what they submit.

This document is for contributors using tools such as ChatGPT, Codex, Claude, Copilot, Cursor, Gemini, and similar systems.

## Principles

- AI assistance is permitted.
- You remain fully responsible for the submission.
- Keep PRs small and reviewable.
- Work on a branch where it helps keep the change reviewable; direct pushes to `master` are temporarily allowed while branch protection is off, but that is an exception rather than the long-term default.
- Do not submit “AI dumps”: large, unfocused changes without clear intent or verification.
- Do not invent sources, facts, or implemented functionality.

## Areas requiring extra care

Be especially cautious with AI-generated output when touching:

- public data and provenance
- evidence and investigations
- FOI / EIR wording or interpretation
- moderation, trust, and reputation systems
- legal or policy language
- security and vulnerability descriptions
- governance documents and maintainer responsibilities

If you are unsure, slow down and ask for maintainer input before submitting a large change.

## Source and evidence discipline

When writing anything that could be read as a factual claim about a council, supplier, contract, or behaviour:

- link primary sources where possible
- distinguish raw data from derived interpretation
- record uncertainty explicitly (do not “smooth over” missing details)

See:

- [data and evidence principles](data-and-evidence-principles.md)
- [community and editorial model](community-and-editorial-model.md)

## Disclosing AI assistance

We do not require disclosure for small, routine assistance (e.g. spelling fixes, trivial refactors).

We do expect disclosure when AI assistance was material, such as:

- drafting governance/policy text
- designing data/evidence workflows
- implementing non-trivial code changes
- producing security-relevant changes

Disclosure can be a short note in the PR description, for example:

- “Drafted with AI assistance and reviewed/edited by me.”

## Practical checklist (before you submit)

- [ ] I understand I am responsible for what I submit, even if AI helped generate it.
- [ ] I reviewed all AI-generated text/code and removed anything I cannot vouch for.
- [ ] I did not introduce invented facts, sources, contacts, or implemented features.
- [ ] For any evidence-sensitive changes, I included primary sources (URLs) or clearly marked uncertainty.
- [ ] The PR is scoped and reviewable (small where possible).
- [ ] I ran relevant checks/tests locally, or I stated clearly what I did not run and why.
- [ ] I updated docs where behaviour or workflow changed.
- [ ] I avoided adding dependencies without a clear need and discussion.

## For AI agents

If you are configuring or operating an AI agent, read:

- [AGENTS.md](../AGENTS.md)

Additional maintainer/contributor guidance:

- [docs/ai-workflows.md](ai-workflows.md)
- [docs/ai-task-templates.md](ai-task-templates.md)
