<?php
// Проверка входа: подключается в начале каждой закрытой страницы
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

// Является ли вошедший сотрудник администратором
function isAdmin()
{
    return $_SESSION['role'] === 'admin';
}

// Закрывает страницу для всех, кроме администратора
function onlyAdmin()
{
    if (!isAdmin()) {
        http_response_code(403);
        die('Страница доступна только администратору. <a href="orders.php">К списку заказов</a>');
    }
}
