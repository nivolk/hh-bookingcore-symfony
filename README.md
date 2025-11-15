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

Модуль построен по принципу feature-first и придерживается подхода лайтового DDD, то есть выделены основные
слои (Domain, Application, Infrastructure), но без излишней бюрократии и оверхеда.

Для удобства и быстрого запуска применена база SQLite.

---

## Запуск

```bash
composer install
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --group=hunting_guides --no-interaction
php -S 127.0.0.1:8000 -t public
```

## Тесты: 
```bash
./vendor/bin/phpunit
```

## Документация API:
```
http://127.0.0.1:8000/api/doc
```
