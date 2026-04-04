# Backend coverage output

This directory is the stable, repo-visible location for backend (Laravel) test coverage output.

Generated artefacts are **not** committed to git by default.

## What gets generated here

- `coverage/html/` (HTML report)
- `coverage/clover.xml` (Clover XML, useful for CI tooling)
- `coverage/coverage.txt` (optional text summary, if enabled)

## How to generate coverage

From the repository root:

```bash
make backend-coverage
```

Or from `backend/`:

```bash
../scripts/backend-coverage.sh
```

## Viewing the HTML report

```bash
make backend-coverage-open
```

Or open `backend/coverage/html/index.html` in your browser.

## Notes

- Coverage requires a PHP coverage driver:
    - Prefer **PCOV** for coverage (faster), or
    - Use **Xdebug** when you also want interactive step debugging.
- For step debugging, see `docs/xdebug-and-coverage.md`.
