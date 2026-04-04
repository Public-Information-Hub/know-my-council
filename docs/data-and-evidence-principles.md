# Data and evidence principles

KnowMyCouncil is intended to support public accountability. That only works if our records are traceable back to primary sources and the steps from source to output are reproducible.

This document describes the standards we aim to hold ourselves to. It is direction-setting and will evolve as the platform develops.

## 1) Provenance first

For any record we publish (now or in the future), we should be able to answer:

- Where did this come from?
- When did we obtain it?
- What transformations were applied?
- Can someone else reproduce the same result from the same source?

## 2) Source retention (where lawful and practical)

Where possible, we should retain:

- original source files (spreadsheets, PDFs, notices, registers)
- the URL and access path to the source
- the fetch timestamp and any access constraints

We should be explicit when we cannot retain a source (e.g. licensing constraints, access restrictions).

## 3) Distinguish raw, normalised, and derived data

We aim to keep a clear separation between:

- **Raw:** files or payloads as obtained from the source, with minimal handling.
- **Normalised:** structured records created by parsing and standardising raw data.
- **Derived:** aggregations, classifications, comparisons, rankings, or narratives computed from normalised data.

User interfaces should not blur this line. Derived outputs should always be explainable and traceable back to sources.

## 4) Reproducibility and deterministic imports

Ingestion should be designed so that:

- rerunning an import is safe (idempotent)
- the same input yields the same output (within defined version constraints)
- differences between runs can be explained (source changed, parser changed, mapping changed)

## 5) Handling uncertain or conflicting data

Councils publish inconsistent data. When fields are uncertain, missing, or contradictory:

- store the original text/value where possible
- store confidence or status flags where appropriate
- avoid silently "fixing" without recording the decision and source

When multiple sources disagree, we should capture that fact rather than forcing a single truth without explanation.

## 6) Corrections and audit trails

Corrections should be:

- recorded (who/what/when/why)
- reversible where possible
- linked to evidence (source document, council clarification, corrected dataset)

## 7) Avoid over-interpretation

The project is evidence-led, not narrative-led.

- We should avoid insinuations without sources.
- Where interpretation is offered, it should be clearly labelled as interpretation.

## 8) Privacy and harm minimisation

Even public data can cause harm if presented irresponsibly.

- Avoid publishing personal data unless clearly lawful and necessary for public interest.
- Consider redaction or aggregation where appropriate.
- Treat security issues as private (see [SECURITY.md](../SECURITY.md)).

