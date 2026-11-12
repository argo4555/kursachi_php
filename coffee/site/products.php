<?php
require 'auth.php';
require 'db.php';
require 'product_check.php';
onlyAdmin();

$error = '';
$form = ['name' => '', 'category' => '', 'volume' => '', 'price' => ''];

// Добавление товара
if (isset($_POST['add'])) {
    $form = readProduct();
    $error = checkProduct($form, $categories);
    if ($error === '') {
        $stmt = $pdo->prepare('INSERT INTO products (name, category, volume, price) VALUES (?, ?, ?, ?)');
        $stmt->execute([$form['name'], $form['category'], $form['volume'], $form['price']]);
        header('Location: products.php');
        exit;
    }
}

// Удаление товара, если он ещё ни разу не продавался
if (isset($_POST['delete'])) {
    $id = (int)$_POST['delete'];
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM sale_items WHERE id_product = ?');
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $error = 'Нельзя удалить товар: он уже есть в чеках продаж';
    } else {
        $pdo->prepare('DELETE FROM products WHERE id_product = ?')->execute([$id]);
        header('Location: products.php');
        exit;
    }
}

// Товары и сколько штук каждого продано
$products = $pdo->query("SELECT p.*, IFNULL(SUM(i.quantity), 0) AS sold
                         FROM products p LEFT JOIN sale_items i ON i.id_product = p.id_product
                         GROUP BY p.id_product, p.name, p.category, p.volume, p.price
                         ORDER BY FIELD(p.category, 'Кофе', 'Чай', 'Напитки', 'Выпечка', 'Десерты'), p.name")->fetchAll();

$title = 'Меню';
require 'header.php';
?>
<h3 class="mb-3">Меню кофейни</h3>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<table class="table table-hover sales-table">
    <thead>
        <tr><th>№</th><th>Название</th><th>Категория</th><th>Объём / вес</th><th class="text-end">Цена</th><th class="text-end">Продано, шт.</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($products as $p): ?>
        <tr>
            <td><?= $p['id_product'] ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= $p['category'] ?></td>
            <td><?= htmlspecialchars($p['volume']) ?></td>
            <td class="text-end"><?= money($p['price']) ?></td>
            <td class="text-end"><?= $p['sold'] ?></td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-secondary" href="product_edit.php?id=<?= $p['id_product'] ?>">Изменить</a>
                <form class="d-inline" method="post" onsubmit="return confirm('Удалить товар?')">
                    <button class="btn btn-sm btn-outline-danger" name="delete" value="<?= $p['id_product'] ?>">Удалить</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="card-box mt-4">
    <h5 class="mb-3">Добавить товар</h5>
    <form class="row g-2" method="post">
        <div class="col-md-4">
            <label class="form-label" for="name">Название *</label>
            <input class="form-control" id="name" name="name" value="<?= htmlspecialchars($form['name']) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label" for="category">Категория *</label>
            <select class="form-select" id="category" name="category">
                <option value="">—</option>
                <?php foreach ($categories as $name): ?>
                    <option <?= $name === $form['category'] ? 'selected' : '' ?>><?= $name ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="volume">Объём / вес *</label>
            <input class="form-control" id="volume" name="volume" placeholder="300 мл" value="<?= htmlspecialchars($form['volume']) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label" for="price">Цена, ₽ *</label>
            <input class="form-control" id="price" name="price" value="<?= htmlspecialchars($form['price']) ?>">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100" name="add">Добавить</button>
        </div>
    </form>
</div>
<?php require 'footer.php'; ?>
