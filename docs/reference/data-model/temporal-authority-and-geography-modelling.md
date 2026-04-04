# Temporal Authority and Geography Modelling (intended approach)

English local government changes over time. Councils reorganise, merge, split, rename, and change boundaries. External identifiers evolve. Datasets are published inconsistently and often reflect the publisher’s organisational view at the time.

A civic transparency platform that models only “current state” will eventually mislead people. This document describes an intended modelling approach that remains:

- accurate to what was published at the time
- usable for comparisons and navigation today
- explicit about uncertainty and assumptions

This is reference guidance. It is not a final schema.

Related decision:

- [../decisions/ADR-0004-temporal-authority-and-geography-model.md](../decisions/ADR-0004-temporal-authority-and-geography-model.md)

## Goals and non-goals

Goals:

- preserve as-published authority identity observations with provenance
- support temporal validity (effective dates) for names, codes, and boundaries
- represent reorganisations and lineage explicitly (merge/split/rename/abolition/creation)
- enable reproducible derived “comparison” views without overwriting historical truth

Non-goals (for now):

- choose a definitive taxonomy for every authority type
- define the full database schema
- promise specific external code systems as canonical

## Core principles (invariants)

1. **Stable internal IDs, external IDs as attributes**
External identifiers are inputs and references, not primary keys.

2. **Time is explicit**
If something can change over time, the model should be able to represent that change without overwriting history.

3. **Lineage is first-class**
Reorganisations are relationships/events that must be represented explicitly and evidence-backed where possible.

4. **Raw vs normalised vs derived are separated**
As-published observations are preserved. Normalisation and derived comparison views are optional layers that must be reproducible and labelled.

5. **Comparability is disclosed**
Comparisons across time must disclose the basis used (administrative continuity, geographic continuity, or an analytical basis).

## Modelling primitives (conceptual, not schema)

To keep future work consistent, it helps to name the conceptual building blocks:

- **Authority (concept):** a stable internal identity used as the anchor for continuity.
- **Authority version:** a time-bounded representation of the authority as observed/defined in a period (name, classification, etc.).
- **Identifier assignment:** a link between an authority (or authority version) and an external identifier/code, with provenance and (where needed) validity dates.
- **Authority relationship / lineage edge:** a time-scoped relationship describing reorganisation (for example “A merged into B effective 20XX-YY-ZZ”), with evidence.
- **Geography (concept):** a named area used for interpretation/aggregation that may or may not be the same as an authority.
- **Boundary definition (versioned artefact):** a specific boundary representation for a geography at a point in time (if/when stored), with provenance.
- **Mapping basis:** a named rule set for normalisation/comparison (for example “as published”, “as of 2026-04-04”, “analytical geography X”).

These primitives help us avoid an anti-pattern where “authority” is treated as a single row that gets updated in place as the world changes.

## Authority identity and time

### Authority concept vs authority version

Treat “authority” as the continuity anchor and “authority version” as the time-scoped representation.

Authority versions are the thing that carry:

- a specific name/spelling used in a period
- a type/classification (where useful)
- external identifiers/codes as observed in that period
- links to geography/boundary definitions (where relevant and known)

This supports:

- showing historic names as published
- tracking renames without losing continuity
- representing reorganisations without overwriting history

### External identifiers and codes

External identifiers should be:

- stored with provenance (where did we get this code?)
- treated as time-scoped if they can change, be retired, or be corrected
- allowed to have multiple assignments over time

Practical rule: never assume an external code is globally stable forever without evidence.

## Reorganisations and lineage

Reorganisation modelling should capture the *relationship/event*, not only the end state.

We need to represent relationships such as:

- predecessor -> successor (merge or replacement)
- predecessor -> successors (split)
- successor <- predecessors (merge inputs)

Guidance:

- record effective dates (or best-known ranges)
- store evidence/provenance for each lineage statement
- allow partial or uncertain mappings where the evidence is incomplete

Avoid implicit inference that quietly “fixes” the world. If we are not sure, we should represent uncertainty rather than smoothing it away.

## Geography and boundaries

### Geography is not always “authority”

“Geography” covers spatial definitions used to interpret or aggregate information:

- administrative boundaries (authority areas at a time)
- analytical geographies (used for comparison/reporting)
- dataset-specific areas or organisational units used by a publisher

We must not conflate:

- authority continuity (legal/administrative identity)
- boundary continuity (geographic footprint)
- analytical comparability (chosen basis for comparison)

### Boundary change without reorganisation

An authority can continue while its boundary changes. Our model needs to represent:

- that the authority concept remains continuous
- that the geography/boundary definition changed
- that comparisons across the change may not be “like-for-like”

If/when boundary geometry is stored, treat it as versioned artefacts with provenance, and time-scope the association to authority versions.

## “As published” vs “comparison” views

We will likely need at least two distinct ways of grouping/serving records:

- **As published (truth layer):** records are attached to the authority version and identifiers observed in the source material at the relevant time.
- **Comparison view (derived layer):** records are mapped onto a chosen basis (for example “as-of date”, or “current grouping”), explicitly and reproducibly.

Rules:

- derived comparison views must be labelled as derived
- the mapping basis must be named and reproducible
- the mapping should be evidence-backed where possible and must record assumptions

## Practical examples (how the model should behave)

These examples are intentionally abstract (no real-world claims).

### Example A: rename only

An authority changes its name but remains administratively continuous.

Expected behaviour:

- the authority concept remains the same
- a new authority version becomes effective from the rename date
- as-published records before/after show the correct name at the time
- a comparison view can group both versions under the same authority concept

### Example B: merge

Authorities A and B are replaced by a new authority C.

Expected behaviour:

- authority concepts for A and B remain as historic anchors
- C is introduced as a new authority concept (or an explicitly chosen continuity anchor if evidence supports it)
- lineage edges record A -> C and B -> C with effective date and provenance
- derived views that map historic records into C must be explicit about the mapping basis

### Example C: split

Authority A is replaced by A1 and A2.

Expected behaviour:

- A remains as a historic anchor
- lineage edges record A -> A1 and A -> A2
- if we support allocating historic records, any allocation rule must be explicit and reproducible (and may be “unknown”)

### Example D: boundary change

Authority continues but its boundary changes.

Expected behaviour:

- the authority concept remains continuous
- boundary definitions are versioned and time-scoped
- any “compare across time” feature discloses boundary basis and potential non-comparability

## Import pipeline implications (future)

Even before we have a full canonical model, imports should preserve raw observations:

- as-published authority name string(s)
- any as-published codes/identifiers found in the dataset
- publication date or relevant period (where available)
- the source artefact reference (URL/file capture) and when it was captured
- any assumptions used to link to a normalised authority identity (if performed)

Never discard raw observations “because we have a normalised version”. Raw observations are the audit trail.

## Public API and UI implications (future)

To avoid misleading outputs, endpoints and pages that compare across time will need to:

- disclose the comparison basis (“as published” vs “normalised as-of date”)
- allow an `as_of` date conceptually (even if not implemented immediately)
- represent “unknown/uncertain” mappings without forcing a tidy answer

## Minimum auditability requirements

Where an authority mapping or lineage statement exists, we should be able to answer:

- which sources support it?
- what assumptions were made?
- what changed over time?

This is both a technical requirement and a trust requirement for a civic transparency project.
