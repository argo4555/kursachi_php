<?php
require 'auth.php';
require 'db.php';

// Отбор по дате и способу оплаты
$date = $_GET['date'] ?? '';
$payment = $_GET['payment'] ?? '';

// Продажи вместе с составом чека и ФИО бариста
$sql = "SELECT s.id_sale, s.date_sale, s.payment, s.total, u.fio AS barista,
               GROUP_CONCAT(CONCAT(p.name, ' × ', i.quantity) ORDER BY i.id_item SEPARATOR ', ') AS items
        FROM sales s
        JOIN users u ON u.id_user = s.id_user
        JOIN sale_items i ON i.id_sale = s.id_sale
        JOIN products p ON p.id_product = i.id_product";
$where = [];
$params = [];
if ($date !== '') {
    $where[] = 'DATE(s.date_sale) = ?';
    $params[] = $date;
}
if ($payment !== '') {
    $where[] = 's.payment = ?';
    $params[] = $payment;
}
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= " GROUP BY s.id_sale, s.date_sale, s.payment, s.total, u.fio
          ORDER BY s.date_sale DESC, s.id_sale DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sales = $stmt->fetchAll();

// Итоги по отобранным продажам
$count = count($sales);
$sum = array_sum(array_column($sales, 'total'));
$avg = $count ? $sum / $count : 0;

$title = 'Продажи';
require 'header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Продажи</h3>
    <a class="btn btn-primary" href="sale_add.php">+ Новая продажа</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="stat"><span>Продаж</span><b><?= $count ?></b></div></div>
    <div class="col-md-4"><div class="stat"><span>Выручка</span><b><?= money($sum) ?></b></div></div>
    <div class="col-md-4"><div class="stat"><span>Средний чек</span><b><?= money($avg) ?></b></div></div>
</div>

<form class="row g-2 mb-3 align-items-end" method="get">
    <div class="col-auto">
        <label class="form-label" for="date">Дата</label>
        <input class="form-control" type="date" id="date" name="date" value="<?= htmlspecialchars($date) ?>">
    </div>
    <div class="col-auto">
        <label class="form-label" for="payment">Оплата</label>
        <select class="form-select" id="payment" name="payment">
            <option value="">Любая</option>
            <?php foreach ($payments as $name): ?>
                <option <?= $name === $payment ? 'selected' : '' ?>><?= $name ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary">Показать</button>
        <a class="btn btn-link" href="sales.php">Сбросить</a>
    </div>
</form>

<table class="table table-hover sales-table">
    <thead>
        <tr><th>№</th><th>Дата и время</th><th>Состав чека</th><th>Оплата</th><th>Бариста</th><th class="text-end">Сумма</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($sales as $sale): $id = $sale['id_sale']; ?>
        <tr>
            <td><?= $id ?></td>
            <td class="text-nowrap"><?= date('d.m.Y H:i', strtotime($sale['date_sale'])) ?></td>
            <td><?= htmlspecialchars($sale['items']) ?></td>
            <td><span class="pay pay-<?= array_search($sale['payment'], $payments) ?>"><?= $sale['payment'] ?></span></td>
            <td><?= htmlspecialchars($sale['barista']) ?></td>
            <td class="text-end text-nowrap fw-semibold"><?= money($sale['total']) ?></td>
            <td class="text-nowrap text-end">
                <a class="btn btn-sm btn-outline-secondary" href="sale_view.php?id=<?= $id ?>">Чек</a>
                <?php if (isAdmin()): ?>
                    <form class="d-inline" method="post" action="sale_delete.php" onsubmit="return confirm('Удалить продажу № <?= $id ?>?')">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button class="btn btn-sm btn-outline-danger">Удалить</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$sales): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">Продаж не найдено</td></tr>
    <?php endif; ?>
    </tbody>
</table>
<?php require 'footer.php'; ?>
