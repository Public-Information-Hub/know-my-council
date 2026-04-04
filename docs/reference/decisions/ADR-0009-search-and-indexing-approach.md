# ADR-0009: Search and Indexing Approach

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil is read-heavy. Public users need fast search and discovery across councils, suppliers, contracts, and documents. Relational queries alone will not provide a good search experience at scale, especially for full-text search and ranking.

We also need an approach that is realistic for an open-source project and can evolve as the platform matures.

## Decision

Adopt Meilisearch as the initial search engine with denormalised search documents:

- **Meilisearch** is the initial choice for full-text search and ranking.
- Index **denormalised documents** representing user-facing concepts, rather than attempting to execute complex relational joins at query time.
- Initial index scope (intended):
  - authorities/councils (names, identifiers, aliases)
  - suppliers (names, aliases, linked identifiers)
  - contracts (titles, descriptions, parties, key dates/values where available)
  - documents (titles, extracted text, metadata)
- Search indices are **derived** outputs that can be rebuilt from canonical data and provenance-tracked inputs.

Future evolution:

- Keep the indexing boundary explicit so the project can consider OpenSearch (or another engine) later if requirements change (scale, advanced query needs, operational constraints).
- Do not couple domain logic to Meilisearch-specific features in a way that makes migration impossible.

## Consequences

- Indexing will require projection jobs and backfill tooling (reindex by authority, by dataset, or full rebuild).
- Search documents must be carefully designed to avoid misleading users (for example, expose as-published names vs normalised identity clearly in the indexed fields).
- Search results must remain explainable:
  - which record is this?
  - what is the underlying canonical entity?
  - where did the data come from?
- Meilisearch is suitable for the intended early search experience; if the project outgrows it, the denormalised-document approach reduces migration risk.

