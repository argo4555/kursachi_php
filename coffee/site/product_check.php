<?php
// Чтение и проверка полей формы товара (products.php и product_edit.php)

function readProduct()
{
    return [
        'name'     => trim($_POST['name'] ?? ''),
        'category' => trim($_POST['category'] ?? ''),
        'volume'   => trim($_POST['volume'] ?? ''),
        'price'    => trim($_POST['price'] ?? ''),
    ];
}

// Возвращает текст ошибки или пустую строку
function checkProduct($product, $categories)
{
    if (in_array('', $product, true)) {
        return 'Заполните все поля товара';
    }
    if (!in_array($product['category'], $categories, true)) {
        return 'Выберите категорию из списка';
    }
    if (!is_numeric($product['price']) || $product['price'] <= 0 || $product['price'] > 10000) {
        return 'Цена должна быть числом от 1 до 10 000';
    }
    return '';
}
