# ADR-0011: FOI and EIR Integration Approach

Status: accepted
Date: 2026-04-04

## Context

Some public information is missing, incomplete, or only available on request. FOI (Freedom of Information) and EIR (Environmental Information Regulations) requests are a practical route to obtain evidence for civic transparency work.

FOI/EIR introduces additional sensitivities:

- legal and ethical constraints
- the need to keep a clear separation between evidence and commentary
- personal data and redaction handling
- the risk of publishing misleading summaries without the underlying documents

## Decision

Treat FOI/EIR as an evidence acquisition and provenance pathway, not as “facts by default”.

Intended approach (future-facing):

- **Identify missing data:** allow the platform/community to record transparency gaps (what dataset/document appears missing, and why).
- **Request drafting support (future):** the platform may help users draft requests, but must avoid legal-advice framing and must keep responsibility with the requester.
- **Store responses as source artefacts:** responses (and attached documents) are stored as immutable artefacts with capture metadata and provenance, like any other source.
- **Link responses to datasets and records:** a response can be linked to an authority, a reporting period, and to the dataset(s) or entity claims it supports.
- **Ethical/legal considerations:** the platform must support:
  - respecting redactions and handling personal data carefully
  - not encouraging harassment or repeated bad-faith requests
  - clear disclaimers that content is evidence, not legal advice

FOI/EIR content must be treated as source-backed evidence once stored, but any interpretations or summaries derived from it must remain clearly labelled as derived.

## Consequences

- Object storage and provenance tracking (ADR-0007) must apply to FOI/EIR responses and attachments.
- Community workflows must distinguish:
  - “a response exists and is stored”
  - “the response supports claim X”
  - “claim X is interpreted from response text”
- Moderation and privacy handling become part of FOI/EIR support, even before full feature implementation.

