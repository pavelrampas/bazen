SHELL = /bin/sh

-include .env

help: ## This help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

run-dev: ## Run local development
	docker compose up --build -d

run-dev-force: ## Run local development force-recreate
	docker compose up --build --force-recreate -d

stop-dev: ## Stop local development
	docker compose stop

down-dev: ## Drop local development
	docker compose down

cli-nginx: ## Run shell inside local container nginx
	docker compose exec nginx sh

cli-php: ## Run shell inside local container php
	docker compose exec php sh

cli-db: ## Run shell inside local container mariadb
	docker compose exec mariadb sh
