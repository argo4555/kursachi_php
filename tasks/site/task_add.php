<?php
require 'auth.php';
require 'db.php';
require 'task_check.php';
onlyRoles([1, 2]);

$users = $pdo->query('SELECT u.id, u.fio, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.fio')->fetchAll();
$statuses = $pdo->query('SELECT * FROM task_statuses ORDER BY id')->fetchAll();

$errors = [];
$task = ['title' => '', 'description' => '', 'executor_id' => 0, 'priority' => 'Средний', 'deadline' => '', 'status_id' => 1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task = readTask();
    $errors = checkTask($task, $pdo, $priorities, true);

    if (!$errors) {
        // Автором задачи записывается текущий сотрудник
        $stmt = $pdo->prepare('INSERT INTO tasks (title, description, author_id, executor_id, status_id, priority, deadline)
                               VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$task['title'], $task['description'], $_SESSION['user_id'], $task['executor_id'],
                        $task['status_id'], $task['priority'], $task['deadline']]);
        header('Location: tasks.php?saved=1');
        exit;
    }
}

$title = 'Новая задача';
require 'header.php';
require 'task_form.php';
require 'footer.php';
