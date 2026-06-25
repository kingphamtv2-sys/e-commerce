#!/usr/bin/env bash

set -Eeuo pipefail

BACKUP_DIR="${1:-${BACKUP_ROOT:-/var/backups/ecommerce-system}/latest}"

fail() {
    printf 'ERROR: %s\n' "$1" >&2
    exit 1
}

[[ -d "${BACKUP_DIR}" ]] || fail "Backup directory not found: ${BACKUP_DIR}"
BACKUP_DIR="$(realpath "${BACKUP_DIR}")"

[[ -f "${BACKUP_DIR}/SUCCESS" ]] || fail "SUCCESS marker is missing."
[[ -s "${BACKUP_DIR}/database.sql.gz" ]] || fail "Database archive is missing or empty."
[[ -s "${BACKUP_DIR}/storage-public.tar.gz" ]] || fail "Storage archive is missing or empty."
[[ -s "${BACKUP_DIR}/METADATA" ]] || fail "Backup metadata is missing."
[[ -s "${BACKUP_DIR}/SHA256SUMS" ]] || fail "Checksum manifest is missing."

(
    cd "${BACKUP_DIR}"
    sha256sum --check SHA256SUMS
)

gzip -t "${BACKUP_DIR}/database.sql.gz"
tar -tzf "${BACKUP_DIR}/storage-public.tar.gz" >/dev/null

if grep -q '^env_encrypted=1$' "${BACKUP_DIR}/METADATA"; then
    [[ -s "${BACKUP_DIR}/production-env.age" ]] || fail "Encrypted .env archive is missing."
fi

printf 'Backup verification passed: %s\n' "${BACKUP_DIR}"

