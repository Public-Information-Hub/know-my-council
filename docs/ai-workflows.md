# AI workflows (practical)

This document describes practical ways to use AI assistance in KnowMyCouncil without lowering standards for accuracy, evidence discipline, or reviewability.

It complements:

- [AGENTS.md](../AGENTS.md) (canonical rules)
- [docs/ai-contributing.md](ai-contributing.md) (human-facing guidance)

## Good uses of AI here

- summarising a small code area before you change it
- drafting a focused implementation plan for a narrow task
- producing a first draft of documentation that is then corrected to match repo reality
- generating test scaffolding for code that you have already designed
- proposing a small refactor with clear before/after and verification steps

## Bad uses of AI here

- inventing data, sources, or “example” councils that look real
- large cross-cutting changes without a clear goal
- rewriting governance/security/disclaimer text into generic fluff
- adding dependencies because the model “likes” them
- describing planned functionality as if it exists
- claiming checks passed when they were not run

## When not to use AI for a first draft

- legal/policy wording where precision matters (FOI/EIR guidance, security policy)
- investigation leads that could be misread as allegations
- anything that would publish or imply facts about a real organisation without sources

## Workflow A: small implementation task

Goal: make a narrow, reviewable change (bug fix, small feature, dev tooling).

1. Inspect relevant files first (read, do not guess).
2. Restate the narrow goal in one sentence.
3. Make the smallest change that achieves the goal.
4. Run targeted verification (tests, lint, a single endpoint).
5. Summarise what changed, what you verified, and any assumptions.

## Workflow B: documentation improvement

Goal: fix or improve docs without overclaiming.

1. Verify the current repo state (commands, file paths, scripts).
2. Update documentation to match what exists today.
3. Avoid promises: keep future direction in docs that are explicitly “planning”.
4. Keep changes scoped: do not rewrite unrelated sections.

## Workflow C: architecture proposal

Goal: propose a significant change without implementing it prematurely.

1. Write a proposal first (issue or doc) before coding.
2. Identify trade-offs (performance, complexity, maintenance burden).
3. List impacts (data model, ingestion, read layer, search, governance).
4. Separate proposal from implementation.
5. Only implement once maintainers agree on direction.

## Workflow D: evidence/data-sensitive work

Goal: work on anything that touches public data, provenance, or investigations.

1. Inspect actual sources or schemas (do not guess).
2. Preserve provenance: record where data came from and how it was transformed.
3. Distinguish raw, normalised, and derived outputs.
4. Flag uncertainty explicitly.
5. Avoid legal-advice language and avoid turning suspicion into assertion.

## Workflow E: open-source/community/governance changes

Goal: adjust templates, governance, or community health docs.

1. Be conservative: keep maintainers in control.
2. Avoid inflated language and “policy theatre”.
3. Do not introduce new commitments (response times, processes) unless maintainers agree.
4. Keep text practical and enforceable.

