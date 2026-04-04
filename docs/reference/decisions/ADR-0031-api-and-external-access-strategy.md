# ADR-0031: API and External Access Strategy

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil is intended to be useful to the public, researchers, and journalists. A public API enables reuse, reproducibility, and external scrutiny.

However:

- the platform will contain restricted/internal data (ADR-0024)
- not all records will be verified/published (ADR-0027)
- the project must protect service availability against abuse

## Decision

Adopt a public API strategy with explicit versioning, exposure boundaries, and rate limiting expectations.

Principles:

- **Public-by-default for published data:** API endpoints may expose `published` records and derived read models intended for public pages (ADR-0010, ADR-0027).
- **Explicit boundaries:** restricted/private fields and non-published states are not exposed via public API.
- **Versioned API namespaces:** introduce an explicit versioning scheme for public endpoints (for example a versioned path namespace). Breaking changes require a new version.
- **Rate limiting:** apply rate limiting for anonymous API access and provide a path for higher limits for responsible users (implementation may evolve).
- **Bulk access:** prefer reproducible exports for large-scale use (future), rather than encouraging aggressive scraping of page endpoints.

This ADR defines API behaviour expectations, not a full endpoint catalogue.

## Consequences

- API design must align with the visibility model (ADR-0024) and publishing states (ADR-0027).
- The platform can support external reuse while protecting internal moderation/workflow data.
- Rate limiting and versioning add operational overhead but reduce breakage and service abuse.

