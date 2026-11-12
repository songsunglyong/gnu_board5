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
    // 기존 설치의 본문도 이름을 바꾸며, URL에 사용하는 JSON 키는 보존한다.
    $names = [
        '아람' => '우람',
        'ARAM BOOKS' => 'URAM BOOKS',
        '문어빵' => '오징어빵',
        '세계 창작' => '우주창작',
        '세계창작' => '우주창작',
        '수학특공대' => '산수특공대',
        '지식' => '지혜',
        '과학특공대' => '물리특공대',
        '호호' => '하하',
        '코코' => '쿠쿠',
        '키즈올' => '차일드올',
        'ORT' => 'ENGLISH READ TIME',
        '베이비올' => '아기올',
        '경제특공대' => '이코노믹 스페셜팀',
        'EYE 그림책 심쿵' => '감정충만한 그림책',
        '참 똑똑한 사회씨' => '스마트한 사회',
        '세계 다글리' => '우주여행',
        '인물 세미나' => '인물사전',
        '그리스 로마 신화' => '신화이야기',
        '아우라 한국사' => '알송달송 한국사',
        '원리 과학 도깨비' => '과학 이세계',
        '명작문학' => '우주 문학',
        '소전집' => '작은 책 모음',
        '교구' => '교육용 장난감',
        '소통해요' => '교감해요 및 고객센터',
    ];
    $rows = $db->query("SELECT page_key, payload FROM `$table` FOR UPDATE");
    $update = $db->prepare("UPDATE `$table` SET payload = ? WHERE page_key = ?");
    $update->bind_param('ss', $payload, $key);
    foreach ($rows as $row) {
        $page = json_decode($row['payload'], true, 512, JSON_THROW_ON_ERROR);
        $original = $page;
        array_walk_recursive($page, static function (&$value) use ($names): void {
            if (is_string($value)) {
                $value = strtr($value, $names);
            }
        });
        if ($page !== $original) {
            $payload = json_encode($page, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $key = $row['page_key'];
            $update->execute();
        }
    }
    $db->commit();
} catch (mysqli_sql_exception | JsonException $error) {
    fwrite(STDERR, "우람북스 초기 데이터를 저장할 수 없습니다. DB 설정과 테이블을 확인하세요.\n");
    exit(1);
}

echo "우람북스 DB 설정 완료: /sample/index.php\n";
