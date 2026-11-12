<?php
// Проверка входа. Подключается в начале каждой страницы, кроме login.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Роли: admin — администратор приюта, vet — ветеринар, volunteer — волонтёр
$roleNames = ['admin' => 'администратор', 'vet' => 'ветеринар', 'volunteer' => 'волонтёр'];

function isAdmin()
{
    return $_SESSION['role'] === 'admin';
}

// Ветеринарные записи и смену статуса вносят ветеринар и администратор
function canTreat()
{
    return in_array($_SESSION['role'], ['admin', 'vet']);
}

// Закрывает страницу от сотрудников без нужной роли
function onlyRoles($roles)
{
    if (!in_array($_SESSION['role'], $roles)) {
        http_response_code(403);
        die('Доступ запрещён: недостаточно прав');
    }
}
