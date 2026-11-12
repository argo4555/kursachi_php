<?php
require 'auth.php';
require 'db.php';
onlyAdmin();

// Период отчёта: по умолчанию последние 7 дней
$from = $_GET['from'] ?? date('Y-m-d', strtotime('-6 days'));
$to = $_GET['to'] ?? date('Y-m-d');
$error = '';
if (!strtotime($from) || !strtotime($to) || $from > $to) {
    $error = 'Неверно указан период: дата начала должна быть не позже даты окончания';
    $from = $to = date('Y-m-d');
}
$period = [$from, $to];

// Выручка по дням
$stmt = $pdo->prepare("SELECT DATE(date_sale) AS day, COUNT(*) AS cnt, SUM(total) AS revenue
                       FROM sales WHERE DATE(date_sale) BETWEEN ? AND ?
                       GROUP BY DATE(date_sale) ORDER BY day");
$stmt->execute($period);
$days = $stmt->fetchAll();

// Пять самых продаваемых товаров по выручке
$stmt = $pdo->prepare("SELECT p.name, p.category, SUM(i.quantity) AS qty, SUM(i.quantity * i.price) AS revenue
                       FROM sale_items i
                       JOIN sales s ON s.id_sale = i.id_sale
                       JOIN products p ON p.id_product = i.id_product
                       WHERE DATE(s.date_sale) BETWEEN ? AND ?
                       GROUP BY p.id_product, p.name, p.category
                       ORDER BY revenue DESC LIMIT 5");
$stmt->execute($period);
$top = $stmt->fetchAll();

// Выручка по способам оплаты
$stmt = $pdo->prepare("SELECT payment, COUNT(*) AS cnt, SUM(total) AS revenue
                       FROM sales WHERE DATE(date_sale) BETWEEN ? AND ?
                       GROUP BY payment ORDER BY revenue DESC");
$stmt->execute($period);
$byPayment = $stmt->fetchAll();

$count = array_sum(array_column($days, 'cnt'));
$sum = array_sum(array_column($days, 'revenue'));
$max = $days ? max(array_column($days, 'revenue')) : 0;

$title = 'Отчёт';
require 'header.php';
?>
<h3 class="mb-3">Отчёт о продажах</h3>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form class="row g-2 mb-3 align-items-end" method="get">
    <div class="col-auto">
        <label class="form-label" for="from">С</label>
        <input class="form-control" type="date" id="from" name="from" value="<?= htmlspecialchars($from) ?>">
    </div>
    <div class="col-auto">
        <label class="form-label" for="to">По</label>
        <input class="form-control" type="date" id="to" name="to" value="<?= htmlspecialchars($to) ?>">
    </div>
    <div class="col-auto"><button class="btn btn-outline-secondary">Сформировать</button></div>
</form>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="stat"><span>Выручка за период</span><b><?= money($sum) ?></b></div></div>
    <div class="col-md-4"><div class="stat"><span>Продаж</span><b><?= $count ?></b></div></div>
    <div class="col-md-4"><div class="stat"><span>Средний чек</span><b><?= money($count ? $sum / $count : 0) ?></b></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-box">
            <h5 class="mb-3">Выручка по дням</h5>
            <?php foreach ($days as $day): ?>
                <div class="bar-row">
                    <span class="bar-label"><?= date('d.m', strtotime($day['day'])) ?></span>
                    <div class="bar-track"><div class="bar" style="width: <?= round($day['revenue'] / $max * 100) ?>%"></div></div>
                    <span class="bar-value"><?= money($day['revenue']) ?> · <?= $day['cnt'] ?> прод.</span>
                </div>
            <?php endforeach; ?>
            <?php if (!$days): ?><p class="text-muted mb-0">За период продаж нет</p><?php endif; ?>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-box mb-4">
            <h5 class="mb-3">Топ-5 товаров</h5>
            <table class="table table-sm mb-0">
                <tr class="small-muted"><td>Товар</td><td class="text-end">Шт.</td><td class="text-end">Выручка</td></tr>
                <?php foreach ($top as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td class="text-end"><?= $p['qty'] ?></td>
                        <td class="text-end"><?= money($p['revenue']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="card-box">
            <h5 class="mb-3">По способам оплаты</h5>
            <table class="table table-sm mb-0">
                <?php foreach ($byPayment as $row): ?>
                    <tr>
                        <td><?= $row['payment'] ?></td>
                        <td class="text-end"><?= $row['cnt'] ?> прод.</td>
                        <td class="text-end"><?= money($row['revenue']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
<?php require 'footer.php'; ?>
