<?php
require 'auth.php';
require 'db.php';
onlyAdmin();

$error = '';
$form = ['name' => '', 'category' => '', 'price' => '', 'weight' => ''];

// Добавление блюда
if (isset($_POST['add'])) {
    $form = [
        'name'     => trim($_POST['name']),
        'category' => trim($_POST['category']),
        'price'    => trim($_POST['price']),
        'weight'   => trim($_POST['weight']),
    ];
    if (in_array('', $form, true)) {
        $error = 'Заполните все поля блюда';
    } elseif (!is_numeric($form['price']) || $form['price'] <= 0) {
        $error = 'Цена должна быть положительным числом';
    } elseif (!ctype_digit($form['weight']) || $form['weight'] == 0) {
        $error = 'Вес должен быть целым положительным числом';
    } else {
        $stmt = $pdo->prepare('INSERT INTO dishes (name, category, price, weight) VALUES (?, ?, ?, ?)');
        $stmt->execute([$form['name'], $form['category'], $form['price'], $form['weight']]);
        header('Location: dishes.php');
        exit;
    }
}

// Удаление блюда, если его нет в заказах
if (isset($_POST['delete'])) {
    $id = (int)$_POST['delete'];
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE id_dish = ?');
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $error = 'Нельзя удалить блюдо: оно есть в заказах';
    } else {
        $pdo->prepare('DELETE FROM dishes WHERE id_dish = ?')->execute([$id]);
        header('Location: dishes.php');
        exit;
    }
}

$dishes = $pdo->query('SELECT * FROM dishes ORDER BY category, name')->fetchAll();

$title = 'Блюда';
require 'header.php';
?>
<h3 class="mb-3">Блюда</h3>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<table class="table table-bordered table-hover">
    <thead class="table-light">
        <tr><th>№</th><th>Название</th><th>Категория</th><th>Цена, ₽</th><th>Вес, г</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($dishes as $dish): ?>
        <tr>
            <td><?= $dish['id_dish'] ?></td>
            <td><?= htmlspecialchars($dish['name']) ?></td>
            <td><?= htmlspecialchars($dish['category']) ?></td>
            <td><?= number_format($dish['price'], 0, ',', ' ') ?></td>
            <td><?= $dish['weight'] ?></td>
            <td>
                <form method="post" onsubmit="return confirm('Удалить блюдо?')">
                    <button class="btn btn-sm btn-outline-danger" name="delete" value="<?= $dish['id_dish'] ?>">Удалить</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h5 class="mt-4">Добавить блюдо</h5>
<form class="row g-2 mb-4" method="post">
    <div class="col-md-4">
        <label class="form-label" for="name">Название</label>
        <input class="form-control" id="name" name="name" value="<?= htmlspecialchars($form['name']) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="category">Категория</label>
        <input class="form-control" id="category" name="category" value="<?= htmlspecialchars($form['category']) ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label" for="price">Цена, ₽</label>
        <input class="form-control" id="price" name="price" value="<?= htmlspecialchars($form['price']) ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label" for="weight">Вес, г</label>
        <input class="form-control" id="weight" name="weight" value="<?= htmlspecialchars($form['weight']) ?>">
    </div>
    <div class="col-md-1 d-flex align-items-end">
        <button class="btn btn-primary w-100" name="add">Добавить</button>
    </div>
</form>
<?php require 'footer.php'; ?>
