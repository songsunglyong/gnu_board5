<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/check-php.php';

$dbConfig = __DIR__ . '/../.runtime/gnuboard5/data/dbconfig.php';
if (!is_file($dbConfig)) {
    fwrite(STDERR, "웹 설치를 먼저 완료하세요.\n");
    exit(1);
}

define('_GNUBOARD_', true);
require $dbConfig;

if (!preg_match('/\A[a-zA-Z0-9_]+\z/', $g5['board_table'])) {
    fwrite(STDERR, "게시판 테이블 이름을 확인하세요.\n");
    exit(1);
}

try {
    $db = new mysqli(G5_MYSQL_HOST, G5_MYSQL_USER, G5_MYSQL_PASSWORD, G5_MYSQL_DB);
    $db->set_charset('utf8mb4');
    $table = $g5['board_table'];
    $board = $db->query("SELECT bo_table FROM `$table` WHERE bo_table = 'free'");
    if (!$board->num_rows) {
        fwrite(STDERR, "그누보드 기본 자유게시판(free)이 없습니다.\n");
        exit(1);
    }

    $db->query("UPDATE `$table`
                SET bo_subject = '샘플 게시판', bo_mobile_subject = '샘플 게시판',
                    bo_list_level = 1, bo_read_level = 1,
                    bo_write_level = 2, bo_reply_level = 2, bo_comment_level = 2,
                    bo_upload_count = 0, bo_use_dhtml_editor = 0
                WHERE bo_table = 'free'");
} catch (mysqli_sql_exception $error) {
    fwrite(STDERR, "샘플 게시판을 설정할 수 없습니다. DB 접속 정보와 테이블을 확인하세요.\n");
    exit(1);
}

echo "샘플 게시판 설정 완료: /bbs/board.php?bo_table=free\n";
