# ADR-0015: Dataset Metadata and Geography Basis

Status: accepted
Date: 2026-04-04

## Context

Bad joins are a major source of misinformation in transparency work. Councils reorganise, boundaries change, and datasets are often published against unclear or shifting geography bases.

Without explicit dataset metadata, the platform risks:

- comparing values across non-comparable geographies
- attributing records to the wrong authority version
- treating an external code as stable when it is not
- silently mixing “as published” records with normalised comparison views

This is especially important given the temporal authority/geography modelling approach (ADR-0004).

## Decision

Require dataset-level metadata as a first-class concept for ingestion and interpretation.

At minimum, each dataset (or dataset release) should record:

- **Geography type / basis:** what area the dataset is intended to cover (administrative authority boundary, analytical geography, organisational unit basis, or unknown).
- **Code system(s):** which external identifier system is used in the dataset (if any), treated as a reference not a primary key.
- **Edition/capture date:** when the dataset was published or captured (and ideally both where known).
- **Reporting period:** the time period the data describes (as published).
- **Mapping confidence:** a structured confidence/quality signal for how reliably the dataset maps to normalised authorities/geographies (for example: high/medium/low/unknown), with provenance.

This metadata is part of the truth layer: it describes what the dataset *is* and how it should be interpreted.

## Consequences

- Ingestion must capture and store dataset metadata alongside artefacts and import runs.
- Normalisation and derived comparison views must use the dataset’s geography basis and reporting period rather than guessing.
- Public UI can disclose comparability constraints (for example “boundary basis changed” or “mapping confidence low”) instead of implying false precision.
- This reduces the risk of incorrect joins and misleading comparisons, at the cost of additional metadata capture work.

