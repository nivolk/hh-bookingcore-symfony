#!/bin/sh
set -eu

apk add --no-cache openssh-client bash

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

echo "Deploying stack on remote host..."
ssh "${DEPLOY_USER}@${DEPLOY_HOST}" "
  set -e
  cd ${REMOTE_DIR}
  echo \"Logging in to registry ${CI_REGISTRY}...\"
  echo \"${CI_REGISTRY_PASSWORD}\" | docker login \"${CI_REGISTRY}\" -u \"${CI_REGISTRY_USER}\" --password-stdin

  APP_IMAGE_PHP=\"${CI_REGISTRY_IMAGE}/php-fpm:${CI_COMMIT_SHORT_SHA}\" \
  APP_IMAGE_NGINX=\"${CI_REGISTRY_IMAGE}/nginx:${CI_COMMIT_SHORT_SHA}\" \
  docker compose -f compose.yaml pull

  APP_IMAGE_PHP=\"${CI_REGISTRY_IMAGE}/php-fpm:${CI_COMMIT_SHORT_SHA}\" \
  APP_IMAGE_NGINX=\"${CI_REGISTRY_IMAGE}/nginx:${CI_COMMIT_SHORT_SHA}\" \
  docker compose -f compose.yaml up -d
"

echo "Deploy finished."
