<?php
require 'auth.php';
require 'db.php';
onlyRoles([1]);

// Удаление выполняется только методом POST, чтобы задачу нельзя было удалить переходом по ссылке
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ?');
    $stmt->execute([(int)$_POST['id']]);
}
header('Location: tasks.php');
