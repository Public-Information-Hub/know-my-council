# GitHub branch and CI settings (maintainers)

This document lists recommended GitHub settings that cannot be fully enforced from repository files alone.

These are recommendations, not claims about current enforcement. If branch protection is temporarily disabled, treat the notes below as the target state to restore later.

## Branch protection: `master`

Recommended settings for the `master` branch:

- Require a pull request before merging.
- Require status checks to pass before merging.
- Require branches to be up to date before merging (optional but recommended).
- Require at least one approving review (recommended once you have more than one active maintainer).
- Dismiss stale approvals when new commits are pushed (recommended).
- Restrict who can push to matching branches (recommended).
- Allow force pushes: off.
- Allow deletions: off.

## Required status checks (recommended)

Suggested minimum required checks:

- `CI (Pull Request) / pr-checks` (for PRs)
- `Repo Hygiene`
- `Issue Template Sanity` (only when templates change)

Suggested mainline checks:

- `CI (Master) / mainline` (runs PostgreSQL schema smoke and uploads backend coverage)

Exact check names must match the workflow names in `.github/workflows/`.

## Merge strategy (recommended)

Pick one of:

- **Squash merge** for a clean history (often easiest for volunteer projects).
- **Merge commit** if you want to preserve multi-commit PR history.

Avoid rebasing PRs that have already been reviewed unless needed, to preserve review traceability.

## CI minutes control (recommended)

This repository is set up so:

- pull requests run a lighter CI path
- pushes to `master` run a fuller CI path (including PostgreSQL schema smoke)

If CI minutes become an issue:

- keep PR CI limited to lint/typecheck/unit tests
- reserve coverage uploads and other heavier checks for `master`

## Dependabot (recommended)

Dependabot is configured in `.github/dependabot.yml`.

Recommended settings:

- require PRs for dependency updates (already the default)
- apply a `dependencies` label automatically (workflow already exists)
- do not auto-merge dependency updates by default; review them
