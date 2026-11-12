<?php
require 'auth.php';
require 'db.php';

$id = (int)($_GET['id'] ?? 0);

// Шапка чека
$stmt = $pdo->prepare('SELECT s.*, u.fio FROM sales s JOIN users u ON u.id_user = s.id_user WHERE s.id_sale = ?');
$stmt->execute([$id]);
$sale = $stmt->fetch();
if (!$sale) {
    header('Location: sales.php');
    exit;
}

// Позиции чека
$stmt = $pdo->prepare('SELECT p.name, p.volume, i.quantity, i.price, i.quantity * i.price AS sum
                       FROM sale_items i JOIN products p ON p.id_product = i.id_product
                       WHERE i.id_sale = ? ORDER BY i.id_item');
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$title = 'Чек № ' . $id;
require 'header.php';
?>
<?php if (isset($_GET['new'])): ?>
    <div class="alert alert-success">Продажа проведена</div>
<?php endif; ?>

<div class="receipt">
    <div class="text-center mb-3">
        <b>КОФЕЙНЯ «ЗЕРНО»</b><br>
        <span class="small-muted">ул. Профсоюзная, д. 12</span>
    </div>
    <div class="d-flex justify-content-between"><span>Чек № <?= $id ?></span><span><?= date('d.m.Y H:i', strtotime($sale['date_sale'])) ?></span></div>
    <div class="small-muted mb-2">Бариста: <?= htmlspecialchars($sale['fio']) ?></div>
    <table class="table table-sm mb-2">
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?><div class="small-muted"><?= $item['quantity'] ?> × <?= money($item['price']) ?></div></td>
                <td class="text-end"><?= money($item['sum']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <div class="d-flex justify-content-between fs-5"><b>ИТОГО</b><b><?= money($sale['total']) ?></b></div>
    <div class="d-flex justify-content-between"><span>Оплата</span><span><?= $sale['payment'] ?></span></div>
    <div class="text-center small-muted mt-3">Спасибо за покупку!</div>
</div>

<div class="text-center mt-3">
    <a class="btn btn-outline-secondary" href="sales.php">К списку продаж</a>
    <a class="btn btn-primary" href="sale_add.php">Новая продажа</a>
    <?php if (isAdmin()): ?>
        <form class="d-inline" method="post" action="sale_delete.php" onsubmit="return confirm('Удалить продажу № <?= $id ?>?')">
            <input type="hidden" name="id" value="<?= $id ?>">
            <button class="btn btn-outline-danger">Удалить</button>
        </form>
    <?php endif; ?>
</div>
<?php require 'footer.php'; ?>
