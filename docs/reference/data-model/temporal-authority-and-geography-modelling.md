# Temporal Authority and Geography Modelling (intended approach)

English local government changes over time. Councils may reorganise, merge, split, rename, or change boundaries. External codes change. Datasets are published with inconsistent naming. A transparency platform that ignores time will eventually mislead people.

This document sets out the intended modelling approach for:

- authority reorganisations (mergers, splits, renames)
- boundary changes and geographic comparability
- changing external identifiers/codes
- historic vs current comparisons
- stable internal IDs and temporal validity
- lineage relationships between “entities over time”

This is modelling guidance. It does not define a final schema.

## Principles

### 1. Stable internal IDs, temporal attributes

We should avoid using external codes as primary keys.

Instead:

- assign a stable internal identifier for an “authority concept”
- model time-bounded “versions” or “representations” of that concept
- attach names, types, external identifiers, and boundary definitions to time-bounded records

This allows us to answer both:

- “what did the data say at the time?”
- “how does this compare to today’s administrative reality?”

### 2. Separate raw historical truth from normalised comparison views

For public trust, we must preserve raw imported truth:

- what a council published
- with what name/code at the time
- with what boundary basis (if known)
- with an immutable source reference

Normalised or comparative views (for example, “group historic spend under current authority grouping”) are useful, but must be explicitly labelled as *derived* and reproducible.

### 3. Temporal validity is explicit

Wherever we store authority identity, names, codes, boundaries, or membership:

- use explicit validity periods (effective from/to)
- allow unknown end dates
- avoid implying permanence when the data is time-scoped

### 4. Lineage is first-class

Reorganisations are not just renames.

We need to represent relationships such as:

- predecessor -> successor (merge)
- predecessor -> successors (split)
- successor <- predecessors (merge inputs)

Lineage relationships should be time-scoped and include evidence/provenance (which source supports this mapping).

### 5. Geographic comparability is not free

Boundary changes can break comparisons.

We should distinguish between:

- the authority identity and its legal/administrative continuity
- the geographic footprint at a particular time
- analysis geographies used for aggregation (which may not match administrative boundaries)

Any “compare across time” feature must say which geography basis it is using.

## Authority modelling guidance

### Authority vs authority version (conceptual split)

Treat “authority” as a stable internal concept that can have many time-bounded versions.

An authority version is the thing that carries:

- a specific name/spelling used in a period
- a type/classification (where useful)
- external identifiers/codes as-of that period
- links to geographic boundary definitions (if/when stored)

This supports:

- showing historic names as published
- tracking renames without losing continuity
- representing reorganisations without overwriting history

### External identifiers and codes

Councils and datasets may reference external identifiers (for example, government statistical geography codes, register IDs, legacy codes, dataset-specific IDs).

Treat these as:

- attributes with provenance
- time-bounded where necessary
- potentially many-to-one over time (codes can be retired, reissued, or corrected)

Never assume a code is globally stable forever without evidence.

### Reorganisations (merge/split/abolition/creation)

Represent reorganisations with explicit lineage edges.

Guidance:

- model the event/relationship, not just the end state
- record dates (or date ranges) and the source for the mapping
- allow partial mappings where only some of the evidence is known

Avoid “clever” implicit inference. If the relationship is uncertain, represent it as uncertain and explain why.

### Historic vs current comparisons

We will likely need at least two separate ways of grouping data:

- **as-published grouping:** attach records to the authority version that existed at the time the source data describes
- **normalised comparison grouping:** map historic records onto a chosen comparison basis (for example “current authority grouping” or a specific “as-of” date)

The mapping from as-published to normalised basis must be:

- explicit
- reproducible
- evidence-backed where possible

## Geography modelling guidance

### What “geography” means here

“Geography” covers spatial definitions used for:

- administrative boundaries (authority areas at a time)
- analytical geographies (for example, aggregations or reporting areas)
- dataset-specific areas (where suppliers or spend are reported at different levels)

We should be careful not to conflate:

- administrative authority continuity
- boundary continuity
- analytical comparability

### Boundary changes

Boundary definitions can change even when an authority continues.

When/if the project stores boundaries:

- treat boundary definitions as versioned artefacts
- time-scope the association between an authority version and a boundary definition
- record provenance (where the geometry came from, what revision, what date)

Even without geometry storage, we should still model boundary change *events* where relevant to interpreting comparisons.

### Dataset geography basis

Different datasets may be published against different geography bases. For example:

- a spend dataset might follow a finance system’s organisation units, not legal boundaries
- a supplier register might be national, not authority-scoped
- documents may refer to regions, wards, or service areas

Each dataset import should record:

- the authority identity it claims to refer to (as published)
- the geography basis if known (or “unknown”)
- any assumptions made during normalisation

## Provenance and auditability requirements

At minimum, future modelling should allow:

- linking a record to a source artefact (file/URL/capture) with timestamp
- recording import run metadata (tool/version, date, warnings)
- storing transformation steps for derived/normalised outputs

Where an authority mapping or reorganisation is encoded, it should be possible to answer:

- “which sources support this mapping?”
- “what assumptions were made?”
- “what changed over time?”

## Practical consequences for future implementation

This modelling direction implies:

- we will need tables (or equivalent) representing stable concepts and time-bounded versions
- we will need explicit lineage relationships between authority concepts/versions
- we will need clear labelling of “as published” vs “normalised/derived” views
- we should design public UI to disclose the basis used for comparisons (including uncertainty)

