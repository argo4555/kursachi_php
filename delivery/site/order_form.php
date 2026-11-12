<?php
// Форма заказа: общая для add.php и edit.php.
// Перед подключением задать $title, $order, $error, $dishes, $couriers
?>
<h3 class="mb-3"><?= $title ?></h3>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="post" style="max-width: 640px">
    <div class="row mb-3">
        <div class="col">
            <label class="form-label" for="client_name">Клиент *</label>
            <input class="form-control" id="client_name" name="client_name" value="<?= htmlspecialchars($order['client_name']) ?>">
        </div>
        <div class="col">
            <label class="form-label" for="phone">Телефон *</label>
            <input class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($order['phone']) ?>">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="address">Адрес доставки *</label>
        <input class="form-control" id="address" name="address" value="<?= htmlspecialchars($order['address']) ?>">
    </div>
    <div class="row mb-3">
        <div class="col-8">
            <label class="form-label" for="id_dish">Блюдо *</label>
            <select class="form-select" id="id_dish" name="id_dish">
                <option value="">— выберите блюдо —</option>
                <?php foreach ($dishes as $dish): ?>
                    <option value="<?= $dish['id_dish'] ?>" <?= $dish['id_dish'] == $order['id_dish'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dish['name']) ?> — <?= number_format($dish['price'], 0, ',', ' ') ?> ₽
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-4">
            <label class="form-label" for="quantity">Количество *</label>
            <input class="form-control" id="quantity" name="quantity" value="<?= htmlspecialchars($order['quantity']) ?>">
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <label class="form-label" for="id_courier">Курьер</label>
            <select class="form-select" id="id_courier" name="id_courier">
                <option value="">не назначен</option>
                <?php foreach ($couriers as $courier): ?>
                    <option value="<?= $courier['id_courier'] ?>" <?= $courier['id_courier'] == $order['id_courier'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($courier['fio']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col">
            <label class="form-label" for="status">Статус</label>
            <select class="form-select" id="status" name="status">
                <?php foreach ($statuses as $name => $class): ?>
                    <option <?= $name === $order['status'] ? 'selected' : '' ?>><?= $name ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <button class="btn btn-primary">Сохранить</button>
    <a class="btn btn-secondary" href="orders.php">Отмена</a>
</form>
