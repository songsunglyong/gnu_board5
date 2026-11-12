<?php
require __DIR__ . '/../.runtime/gnuboard5/common.php';

try {
    $menu = require __DIR__ . '/aram/catalog.php';
} catch (RuntimeException | mysqli_sql_exception | JsonException $error) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('아람북스 콘텐츠를 불러오지 못했습니다. DB 설정과 초기 데이터 설치를 확인하세요.');
}
$home = $menu['_home'];
unset($menu['_home']);
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$url = static function (string ...$keys): string {
    $params = array_combine(array_slice(['group', 'item', 'detail'], 0, count($keys)), $keys);
    return '/sample/index.php' . ($params ? '?' . http_build_query($params) : '');
};
$param = static fn(string $key): ?string => is_string($_GET[$key] ?? null) ? $_GET[$key] : null;
$groupKey = $param('group');
$itemKey = $param('item');
$detailKey = $param('detail');
$group = $menu[$groupKey] ?? null;
$item = $group['items'][$itemKey] ?? null;
$detail = $item['children'][$detailKey] ?? null;
$invalid = (isset($_GET['group']) && !$group) || (isset($_GET['item']) && !$item)
    || (isset($_GET['detail']) && !$detail);
if ($invalid) {
    http_response_code(404);
}
$title = $invalid ? '페이지를 찾을 수 없습니다' : ($item['title'] ?? $group['title'] ?? str_replace("\n", ' ', $home['title']));
$query = trim($param('q') ?? '');
$searching = !$group && isset($_GET['q']);
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($title) ?> | 아람북스 샘플</title>
    <link rel="stylesheet" href="/sample/aram/site.css">
    <script src="/sample/aram/site.js" defer></script>
