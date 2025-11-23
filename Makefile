SHELL := /bin/bash

DOCKER_COMPOSE := docker compose
PHP_SERVICE := php-fpm
DB_SERVICE := database

# окружение
ENV ?= dev

# Настройки базы (должны совпадать с compose.yaml)
DB_NAME := app
DB_USER := app
DB_PASSWORD := app_password

# Настройки бэкапов
BACKUP_DIR := var/backups
DATE := $(shell date +%Y%m%d_%H%M%S)
BACKUP_FILE ?= $(BACKUP_DIR)/db_$(ENV)_$(DATE).sql

# -------------------------
# Подтверждение действий
# -------------------------

define confirm-exec
	@echo ""
	@echo "==============================================="
	@echo "  ВНИМАНИЕ!"
	@echo "  Действие: $(1)"
	@echo "  Окружение: $(ENV)"
	@echo "==============================================="
	@read -p "Продолжить? Напишите 'yes' для подтверждения: " ans; \
	if [ "$$ans" != "yes" ]; then \
		echo "Операция отменена."; \
		exit 1; \
	fi
	@echo "Подтверждено. Выполняю..."
	@echo ""
endef

# -------------------------
# Help
# -------------------------

.PHONY: help
help: ## Показать список доступных команд
	@echo ""
	@echo "Доступные команды (ENV=$(ENV)):"
	@echo ""
	@grep -E '^[a-zA-Z0-9_-]+:.*##' Makefile | sort | awk 'BEGIN {FS=":.*##"} {printf "  %-30s %s\n", $$1, $$2}'
	@echo ""
	@echo "Примеры:"
	@echo "  make docker-up-d          # поднять контейнеры в фоне"
	@echo "  make app-init             # полный цикл: миграции + фикстуры"
	@echo "  make db-migrate-reset     # дропнуть схему и накатить миграции (НЕ в prod)"
	@echo "  make backup-db            # сделать бэкап базы"
	@echo "  make backup-restore BACKUP_FILE=var/backups/....sql"
	@echo "  ENV=test make db-migrate  # миграции в тестовом окружении"
	@echo ""

.PHONY: \
	docker-up docker-up-d docker-down docker-restart docker-logs docker-ps docker-bash docker-build \
	app-init app-test app-console app-cache-clear app-cache-warmup \
	composer-install composer-dump-autoload composer-update \
	db-drop db-migrate db-migrate-diff db-migrate-reset db-status \
	db-fixtures backup-db backup-restore

# -------------------------
# Docker: управление сервисами
# -------------------------

docker-up: ## Запустить контейнеры
	$(DOCKER_COMPOSE) up

docker-up-d: ## Запустить контейнеры в фоне
	$(DOCKER_COMPOSE) up -d

docker-down: ## Остановить и удалить контейнеры
	$(DOCKER_COMPOSE) down

docker-restart: ## Перезапустить контейнеры
	$(DOCKER_COMPOSE) down
	$(DOCKER_COMPOSE) up -d

docker-logs: ## Смотреть логи всех контейнеров
	$(DOCKER_COMPOSE) logs -f

docker-ps: ## Показать статус контейнеров
	$(DOCKER_COMPOSE) ps

docker-bash: ## Зайти в bash внутри php-fpm контейнера
	$(DOCKER_COMPOSE) exec $(PHP_SERVICE) bash

docker-build: ## Пересобрать образ php-fpm
	$(DOCKER_COMPOSE) build $(PHP_SERVICE)

# -------------------------
# Symfony / App / Cache / Тесты
# -------------------------

app-init: ## Полный цикл инициализации dev: composer install, миграции, фикстуры гидов
	$(call confirm-exec,полную начальную инициализацию с миграциями и фикстурами)
	$(DOCKER_COMPOSE) up -d
	$(DOCKER_COMPOSE) exec $(PHP_SERVICE) composer install
	$(DOCKER_COMPOSE) exec -e APP_ENV=$(ENV) $(PHP_SERVICE) php bin/console doctrine:migrations:migrate --no-interaction
	$(DOCKER_COMPOSE) exec -e APP_ENV=$(ENV) $(PHP_SERVICE) php bin/console doctrine:fixtures:load --group=hunting_guides --no-interaction

app-test: ## Запустить phpunit-тесты
	$(DOCKER_COMPOSE) exec -e APP_ENV=test $(PHP_SERVICE) ./vendor/bin/phpunit

