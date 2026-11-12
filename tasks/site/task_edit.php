<?php
require 'auth.php';
require 'db.php';
require 'task_check.php';
onlyRoles([1, 2]);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
$stmt->execute([$id]);
$task = $stmt->fetch();
if (!$task) {
    header('Location: tasks.php');
    exit;
}

$users = $pdo->query('SELECT u.id, u.fio, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.fio')->fetchAll();
$statuses = $pdo->query('SELECT * FROM task_statuses ORDER BY id')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task = readTask();
    $errors = checkTask($task, $pdo, $priorities, false);

    if (!$errors) {
        // Поле updated_at обновляется автоматически (ON UPDATE CURRENT_TIMESTAMP)
        $stmt = $pdo->prepare('UPDATE tasks SET title = ?, description = ?, executor_id = ?, status_id = ?,
                               priority = ?, deadline = ? WHERE id = ?');
        $stmt->execute([$task['title'], $task['description'], $task['executor_id'], $task['status_id'],
                        $task['priority'], $task['deadline'], $id]);
        header('Location: tasks.php?saved=1');
        exit;
    }
}

$title = 'Изменение задачи № ' . $id;
require 'header.php';
require 'task_form.php';
require 'footer.php';
