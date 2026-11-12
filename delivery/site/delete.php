<?php
require 'auth.php';
require 'db.php';
onlyAdmin();

// Удаление выполняется только формой (методом POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('DELETE FROM orders WHERE id_order = ?');
    $stmt->execute([(int)$_POST['id']]);
}
header('Location: orders.php');
exit;
