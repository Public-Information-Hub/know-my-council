# Domain Reference

Domain reference documents define key concepts in project terms so contributors model and name things consistently.

These documents should:

- define terms in plain language
- describe modelling implications (identity, provenance, ambiguity)
- highlight common pitfalls (name collisions, time variance, uncertain mappings)

They should not attempt to be full schemas. Schema detail belongs in code and in data-model references where it is genuinely stable.

If a topic is mainly about cross-cutting modelling rules (temporal validity, identifiers, provenance), it likely belongs in `../data-model/` instead.

Initial domain docs:

- [authorities.md](authorities.md)
- [geographies.md](geographies.md)
- [suppliers.md](suppliers.md)
