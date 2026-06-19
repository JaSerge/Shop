ARG WORKDIR_PATH=/var/www/html

FROM php:8.3-fpm-alpine AS platform-fpm
ARG WORKDIR_PATH
COPY ./docker/install-deps.sh /tmp/
RUN chmod +x /tmp/install-deps.sh && /tmp/install-deps.sh && rm /tmp/install-deps.sh
RUN apk add --no-cache fcgi \
    && wget -O /usr/local/bin/php-fpm-healthcheck \
        https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
    && chmod +x /usr/local/bin/php-fpm-healthcheck \
    && echo "pm.status_path = /status" >> /usr/local/etc/php-fpm.d/zz-docker.conf
RUN mkdir -p ${WORKDIR_PATH}
WORKDIR ${WORKDIR_PATH}



FROM php:8.3-alpine AS platform-cli
ARG WORKDIR_PATH
COPY ./docker/install-deps.sh /tmp/
RUN chmod +x /tmp/install-deps.sh && /tmp/install-deps.sh && rm /tmp/install-deps.sh
RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin
RUN mkdir -p ${WORKDIR_PATH}
WORKDIR ${WORKDIR_PATH}


FROM platform-cli AS vendor-prod
ARG WORKDIR_PATH
COPY . ${WORKDIR_PATH}
WORKDIR ${WORKDIR_PATH}
RUN composer install



FROM platform-fpm AS platform-fpm-prod
ARG WORKDIR_PATH
COPY . ${WORKDIR_PATH}
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
COPY --from=vendor-prod ${WORKDIR_PATH}/vendor ${WORKDIR_PATH}/vendor
WORKDIR ${WORKDIR_PATH}



FROM platform-fpm AS platform-fpm-dev
ARG WORKDIR_PATH
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
WORKDIR ${WORKDIR_PATH}



#FROM platform-cli AS platform-cli-schedule-prod
#ARG WORKDIR_PATH
#COPY --from=platform-fpm-prod ${WORKDIR_PATH} ${WORKDIR_PATH}
#WORKDIR ${WORKDIR_PATH}
#CMD ["sh", "-c", "php artisan schedule:work"]



#FROM platform-cli AS platform-cli-schedule-dev
#ARG WORKDIR_PATH
#WORKDIR ${WORKDIR_PATH}
#CMD ["sh", "-c", "php artisan schedule:work"]



FROM nginx:alpine AS gateway
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/nginx/default.conf /etc/nginx/sites-available/default