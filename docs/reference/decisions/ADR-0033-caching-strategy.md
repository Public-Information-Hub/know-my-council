# ADR-0033: Caching Strategy

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil is read-heavy. Many users will hit the same council/supplier pages and the same common aggregates. Without caching, the platform will either be slow or will force premature optimisation into canonical queries.

Caching must not compromise correctness or visibility boundaries.

## Decision

Adopt a layered caching strategy with conservative invalidation and visibility safety.

Principles:

- **Redis as the primary cache store** for application-level caching.
- **Cache the right layers:**
  - prefer caching derived read model outputs and stable public API responses
  - avoid caching raw/canonical queries that are used for editorial/moderation tooling unless scoped carefully
- **Visibility-aware caching:** never cache restricted/private views under public cache keys (ADR-0024).
- **Invalidation approach:**
  - prefer cache keys that include versioning inputs (for example dataset release ID, projection run ID, or “as_of” concept)
  - invalidate caches on publication and correction events (ADR-0014, ADR-0027)
  - use sensible TTLs as a safety net, not as the only correctness mechanism

This ADR sets strategy, not exact key formats.

## Consequences

- Read model and publishing workflows need to emit cache invalidation signals (or versioned keys) as part of their operation.
- Correctness and privacy boundaries require extra care in caching and in any future CDN/edge caching strategy.
- Caching improves user experience and reduces cost, but it adds complexity to debugging and requires good observability (ADR-0034).

