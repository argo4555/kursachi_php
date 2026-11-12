<?php
// Подключение к базе данных
// На Railway параметры берутся из переменных окружения сервиса MySQL
$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$dbname = 'shelter';
$user = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
}

// Справочники: виды животных, статусы (с классом CSS метки), виды ветеринарных записей
$species = ['Собака', 'Кошка', 'Другое'];
$statuses = [
    'В приюте'     => 'st-home',
    'На карантине' => 'st-quar',
    'На лечении'   => 'st-treat',
    'Пристроен'    => 'st-adopted',
];
$recordTypes = ['Осмотр', 'Вакцинация', 'Лечение', 'Стерилизация'];

// Значок животного по виду
function petIcon($kind)
{
    return ['Собака' => '🐶', 'Кошка' => '🐱'][$kind] ?? '🐾';
}

// Возраст по дате рождения: «2 г. 3 мес.»
function age($birth)
{
    $diff = (new DateTime($birth))->diff(new DateTime('today'));
    if ($diff->y == 0) {
        return $diff->m . ' мес.';
    }
    return $diff->y . ' г.' . ($diff->m ? ' ' . $diff->m . ' мес.' : '');
}

// Дата в формате 12.11.2026
function d($date)
{
    return $date ? date('d.m.Y', strtotime($date)) : '—';
}
