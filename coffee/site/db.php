<?php
// Подключение к базе данных
// На Railway параметры берутся из переменных окружения сервиса MySQL
$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$dbname = 'coffee';
$user = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
}

// Справочники: способы оплаты и категории товаров
$payments = ['Наличные', 'Карта', 'СБП'];
$categories = ['Кофе', 'Чай', 'Напитки', 'Выпечка', 'Десерты'];

// Вывод суммы в рублях: 1 250 ₽
function money($sum)
{
    return number_format($sum, 0, ',', ' ') . ' ₽';
}
