<?php
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo '{"error":"method_not_allowed"}';
    return;
}

if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) !== '/api/v1/orders') {
    http_response_code(404);
    echo '{"error":"not_found"}';
    return;
}

readfile(__DIR__ . '/orders.json');
