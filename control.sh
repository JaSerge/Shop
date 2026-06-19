#!/bin/bash

set -e

source .env

FileEnvBack="${FILE_ENV_BACKEND}"

if [ ! -f "$FileEnvBack" ] && [ -f "${PATH_BACKEND}/.env.docker" ]; then
    cp "${PATH_BACKEND}/.env.docker" "$FileEnvBack"
fi

# Проверяем существование файла
if [ ! -f "$FileEnvBack" ]; then
    echo "Файл не назадан: $FileEnvBack"
    echo "💡 Укажите FILE_ENV_BACKEND в .env"
    exit 1
fi

function docker_init() {
    output "Copy .env"
    cp -n .env.example .env
    cp -n $PATH_BACKEND/.env.docker $PATH_BACKEND/.env
    if [ -f "$PATH_FRONTEND/.env.docker" ]; then
        cp -n $PATH_FRONTEND/.env.docker $PATH_FRONTEND/.env
    fi
    output "Project initialization finished, now you need to check your .env files and continue with ./control.sh startup"
}

function docker_startup() {
    output "Docker containers building"
    docker compose down -v
    source .env
    docker_build

    docker compose -f docker-compose.yml -f docker-compose.web-tools.yml run --rm web-backend-cli composer install --no-interaction
    if [ -f "$PATH_FRONTEND/package.json" ]; then
        docker compose -f docker-compose.yml -f docker-compose.web-tools.yml run --rm web-frontend-cli npm install
    fi

    docker compose up -d --wait
    docker_init_volumes
    output "Project startup finished, now you can visit web by going into http://localhost:${GATEWAY_PORT}/"
}

function docker_init_volumes() {
    select opt in "Локальная БД (докер)" "Тестовая БД" "Удаленная БД"; do
      case $REPLY in
          1)
            _database_config_clear
            
            
            output "Backend - migrations"
            docker_migrate
            output "Backend successfully migrated and configured"

            break
            ;;
          2)
            _database_config_clear

            _database_add_test_config

            break
            ;;
          3)            
            echo "Укажите настройки для удаленного сервера в ${FileEnvBack}"              

            break
            ;;            
         *)
            echo "Неверный выбор, попробуйте снова"
            ;;
      esac
    done
}

function docker_up() {
    source .env
    output "Start application"
    docker compose up -d --wait
    output "Project startup finished, now you can visit web by going into http://localhost:${GATEWAY_PORT}/"
}

function docker_down() {
    output "Stop application"
    docker compose down
}

function docker_restart() {
    output "Restart application"
    docker_down
    docker_up
}

function docker_recreate_volumes() {
    output "Recreation of volumes"
    docker compose down -v
    docker compose up -d --wait
    docker_init_volumes
    docker_up
}

function docker_rebuild() {
    output "Rebuild application"
    docker_down
    docker_build
    docker_up
}

function docker_build() {
    output "Build images"
    docker compose build --progress=plain
}

function docker_migrate() {
    output "Starting migrations"
    docker compose -f docker-compose.yml -f docker-compose.web-tools.yml run --rm web-backend-cli php bin/console doctrine:migrations:migrate --no-interaction
}

function docker_backend_sh() {
  docker compose -f docker-compose.web-tools.yml run --rm web-backend-cli sh
}

function docker_frontend_sh() {
  docker compose -f docker-compose.web-tools.yml run --rm web-frontend-cli sh
}

function output() {
    echo -e "------------------------------------\n$1\n------------------------------------"
}

function read_composer_token() {
    local check=0
    while [[ $# -gt 0 ]]; do
        case $1 in
            -c|--check) check=1; shift;;
            *) break;;
        esac
    done

    if [ "$check" = "1" ]; then
        if [ -f "${PATH_BACKEND}/auth.json" ]; then
            output "Token exists"
            return 0
        fi
    fi

    read -p "Enter composer token: " SC_CI_TOKEN_COMMON
    if [ -z "$SC_CI_TOKEN_COMMON" ]; then
      output "Token is empty"
      exit 1
    fi

    docker compose -f docker-compose.web-tools.yml run --rm web-backend-cli composer config http-basic.gitsrv.svyazcom.ru ___token___ $SC_CI_TOKEN_COMMON
}



function _database_download_dump() {
    # Получаем дамп с удаленной машины
    mysqldump -u eir -p'123' -h 192.168.17.29 -P 3306 eir_tj eir > eir_dump.sql
}

# Функция для удаления настроек подключения к БД из .env
function _database_config_clear() {
    echo "Создаем бэкап: ${FileEnvBack}.bak"
    cp "$FileEnvBack" "${FileEnvBack}.bak"
    
    echo "🗑️  Удаляем настройки подключения к БД из $FileEnvBack..."
    
    sed -i '
    /^# *=*$/d;
    /^# Настройки для подключения к БД/d;    
    /^DB_\(CONNECTION\|HOST\|PORT\|DATABASE\|USERNAME\|PASSWORD\|SCHEMA\)=/d;
    /^DB_\(CONNECTION\|HOST\|PORT\|DATABASE\|USERNAME\|PASSWORD\|SCHEMA\)_MAIN=/d
    ' "$FileEnvBack"

    # Удаляем пустые строки в конце файла
    sed -i '${/^$/d;}' "$FileEnvBack"
    
    echo "✅ Настройки подключения к БД удалены (бэкап: ${FileEnvBack}.bak)"
}



# Функция для добавления в backend/.env настроек для подключения к тестовой БД



case "$1" in
    init)
        docker_init
        ;;
    init-volumes)
        docker_init_volumes
        ;;
    startup)
        docker_startup
        ;;
    up)
        docker_up
        ;;
    down)
        docker_down
        ;;
    restart)
        docker_restart
        ;;
    build)
        docker_build
        ;;
    rebuild)
        docker_rebuild
        ;;
    migrate)
        docker_migrate
        ;;
    backend-sh)
        docker_backend_sh
        ;;
    frontend-sh)
        docker_frontend_sh
        ;;
    read-composer-token)
        shift
        read_composer_token "$@"
        ;;
    recreate-volumes)
        docker_recreate_volumes
        ;;
    *)
        output "Use: control <init | init-volumes | startup | up | down | restart | build | rebuild | migrate | backend-sh | frontend-sh | read-composer-token | recreate-volumes>"
        ;;
esac