ARG WORKDIR_PATH=/var/www

FROM node:20-alpine AS platform
ARG WORKDIR_PATH
RUN mkdir -p ${WORKDIR_PATH}
WORKDIR ${WORKDIR_PATH}


FROM platform AS runtime-prod
ARG WORKDIR_PATH
WORKDIR ${WORKDIR_PATH}
COPY . ${WORKDIR_PATH}
RUN npm i
RUN npm run build


FROM nginx:alpine AS gateway-prod
ARG WORKDIR_PATH
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/nginx/default.conf /etc/nginx/sites-available/default
COPY --from=runtime-prod /var/www/dist /var/www
WORKDIR ${WORKDIR_PATH}


FROM platform AS gateway-dev
ARG WORKDIR_PATH
WORKDIR ${WORKDIR_PATH}
CMD ["sh", "-c", "npm install && npm run serve -- --host 0.0.0.0 --port 80"]