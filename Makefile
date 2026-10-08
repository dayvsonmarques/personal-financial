DC = docker compose
PHP = $(DC) exec php
WEB = $(DC) exec web corepack pnpm

.PHONY: up down build sh artisan composer migrate fresh test test-api test-web lint e2e logs

up: ## Sobe o ambiente
	$(DC) up -d

down: ## Derruba o ambiente
	$(DC) down

build: ## Reconstrói a imagem PHP
	$(DC) build php

sh: ## Shell no container PHP
	$(PHP) sh

artisan: ## make artisan c="migrate"
	$(PHP) php artisan $(c)

composer: ## make composer c="require x/y"
	$(PHP) composer $(c)

migrate:
	$(PHP) php artisan migrate

fresh: ## Recria o banco com seeders
	$(PHP) php artisan migrate:fresh --seed

test: test-api test-web

test-api:
	$(PHP) php artisan test

test-web:
	$(WEB) test

lint:
	$(PHP) vendor/bin/pint --test
	$(PHP) vendor/bin/phpstan analyse --memory-limit=1G
	$(WEB) lint
	$(WEB) typecheck

PLAYWRIGHT_IMAGE = mcr.microsoft.com/playwright:v1.64.0-noble

e2e: ## Testes de ponta a ponta (requer make up). Ex.: make e2e p="--project=chromium"
	docker run --rm --network host --ipc=host -u $$(id -u):$$(id -g) -e HOME=/tmp \
		-v $(CURDIR)/web:/app -w /app $(PLAYWRIGHT_IMAGE) ./node_modules/.bin/playwright test $(p)

logs:
	$(DC) logs -f --tail=100
