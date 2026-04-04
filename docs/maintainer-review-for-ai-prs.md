# Maintainer review guide for AI-assisted PRs

This is a lightweight checklist to help maintainers review AI-assisted contributions. It is not intended to be punitive; it exists because this is a civic transparency project where trust matters.

## Quick triage

- Is the PR scoped and reviewable?
- Does the PR description state what was verified?
- If the PR touches evidence/governance/security, is it conservative and source-led?

## Red flags (consider requesting changes)

- invented facts, invented sources, or suspiciously specific examples without URLs
- “future plans” described as current behaviour
- large mixed-concern changes without justification
- dependency additions without trade-off discussion
- governance/security/disclaimer text rewritten into generic fluff
- claims that tests passed without evidence of execution

## Evidence-sensitive review

If the PR affects data provenance, evidence models, FOI/EIR direction, investigations, or reputational content:

- check that uncertainty is preserved and labelled
- ensure raw vs normalised vs derived distinctions are explicit
- ensure the text avoids legal-advice phrasing and avoids allegations

## Code review basics

- verify the change is minimal and consistent with repo structure
- run targeted checks locally or in CI (tests, lint, typecheck)
- ensure docs are updated when workflows change

## What “good” looks like

- small PR with clear motivation
- minimal diff
- explicit verification notes
- conservative language for civic-sensitive areas

