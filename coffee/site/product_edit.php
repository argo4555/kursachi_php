<?php
require 'auth.php';
require 'db.php';
require 'product_check.php';
onlyAdmin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM products WHERE id_product = ?');
$stmt->execute([$id]);
$form = $stmt->fetch();
if (!$form) {
    header('Location: products.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = readProduct();
    $error = checkProduct($form, $categories);
    if ($error === '') {
        // Новая цена действует для новых продаж, в старых чеках цена сохранена
        $stmt = $pdo->prepare('UPDATE products SET name = ?, category = ?, volume = ?, price = ? WHERE id_product = ?');
        $stmt->execute([$form['name'], $form['category'], $form['volume'], $form['price'], $id]);
        header('Location: products.php');
        exit;
    }
}

$title = 'Изменение товара';
require 'header.php';
?>
<h3 class="mb-3">Изменение товара</h3>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form class="card-box" method="post" style="max-width: 560px">
    <div class="mb-3">
        <label class="form-label" for="name">Название *</label>
        <input class="form-control" id="name" name="name" value="<?= htmlspecialchars($form['name']) ?>">
    </div>
    <div class="row mb-3">
        <div class="col">
            <label class="form-label" for="category">Категория *</label>
            <select class="form-select" id="category" name="category">
                <?php foreach ($categories as $name): ?>
                    <option <?= $name === $form['category'] ? 'selected' : '' ?>><?= $name ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col">
            <label class="form-label" for="volume">Объём / вес *</label>
            <input class="form-control" id="volume" name="volume" value="<?= htmlspecialchars($form['volume']) ?>">
        </div>
        <div class="col">
            <label class="form-label" for="price">Цена, ₽ *</label>
            <input class="form-control" id="price" name="price" value="<?= htmlspecialchars((string)$form['price']) ?>">
        </div>
    </div>
    <button class="btn btn-primary">Сохранить</button>
    <a class="btn btn-secondary" href="products.php">Отмена</a>
</form>
<?php require 'footer.php'; ?>
