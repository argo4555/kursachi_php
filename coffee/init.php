<?php
// Создаёт базу данных проекта при запуске контейнера.
// Имя базы берётся из строки «CREATE DATABASE ...» в SQL-скрипте папки sql.
// Если база уже есть и в ней есть таблицы — она не трогается.
// Переменная RESET_DB=1 заставляет пересоздать базу с тестовыми данными.
$host = getenv('MYSQLHOST') ?: '127.0.0.1';
$port = getenv('MYSQLPORT') ?: '3306';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$reset = getenv('RESET_DB') === '1';

function connect($host, $port, $user, $pass)
{
    return new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
}

// MySQL на Railway может стартовать позже приложения — ждём до минуты
$pdo = null;
for ($i = 0; $i < 30 && !$pdo; $i++) {
    try {
        $pdo = connect($host, $port, $user, $pass);
    } catch (PDOException $e) {
        echo "init: MySQL ещё не готов ({$e->getMessage()}), ждём...\n";
        sleep(2);
    }
}
if (!$pdo) {
    echo "init: нет подключения к MySQL, база не создана\n";
    exit(0);
}

// Время сервера MySQL — московское, как в PHP (нужно для NOW() в запросах)
try {
    $pdo->exec("SET GLOBAL time_zone = '+03:00'");
} catch (PDOException $e) {
    echo "init: не удалось задать часовой пояс MySQL: {$e->getMessage()}\n";
}

foreach (glob(__DIR__ . '/sql/*.sql') as $file) {
    $sql = file_get_contents($file);
    if (!preg_match('/CREATE DATABASE IF NOT EXISTS (\w+)/', $sql, $m)) {
        continue;
    }
    $db = $m[1];
    $tables = $pdo->query('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = ' . $pdo->quote($db))->fetchColumn();
    if ($tables > 0 && !$reset) {
        echo "init: база $db уже есть, пропускаем\n";
        continue;
    }
    // Скрипт делится на отдельные команды по «;» в конце строки
    $conn = connect($host, $port, $user, $pass);
    $conn->exec("SET time_zone = '+03:00'");
    foreach (preg_split('/;\s*$/m', $sql) as $statement) {
        $code = trim(preg_replace('/^--.*$/m', '', $statement));
        if ($code !== '') {
            $conn->exec($code);
        }
    }
    echo "init: база $db создана из " . basename($file) . "\n";
}
