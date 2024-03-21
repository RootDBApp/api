#!/usr/bin/env bash

###############################################################################################
declare SCRIPT_PATH
pushd . >/dev/null
SCRIPT_PATH="${BASH_SOURCE[0]}"
if ([ -h "${SCRIPT_PATH}" ]); then
  while ([ -h "${SCRIPT_PATH}" ]); do
    cd "$(dirname "$SCRIPT_PATH")" || exit
    SCRIPT_PATH=$(readlink "${SCRIPT_PATH}")
  done
fi
cd "$(dirname ${SCRIPT_PATH})" || exit >/dev/null
SCRIPT_PATH=$(pwd)
popd || exit >/dev/null

# shellcheck source=./functions.sh
source "${SCRIPT_PATH}/functions.sh"
###############################################################################################

function help() {

  echo "$0  [OPTIONS]"
  echo
  echo "This script will create a new database dump, containing only schema definition, used by the first migration."
  echo "It assumes that the dev-rootdb-db-api container is running."
  echo
  echo "Args:"
  echo
  echo "Options:"
  echo -e "\t-h - display this help and quit."
  echo
  exit 0
}

while getopts h option; do
  case "${option}" in
  h) help ;;
  *) echo "Unrecognized option." ;;
  esac
done

# $1 : command to execute inside dev-rootdb-api container.
# $2 .. $10 : command args.
function dockerExec() {

  docker exec -u rootdb -it dev-rootdb-api ${1} ${2} ${3} ${4} ${5} ${6} ${7} ${8} ${9} ${10}
}

# $1 APP_ENV value
function setAppEnv() {

  dockerExec sed -i "s/APP_ENV=.*/APP_ENV=${1}/" .env
}

declare rootdb_db_api_ip=172.20.0.50
declare db_user=root
declare db_password=thee1uuWiechieneiyieZ0aif3aefe
declare prod_seeder_init_dump_directory="${SCRIPT_PATH}/../storage/app/seeders/production/"
declare prod_seeder_init_dump_file="seeder_init.sql"
declare dev_seeder_init_dump_directory="${SCRIPT_PATH}/../storage/app/seeders/local/"
declare dev_seeder_init_dump_file="seeder_init.sql"

logInfo "-----------------------------------------"
logInfo "Handling \"production\" public release..."
logInfo "-----------------------------------------"
logInfo
logInfo "Setup \"production\" in .env file..."
setAppEnv "production"

logInfo "Wipe database...\" in .env file"
dockerExec php artisan db:wipe -n --force

logInfo "Seed database..."
mysql -u "${db_user}" -p"${db_password}" -h "${rootdb_db_api_ip}" "rootdb-api" <"${prod_seeder_init_dump_directory}${prod_seeder_init_dump_file}"

logInfo "Execute migrations..."
dockerExec php artisan migrate -n --force

logInfo "Dumping schema database into \"${prod_seeder_init_dump_directory}${prod_seeder_init_dump_file}\" file..."
mysqldump -u "${db_user}" -p"${db_password}" -h "${rootdb_db_api_ip}" "rootdb-api" --ignore-table=rootdb-api.telescope_entries --ignore-table=rootdb-api.telescope_entries_tags --ignore-table=rootdb-api.telescope_monitoring >"${prod_seeder_init_dump_directory}${prod_seeder_init_dump_file}"

logInfo
logInfo
logInfo "-----------------------------------------"
logInfo "Handling \"local\" release..."
logInfo "-----------------------------------------"
logInfo
logInfo "Setup \"local\" in .env file..."
setAppEnv "local"

logInfo "Wipe database...\" in .env file"
dockerExec php artisan db:wipe -n --force

logInfo "Seed database..."
mysql -u "${db_user}" -p"${db_password}" -h "${rootdb_db_api_ip}" "rootdb-api" <"${dev_seeder_init_dump_directory}${dev_seeder_init_dump_file}"

logInfo "Execute migrations..."
dockerExec php artisan migrate -n --force

logInfo "Dumping schema database into \"${dev_seeder_init_dump_directory}${dev_seeder_init_dump_file}\" file..."
mysql -u "${db_user}" -p"${db_password}" -h "${rootdb_db_api_ip}" "rootdb-api" -e "SET SESSION foreign_key_checks=OFF; TRUNCATE telescope_entries; SET SESSION foreign_key_checks=ON; "
mysql -u "${db_user}" -p"${db_password}" -h "${rootdb_db_api_ip}" "rootdb-api" -e "SET SESSION foreign_key_checks=OFF; TRUNCATE telescope_entries_tags; SET SESSION foreign_key_checks=ON; "
mysql -u "${db_user}" -p"${db_password}" -h "${rootdb_db_api_ip}" "rootdb-api" -e "SET SESSION foreign_key_checks=OFF; TRUNCATE telescope_monitoring; SET SESSION foreign_key_checks=ON; "
mysql -u "${db_user}" -p"${db_password}" -h "${rootdb_db_api_ip}" "rootdb-api" -e "SET SESSION foreign_key_checks=OFF; TRUNCATE websockets_statistics_entries; SET SESSION foreign_key_checks=ON; "
mysqldump -u "${db_user}" -p"${db_password}" -h "${rootdb_db_api_ip}" "rootdb-api" >"${dev_seeder_init_dump_directory}${dev_seeder_init_dump_file}"

logInfo
logInfo
logInfo "------------------------------------------"
logInfo "Git - add, commit, push both seed files..."
logInfo "------------------------------------------"
git add -f "${dev_seeder_init_dump_directory}${dev_seeder_init_dump_file}" -f "${prod_seeder_init_dump_directory}${prod_seeder_init_dump_file}" && git commit -m "[auto] update production/${prod_seeder_init_dump_file} & local/${dev_seeder_init_dump_file} "
git pull && git push origin master
