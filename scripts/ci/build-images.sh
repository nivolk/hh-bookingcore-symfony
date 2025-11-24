#!/bin/sh
set -eu

IMAGE_PHP="${CI_REGISTRY_IMAGE}/php-fpm"
IMAGE_NGINX="${CI_REGISTRY_IMAGE}/nginx"

echo "Logging in to registry ${CI_REGISTRY}..."
echo "${CI_REGISTRY_PASSWORD}" | docker login "${CI_REGISTRY}" -u "${CI_REGISTRY_USER}" --password-stdin

echo "Trying to pull previous PHP-FPM image for cache..."
docker pull "${IMAGE_PHP}:latest" || true

echo "Building PHP-FPM image: ${IMAGE_PHP}:${CI_COMMIT_SHORT_SHA}"
docker build \
  --cache-from="${IMAGE_PHP}:latest" \
  -t "${IMAGE_PHP}:${CI_COMMIT_SHORT_SHA}" \
  -f docker/php-fpm/Dockerfile .

echo "Trying to pull previous Nginx image for cache..."
docker pull "${IMAGE_NGINX}:latest" || true

echo "Building Nginx image: ${IMAGE_NGINX}:${CI_COMMIT_SHORT_SHA}"
docker build \
  --cache-from="${IMAGE_NGINX}:latest" \
  -t "${IMAGE_NGINX}:${CI_COMMIT_SHORT_SHA}" \
  -f docker/nginx/Dockerfile .

echo "Pushing images with commit tags..."
docker push "${IMAGE_PHP}:${CI_COMMIT_SHORT_SHA}"
docker push "${IMAGE_NGINX}:${CI_COMMIT_SHORT_SHA}"

echo "Tagging images as latest..."
docker tag "${IMAGE_PHP}:${CI_COMMIT_SHORT_SHA}" "${IMAGE_PHP}:latest"
docker tag "${IMAGE_NGINX}:${CI_COMMIT_SHORT_SHA}" "${IMAGE_NGINX}:latest"

echo "Pushing latest tags..."
docker push "${IMAGE_PHP}:latest"
docker push "${IMAGE_NGINX}:latest"

echo "Build and push finished."
