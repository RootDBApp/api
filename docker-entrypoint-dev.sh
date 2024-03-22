#!/usr/bin/env bash
set -Eeuo pipefail

declare api_dir="/var/www/api"
declare api_env_file="${api_dir}/.env"
declare root_api_env_file="/var/www/.api_env"

declare frontend_dir="/var/www/frontend"
declare frontend_app_config_js_file="${frontend_dir}/app-config.js"
declare root_frontend_app_config_js_file="/var/www/.app-config.js"

declare api_db_init_file="/var/www/.api_db_initialized"
declare api_init_file="/var/www/.api_initialized"
declare front_init_file="/var/www/.front_initialized"

declare rdb_init_file="/var/www/.rdb_initialized"
declare rdb_init_log_file="/var/www/.rdb_initialization.log"
declare rdb_init_from_file="/var/www/.rdb_initialized_from"
declare rdb_upgraded_to="/var/www/.rdb_upgraded_to"

[[ -f ${rdb_init_log_file} ]] && rm -f ${rdb_init_log_file}

if [[ ${ROOTDB_RESET} == 1 ]]; then

  echo "Reset RootDB initialization..."
  rm -f "${root_api_env_file}"
  rm -f "${api_db_init_file}"
  rm -f "${api_init_file}"
  rm -f "${rdb_init_file}"
  rm -f "${rdb_init_from_file}"
  rm -f "${rdb_upgraded_to}"
fi

#
# Initialization
#
echo -en "RootDB initialized ? "
if [[ ! -f "${rdb_init_file}" ]]; then

  echo "no"

  #
  # API
  #
  if [[ ! -f "${api_init_file}" ]]; then

    # Make sure we have a symlink
    echo "[API] check symlinks..."
    if [[ ! -L /var/www/api ]]; then
      ln -s /var/www/archives/dev-git/api /var/www/api
    fi

    echo "[API] Handling .env file..."
    if [[ ! -f "${api_env_file}.dev" ]]; then
      echo "[error] Unable to find API .env file. (${api_env_file}.dev)"
      exit 1
    fi

    rm -f "${api_env_file}" # should be a symlink
    ln -s "${api_env_file}.dev" "${root_api_env_file}"
    ln -s "${root_api_env_file}" "${api_env_file}"

    composer install

    touch "${api_init_file}"
  fi

  if [[ ! -f "${api_db_init_file}" ]]; then

    declare db_host
    declare db_port
    declare db_database
    declare db_username
    declare db_password
    declare app_env

    db_host=$(grep 'DB_HOST' ${api_env_file} | sed "s|DB_HOST=||g")
    db_port=$(grep 'DB_PORT' ${api_env_file} | sed "s|DB_PORT=||g")
    db_database=$(grep 'DB_DATABASE' ${api_env_file} | sed "s|DB_DATABASE=||g")
    db_username=$(grep 'DB_USERNAME' ${api_env_file} | sed "s|DB_USERNAME=||g")
    db_password=$(grep 'DB_PASSWORD' ${api_env_file} | sed "s|DB_PASSWORD=||g")
    app_env=$(grep 'APP_ENV' ${api_env_file} | sed "s|APP_ENV=||g")

    echo "[API] Wait 10s before \"${app_env}\" database initialization..."
    sleep 10

    if [[ ${ROOTDB_RESET} == 1 ]]; then

      echo "Reset database..."
      php "${api_dir}/artisan" db:wipe -n --force >>"${rdb_init_log_file}" 2>&1
    fi

    echo "[API] database initialization..."
    echo "[API] If \"Done\" is not displayed below, check log file here: ${rdb_init_log_file} (container path)"

    mysql -h "${db_host}" -P "${db_port}" -u "${db_username}" -p"${db_password}" "${db_database}" <"./storage/app/seeders/${app_env}/seeder_init.sql"


    echo "[API] initialize telescope & co"
    php artisan vendor:publish --force --all
    php artisan telescope:install


    echo
    echo "[API] Done."
    echo
    touch "${api_db_init_file}"
  fi

  #
  # Frontend
  #
  if [[ ! -f "${front_init_file}" ]]; then

    # Make sure we have a symlink
    echo "[Frontend] check symlinks..."
    if [[ ! -L /var/www/frontend ]]; then
      ln -s /var/www/archives/dev-git/frontend /var/www/frontend
    fi

    echo "[Frontend] Handling app-config-js.dev file..."
    if [[ ! -f "${frontend_app_config_js_file}.dev" ]]; then
      echo "[error] Unable to find frontend app-config.js.dev file. (${frontend_app_config_js_file}.dev)"
      exit 1
    fi

    rm -f "${frontend_app_config_js_file}" # should be a symlink
    ln -s "${frontend_app_config_js_file}.dev" "${root_frontend_app_config_js_file}"
    ln -s "${root_frontend_app_config_js_file}" "${frontend_app_config_js_file}"

    echo
    echo "[Frontend] Done."
    echo
    touch "${front_init_file}"
  fi

  # We get the version of the directory's name containing the API code.
  # 2022-02 - API & frontend - same package, unique version for now.
  echo "Storing current version..."
  declare api_version
  api_version=$(dirname $(readlink -f /var/www/api) | sed 's/^\/.*\/\(.*\)$/\1/')

  if [[ -z ${api_version} ]]; then
    echo "[error] Unable to fetch RootDB version."
    exit 0
  fi

  echo "RootDB version: ${api_version}"
  echo "${api_version}" >"${rdb_init_from_file}"

  echo
  touch "${rdb_init_file}"
else

  echo "yes"
  echo "Nothing to do."
fi

#
# Dev stuff
#
composer install
php artisan migrate

#
# API services
#
echo "Starting services with supervisor..."
/usr/bin/supervisord -c /etc/supervisord.conf


#
# Serve PHP files
#
#php artisan serve --port=8092 --host=172.20.0.30 &


# Will run `php-fpm` by default
exec "$@"
