<?php
if (PHP_VERSION_ID < 80512 || PHP_VERSION_ID >= 80600 || PHP_EXTRA_VERSION !== '') {
    fwrite(STDERR, "PHP 8.5.12 이상의 안정판 8.5.x가 필요합니다. 현재 버전: " . PHP_VERSION . "\n");
    exit(1);
}

foreach (['ctype', 'curl', 'dom', 'fileinfo', 'filter', 'gd', 'hash', 'json', 'mbstring',
          'mysqli', 'openssl', 'pcre', 'pdo', 'pdo_mysql', 'session', 'simplexml',
          'tokenizer', 'xml', 'zip'] as $extension) {
    if (!extension_loaded($extension)) {
        fwrite(STDERR, "필수 PHP 확장이 없습니다: $extension\n");
        exit(1);
    }
}
