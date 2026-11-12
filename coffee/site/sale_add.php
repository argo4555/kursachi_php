<?php
require 'auth.php';
require 'db.php';
require 'sale_check.php';

// Меню кофейни, сгруппированное по категориям
$products = $pdo->query("SELECT * FROM products
                         ORDER BY FIELD(category, 'Кофе', 'Чай', 'Напитки', 'Выпечка', 'Десерты'), name")->fetchAll();
$prices = array_column($products, 'price', 'id_product');

$error = '';
$qty = [];
$payment = 'Карта';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qty = $_POST['qty'] ?? [];
    $payment = $_POST['payment'] ?? '';
    $error = checkSale($qty, $payment, $prices, $payments);

    if ($error === '') {
        // Сумма считается по ценам из базы, а не по данным из браузера
        $total = 0;
        foreach ($qty as $id => $q) {
            $total += $prices[$id] * (int)$q;
        }

        // Продажа и её позиции записываются в одной транзакции
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO sales (id_user, payment, total) VALUES (?, ?, ?)');
        $stmt->execute([$_SESSION['id_user'], $payment, $total]);
        $idSale = $pdo->lastInsertId();

        $stmt = $pdo->prepare('INSERT INTO sale_items (id_sale, id_product, quantity, price) VALUES (?, ?, ?, ?)');
        foreach ($qty as $id => $q) {
            if ((int)$q > 0) {
                $stmt->execute([$idSale, $id, (int)$q, $prices[$id]]);
            }
        }
        $pdo->commit();

        header("Location: sale_view.php?id=$idSale&new=1");
        exit;
    }
}

$title = 'Новая продажа';
require 'header.php';
?>
<h3 class="mb-3">Новая продажа</h3>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="post" class="row g-4">
    <div class="col-lg-8">
        <?php $category = ''; ?>
        <?php foreach ($products as $p): ?>
            <?php if ($p['category'] !== $category): $category = $p['category']; ?>
                <h6 class="category"><?= $category ?></h6>
            <?php endif; ?>
            <div class="product-row">
                <div class="flex-grow-1">
                    <label for="qty<?= $p['id_product'] ?>"><?= htmlspecialchars($p['name']) ?></label>
                    <span class="small-muted"><?= htmlspecialchars($p['volume']) ?></span>
                </div>
                <div class="price"><?= money($p['price']) ?></div>
                <div class="qty-box">
                    <button type="button" class="btn btn-sm btn-light step" data-step="-1">−</button>
                    <input class="form-control form-control-sm qty" type="number" min="0" max="20"
                           id="qty<?= $p['id_product'] ?>" name="qty[<?= $p['id_product'] ?>]"
                           value="<?= htmlspecialchars($qty[$p['id_product']] ?? 0) ?>"
                           data-name="<?= htmlspecialchars($p['name']) ?>" data-price="<?= $p['price'] ?>">
                    <button type="button" class="btn btn-sm btn-light step" data-step="1">+</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="col-lg-4">
        <div class="check-card">
            <h5>Чек</h5>
            <div id="check-lines" class="check-lines"></div>
            <div class="check-total">
                <span>Итого</span><b id="check-total">0 ₽</b>
            </div>
            <div class="mb-3">
                <div class="form-label">Способ оплаты *</div>
                <?php foreach ($payments as $name): ?>
                    <input type="radio" class="btn-check" name="payment" id="pay-<?= $name ?>" value="<?= $name ?>"
                        <?= $name === $payment ? 'checked' : '' ?>>
                    <label class="btn btn-outline-secondary btn-sm" for="pay-<?= $name ?>"><?= $name ?></label>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-primary w-100">Провести продажу</button>
            <a class="btn btn-link w-100 mt-1" href="sales.php">Отмена</a>
        </div>
    </div>
</form>

<script>
// Кнопки «−» и «+» меняют количество товара
document.querySelectorAll('.step').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const input = btn.parentElement.querySelector('.qty');
        const value = (parseInt(input.value) || 0) + parseInt(btn.dataset.step);
        input.value = Math.min(20, Math.max(0, value));
        recalc();
    });
});

// Пересчёт чека справа при каждом изменении количества
function recalc() {
    const lines = document.getElementById('check-lines');
    let total = 0;
    lines.innerHTML = '';
    document.querySelectorAll('.qty').forEach(function (input) {
        const q = parseInt(input.value) || 0;
        if (q > 0) {
            const sum = q * input.dataset.price;
            total += sum;
            const row = document.createElement('div');
            row.textContent = input.dataset.name + ' × ' + q + ' — ' + sum.toLocaleString('ru-RU') + ' ₽';
            lines.appendChild(row);
        }
    });
    if (total === 0) {
        lines.textContent = 'Товары не выбраны';
    }
    document.getElementById('check-total').textContent = total.toLocaleString('ru-RU') + ' ₽';
}
document.querySelectorAll('.qty').forEach(function (input) {
    input.addEventListener('input', recalc);
});
recalc();
</script>
<?php require 'footer.php'; ?>
