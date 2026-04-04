#!/usr/bin/env bash
set -euo pipefail

cat <<TXT
Useful local URLs

Backend API health:  http://127.0.0.1:8000/api/health
Backend API version: http://127.0.0.1:8000/api/version
Frontend:            http://127.0.0.1:3000
Mailpit UI:          http://127.0.0.1:8025
Meilisearch:         http://127.0.0.1:7700
MinIO API:           http://127.0.0.1:9000
MinIO Console:       http://127.0.0.1:9001
TXT
