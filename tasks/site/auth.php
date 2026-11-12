<?php
// Проверка входа. Подключается в начале каждой страницы, кроме login.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Роли: 1 — начальник проекта, 2 — секретарь, 3 — офисный работник, 4 — работник колл-центра

// Начальник проекта
function isBoss()
{
    return $_SESSION['role_id'] == 1;
}

// Может ставить и изменять задачи: начальник и секретарь
function canManage()
{
    return in_array($_SESSION['role_id'], [1, 2]);
}

// Закрывает страницу от сотрудников без нужной роли
function onlyRoles($roles)
{
    if (!in_array($_SESSION['role_id'], $roles)) {
        http_response_code(403);
        die('Доступ запрещён: недостаточно прав');
    }
}
