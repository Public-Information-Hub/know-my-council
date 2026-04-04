# ADR-0019: Contributor Trust and Reputation Model

Status: accepted
Date: 2026-04-04

## Context

This platform’s credibility depends on evidence discipline. A naïve “likes/votes = trust” approach is vulnerable to brigading, social dynamics, and popularity bias.

We need a trust model that encourages:

- source-backed contributions
- careful review
- reproducibility
- conservative behaviour around uncertainty and claims

## Decision

Adopt an evidence-weighted trust model. Trust is earned through demonstrated evidence discipline and review quality, not voting alone.

Principles:

- **Evidence-first:** contributions that add or improve provenance, capture sources, and reduce uncertainty count more than commentary.
- **Review quality matters:** helpful review activity (catching errors, improving provenance links, clarifying uncertainty) is a trust signal.
- **Negative signals exist:** repeated low-quality submissions, unsupported assertions presented as fact, or abusive behaviour reduce trust.
- **Votes are not sufficient:** community feedback can be a signal, but must not be the primary driver of elevated permissions.
- **Reversibility:** trust and elevated capabilities can be reduced if standards slip.

This ADR defines the trust direction; it does not define a scoring algorithm.

## Consequences

- Workflows should capture structured signals (evidence links, review states, corrections) that support maintainers making trust decisions.
- Permission elevation should be conservative and tied to capabilities (ADR-0018) and role principles (ADR-0013).
- UI and public messaging should avoid gamification that encourages volume over accuracy.

