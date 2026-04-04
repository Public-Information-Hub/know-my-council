# Licensing and data (practical note)

KnowMyCouncil is an open-source project. This document explains what is and is not covered by the repository's licence.

This is a practical guide for contributors and users. It is not legal advice.

## Code

The platform code in this repository is licensed under the **MIT Licence** (see [LICENSE](../LICENSE)).

This includes:

- backend code in `backend/`
- frontend code in `frontend/`
- infrastructure and scripts in `infra/` and `scripts/`
- project documentation written in this repository (unless explicitly stated otherwise)

## Data and source artefacts

KnowMyCouncil will ingest and store datasets and documents published by councils and other bodies.

Those upstream datasets and documents:

- are governed by their original licences and terms
- are not automatically relicensed by this repository
- may have different reuse permissions depending on the source

Some UK public sector sources publish under the **Open Government Licence (OGL)**, but this is not universal. Where possible, the platform should record and surface licensing/terms metadata alongside datasets and artefacts.

## User-submitted content

In future, the platform may accept user-submitted material (files, links, notes, evidence descriptions).

For now:

- contributors should only submit material they have the right to submit
- the project should not assume it can publish or relicense user submissions without clear terms

Any contributor terms for user-submitted content will be documented explicitly when those features exist.

## Published analysis and editorial content

The project intends to support analysis and editorial/journalism layers later.

Those materials should:

- be clearly separated from factual records and evidence
- be attributed and review-gated where appropriate
- have explicit licensing terms if they are published for reuse

## What this document does not do

- It does not claim that all ingested data is open for reuse.
- It does not claim ownership of third-party data or documents.
- It does not define platform Terms of Service or a contributor licence agreement.

## Related project decisions

- Open data and licensing model: [ADR-0035](/docs/reference/decisions/ADR-0035-open-data-and-licensing-model.md)

