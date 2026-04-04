# Geographies

KnowMyCouncil deals with “geography” because councils and their responsibilities are spatial, and because many datasets and documents are only meaningful when you know which area they refer to.

This document defines “geography” in project terms.

## What we mean by “geography”

A **geography** is a spatial or administrative area used to interpret or aggregate information. Examples include:

- an authority’s boundary at a point in time
- an analytical reporting area used for comparisons
- dataset-specific areas used by a publisher

Geography is not always the same as “authority”. Authorities can persist while boundaries change; boundaries can exist independently of an authority identity.

## Why this matters

Comparisons across time can be wrong if the underlying geography changed.

The project should be able to:

- indicate which geography basis a dataset uses (or that it is unknown)
- avoid silently implying comparability where it does not exist
- separate “as published” boundary assumptions from normalised comparison views

See:

- [../data-model/temporal-authority-and-geography-modelling.md](../data-model/temporal-authority-and-geography-modelling.md)

## Boundaries and artefacts (future)

If/when we store boundary data, it should be treated as:

- versioned artefacts with provenance
- linked to time-scoped authority versions (not overwritten)

We should not treat a single stored boundary as “the authority boundary” without a date and source.