</head>
<body>
<a class="skip" href="#main">본문으로 이동</a>
<header class="site-header">
    <div class="wrap header-inner">
        <a class="brand" href="<?= $url() ?>" aria-label="아람북스 샘플 홈">
            <span class="brand-mark" aria-hidden="true">✿</span> 아람북스 <small>DEMO</small>
        </a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">메뉴</button>
        <nav id="site-nav" class="site-nav" aria-label="상단 메뉴">
            <ul class="top-links">
                <?php foreach ($menu as $key => $category) { ?>
                    <li>
                        <a class="top-link<?= $key === $groupKey ? ' active' : '' ?>" href="<?= $escape($url($key)) ?>"<?= $key === $groupKey && !$item ? ' aria-current="page"' : '' ?>><?= $escape($category['title']) ?></a>
                        <button class="sub-toggle" type="button" aria-expanded="false" aria-controls="nav-<?= $escape($key) ?>" aria-label="<?= $escape($category['title']) ?> 하위 메뉴">⌄</button>
                        <ul class="dropdown" id="nav-<?= $escape($key) ?>">
                            <?php foreach ($category['items'] as $childKey => $child) { ?>
                                <li class="<?= in_array($childKey, ['consult', 'help'], true) ? 'mobile-only' : '' ?>">
                                    <a href="<?= $escape($url($key, $childKey)) ?>"<?= $key === $groupKey && $childKey === $itemKey && !$detail ? ' aria-current="page"' : '' ?>><?= $escape($child['title']) ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </nav>
        <a class="search-link" href="<?= $url() ?>?q=" aria-label="도서검색">⌕ <span>검색</span></a>
    </div>
</header>

<main id="main">
<?php if ($invalid) { ?>
    <section class="wrap section"><h1><?= $title ?></h1><p><a href="<?= $url() ?>">홈으로 돌아가기</a></p></section>
<?php } elseif ($searching) { ?>
    <section class="wrap section">
        <h1>도서검색</h1>
        <form class="search-form" action="<?= $url() ?>" method="get">
            <label for="query">전집명 또는 키워드</label>
            <div><input id="query" name="q" value="<?= $escape($query) ?>" required><button type="submit">검색</button></div>
        </form>
        <ul class="card-grid">
            <?php $count = 0; foreach ($menu as $key => $category) {
                foreach ($category['items'] as $childKey => $child) {
                    if ($child['kind'] !== 'book' || $query === '' || !str_contains(
                        mb_strtolower($child['title'] . ' ' . $child['topic'] . ' ' . $category['title']),
                        mb_strtolower($query)
                    )) {
                        continue;
                    }
                    $count++;
                    ?>
                    <li><a class="card" href="<?= $escape($url($key, $childKey)) ?>"><small><?= $escape($category['title']) ?></small><strong><?= $escape($child['title']) ?></strong></a></li>
                <?php }
            } ?>
        </ul>
        <?php if ($query !== '' && !$count) { ?><p>검색 결과가 없습니다.</p><?php } ?>
    </section>
<?php } elseif (!$group) { ?>
    <section class="hero">
        <div class="wrap hero-inner">
            <div class="hero-copy">
                <p class="eyebrow">ARAM BOOKS · PICTURE BOOKS</p>
                <h1><?= nl2br($escape($home['title']), false) ?></h1>
                <p><?= nl2br($escape($home['intro']), false) ?></p>
                <a class="button" href="#series">전집 둘러보기 <span aria-hidden="true">→</span></a>
            </div>
            <div class="hero-art" aria-hidden="true">
                <span class="spark spark-one">✦</span><span class="spark spark-two">✧</span>
                <div class="cover cover-one"><span>01</span><b>첫<br>그림책</b><i>☼</i></div>
                <div class="cover cover-two"><span>02</span><b>이야기<br>숲</b><i>✿</i></div>
                <div class="cover cover-three"><span>03</span><b>호기심<br>탐험</b><i>✦</i></div>
            </div>
        </div>
    </section>
    <section id="series" class="wrap section">
        <p class="eyebrow">BOOK COLLECTION</p>
        <h2><?= $escape($home['series_title']) ?></h2>
        <ul class="card-grid">
            <?php foreach ($menu as $key => $category) {
                if (!isset($category['age'])) { continue; } ?>
                <li><a class="card tone-<?= $escape($key) ?>" href="<?= $escape($url($key)) ?>"><small><?= $escape($category['age']) ?></small><strong><?= $escape($category['title']) ?></strong><span><?= $escape($category['tagline']) ?> →</span></a></li>
            <?php } ?>
        </ul>
    </section>
    <section class="quick">
        <div class="wrap quick-inner">
            <a href="<?= $escape($url('community', 'consult')) ?>">제품상담신청 →</a>
            <a href="<?= $escape($url('community', 'events')) ?>">이벤트 및 행사 →</a>
            <a href="<?= $escape($url('community', 'help')) ?>">고객센터 →</a>
            <a href="<?= $escape($url('about', 'stores')) ?>">정품 판매처 →</a>
        </div>
    </section>
<?php } else { ?>
    <div class="sub-hero tone-<?= $escape($groupKey) ?>"><div class="wrap"><p class="eyebrow">ARAM BOOKS</p><p class="section-name"><?= $escape($group['title']) ?></p></div></div>
    <section class="wrap section sub-section">
        <nav class="breadcrumb" aria-label="현재 위치">
            <a href="<?= $url() ?>">홈</a><span aria-hidden="true">/</span>
            <a href="<?= $escape($url($groupKey)) ?>"<?= !$item ? ' aria-current="page"' : '' ?>><?= $escape($group['title']) ?></a>
            <?php if ($item) { ?>
                <span aria-hidden="true">/</span><a href="<?= $escape($url($groupKey, $itemKey)) ?>"<?= !$detail ? ' aria-current="page"' : '' ?>><?= $escape($item['title']) ?></a>
            <?php } ?>
            <?php if ($detail) { ?><span aria-hidden="true">/</span><span aria-current="page"><?= $escape($detail) ?></span><?php } ?>
        </nav>
        <?php if ($item) { ?>
            <nav class="pills" aria-label="<?= $escape($group['title']) ?> 하위 메뉴">
                <?php foreach ($group['items'] as $key => $entry) { ?>
                    <a href="<?= $escape($url($groupKey, $key)) ?>"<?= $key === $itemKey ? ($detail ? ' class="selected"' : ' aria-current="page"') : '' ?>><?= $escape($entry['title']) ?></a>
                <?php } ?>
            </nav>
        <?php } ?>
        <h1><?= $escape($title) ?></h1>
        <?php if (!$item) { ?>
            <?php if (isset($group['tagline'])) { ?><p><?= $escape($group['tagline']) ?></p><?php } ?>
            <ul class="card-grid">
                <?php foreach ($group['items'] as $key => $entry) { ?>
                    <li><a class="card tone-<?= $escape($groupKey) ?>" href="<?= $escape($url($groupKey, $key)) ?>"><strong><?= $escape($entry['title']) ?></strong><span><?= $escape($entry['topic'] ?? '자세히 보기') ?> →</span></a></li>
                <?php } ?>
            </ul>
        <?php } else {
            require __DIR__ . '/aram/detail.php';
        } ?>
    </section>
<?php } ?>
</main>

<footer class="site-footer">
    <div class="wrap footer-inner">
        <div><strong>아람북스 샘플</strong><p>그누보드5 홈페이지 구현 데모</p></div>
        <?php if (!$is_member) { ?>
            <nav aria-label="회원 메뉴">
                <a href="<?= $escape(G5_BBS_URL . '/login.php?url=' . rawurlencode($url())) ?>">로그인</a>
                <a href="<?= $escape(G5_BBS_URL . '/register.php') ?>">회원가입</a>
            </nav>
        <?php } ?>
    </div>
</footer>
</body>
</html>
