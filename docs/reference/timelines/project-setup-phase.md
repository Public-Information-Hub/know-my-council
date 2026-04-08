# Project Setup Phase (context)

Date: 2026-04-04

This note summarises the early repository setup direction for KnowMyCouncil. It is context, not a release log.

## What was established

- **Open-source foundation:** repository hygiene and community health files suitable for a public-interest civic project.
- **Evidence discipline:** documentation emphasising provenance, source retention, reproducibility, and non-partisan standards.
- **AI-assisted contribution support:** explicit standards and templates so AI assistance does not degrade truthfulness or maintainability.
- **Initial platform scaffold:** a monorepo structure with Laravel backend, Nuxt 3 frontend, and local infra services (PostgreSQL, Redis, Meilisearch, MinIO, Mailpit).

## Why temporal authority/geography modelling is highlighted early

UK local government reorganises over time. Without temporal modelling and explicit lineage, the project would eventually:

- overwrite history
- produce misleading comparisons
- lose the ability to explain what was published when

The project therefore treats temporal authority and geography modelling as a foundational reference area, to be used when designing the canonical data model and ingestion pipeline.

See:

- [../data-model/temporal-authority-and-geography-modelling.md](../data-model/temporal-authority-and-geography-modelling.md)
- [../decisions/ADR-0004-temporal-authority-and-geography-model.md](../decisions/ADR-0004-temporal-authority-and-geography-model.md)
