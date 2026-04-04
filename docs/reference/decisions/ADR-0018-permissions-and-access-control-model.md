# ADR-0018: Permissions and Access Control Model

Status: accepted
Date: 2026-04-04

## Context

KnowMyCouncil will have multiple participation workflows (evidence submission, corrections, investigations, moderation). A simple “admin vs user” split will not scale, and overly rigid role-based systems tend to break down as workflows expand.

We need permissions that are:

- reviewable and auditable
- easy to extend without creating a combinatorial role explosion
- compatible with least privilege and reversible access

## Decision

Use a capability-based permissions model with role bundles.

- **Capabilities** are the atomic permissions (for example: “submit evidence”, “approve evidence”, “create investigation”, “moderate content”, “manage roles”).
- **Roles** are named bundles of capabilities for convenience and community clarity (aligned with ADR-0013), but capabilities remain the source of truth.
- **Separation of duties:** define capabilities so high-risk actions are distinct, for example:
  - approve/merge normalised mappings vs submit a suggestion
  - moderate content vs manage user permissions
  - publish to public pages vs view restricted/internal fields
- **Scalability:** new workflows add new capabilities; roles are updated deliberately rather than proliferated ad-hoc.

## Consequences

- Permissions remain extensible without inventing dozens of bespoke roles.
- Auditing becomes more meaningful (“user X exercised capability Y”) (ADR-0025).
- Role discussions stay understandable for contributors while the underlying access control remains precise.

