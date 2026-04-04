# Spend CSV ingestion (Phase 1)

Phase 1 supports ingesting a single council "spend over £500" style CSV file into the Phase 1 schema.

This is an intentionally narrow, practical ingestion path:

- it preserves the raw CSV as a `source_files` record stored in object storage (MinIO in local dev)
- it normalises rows into `spend_records`
- it records import tracking via `datasets`, `dataset_versions`, `imports`, and `import_runs`

It does not implement contracts ingestion, FOI ingestion, moderation, or search indexing.

## Prerequisites

1. Start local infra:

```bash
make infra-up
```

2. Configure the backend env file:

```bash
cp backend/.env.example backend/.env
```

3. Run migrations:

```bash
make backend-migrate
```

## Command

Run:

```bash
cd backend
php artisan kmc:ingest:council-spend-csv <council_slug> <path-to-file.csv>
```

Example:

```bash
cd backend
php artisan kmc:ingest:council-spend-csv bristol-city-council ./storage/app/private/example-spend.csv --create-council --council-name="Bristol City Council"
```

Useful options:

- `--council-version-id=...` Use a specific `council_versions.id` rather than auto-selecting the latest published version.
- `--dataset-key=...` Override the default dataset key (`council:<slug>:spend_over_500_csv`).
- `--edition-date=YYYY-MM-DD` Set the dataset version edition date (defaults to today).
- `--published-at=...` Timestamp from the publisher (stored on `dataset_versions.published_at` and `source_files.published_at`).
- `--captured-at=...` When this file was captured (defaults to now).
- `--idempotency-key=...` Prevent accidental duplicates. If an earlier run for the same import key succeeded with the same key, the command reports `skipped`.
- `--storage-disk=minio` Disk used to store the raw CSV (defaults to `FILESYSTEM_DISK`).
- `--visibility=restricted` One of `public|restricted|private` for the `source_files` row.
- `--supplier-header=...` Override the supplier column header (case-insensitive match).
- `--amount-header=...` Override the amount column header (case-insensitive match).
- `--date-header=...` Override the date column header (case-insensitive match).
- `--description-header=...` Override the description column header (case-insensitive match).
- `--dry-run` Parse/validate only; does not write to the database or object storage.

## What it creates (high level)

- `datasets` row (created if missing)
- `dataset_versions` row (new row each run)
- `imports` row (created if missing)
- `import_runs` row (one per execution)
- `source_files` row (points at the raw CSV stored on the configured filesystem disk)
- `spend_records` rows (normalised records)
- `audit_logs` entries for import run start and completion/failure
- `state_transitions` entries for `import_runs.run_state` transitions (queued -> running -> succeeded/failed)

## Provenance and traceability

- `spend_records` link to `import_runs` and `dataset_versions`.
- The raw file is represented by `source_files` linked to the `import_run` and `dataset_version`.
- Phase 1 does not store row-level provenance pointers (for example, raw row number per spend record). That can be added later if required.

## Organisation matching (Phase 1)

Organisation linking is conservative:

1. Case-insensitive exact match against `organisations.canonical_name` gives `mapping_confidence=high`
2. Case-insensitive exact match against `organisation_aliases.alias` gives `mapping_confidence=medium`
3. Otherwise `organisation_id` is left `NULL` with `mapping_confidence=unknown`

No fuzzy matching is used in Phase 1.

## Known limitations (Phase 1)

- The header mapper supports a small set of common column names. When you find a real council variation, extend `SpendCsvColumnMap` with a minimal, reviewable change.
- If a council uses unusual header names, use the `--*-header` overrides before changing code.
- Amount and date parsing is deliberately conservative. Rows with missing/invalid amount/date are skipped and counted as warnings.
- Currency is currently fixed to `GBP`.
