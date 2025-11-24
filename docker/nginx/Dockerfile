FROM nginx:1.27-alpine

WORKDIR /var/www/html

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

COPY public /var/www/html/public
