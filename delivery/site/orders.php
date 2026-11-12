<?php
require 'auth.php';
require 'db.php';

$status = $_GET['status'] ?? '';

// Заказы вместе с названием блюда, суммой и курьером
$sql = "SELECT o.id_order, o.client_name, o.phone, o.address, o.quantity,
               o.date_create, o.status, d.name AS dish,
               d.price * o.quantity AS total,
               IFNULL(c.fio, 'не назначен') AS courier
        FROM orders o
        JOIN dishes d ON d.id_dish = o.id_dish
        LEFT JOIN couriers c ON c.id_courier = o.id_courier";
$params = [];
if ($status !== '') {
    $sql .= " WHERE o.status = ?";
    $params[] = $status;
}
$sql .= " ORDER BY o.date_create DESC, o.id_order DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
$sum = array_sum(array_column($orders, 'total'));

$title = 'Заказы';
require 'header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Заказы</h3>
    <a class="btn btn-primary" href="add.php">+ Новый заказ</a>
</div>

<form class="row g-2 mb-3" method="get">
    <div class="col-auto">
        <select class="form-select" name="status">
            <option value="">Все статусы</option>
            <?php foreach ($statuses as $name => $class): ?>
                <option <?= $name === $status ? 'selected' : '' ?>><?= $name ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary">Показать</button>
    </div>
</form>

<table class="table table-bordered table-hover">
    <thead class="table-light">
        <tr>
            <th>№</th><th>Клиент</th><th>Блюдо</th><th>Сумма, ₽</th>
            <th>Курьер</th><th>Создан</th><th>Статус</th><th>Действия</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $order): $id = $order['id_order']; ?>
        <tr>
            <td><?= $id ?></td>
            <td>
                <?= htmlspecialchars($order['client_name']) ?>
                <div class="small-muted"><?= htmlspecialchars($order['phone']) ?>, <?= htmlspecialchars($order['address']) ?></div>
            </td>
            <td><?= htmlspecialchars($order['dish']) ?> × <?= $order['quantity'] ?></td>
            <td><?= number_format($order['total'], 0, ',', ' ') ?></td>
            <td><?= htmlspecialchars($order['courier']) ?></td>
            <td class="text-nowrap"><?= date('d.m.Y H:i', strtotime($order['date_create'])) ?></td>
            <td class="status <?= $statuses[$order['status']] ?>"><?= $order['status'] ?></td>
            <td class="text-nowrap">
                <a class="btn btn-sm btn-outline-secondary" href="edit.php?id=<?= $id ?>">Изменить</a>
                <?php if (isAdmin()): ?>
                    <form class="d-inline" method="post" action="delete.php" onsubmit="return confirm('Удалить заказ № <?= $id ?>?')">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button class="btn btn-sm btn-outline-danger">Удалить</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$orders): ?>
        <tr><td colspan="8" class="text-center text-muted">Заказов нет</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<p><b>Заказов:</b> <?= count($orders) ?>, <b>на сумму:</b> <?= number_format($sum, 0, ',', ' ') ?> ₽</p>
<?php require 'footer.php'; ?>
