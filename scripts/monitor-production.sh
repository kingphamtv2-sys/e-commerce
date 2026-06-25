#!/usr/bin/env bash

set -Eeuo pipefail

APP_ROOT="${APP_ROOT:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
ENV_FILE="${APP_ROOT}/.env"
BACKUP_ROOT="${BACKUP_ROOT:-/var/backups/ecommerce-system}"
BACKUP_MAX_AGE_HOURS="${BACKUP_MAX_AGE_HOURS:-30}"
DISK_WARNING_PERCENT="${DISK_WARNING_PERCENT:-80}"
DISK_CRITICAL_PERCENT="${DISK_CRITICAL_PERCENT:-90}"
HEALTHCHECK_URL="${HEALTHCHECK_URL:-}"
LARAVEL_LOG_LINES="${LARAVEL_LOG_LINES:-2000}"
MONITOR_PAYMENT="${MONITOR_PAYMENT:-0}"
MONITOR_QUEUE="${MONITOR_QUEUE:-0}"
MONITOR_SCHEDULER="${MONITOR_SCHEDULER:-0}"
MYSQL_CONFIG="${MONITOR_MYSQL_CONFIG:-${BACKUP_MYSQL_CONFIG:-/etc/ecommerce-system/backup-my.cnf}}"
QUEUE_SERVICE="${QUEUE_SERVICE:-ecommerce-queue.service}"
SCHEDULER_HEARTBEAT="${SCHEDULER_HEARTBEAT:-/var/lib/ecommerce-system/scheduler.last-run}"
SCHEDULER_MAX_AGE_SECONDS="${SCHEDULER_MAX_AGE_SECONDS:-180}"
ALERT_WEBHOOK_URL="${ALERT_WEBHOOK_URL:-}"

warnings=()
criticals=()

env_value() {
    local key="$1"
    local value

    [[ -f "${ENV_FILE}" ]] || return 0
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

add_warning() {
    local existing
    for existing in "${warnings[@]}"; do
        [[ "${existing}" == "$1" ]] && return
    done
    warnings+=("$1")
}

add_critical() {
    local existing
    for existing in "${criticals[@]}"; do
        [[ "${existing}" == "$1" ]] && return
    done
    criticals+=("$1")
}

for numeric_setting in \
    "${BACKUP_MAX_AGE_HOURS}" \
    "${DISK_WARNING_PERCENT}" \
    "${DISK_CRITICAL_PERCENT}" \
    "${LARAVEL_LOG_LINES}" \
    "${SCHEDULER_MAX_AGE_SECONDS}"; do
    [[ "${numeric_setting}" =~ ^[0-9]+$ ]] || {
        printf 'ERROR: Monitoring thresholds must be non-negative integers.\n' >&2
        exit 2
    }
done

(( DISK_WARNING_PERCENT < DISK_CRITICAL_PERCENT )) || {
    printf 'ERROR: DISK_WARNING_PERCENT must be lower than DISK_CRITICAL_PERCENT.\n' >&2
    exit 2
}

mysql_count() {
    if ! MYSQL_RESULT="$(
        mysql --defaults-extra-file="${MYSQL_CONFIG}" \
            --batch --skip-column-names \
            "$(env_value DB_DATABASE)" \
            --execute "$1" 2>/dev/null
    )"; then
        add_critical "Database monitoring query failed."
        return 1
    fi

    [[ "${MYSQL_RESULT}" =~ ^[0-9]+$ ]] || {
        add_critical "Database monitoring returned an invalid count."
        return 1
    }
}

check_disk() {
    local path="$1"
    local usage

    [[ -e "${path}" ]] || return 0
    usage="$(df -P "${path}" | awk 'NR == 2 {gsub("%", "", $5); print $5}')"
    [[ "${usage}" =~ ^[0-9]+$ ]] || return 0

    if (( usage >= DISK_CRITICAL_PERCENT )); then
        add_critical "Disk usage is ${usage}% for ${path}."
    elif (( usage >= DISK_WARNING_PERCENT )); then
        add_warning "Disk usage is ${usage}% for ${path}."
    fi
}

if [[ -z "${HEALTHCHECK_URL}" ]]; then
    APP_URL="$(env_value APP_URL)"
    [[ -n "${APP_URL}" ]] && HEALTHCHECK_URL="${APP_URL%/}/health"
fi

if [[ -z "${HEALTHCHECK_URL}" ]]; then
    add_critical "HEALTHCHECK_URL and APP_URL are both missing."
elif ! curl --fail --silent --show-error --max-time 10 --output /dev/null "${HEALTHCHECK_URL}"; then
    add_critical "Application health endpoint failed."
fi

LATEST_SUCCESS="${BACKUP_ROOT}/latest/SUCCESS"
if [[ ! -f "${LATEST_SUCCESS}" ]]; then
    add_critical "No successful production backup was found."
else
    backup_age_seconds=$(( $(date +%s) - $(stat -c %Y "${LATEST_SUCCESS}") ))
    if (( backup_age_seconds > BACKUP_MAX_AGE_HOURS * 3600 )); then
        add_critical "Latest successful backup is older than ${BACKUP_MAX_AGE_HOURS} hours."
    fi
fi

check_disk "/var/www"
check_disk "${BACKUP_ROOT}"
check_disk "/var/log"

