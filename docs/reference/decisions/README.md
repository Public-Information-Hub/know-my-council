# Architectural Decision Records (ADRs)

This folder stores **Architectural Decision Records** for KnowMyCouncil.

ADRs are short, dated documents explaining decisions that matter for future work, such as:

- major architectural approaches
- data modelling principles
- contributor workflow and governance choices that affect implementation

ADRs are not a replacement for issues or PR discussions. They are the durable, reviewable outcome once a decision is accepted.

## Format

Each ADR should include:

- Title
- Status (`proposed`, `accepted`, or `superseded`)
- Date (YYYY-MM-DD)
- Context
- Decision
- Consequences
- Links (optional but recommended: to reference docs, issues, PRs)

## Editing ADRs

- After an ADR is **accepted**, do not rewrite history. If the decision changes, add a new ADR and mark the old one as `superseded`.
- Keep ADRs concise and practical.

## When to write an ADR

Write an ADR when a choice will otherwise be re-litigated or inconsistently applied, such as:

- identifier strategy and temporal modelling rules
- separation between truth/ingestion and read/query layers
- evidence/provenance boundaries
- contributor workflow changes that affect implementation
