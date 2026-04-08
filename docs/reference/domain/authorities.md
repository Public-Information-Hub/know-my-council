# Authorities (councils)

In KnowMyCouncil, an **authority** is a public body in the UK with local government responsibilities that publishes (or should publish) information relevant to public spending, contracts, assets, decisions, and supporting documents.

This document defines the concept at a high level so we model it consistently.

## Scope (initial)

The project is focused on UK councils and local authorities. That includes the reality that:

- there are different authority types and structures (and this can change over time)
- the same authority may appear under different names in different datasets
- councils reorganise, merge, split, and rename

We should avoid hard-coding a single fixed taxonomy too early. The model should accommodate change.

## Identity and time

Authorities are time-varying concepts.

Modelling guidance:

- represent an authority as a stable internal identity (not just a current name)
- represent published names/codes as time-scoped attributes (authority “versions”)
- record provenance: where a name/code came from, and for which period it was observed

See:

- [../data-model/temporal-authority-and-geography-modelling.md](../data-model/temporal-authority-and-geography-modelling.md)

## Evidence and user-facing representation

Public trust requires that we can show:

- what a dataset claimed at the time (as published)
- what we believe that maps to in a normalised view (if we offer one)
- why we think that mapping is correct (sources and assumptions)

When there is uncertainty, the model and UI must allow “unknown” and “uncertain” states without forcing a misleading tidy answer.
