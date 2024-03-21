#!/usr/bin/env bash

declare current_advancement
declare current_step=0
declare num_steps=11
declare running_in_docker=1

declare log_file="/var/www/.update_logs"
declare -i php_built_in_web_server_pid
declare host_ip
declare res_grep
declare website_update=0

declare www_dir=$(readlink -m "${SCRIPT_PATH}/../../../..")
declare remote_builds="https://builds.rootdb.fr"

declare root_api_env="${www_dir}/.api_env"
declare root_frontend_app_config_js="${www_dir}/.app-config.js"
declare rdb_archives_dir="${www_dir}/archives"
declare rdb_to_update # true/false
declare rdb_upgraded_to="${www_dir}/.rdb_upgraded_to"
declare rdb_current_version     # x.y.z
declare rdb_version_to          # x.y.z
declare to_rdb_archive_name     # /rootdb-api-x.y.z.tar.bz2
declare to_rdb_archive_pathname # /var/www/api/archives/
declare to_rdb_archive_dir      # /var/www/api/archives/x.y.z
declare to_rdb_remote_build_archive
declare from_api_archive_dir # /var/www/api/archives/x.y.z

declare api_dir="${www_dir}/api"
declare api_env="${api_dir}/.env"
declare api_frontend_themes_dir="${api_dir}/frontend-themes"

declare frontend_dir="${www_dir}/frontend"
declare frontend_app_config_js="${frontend_dir}/app-config.js"

declare api_db_host
declare api_db_port
declare api_db_name
declare api_db_username
declare api_db_password
declare api_db_backup_filename
declare api_db_backup_pathname
declare api_db_backup_size

# @param $1 step title.
function newStep() {

  current_advancement=$(incrementCurrentStepAndGetPercentage)
  current_step=$?
  echo "step|${current_advancement}|$1" >>"${log_file}" 2>&1
  echo "${current_step}| $1"
}

# @param $1 info message.
function logInfo() {

  echo "${current_step}| [info] $1"
}

# @param $1 error message.
function logError() {

  echo "${current_step}| [error] $1"
}

# @param $1 error message.
function logErrorAndExit() {

  echo "${current_step}| [error] error $1"
  echo "step|100|error"
  exit 1
}

function waitAndKillPHPBuiltInWebServerAndExit() {

  echo "step|100|error"
  if [[ ${update_from_webapp} == 1 ]]; then
    sleep 5
    kill -9 "${php_built_in_web_server_pid}"
  fi
  exit 0
}

function incrementCurrentStepAndGetPercentage() {

  current_step=$((current_step + 1))
  declare current_percentage=$((current_step * 100 / num_steps))

  echo $current_percentage
  return $current_step
}

function cleanupArchives() {

  rm -f "${to_rdb_archive_pathname}"
  rm -f "${to_frontend_archive_pathname}"
}

function rollBack() {

  rm -f "${api_dir}/latest"
  ln -s "${from_api_archive_dir}" "${api_dir}/latest"

  rm -f "${frontend_dir}/latest"
  ln -s "${from_frontend_archive_dir}" "${frontend_dir}/latest"
}

#############################################################
#               Files & directories
#############################################################
# @param $1 directory path.
function directoryExistsOrExit() {

  if [[ ! -d ${1} ]]; then
    logError "directory \"${1}\" does not exist."
    exit 1
  fi
}

# @param $1 file path.
function fileExistsOrExit() {

  if [[ ! -f ${1} ]]; then
    logError "file \"${1}\" does not exist."
    exit 1
  fi

}
