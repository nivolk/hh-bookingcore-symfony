# Hunting Booking Module (Symfony 7.3)

## Задание

Создать минимальный Symfony-модуль, который реализует:

### Миграции и модели

* `Guide`: (`name`, `experience_years`, `is_active`)
* `HuntingBooking`: (`tour_name`, `hunter_name`, `guide_id`, `date`, `participants_count`)

### API-эндпоинты

* `GET /api/guides`: список активных гидов
* `POST /api/bookings`: создание нового бронирования

### Логика бронирования

* Проверить, что у выбранного гида нет других бронирований на ту же дату.
* Проверить, что `participants_count <= 10`.
* Вернуть осмысленные ответы (`200`, `201`, `400`, `409`, `422` и т.д.).

## Что оценивается

* Корректность и чистота кода.
* Использование Symfony best practices (entity, валидация, контроллеры, ресурсы).
* Структура проекта и понятность решений.
* Минимум "магии" - максимум логики.

## Бонус (по желанию)

* Добавить простейший Unit/Feature-тест.
* Сделать фильтр `GET /api/guides?min_experience=3`.
* Коротко описать в README.

---

## Реализация

Проект следует подходу лёгкого DDD: код разделён на слои `Domain`, `Application`, `Infrastructure`, но без лишней бюрократии и оверхеда.

---

## Архитектура и структура каталогов

Проект организован по принципу *feature-first*: функциональность вынесена в модули в `modules/`.

- `modules/Common` - общая инфраструктура и базовый домен:
  базовые типы, абстракции и исключения, HTTP-слой (`ProblemDetails` в формате RFC 7807, глобальный обработчик ошибок, базовые ответы и `ApiResponder`).

- `modules/Hunting` - предметная область "охотничьи туры и бронирования":
  доменные сущности, application-сервисы, контроллеры, репозитории и запросы/ответы.

---

## Запуск в Docker

### Быстрый старт

Для удобства локальной разработки используется `Makefile`.

```bash
# Полная инициализация проекта: контейнеры + зависимости + миграции + фикстуры
make app-init
```

После этого приложение будет доступно по адресу:

```text
http://127.0.0.1:8000
```

### Ручной запуск

```bash
docker compose up -d
docker compose exec php-fpm composer install
docker compose exec php-fpm php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php-fpm php bin/console doctrine:fixtures:load --group=hunting_guides --no-interaction
```

---

## Makefile: основные команды

Все команды запускаются из корня проекта.

```bash
# Поднять контейнеры
make docker-up
make docker-up-d

# Остановить контейнеры
make docker-down

# Просмотреть логи
make docker-logs

# Зайти внутрь php-fpm контейнера
make docker-bash

# Полная инициализация (контейнеры + composer install + миграции + фикстуры)
make app-init

# Миграции
make db-migrate          # накатить все миграции
make db-migrate-diff     # сгенерировать новую миграцию
make db-migrate-reset    # дропнуть схему и накатить миграции заново

# Фикстуры
make db-fixtures         # залить все фикстуры

# Бэкапы БД
make backup-db                               # сохранить дамп (по умолчанию в var/backups/...)
make backup-db BACKUP_FILE=/tmp/dump.sql     # сохранить дамп в указанный файл
make backup-restore BACKUP_FILE=path/to.sql  # восстановить БД из дампа

# Прочее
make app-cache-clear      # очистить кеш Symfony
make app-cache-warmup     # прогреть кеш
make app-test             # запустить тесты
make help                 # показать список всех доступных команд
```

---

## Тесты

```bash
# Локально
./vendor/bin/phpunit

# В Docker
make app-test
```

---

## Документация API

После запуска приложения документация api доступна по адресу:

```text
http://127.0.0.1:8000/api/doc
```
