<?php
require __DIR__ . '/../.runtime/gnuboard5/common.php';

if (!$is_member) {
    goto_url(G5_BBS_URL . '/login.php?url=' . rawurlencode('/sample/orders.php'));
}

header('Cache-Control: no-store');

function order_html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function load_erp_orders(array $statuses): array
{
    $context = stream_context_create([
        'http' => ['timeout' => 2, 'follow_location' => 0],
    ]);
    $response = @file_get_contents('http://127.0.0.1:8081/api/v1/orders', false, $context, 0, 65537);
    if ($response === false || strlen($response) > 65536) {
        throw new RuntimeException('ERP 응답을 읽을 수 없습니다.');
    }

    $payload = json_decode($response, true, 16, JSON_THROW_ON_ERROR);
    $orders = $payload['orders'] ?? null;
    if (!is_array($orders) || !array_is_list($orders)) {
        throw new RuntimeException('ERP 주문 목록 형식이 올바르지 않습니다.');
    }

    $orderNumbers = [];
    foreach ($orders as $order) {
        foreach (['orderNo', 'orderedOn', 'sku', 'product', 'dealerCode', 'dealer', 'status'] as $field) {
            if (!is_string($order[$field] ?? null) || $order[$field] === '') {
                throw new RuntimeException('ERP 주문 항목 형식이 올바르지 않습니다.');
            }
        }

        if (!isset($statuses[$order['status']]) || isset($orderNumbers[$order['orderNo']])
            || !is_int($order['quantity'] ?? null) || $order['quantity'] < 1 || $order['quantity'] > 1000000
            || !is_int($order['unitPrice'] ?? null) || $order['unitPrice'] < 0 || $order['unitPrice'] > 1000000000) {
            throw new RuntimeException('ERP 주문 항목 값이 올바르지 않습니다.');
        }

        $orderNumbers[$order['orderNo']] = true;
    }

    return $orders;
}

$statuses = ['confirmed' => '주문 확정', 'shipped' => '배송 완료', 'cancelled' => '취소'];
$dealerFilter = is_string($_GET['dealer'] ?? null) ? $_GET['dealer'] : '';
$statusFilter = is_string($_GET['status'] ?? null) ? $_GET['status'] : '';
$error = false;
$dealers = $visibleOrders = [];
$total = 0;

try {
    $orders = load_erp_orders($statuses);
    foreach ($orders as $order) {
        $dealers[$order['dealerCode']] = $order['dealer'];
    }
    if (!isset($dealers[$dealerFilter])) {
        $dealerFilter = '';
    }
    if (!isset($statuses[$statusFilter])) {
        $statusFilter = '';
    }

    foreach ($orders as $order) {
        if (($dealerFilter !== '' && $dealerFilter !== $order['dealerCode'])
            || ($statusFilter !== '' && $statusFilter !== $order['status'])) {
            continue;
        }
        $order['amount'] = $order['quantity'] * $order['unitPrice'];
        $visibleOrders[] = $order;
        if ($order['status'] !== 'cancelled') {
            $total += $order['amount'];
        }
    }
} catch (RuntimeException | JsonException $exception) {
    http_response_code(502);
    $error = true;
}
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ERP 주문 조회 | 그누보드5 샘플</title>
    <style>
        body { margin: 2rem auto; padding: 0 1rem; max-width: 66rem; font: 1rem/1.6 system-ui, sans-serif; color: #172638; }
        a { color: #17489e; }
        form { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; }
        select, button { font: inherit; padding: .3rem .5rem; }
        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        th, td { padding: .55rem .7rem; border-bottom: 1px solid #d9e2eb; text-align: left; }
        th { background: #edf3fa; }
        .number { text-align: right; }
    </style>
</head>
<body>
<main>
    <p><a href="/sample/index.php">샘플 홈</a></p>
    <h1>ERP 주문 조회</h1>
    <p>로컬 모의 ERP에서 가져온 주문 데이터입니다.</p>
    <?php if ($error) { ?>
        <p role="alert">ERP 주문을 불러오지 못했습니다. 모의 ERP가 실행 중인지 확인한 뒤 다시 조회하세요.</p>
    <?php } else { ?>
        <form method="get">
            <label>대리점
                <select name="dealer">
                    <option value="">전체</option>
                    <?php foreach ($dealers as $code => $name) { ?>
                        <option value="<?= order_html((string) $code) ?>"<?= $dealerFilter === (string) $code ? ' selected' : '' ?>><?= order_html($name) ?></option>
                    <?php } ?>
                </select>
            </label>
            <label>상태
                <select name="status">
                    <option value="">전체</option>
                    <?php foreach ($statuses as $code => $label) { ?>
                        <option value="<?= order_html($code) ?>"<?= $statusFilter === $code ? ' selected' : '' ?>><?= order_html($label) ?></option>
                    <?php } ?>
                </select>
            </label>
            <button type="submit">조회</button>
        </form>
        <p>조회 주문 <strong><?= count($visibleOrders) ?>건</strong> · 주문금액 합계(취소 제외) <strong><?= number_format($total) ?>원</strong></p>
        <?php if (!$visibleOrders) { ?>
            <p>조회 조건에 해당하는 주문이 없습니다.</p>
        <?php } else { ?>
            <div class="table-scroll" tabindex="0" role="region" aria-label="ERP 주문 목록">
                <table>
                    <thead><tr>
                        <th scope="col">주문번호</th><th scope="col">주문일</th>
                        <th scope="col">상품</th><th scope="col">대리점</th><th scope="col">상태</th>
                        <th scope="col" class="number">수량</th><th scope="col" class="number">주문금액</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($visibleOrders as $order) { ?>
                        <tr>
                            <td><?= order_html($order['orderNo']) ?></td>
                            <td><?= order_html($order['orderedOn']) ?></td>
                            <td><?= order_html($order['product']) ?> (<?= order_html($order['sku']) ?>)</td>
                            <td><?= order_html($order['dealer']) ?></td>
                            <td><?= order_html($statuses[$order['status']]) ?></td>
                            <td class="number"><?= number_format($order['quantity']) ?></td>
                            <td class="number"><?= number_format($order['amount']) ?>원</td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    <?php } ?>
</main>
</body>
</html>
