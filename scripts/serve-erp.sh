#!/bin/sh
set -eu
cd "$(dirname "$0")/.."

php_bin=${PHP_BIN:-php}
"$php_bin" scripts/check-php.php
exec "$php_bin" -S 127.0.0.1:8081 -d display_errors=0 -d log_errors=1 \
    -t sample-erp sample-erp/router.php
