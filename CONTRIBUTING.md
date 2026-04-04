# Contributing to KnowMyCouncil

KnowMyCouncil is a civic transparency project focused on English councils. We welcome contributions from developers, civic technologists, journalists, researchers, and members of the public.

This guide aims to make contributions predictable, source-led, and safe.

## Ground rules (important)

- **Be respectful:** follow [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md).
- **Be evidence-led:** when making factual claims about councils, suppliers, or contracts, link to the original source where possible.
- **Stay non-partisan:** focus on verifiable facts, reproducible methods, and clear documentation.
- **Prefer small PRs:** smaller changes are easier to review, safer to merge, and easier to revert.
- **Discuss bigger changes first:** if you want to change architecture, data models, or governance, open an issue and describe the proposal before implementing it.

For durable modelling and architectural context, see the reference system:

- [docs/reference/README.md](docs/reference/README.md)

## AI-assisted contributions

AI-assisted contribution is allowed.

However:

- The contributor remains responsible for the submission.
- All generated output must be reviewed and verified.
- Evidence-sensitive changes (data, sources, FOI/EIR, moderation/trust, governance, security) require extra care.
- Low-quality, unverifiable, or bloated AI-generated changes may be rejected.

Read:

- [docs/ai-contributing.md](docs/ai-contributing.md)
- [AGENTS.md](AGENTS.md)

## Ways to contribute

KnowMyCouncil expects different contribution types. Choose the one that matches your intent:

### 1) Code contributions

Examples:

- bug fixes
- developer experience improvements
- internal tooling
- API or UI scaffolding (when requested)

Expectations:

- follow existing conventions in `backend/` and `frontend/`
- include tests where appropriate (or explain why not)
- avoid adding new dependencies without discussing them first

### 2) Documentation contributions

Examples:

- improving clarity, reducing ambiguity
- correcting outdated instructions
- adding missing operational notes

Expectations:

- use en_GB English
- keep it specific and practical

### 3) Data and source issue contributions

Examples:

- broken source links
- missing datasets for a council
- suspected incorrect records or mappings

How:

- use the **Data issue** template
- include URLs to original sources and the exact council/dataset you mean

### 4) Civic / investigation contributions

Examples:

- patterns worth researching (spend anomalies, repeat suppliers, contract concerns)
- a lead that needs validation or a paper trail

How:

- use the **Investigation / lead** template
- keep assertions clearly separated from evidence
- include links to primary sources (council sites, official registers, released documents)

See: [docs/community-and-editorial-model.md](docs/community-and-editorial-model.md) and [docs/data-and-evidence-principles.md](docs/data-and-evidence-principles.md).

## Reporting bugs and requesting features

- Bugs: open an issue using the **Bug report** template.
- Feature requests: open an issue using the **Feature request** template.
- Unclear or broad ideas: open an issue and label it as "needs discussion" (or state that in the description).

## Branch and PR expectations

- Create a branch from `master`.
- Do not push directly to `master` as part of normal development. Use pull requests.
- Use clear branch names (e.g. `docs/evidence-principles`, `infra/minio-init`).
- Open a PR early if you want feedback.

PRs should include:

- what changed and why
- how it was tested (or why not)
- whether docs need updating
- any data/evidence implications (especially if changes affect provenance or reproducibility)

## Adding dependencies

Please avoid adding new dependencies without discussion, especially in the backend. For a public-interest project, we try to keep:

- supply chain risk low
- upgrade cost manageable
- maintenance burden realistic for volunteers

Open an issue first explaining:

- the problem being solved
- alternatives considered
- security and maintenance considerations

## Architecture changes

If your work changes any of the following, discuss it first:

- canonical identifiers and data boundaries
- ingestion pipeline structure (idempotency, retries, provenance)
- read model strategy and caching approach
- search indexing strategy
- editorial and trust model assumptions

Start with an issue labelled "needs discussion" and link any prior art or similar projects.

## License and contributor terms

By contributing, you agree that your contributions will be licensed under the repository's [LICENSE](LICENSE).
