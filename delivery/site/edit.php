<?php
require 'auth.php';
require 'db.php';
require 'order_check.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id_order = ?');
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) {
    die('Заказ не найден. <a href="orders.php">К списку заказов</a>');
}

$dishes = $pdo->query('SELECT * FROM dishes ORDER BY name')->fetchAll();
$couriers = $pdo->query('SELECT * FROM couriers ORDER BY fio')->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order = readOrder();
    $error = checkOrder($order, $statuses);
    if ($error === '') {
        $sql = "UPDATE orders
                SET client_name = ?, phone = ?, address = ?, id_dish = ?,
                    quantity = ?, id_courier = ?, status = ?
                WHERE id_order = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$order['client_name'], $order['phone'], $order['address'], $order['id_dish'],
                        $order['quantity'], $order['id_courier'] ?: null, $order['status'], $id]);
        header('Location: orders.php');
        exit;
    }
}

$title = "Изменение заказа № $id";
require 'header.php';
require 'order_form.php';
require 'footer.php';
