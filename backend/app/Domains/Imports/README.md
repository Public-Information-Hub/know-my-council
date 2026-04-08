# Imports

Import orchestration, parsing, normalisation, and import-run tracking live under this domain.

Phase 1 includes a single concrete ingestion path, but it should be treated as the first adapter rather than the last:

- council spend CSV ingestion: see [docs/ingestion/spend-csv.md](../../../../docs/ingestion/spend-csv.md)

Longer term, this domain is expected to host adapter-backed ingestion for API feeds, CSV/TSV exports, HTML tables, and document-backed sources.