shopt -s nullglob
laravel_logs=("${APP_ROOT}"/storage/logs/laravel*.log)
if (( ${#laravel_logs[@]} > 0 )); then
    critical_log_count="$(
        tail -n "${LARAVEL_LOG_LINES}" "${laravel_logs[@]}" 2>/dev/null \
            | grep -Eci '\.(EMERGENCY|ALERT|CRITICAL|ERROR):' || true
    )"
    if (( critical_log_count > 0 )); then
        add_warning "Laravel logs contain ${critical_log_count} recent error-level entries."
    fi
fi

if [[ "${MONITOR_PAYMENT}" == "1" ]]; then
    if [[ ! -f "${MYSQL_CONFIG}" ]]; then
        add_critical "Payment monitoring MySQL config is missing."
    else
        if mysql_count "SELECT COUNT(*) FROM payment_webhook_logs WHERE signature_valid = 0 AND created_at >= UTC_TIMESTAMP() - INTERVAL 1 HOUR;"; then
            invalid_signatures="${MYSQL_RESULT}"
        fi
        if mysql_count "SELECT COUNT(*) FROM payment_webhook_logs WHERE processing_error IS NOT NULL AND created_at >= UTC_TIMESTAMP() - INTERVAL 1 HOUR;"; then
            processing_errors="${MYSQL_RESULT}"
        fi
        if mysql_count "SELECT COUNT(*) FROM payment_transactions WHERE status IN ('pending','processing') AND created_at < UTC_TIMESTAMP() - INTERVAL 1 HOUR;"; then
            stale_pending="${MYSQL_RESULT}"
        fi
        if mysql_count "SELECT COUNT(*) FROM payment_transactions pt INNER JOIN orders o ON o.id = pt.order_id WHERE pt.status = 'paid' AND o.payment_status <> 'paid';"; then
            paid_mismatch="${MYSQL_RESULT}"
        fi

        if [[ -n "${invalid_signatures:-}" && -n "${processing_errors:-}" && -n "${stale_pending:-}" && -n "${paid_mismatch:-}" ]]; then
            (( invalid_signatures > 0 )) && add_warning "${invalid_signatures} invalid payment signatures in the last hour."
            (( processing_errors > 0 )) && add_critical "${processing_errors} payment webhook processing errors in the last hour."
            (( stale_pending > 0 )) && add_warning "${stale_pending} payment transactions have been pending for over one hour."
            (( paid_mismatch > 0 )) && add_critical "${paid_mismatch} paid transactions do not match order payment status."
        fi
    fi
fi

if [[ "${MONITOR_QUEUE}" == "1" ]]; then
    if ! systemctl is-active --quiet "${QUEUE_SERVICE}"; then
        add_critical "Queue worker service is not active."
    fi

    if [[ -f "${MYSQL_CONFIG}" ]]; then
        if mysql_count "SELECT COUNT(*) FROM failed_jobs;"; then
            failed_jobs="${MYSQL_RESULT}"
        fi
        if mysql_count "SELECT COUNT(*) FROM jobs;"; then
            queued_jobs="${MYSQL_RESULT}"
        fi

        if [[ -n "${failed_jobs:-}" && -n "${queued_jobs:-}" ]]; then
            (( failed_jobs > 0 )) && add_critical "${failed_jobs} failed queue jobs require review."
            (( queued_jobs > 100 )) && add_warning "Queue backlog is ${queued_jobs} jobs."
        fi
    fi
fi

if [[ "${MONITOR_SCHEDULER}" == "1" ]]; then
    if [[ ! -f "${SCHEDULER_HEARTBEAT}" ]]; then
        add_critical "Scheduler heartbeat is missing."
    else
        scheduler_age=$(( $(date +%s) - $(stat -c %Y "${SCHEDULER_HEARTBEAT}") ))
        (( scheduler_age > SCHEDULER_MAX_AGE_SECONDS )) \
            && add_critical "Scheduler heartbeat is stale."
    fi
fi

status="ok"
exit_code=0
if (( ${#criticals[@]} > 0 )); then
    status="critical"
    exit_code=2
elif (( ${#warnings[@]} > 0 )); then
    status="warning"
    exit_code=1
fi

printf 'status=%s warnings=%d criticals=%d\n' \
    "${status}" "${#warnings[@]}" "${#criticals[@]}"
for message in "${warnings[@]}"; do
    printf 'WARNING: %s\n' "${message}"
done
for message in "${criticals[@]}"; do
    printf 'CRITICAL: %s\n' "${message}"
done

if [[ -n "${ALERT_WEBHOOK_URL}" && "${status}" != "ok" ]]; then
    alert_text="E-commerce production monitor: ${status}; warnings=${#warnings[@]}; criticals=${#criticals[@]}"
    escaped_text="${alert_text//\\/\\\\}"
    escaped_text="${escaped_text//\"/\\\"}"
    curl --fail --silent --show-error --max-time 10 \
        --header 'Content-Type: application/json' \
        --data "{\"text\":\"${escaped_text}\"}" \
        "${ALERT_WEBHOOK_URL}" >/dev/null \
        || printf 'WARNING: alert webhook delivery failed.\n' >&2
fi

exit "${exit_code}"