app-cache-clear: ## Очистить кеш Symfony
	$(DOCKER_COMPOSE) exec -e APP_ENV=$(ENV) $(PHP_SERVICE) php bin/console cache:clear

app-cache-warmup: ## Прогреть кеш Symfony
	$(DOCKER_COMPOSE) exec -e APP_ENV=$(ENV) $(PHP_SERVICE) php bin/console cache:warmup

app-console: ## Выполнить произвольную Symfony-команду: make app-console CMD='cache:clear'
	@if [ -z "$(CMD)" ]; then \
		echo "Usage: make app-console CMD='your:command'"; \
		exit 1; \
	fi
	$(DOCKER_COMPOSE) exec -e APP_ENV=$(ENV) $(PHP_SERVICE) php bin/console $(CMD)

# -------------------------
# Composer
# -------------------------

composer-install: ## Установить зависимости composer install
	$(DOCKER_COMPOSE) exec $(PHP_SERVICE) composer install

composer-dump-autoload: ## Обновить автозагрузку composer dump-autoload
	$(DOCKER_COMPOSE) exec $(PHP_SERVICE) composer dump-autoload

composer-update: ## Обновить зависимости composer update
	$(call confirm-exec,запускать composer update)
	$(DOCKER_COMPOSE) exec $(PHP_SERVICE) composer update

# -------------------------
# База / Миграции / Фикстуры
# -------------------------

db-drop: ## Полный drop схемы БД
	$(call confirm-exec,полный сброс базы)
	@if [ "$(ENV)" = "prod" ]; then \
		echo "ERROR: db-drop запрещён для prod."; \
		exit 1; \
	fi
	$(DOCKER_COMPOSE) exec -e APP_ENV=$(ENV) $(PHP_SERVICE) php bin/console doctrine:schema:drop --force --full-database

db-migrate: ## Накатить все миграции
	$(call confirm-exec,накатывать миграции)
	$(DOCKER_COMPOSE) exec -e APP_ENV=$(ENV) $(PHP_SERVICE) php bin/console doctrine:migrations:migrate --no-interaction

db-migrate-diff: ## Сгенерировать новую миграцию из diff
	$(call confirm-exec,генерировать миграцию (diff))
	$(DOCKER_COMPOSE) exec -e APP_ENV=$(ENV) $(PHP_SERVICE) php bin/console doctrine:migrations:diff

db-migrate-reset: ## Полный ресет: drop + migrate
	$(call confirm-exec,сбросить схему (drop + migrate))
	$(MAKE) db-drop
	$(MAKE) db-migrate

db-status: ## Показать статус миграций
	$(DOCKER_COMPOSE) exec -e APP_ENV=$(ENV) $(PHP_SERVICE) php bin/console doctrine:migrations:status

db-fixtures: ## Залить ВСЕ фикстуры
	$(call confirm-exec,заливать все фикстуры (purge + load))
	$(DOCKER_COMPOSE) exec -e APP_ENV=$(ENV) $(PHP_SERVICE) php bin/console doctrine:fixtures:load --no-interaction

# -------------------------
# Бэкапы MariaDB
# -------------------------

backup-db: ## Сделать бэкап БД в указанный файл (по умолчанию var/backups/...)
	mkdir -p $(dir $(BACKUP_FILE))
	$(DOCKER_COMPOSE) exec -T $(DB_SERVICE) sh -c "mysqldump -u$(DB_USER) -p$(DB_PASSWORD) $(DB_NAME)" > "$(BACKUP_FILE)"
	@echo "Database backup saved to $(BACKUP_FILE)"

backup-restore: ## Восстановить БД из дампа: make backup-restore BACKUP_FILE=...
	$(call confirm-exec,восстанавливать БД из дампа)
	@if [ -z "$(BACKUP_FILE)" ]; then \
		echo "ERROR: BACKUP_FILE не задан."; \
		echo "Usage: make backup-restore BACKUP_FILE=path/to/file.sql"; \
		exit 1; \
	fi
	@if [ ! -f "$(BACKUP_FILE)" ]; then \
		echo "ERROR: backup file not found: $(BACKUP_FILE)"; \
		exit 1; \
	fi
	$(DOCKER_COMPOSE) exec -T $(DB_SERVICE) sh -c "mysql -u$(DB_USER) -p$(DB_PASSWORD) $(DB_NAME)" < "$(BACKUP_FILE)"
	@echo "Database restored from $(BACKUP_FILE)"
