# Suppliers

In KnowMyCouncil, a **supplier** is an organisation (or sometimes an individual) that receives money from an authority, or is party to a contract, grant, payment, or other financial relationship captured by council-published data.

This definition is intentionally broader than “company”, because real-world spend data often includes:

- trading names and abbreviations
- charities and public bodies
- partnerships and joint ventures
- sole traders (sometimes)

## Identity and ambiguity

Supplier identity is often messy.

Modelling guidance:

- treat supplier names in datasets as *observations* (as-published strings)
- prefer stable internal IDs for the supplier concept
- link to external identifiers where available (for example a company register identifier), but do not assume they exist or are always correct
- keep multiple names/aliases with provenance (which dataset, which authority, which time period)

## Evidence discipline

Avoid turning inference into fact.

Examples of risky leaps:

- “X Ltd” in a spend export is assumed to be a specific registered company without evidence.
- Two similar names are merged because they look the same.

When a supplier match is uncertain:

- keep it uncertain
- record why we think it might match
- allow later correction with provenance

## Public presentation (future)

Supplier pages should clearly separate:

- raw observed names in council datasets
- normalised/linked identities (with evidence)
- derived analysis (and how it was computed)

