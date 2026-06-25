#!/usr/bin/env bash

set -Eeuo pipefail

APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${APP_ROOT}/.env"

fail() {
    printf 'ERROR: %s\n' "$1" >&2
    exit 1
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

cd "${APP_ROOT}"

[[ "${CONFIRM_PRODUCTION_DEPLOY:-}" == "YES" ]] \
    || fail "Set CONFIRM_PRODUCTION_DEPLOY=YES to confirm this production deployment."
[[ -f "${ENV_FILE}" ]] || fail ".env is missing."
[[ "$(env_value APP_ENV)" == "production" ]] || fail "APP_ENV must be production."
[[ "$(env_value APP_DEBUG)" == "false" ]] || fail "APP_DEBUG must be false."
[[ "$(env_value APP_URL)" =~ ^https://[^/:[:space:]]+\.[^/:[:space:]]+(:[0-9]+)?/?$ ]] \
    || fail "APP_URL must be an HTTPS domain without a path."
[[ -n "$(env_value APP_KEY)" ]] || fail "APP_KEY must be configured."
[[ -n "$(env_value DB_USERNAME)" && "$(env_value DB_USERNAME)" != "root" ]] \
    || fail "DB_USERNAME must be a non-root application user."
[[ -n "$(env_value DB_PASSWORD)" ]] || fail "DB_PASSWORD must be configured."
[[ "$(env_value SESSION_SECURE_COOKIE)" == "true" ]] \
    || fail "SESSION_SECURE_COOKIE must be true."

if git ls-files --error-unmatch .env >/dev/null 2>&1; then
    fail ".env is tracked by Git."
fi

for command in php composer npm; do
    command -v "${command}" >/dev/null 2>&1 || fail "Required command not found: ${command}"
done

printf '%s\n' 'Installing production Composer dependencies...'
composer install --no-dev --optimize-autoloader --no-interaction

printf '%s\n' 'Installing and building frontend assets...'
if [[ -f package-lock.json ]]; then
    npm ci
else
    npm install
fi
npm run build
[[ -f public/build/manifest.json ]] || fail "Vite manifest was not generated."

printf '%s\n' 'Clearing stale Laravel caches...'
php artisan optimize:clear

printf '%s\n' 'Running production migrations...'
php artisan migrate --force

printf '%s\n' 'Creating the public storage link...'
if [[ -L public/storage ]]; then
    [[ "$(readlink -f public/storage)" == "$(realpath storage/app/public)" ]] \
        || fail "public/storage points to an unexpected target."
elif [[ -e public/storage ]]; then
    fail "public/storage exists but is not a symbolic link."
else
    php artisan storage:link
fi

if [[ -n "${WEB_GROUP:-}" ]]; then
    chgrp -R "${WEB_GROUP}" storage bootstrap/cache
fi

find storage bootstrap/cache -type d -exec chmod 775 {} +
find storage bootstrap/cache -type f -exec chmod 664 {} +

printf '%s\n' 'Building Laravel production caches...'
php artisan config:cache
php artisan route:cache
php artisan view:cache

if [[ "${RESTART_QUEUE_WORKERS:-0}" == "1" ]]; then
    php artisan queue:restart
fi

printf '%s\n' 'Production deployment commands completed.'
printf '%s\n' 'Run the post-deployment smoke-test checklist in docs/production-deployment.md.'
