<?php
// Проверка входа. Подключается в начале каждой страницы, кроме login.php
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

// Управляющий кофейней (администратор)?
function isAdmin()
{
    return $_SESSION['role'] === 'admin';
}

// Закрывает страницу от бариста
function onlyAdmin()
{
    if (!isAdmin()) {
        http_response_code(403);
        die('Страница доступна только управляющему');
    }
}
