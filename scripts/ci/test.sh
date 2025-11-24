#!/usr/bin/env bash
set -euo pipefail

echo "Installing system dependencies..."
apt-get update
apt-get install -y git unzip libicu-dev libzip-dev default-mysql-client
docker-php-ext-install intl pdo_mysql zip

echo "Installing composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

echo "Installing PHP dependencies..."
composer install --no-interaction

echo "Waiting for database..."
for i in {1..30}; do
  if mysql -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" -h database -e 'SELECT 1' "${MYSQL_DATABASE}" >/dev/null 2>&1; then
    echo "Database is up"
    break
  fi
  echo "Database is not ready yet, retrying..."
  sleep 2
done

echo "Running doctrine migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --env=test

echo "Running PHPUnit..."
./vendor/bin/phpunit
