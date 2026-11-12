<?php
// Чтение и проверка полей формы задачи (task_add.php и task_edit.php)

function readTask()
{
    // Срок из поля datetime-local (2026-11-15T18:00) приводится к формату MySQL
    $deadline = trim($_POST['deadline'] ?? '');
    if ($deadline !== '' && strtotime($deadline) !== false) {
        $deadline = date('Y-m-d H:i:s', strtotime($deadline));
    }
    return [
        'title'       => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'executor_id' => (int)($_POST['executor_id'] ?? 0),
        'priority'    => $_POST['priority'] ?? '',
        'deadline'    => $deadline,
        'status_id'   => (int)($_POST['status_id'] ?? 1),
    ];
}

// Возвращает массив ошибок; пустой массив — ошибок нет.
// $isNew — новая задача: её срок не может быть в прошлом
function checkTask($task, $pdo, $priorities, $isNew)
{
    $errors = [];
    if ($task['title'] === '') {
        $errors[] = 'Укажите название задачи';
    } elseif (mb_strlen($task['title']) > 255) {
        $errors[] = 'Название не должно быть длиннее 255 символов';
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE id = ?');
    $stmt->execute([$task['executor_id']]);
    if ($stmt->fetchColumn() == 0) {
        $errors[] = 'Выберите исполнителя';
    }

    if (!in_array($task['priority'], $priorities, true)) {
        $errors[] = 'Выберите приоритет';
    }

    $time = strtotime($task['deadline']);
    if ($task['deadline'] === '' || $time === false) {
        $errors[] = 'Укажите срок выполнения';
    } elseif ($isNew && $time < time()) {
        $errors[] = 'Срок выполнения новой задачи не может быть в прошлом';
    }

    if ($task['status_id'] < 1 || $task['status_id'] > 4) {
        $errors[] = 'Выберите статус';
    }
    return $errors;
}
