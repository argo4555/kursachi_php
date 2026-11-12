<?php
require 'auth.php';
require 'db.php';
require 'order_check.php';

$dishes = $pdo->query('SELECT * FROM dishes ORDER BY name')->fetchAll();
$couriers = $pdo->query('SELECT * FROM couriers ORDER BY fio')->fetchAll();

$error = '';
$order = ['client_name' => '', 'phone' => '', 'address' => '', 'id_dish' => '',
          'quantity' => '1', 'id_courier' => '', 'status' => 'Новый'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order = readOrder();
    $error = checkOrder($order, $statuses);
    if ($error === '') {
        $sql = "INSERT INTO orders (client_name, phone, address, id_dish, quantity, id_courier, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$order['client_name'], $order['phone'], $order['address'], $order['id_dish'],
                        $order['quantity'], $order['id_courier'] ?: null, $order['status']]);
        header('Location: orders.php');
        exit;
    }
}

$title = 'Новый заказ';
require 'header.php';
require 'order_form.php';
require 'footer.php';
