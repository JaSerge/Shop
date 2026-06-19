Запуск через docker
1. cp example.env .env

Backend
1. cp example.env .env
2. sudo docker compose up  (в корне проекта)
3. composer config gitlab-token.gitsrv.svyazcom.ru <DEPLOY_TOKEN>
4. composer i
5. php artisan key:generate
6. php artisan migrate и php artisan db:seed

Frontend
1. cp example.env .env
2. .npmrc прописываем токены гитлаб (ошибка E401 или E404)
3. yarn install
4. nvm install 14.17.3 и nvm use 14.17.3
5. yarn run serve
6. web login: support@svyazcom.ru \ password