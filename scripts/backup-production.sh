#!/usr/bin/env bash

set -Eeuo pipefail
umask 077

APP_ROOT="${APP_ROOT:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
ENV_FILE="${APP_ROOT}/.env"
BACKUP_ROOT="${BACKUP_ROOT:-/var/backups/ecommerce-system}"
MYSQL_CONFIG="${BACKUP_MYSQL_CONFIG:-/etc/ecommerce-system/backup-my.cnf}"
RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-14}"
BACKUP_ENV="${BACKUP_ENV:-1}"
ENV_RECIPIENTS_FILE="${BACKUP_ENV_RECIPIENTS_FILE:-/etc/ecommerce-system/backup-age-recipients.txt}"
TIMESTAMP="$(date -u +%Y%m%dT%H%M%SZ)"
HOSTNAME_SHORT="$(hostname -s 2>/dev/null || hostname)"
INCOMPLETE_DIR=""

log() {
    printf '%s [%s] %s\n' "$(date -u +%Y-%m-%dT%H:%M:%SZ)" "$1" "$2"
}

fail() {
    log ERROR "$1" >&2
    exit 1
}

require_command() {
    command -v "$1" >/dev/null 2>&1 || fail "Required command not found: $1"
}

env_value() {
    local key="$1"
    local value

    value="$(awk -F= -v key="$key" '
        $0 !~ /^[[:space:]]*#/ && $1 == key {
            sub(/^[^=]*=/, "")
            print
            exit
        }
    ' "${ENV_FILE}")"
    value="${value%$'\r'}"

    if [[ "${value}" == \"*\" && "${value}" == *\" ]]; then
        value="${value:1:${#value}-2}"
    elif [[ "${value}" == \'*\' && "${value}" == *\' ]]; then
        value="${value:1:${#value}-2}"
    fi

    printf '%s' "${value}"
}

cleanup() {
    local status=$?

    if [[ ${status} -ne 0 && -n "${INCOMPLETE_DIR}" && -d "${INCOMPLETE_DIR}" ]]; then
        rm -rf -- "${INCOMPLETE_DIR}"
        log ERROR "Backup failed; incomplete files removed."
    fi
}

trap cleanup EXIT

[[ -f "${ENV_FILE}" ]] || fail "Production .env not found at ${ENV_FILE}."
[[ "$(env_value APP_ENV)" == "production" ]] || fail "APP_ENV must be production."
[[ "$(env_value APP_DEBUG)" == "false" ]] || fail "APP_DEBUG must be false."
[[ "${BACKUP_ROOT}" == /* ]] || fail "BACKUP_ROOT must be an absolute path."
[[ "${RETENTION_DAYS}" =~ ^[0-9]+$ ]] || fail "BACKUP_RETENTION_DAYS must be a non-negative integer."

require_command flock
require_command gzip
require_command mysqldump
require_command realpath
require_command sha256sum
require_command tar

mkdir -p -- "${BACKUP_ROOT}"
chmod 700 "${BACKUP_ROOT}"

BACKUP_ROOT_REAL="$(realpath "${BACKUP_ROOT}")"
PUBLIC_ROOT_REAL="$(realpath "${APP_ROOT}/public")"
case "${BACKUP_ROOT_REAL}/" in
    "${PUBLIC_ROOT_REAL}/"*) fail "Backup root must not be inside the public web root." ;;
esac

[[ -f "${MYSQL_CONFIG}" ]] || fail "MySQL client config not found: ${MYSQL_CONFIG}."
MYSQL_CONFIG_MODE="$(stat -c '%a' "${MYSQL_CONFIG}")"
(( (8#${MYSQL_CONFIG_MODE} & 8#077) == 0 )) \
    || fail "MySQL client config must not be readable by group or others."

DATABASE_NAME="${BACKUP_DB_NAME:-$(env_value DB_DATABASE)}"
[[ -n "${DATABASE_NAME}" ]] || fail "Database name is not configured."

if [[ "${BACKUP_ENV}" == "1" ]]; then
    require_command age
    [[ -s "${ENV_RECIPIENTS_FILE}" ]] || fail "Age recipients file is missing or empty."
elif [[ "${BACKUP_ENV}" != "0" ]]; then
    fail "BACKUP_ENV must be 0 or 1."
fi

exec 9>"${BACKUP_ROOT}/.backup.lock"
flock -n 9 || fail "Another backup process is already running."

INCOMPLETE_DIR="${BACKUP_ROOT}/.incomplete-${TIMESTAMP}-$$"
FINAL_DIR="${BACKUP_ROOT}/backup-${TIMESTAMP}"
mkdir -p "${INCOMPLETE_DIR}"

log INFO "Starting production backup."

mysqldump \
    --defaults-extra-file="${MYSQL_CONFIG}" \
    --single-transaction \
    --quick \
    --no-tablespaces \
    --triggers \
    --hex-blob \
    "${DATABASE_NAME}" \
    | gzip -9 > "${INCOMPLETE_DIR}/database.sql.gz"

[[ -s "${INCOMPLETE_DIR}/database.sql.gz" ]] || fail "Database backup is empty."
gzip -t "${INCOMPLETE_DIR}/database.sql.gz"

tar -C "${APP_ROOT}" -czf "${INCOMPLETE_DIR}/storage-public.tar.gz" storage/app/public
[[ -s "${INCOMPLETE_DIR}/storage-public.tar.gz" ]] || fail "Uploaded-files backup is empty."
tar -tzf "${INCOMPLETE_DIR}/storage-public.tar.gz" >/dev/null

if [[ "${BACKUP_ENV}" == "1" ]]; then
    age --encrypt \
        --recipients-file "${ENV_RECIPIENTS_FILE}" \
        --output "${INCOMPLETE_DIR}/production-env.age" \
        "${ENV_FILE}"
    [[ -s "${INCOMPLETE_DIR}/production-env.age" ]] || fail "Encrypted .env backup is empty."
fi

{
    printf 'created_at_utc=%s\n' "${TIMESTAMP}"
    printf 'host=%s\n' "${HOSTNAME_SHORT}"
    printf 'database=%s\n' "${DATABASE_NAME}"
    printf 'git_commit=%s\n' "$(git -C "${APP_ROOT}" rev-parse --verify HEAD 2>/dev/null || printf 'unknown')"
    printf 'env_encrypted=%s\n' "${BACKUP_ENV}"
} > "${INCOMPLETE_DIR}/METADATA"

(
    cd "${INCOMPLETE_DIR}"
    checksum_files=(database.sql.gz storage-public.tar.gz METADATA)
    [[ -f production-env.age ]] && checksum_files+=(production-env.age)
    sha256sum "${checksum_files[@]}" > SHA256SUMS
)

touch "${INCOMPLETE_DIR}/SUCCESS"
mv -- "${INCOMPLETE_DIR}" "${FINAL_DIR}"
INCOMPLETE_DIR=""
ln -sfn "backup-${TIMESTAMP}" "${BACKUP_ROOT}/latest"

find "${BACKUP_ROOT}" \
    -mindepth 1 \
    -maxdepth 1 \
    -type d \
    -name 'backup-*' \
    -mtime "+${RETENTION_DAYS}" \
    -exec rm -rf -- {} +

log INFO "Backup completed: ${FINAL_DIR}"
