SHELL := /bin/bash

.PHONY: help infra-up infra-down infra-logs urls \
	backend-install backend-dev backend-queue backend-migrate backend-test backend-coverage backend-coverage-open \
	frontend-install frontend-dev

help:
	@echo "KnowMyCouncil developer commands"
	@echo ""
	@echo "Infra:"
	@echo "  make infra-up        Start local infra (Postgres/Redis/Meilisearch/MinIO/Mailpit)"
	@echo "  make infra-down      Stop local infra"
	@echo "  make infra-logs      Tail infra logs"
	@echo "  make urls            Show useful local URLs"
	@echo ""
	@echo "Backend (Laravel):"
	@echo "  make backend-install Install PHP dependencies"
	@echo "  make backend-migrate Run migrations"
	@echo "  make backend-dev     Run Laravel dev server on http://127.0.0.1:8000"
	@echo "  make backend-queue   Run a queue worker"
	@echo "  make backend-test    Run backend tests"
	@echo "  make backend-coverage Generate backend coverage (HTML + Clover XML)"
	@echo "  make backend-coverage-open Open HTML coverage report"
	@echo ""
	@echo "Frontend (Nuxt 3):"
	@echo "  make frontend-install Install JS dependencies"
	@echo "  make frontend-dev     Run Nuxt dev server on http://127.0.0.1:3000"

infra-up:
	@docker compose -f infra/docker-compose.yml up -d

infra-down:
	@docker compose -f infra/docker-compose.yml down

infra-logs:
	@docker compose -f infra/docker-compose.yml logs -f --tail=200

urls:
	@echo "Backend API:     http://127.0.0.1:8000/api/health"
	@echo "Frontend:        http://127.0.0.1:3000"
	@echo "Mailpit UI:      http://127.0.0.1:8025"
	@echo "Meilisearch:     http://127.0.0.1:7700"
	@echo "MinIO API:       http://127.0.0.1:9000"
	@echo "MinIO Console:   http://127.0.0.1:9001"
	@echo "Postgres:        localhost:5432 (db=knowmycouncil user=knowmycouncil)"
	@echo "Redis:           localhost:6379"

backend-install:
	@cd backend && composer install

backend-migrate:
	@cd backend && php artisan migrate

backend-dev:
	@cd backend && php artisan serve --host=127.0.0.1 --port=8000

backend-queue:
	@cd backend && php artisan queue:work

backend-test:
	@./scripts/backend-test.sh

backend-coverage:
	@./scripts/backend-coverage.sh

backend-coverage-open:
	@./scripts/backend-coverage-open.sh

frontend-install:
	@cd frontend && npm install

frontend-dev:
	@cd frontend && npm run dev -- --host 127.0.0.1 --port 3000
