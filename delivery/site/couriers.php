<?php
require 'auth.php';
require 'db.php';
onlyAdmin();

$transports = ['Пеший', 'Велосипед', 'Автомобиль'];
$error = '';
$form = ['fio' => '', 'phone' => '', 'transport' => 'Пеший'];

// Добавление курьера
if (isset($_POST['add'])) {
    $form = [
        'fio'       => trim($_POST['fio']),
        'phone'     => trim($_POST['phone']),
        'transport' => $_POST['transport'],
    ];
    if ($form['fio'] === '' || $form['phone'] === '') {
        $error = 'Заполните ФИО и телефон курьера';
    } elseif (!in_array($form['transport'], $transports, true)) {
        $error = 'Выберите транспорт из списка';
    } else {
        $stmt = $pdo->prepare('INSERT INTO couriers (fio, phone, transport) VALUES (?, ?, ?)');
        $stmt->execute([$form['fio'], $form['phone'], $form['transport']]);
        header('Location: couriers.php');
        exit;
    }
}

// Удаление курьера, если за ним нет заказов
if (isset($_POST['delete'])) {
    $id = (int)$_POST['delete'];
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE id_courier = ?');
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $error = 'Нельзя удалить курьера: за ним закреплены заказы';
    } else {
        $pdo->prepare('DELETE FROM couriers WHERE id_courier = ?')->execute([$id]);
        header('Location: couriers.php');
        exit;
    }
}

// Курьеры и количество их заказов в работе
$couriers = $pdo->query(
    "SELECT c.*, COUNT(o.id_order) AS active
     FROM couriers c
     LEFT JOIN orders o ON o.id_courier = c.id_courier AND o.status IN ('Готовится', 'В пути')
     GROUP BY c.id_courier, c.fio, c.phone, c.transport
     ORDER BY c.fio"
)->fetchAll();

$title = 'Курьеры';
require 'header.php';
?>
<h3 class="mb-3">Курьеры</h3>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<table class="table table-bordered table-hover">
    <thead class="table-light">
        <tr><th>№</th><th>ФИО</th><th>Телефон</th><th>Транспорт</th><th>Заказов в работе</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($couriers as $courier): ?>
        <tr>
            <td><?= $courier['id_courier'] ?></td>
            <td><?= htmlspecialchars($courier['fio']) ?></td>
            <td><?= htmlspecialchars($courier['phone']) ?></td>
            <td><?= $courier['transport'] ?></td>
            <td><?= $courier['active'] ?></td>
            <td>
                <form method="post" onsubmit="return confirm('Удалить курьера?')">
                    <button class="btn btn-sm btn-outline-danger" name="delete" value="<?= $courier['id_courier'] ?>">Удалить</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h5 class="mt-4">Добавить курьера</h5>
<form class="row g-2 mb-4" method="post">
    <div class="col-md-5">
        <label class="form-label" for="fio">ФИО</label>
        <input class="form-control" id="fio" name="fio" value="<?= htmlspecialchars($form['fio']) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="phone">Телефон</label>
        <input class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($form['phone']) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="transport">Транспорт</label>
        <select class="form-select" id="transport" name="transport">
            <?php foreach ($transports as $transport): ?>
                <option <?= $transport === $form['transport'] ? 'selected' : '' ?>><?= $transport ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-1 d-flex align-items-end">
        <button class="btn btn-primary w-100" name="add">Добавить</button>
    </div>
</form>
<?php require 'footer.php'; ?>
