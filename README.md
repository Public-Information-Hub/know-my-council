# KnowMyCouncil

KnowMyCouncil is an open civic transparency platform focused on English councils. The goal is to help the public inspect, understand, and challenge how councils use money and exercise power.

This repository is public-interest infrastructure: evidence-led, non-partisan, source-driven, and designed to be community-contributed.

## Why it exists

Council information is often:

- spread across many websites and PDF pages
- published inconsistently council-to-council
- hard to search, compare, or reproduce
- missing unless obtained via FOI/EIR

KnowMyCouncil aims to make the underlying evidence easier to find and verify, while keeping a clear link back to primary sources.

## Current status

This repo is at an early stage. It currently contains:

- a monorepo scaffold (`backend/`, `frontend/`, `infra/`)
- local development infrastructure (PostgreSQL, Redis, Meilisearch, MinIO, Mailpit)
- basic health/version endpoints and smoke-test commands
- documentation for intended evidence and community standards

It does **not** yet include full business features or a production deployment.

## Evidence and source standards

This project aims to be trustworthy. That means:

- factual claims should be supported by primary sources where possible
- provenance should be recorded (where the data came from, when, and how it was transformed)
- derived outputs must be reproducible and clearly distinguished from raw sources

See:

- [docs/data-and-evidence-principles.md](docs/data-and-evidence-principles.md)
- [docs/community-and-editorial-model.md](docs/community-and-editorial-model.md)

## Contributing

We welcome contributions from developers, civic technologists, journalists, researchers, and members of the public.

Start here:

- [CONTRIBUTING.md](CONTRIBUTING.md)
- AI-assisted contributing: [docs/ai-contributing.md](docs/ai-contributing.md)
- [docs/github-labels.md](docs/github-labels.md)

Community standards:

- [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md)
- [GOVERNANCE.md](GOVERNANCE.md)

If you have a source-led lead to investigate, use the Investigation template and include primary sources.

### AI assistance

AI-assisted contributions are welcome if they meet our standards for truthfulness, evidence discipline, and reviewability. Keep changes scoped, verify outputs, and avoid invented facts or sources.

### Branch and PR workflow

Normal development happens on branches and is merged into `master` via pull requests. We recommend protecting `master` in GitHub settings (see [docs/github-branch-and-ci-settings.md](docs/github-branch-and-ci-settings.md)).

## Local development

Local dev runs apps on the host and infrastructure services in Docker.

- [docs/local-development.md](docs/local-development.md)
- [docs/architecture.md](docs/architecture.md)
- Phase 1 spend CSV ingestion: [docs/ingestion/spend-csv.md](docs/ingestion/spend-csv.md)
- Reference system (architecture, domain, ADRs): [docs/reference/README.md](docs/reference/README.md)
- [docs/next-steps.md](docs/next-steps.md)
- Backend debugging and coverage: [docs/xdebug-and-coverage.md](docs/xdebug-and-coverage.md)

## Licence

Code in this repository is licensed under the **MIT Licence** (see [LICENSE](LICENSE)).

Data, datasets, and published content are not automatically covered by the code licence. Source data may be governed by third-party terms (for example, some UK public sector sources publish under the Open Government Licence), and terms can vary by council and source.

See: [docs/licensing-and-data.md](docs/licensing-and-data.md).

## Security

Please report security issues privately. See [SECURITY.md](SECURITY.md).

## Support and funding

- Support: [SUPPORT.md](SUPPORT.md)
- Funding direction: [FUNDING.md](FUNDING.md)
- Disclaimer: [DISCLAIMER.md](DISCLAIMER.md)
