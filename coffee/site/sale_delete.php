<?php
require 'auth.php';
require 'db.php';
onlyAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Позиции чека удаляются автоматически (ON DELETE CASCADE)
    $stmt = $pdo->prepare('DELETE FROM sales WHERE id_sale = ?');
    $stmt->execute([(int)$_POST['id']]);
}
header('Location: sales.php');
