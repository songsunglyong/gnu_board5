<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/check-php.php';

$config = __DIR__ . '/../.runtime/gnuboard5/data/dbconfig.php';
if (!is_file($config)) {
    fwrite(STDERR, "그누보드 웹 설치를 먼저 완료하세요.\n");
    exit(1);
}
define('_GNUBOARD_', true);
require $config;
$table = G5_TABLE_PREFIX . 'aram_content';
if (!preg_match('/\A[a-zA-Z0-9_]+\z/', $table)) {
    fwrite(STDERR, "그누보드 테이블 접두사를 확인하세요.\n");
    exit(1);
}

try {
    $db = new mysqli(G5_MYSQL_HOST, G5_MYSQL_USER, G5_MYSQL_PASSWORD, G5_MYSQL_DB);
    $db->set_charset('utf8mb4');
    $db->query("CREATE TABLE IF NOT EXISTS `$table` (
        page_key VARCHAR(32) NOT NULL PRIMARY KEY,
        sort_order INT NOT NULL,
        payload JSON NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $db->begin_transaction();
    $insert = $db->prepare("INSERT INTO `$table` (page_key, sort_order, payload)
        VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE page_key = VALUES(page_key)");
    $insert->bind_param('sis', $key, $order, $payload);
    $order = 0;
    foreach (require __DIR__ . '/aram-seed.php' as $key => $page) {
        $payload = json_encode($page, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $insert->execute();
        $order++;
    }
    $db->commit();
} catch (mysqli_sql_exception | JsonException $error) {
    fwrite(STDERR, "아람북스 초기 데이터를 저장할 수 없습니다. DB 설정과 테이블을 확인하세요.\n");
    exit(1);
}

echo "아람북스 DB 설정 완료: /sample/index.php\n";
