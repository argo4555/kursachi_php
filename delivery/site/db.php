<?php
// Подключение к базе данных
// На Railway параметры берутся из переменных окружения сервиса MySQL
$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$dbname = 'delivery';
$user = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
}

// Статусы заказа и класс CSS для цвета статуса
$statuses = [
    'Новый'     => 'status-new',
    'Готовится' => 'status-cooking',
    'В пути'    => 'status-way',
    'Доставлен' => 'status-done',
    'Отменён'   => 'status-cancel',
];
