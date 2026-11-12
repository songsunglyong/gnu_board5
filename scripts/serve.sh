#!/bin/sh
set -eu
cd "$(dirname "$0")/.."

php_bin=${PHP_BIN:-php}
"$php_bin" scripts/check-php.php
if [ ! -f .runtime/gnuboard5/index.php ]; then
    echo '먼저 sh scripts/prepare.sh 를 실행하세요.' >&2
    exit 1
fi

exec "$php_bin" -S "127.0.0.1:${PORT:-8080}" \
    -d display_errors=0 -d log_errors=1 -t .runtime/gnuboard5 scripts/router.php
