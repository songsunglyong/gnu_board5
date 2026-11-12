<?php
$kind = $item['kind'];
$children = $item['children'];
$body = $item['body'];
$detailUrl = static fn(string $key): string => $escape($url($groupKey, $itemKey, $key));
$indexUrl = $escape($url($groupKey, $itemKey));

if ($kind === 'book') {
    $activeTab = $detailKey ?? 'intro';
    $paragraphs = $body[$activeTab];
    ?>
    <div class="book-summary">
        <div class="book-illustration tone-<?= $escape($groupKey) ?>" aria-hidden="true">
            <div class="mini-cover"><span>ARAM BOOKS</span><strong><?= $escape($item['title']) ?></strong><b>✦</b></div>
        </div>
        <div>
            <p class="eyebrow"><?= $escape($group['title']) ?></p>
            <h2><?= $escape($item['topic']) ?> 그림책</h2>
            <p><?= $escape($group['age']) ?> 독자를 위한 전집 탐색 예시입니다.</p>
            <div class="actions">
                <a class="button" href="<?= $escape($url('community', 'consult')) ?>">제품 상담 신청</a>
                <a class="button secondary" href="<?= $escape($url('about', 'stores')) ?>">정품 판매처</a>
            </div>
        </div>
    </div>
    <nav class="tabs" aria-label="도서 상세 메뉴">
        <?php foreach ($children as $key => $label) { ?>
            <a href="<?= $detailUrl($key) ?>#details"<?= $key === $activeTab ? ' aria-current="page"' : '' ?>><?= $escape($label) ?></a>
        <?php } ?>
    </nav>
    <article class="panel" id="details">
        <p class="eyebrow">SAMPLE CONTENT</p>
        <h2><?= $escape($children[$activeTab]) ?></h2>
        <?php if ($activeTab === 'activities') { ?>
            <ol><?php foreach ($paragraphs as $paragraph) { ?><li><?= $escape($paragraph) ?></li><?php } ?></ol>
        <?php } elseif ($activeTab === 'preview') { ?>
            <div class="sample-spread"><?php foreach ($paragraphs as $paragraph) { ?><p><?= $escape($paragraph) ?></p><?php } ?></div>
        <?php } else { ?>
            <?php foreach ($paragraphs as $paragraph) { ?><p><?= $escape($paragraph) ?></p><?php } ?>
        <?php } ?>
    </article>
<?php } elseif ($kind === 'collection' || $kind === 'articles') { ?>
    <?php if ($detail) { ?>
        <article class="panel">
            <p class="eyebrow"><?= $kind === 'collection' ? 'COLLECTION' : 'SAMPLE POST' ?></p>
            <h2><?= $escape($detail) ?></h2>
            <p><?= $escape($body[$detailKey]) ?></p>
            <a href="<?= $indexUrl ?>">목록으로 돌아가기</a>
        </article>
    <?php } elseif ($kind === 'collection') { ?>
        <ul class="card-grid">
            <?php foreach ($children as $key => $label) { ?>
                <li><a class="card tone-sets" href="<?= $detailUrl($key) ?>"><small><?= $escape($title) ?></small><strong><?= $escape($label) ?></strong><span>상세 보기 →</span></a></li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <ul class="article-list">
            <?php foreach ($children as $key => $label) { ?>
                <li><a href="<?= $detailUrl($key) ?>"><span><?= $escape($label) ?></span><span aria-hidden="true">→</span></a></li>
            <?php } ?>
        </ul>
    <?php } ?>
<?php } elseif ($kind === 'faq') {
    ?>
    <nav class="tabs" aria-label="FAQ 분류">
        <a href="<?= $indexUrl ?>"<?= !$detail ? ' aria-current="page"' : '' ?>>전체</a>
        <?php foreach ($children as $key => $label) { ?>
            <a href="<?= $detailUrl($key) ?>"<?= $key === $detailKey ? ' aria-current="page"' : '' ?>><?= $escape($label) ?></a>
        <?php } ?>
    </nav>
    <div class="article-list">
        <?php foreach ($detail ? [$detailKey => $body[$detailKey]] : $body as $key => [$question, $answer]) { ?>
            <details><summary><small><?= $escape($children[$key]) ?></small> <?= $escape($question) ?></summary><p><?= $escape($answer) ?></p></details>
        <?php } ?>
    </div>
<?php } elseif ($kind === 'company' || $kind === 'stores') { ?>
    <nav class="tabs" aria-label="<?= $escape($title) ?> 메뉴">
        <?php if ($kind === 'stores') { ?><a href="<?= $indexUrl ?>"<?= !$detail ? ' aria-current="page"' : '' ?>>전체</a><?php } ?>
        <?php foreach ($children as $key => $label) { ?>
            <a href="<?= $detailUrl($key) ?>"<?= $key === ($detailKey ?? ($kind === 'company' ? 'intro' : null)) ? ' aria-current="page"' : '' ?>><?= $escape($label) ?></a>
        <?php } ?>
    </nav>
    <article class="panel">
        <?php if ($kind === 'stores') { ?>
            <h2><?= $escape($detail ?? '전체') ?> 판매처</h2>
            <?php if (!$detail || isset($body[$detailKey])) { ?>
                <ul><?php foreach ($detail ? [$body[$detailKey]] : $body as $region) { ?><li><?= $escape($region) ?></li><?php } ?></ul>
            <?php } else { ?><p>이 지역에 등록된 샘플 판매처가 없습니다.</p><?php } ?>
            <p>지역별 탐색을 위한 가상 판매처입니다.</p>
        <?php } elseif ($detailKey === 'history') { ?>
            <h2>아람의 발자취</h2>
            <ul><?php foreach ($body['history'] as $event) { ?><li><?= $escape($event) ?></li><?php } ?></ul>
        <?php } else { ?>
            <h2>아람북스</h2>
            <p><?= $escape($body['intro']) ?></p>
        <?php } ?>
    </article>
<?php } else { ?>
    <article class="panel">
        <h2><?= $escape($body[0]) ?></h2>
        <p><?= $escape($body[1]) ?></p>
        <a class="button" href="<?= $escape($itemKey === 'consult' ? $url('about', 'stores') : $url('community', 'faq')) ?>"><?= $itemKey === 'consult' ? '정품 판매처' : 'FAQ' ?> →</a>
    </article>
<?php } ?>
