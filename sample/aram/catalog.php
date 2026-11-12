<?php
if (!defined('_GNUBOARD_')) {
    http_response_code(404);
    exit;
}

$table = G5_TABLE_PREFIX . 'aram_content';
if (!preg_match('/\A[a-zA-Z0-9_]+\z/', $table)) {
    throw new RuntimeException('Invalid catalog table');
}

$result = sql_query("SELECT page_key, payload FROM `$table` ORDER BY sort_order, page_key", false);
if (!$result) {
    throw new RuntimeException('Catalog query failed');
}

$pages = [];
while ($row = sql_fetch_array($result)) {
    $pages[$row['page_key']] = json_decode($row['payload'], true, 512, JSON_THROW_ON_ERROR);
}
if (empty($pages['_home'])) {
    throw new RuntimeException('Catalog seed is missing');
}

return $pages;
