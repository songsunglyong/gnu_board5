<?php
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$path = preg_replace('~/+~', '/', $path);
$installed = is_file($_SERVER['DOCUMENT_ROOT'] . '/data/dbconfig.php');

if (preg_match('~(?:^|/)\.[^/]*|[\\\\\x00]~', $path)
    || preg_match('~^/(?:tools|tests|migrations)(?:/|$)~i', $path)
    || preg_match('~^/data(?:/|$)~i', $path)
    || ($installed && preg_match('~^/install(?:/|$)~i', $path))) {
    http_response_code(404);
    return true;
}

if ($path === '/') {
    header('Location: ' . ($installed ? '/sample/index.php' : '/install/'));
    return true;
}

return false;
