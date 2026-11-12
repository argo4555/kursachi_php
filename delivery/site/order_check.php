<?php
// Чтение и проверка формы заказа (используется в add.php и edit.php)

function readOrder()
{
    return [
        'client_name' => trim($_POST['client_name']),
        'phone'       => trim($_POST['phone']),
        'address'     => trim($_POST['address']),
        'id_dish'     => $_POST['id_dish'],
        'quantity'    => trim($_POST['quantity']),
        'id_courier'  => $_POST['id_courier'],
        'status'      => $_POST['status'],
    ];
}

// Возвращает текст ошибки или пустую строку, если всё заполнено верно
function checkOrder($order, $statuses)
{
    if ($order['client_name'] === '' || $order['phone'] === ''
        || $order['address'] === '' || $order['id_dish'] === '') {
        return 'Заполните обязательные поля: клиент, телефон, адрес и блюдо';
    }
    if (!preg_match('/^[0-9+()\- ]{6,20}$/', $order['phone'])) {
        return 'Телефон может содержать только цифры, пробелы и символы + ( ) -';
    }
    if (!ctype_digit($order['quantity']) || $order['quantity'] < 1 || $order['quantity'] > 20) {
        return 'Количество должно быть числом от 1 до 20';
    }
    if (!isset($statuses[$order['status']])) {
        return 'Выберите статус заказа из списка';
    }
    return '';
}
