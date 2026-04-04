# Data Model Reference

Data model reference documents capture modelling principles that should remain consistent over time, even as the underlying schema evolves.

These documents should:

- define intended boundaries (raw vs normalised vs derived)
- explain how to preserve provenance and auditability
- describe temporal modelling and identifier strategy
- support long-term comparability (especially across council reorganisations)

If a domain doc defines what a concept means (for example “supplier”), data model docs define the cross-cutting rules for representing it over time and across datasets.

Start with:

- [temporal-authority-and-geography-modelling.md](temporal-authority-and-geography-modelling.md)
