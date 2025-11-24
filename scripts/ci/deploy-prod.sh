#!/bin/sh
set -eu

apk add --no-cache openssh-client

mkdir -p ~/.ssh
echo "${DEPLOY_SSH_KEY}" | tr -d '\r' > ~/.ssh/id_rsa
chmod 600 ~/.ssh/id_rsa
ssh-keyscan "${DEPLOY_HOST}" >> ~/.ssh/known_hosts

REMOTE_DIR="/opt/hunting-booking"

echo "Creating project directory on remote host..."
ssh "${DEPLOY_USER}@${DEPLOY_HOST}" "mkdir -p ${REMOTE_DIR}"

echo "Copying compose.yaml and docker directory to remote host..."
scp compose.yaml "${DEPLOY_USER}@${DEPLOY_HOST}:${REMOTE_DIR}/"
scp -r docker "${DEPLOY_USER}@${DEPLOY_HOST}:${REMOTE_DIR}/"

NEW_PHP_IMAGE="${CI_REGISTRY_IMAGE}/php-fpm:${CI_COMMIT_SHORT_SHA}"
NEW_NGINX_IMAGE="${CI_REGISTRY_IMAGE}/nginx:${CI_COMMIT_SHORT_SHA}"

echo "Deploying stack on remote host..."
ssh "${DEPLOY_USER}@${DEPLOY_HOST}" "
  set -eu
  cd ${REMOTE_DIR}

  echo \"Logging in to registry ${CI_REGISTRY}...\"
  echo \"${CI_REGISTRY_PASSWORD}\" | docker login \"${CI_REGISTRY}\" -u \"${CI_REGISTRY_USER}\" --password-stdin

  echo \"Pulling new images...\"
  APP_IMAGE_PHP=\"${NEW_PHP_IMAGE}\" \
  APP_IMAGE_NGINX=\"${NEW_NGINX_IMAGE}\" \
  docker compose -f compose.yaml pull

  echo \"Running doctrine migrations on new image...\"
  APP_IMAGE_PHP=\"${NEW_PHP_IMAGE}\" \
  APP_IMAGE_NGINX=\"${NEW_NGINX_IMAGE}\" \
  docker compose -f compose.yaml run --rm php-fpm \
    php bin/console doctrine:migrations:migrate --no-interaction --env=prod

  echo \"Starting/refreshing stack with new images...\"
  APP_IMAGE_PHP=\"${NEW_PHP_IMAGE}\" \
  APP_IMAGE_NGINX=\"${NEW_NGINX_IMAGE}\" \
  docker compose -f compose.yaml up -d

  echo \"Cleaning dangling images...\"
  docker image prune -f

  echo \"Deploy finished successfully.\"
"

echo "Deploy script finished."
