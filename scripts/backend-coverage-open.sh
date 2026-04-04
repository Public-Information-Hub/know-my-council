#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "$0")/.." && pwd)"
report="$repo_root/backend/coverage/html/index.html"

if [ ! -f "$report" ]; then
  echo "Coverage report not found: $report"
  echo "Generate it first: make backend-coverage"
  exit 1
fi

if command -v open >/dev/null 2>&1; then
  open "$report"
  exit 0
fi

if command -v xdg-open >/dev/null 2>&1; then
  xdg-open "$report" >/dev/null 2>&1 &
  exit 0
fi

echo "Open this file in your browser: $report"
