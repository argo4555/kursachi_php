<?php
// Подключение к базе данных через PDO
// На Railway параметры берутся из переменных окружения сервиса MySQL
$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$dbname = 'task_manager';
$user = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // ошибки БД — исключения
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // строки — ассоциативные массивы
        PDO::ATTR_EMULATE_PREPARES   => false,                  // настоящие подготовленные запросы
    ]);
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
}

// Приоритеты задач
$priorities = ['Высокий', 'Средний', 'Низкий'];

// Общая часть запроса задач: статус, ФИО исполнителя и автора
$taskSql = "SELECT t.*, s.name AS status_name, e.fio AS executor, a.fio AS author
            FROM tasks t
            JOIN task_statuses s ON s.id = t.status_id
            JOIN users e ON e.id = t.executor_id
            JOIN users a ON a.id = t.author_id";

// Состояние задачи для вывода: название и класс CSS цветной метки.
// Задача «В работе» с прошедшим сроком считается просроченной
function taskState($task)
{
    if ($task['status_id'] == 1 && $task['deadline'] && strtotime($task['deadline']) < time()) {
        return ['Просрочено', 'st-overdue'];
    }
    $classes = [1 => 'st-work', 2 => 'st-done', 3 => 'st-fail', 4 => 'st-cancel'];
    return [$task['status_name'], $classes[$task['status_id']] ?? ''];
}

// Краткое ФИО: Гриднев П. А.
function shortFio($fio)
{
    $parts = explode(' ', $fio);
    $short = $parts[0];
    foreach (array_slice($parts, 1) as $part) {
        $short .= ' ' . mb_substr($part, 0, 1) . '.';
    }
    return $short;
}

// Дата в формате 12.11.2026 18:00
function dt($value)
{
    return $value ? date('d.m.Y H:i', strtotime($value)) : '—';
}
