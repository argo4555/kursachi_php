<?php
// Начало страницы и меню навигации. Перед подключением задать $title
$page = basename($_SERVER['PHP_SELF']);
$menu = ['sales.php' => 'Продажи', 'sale_add.php' => 'Новая продажа'];
if (isAdmin()) {
    $menu['products.php'] = 'Меню';
    $menu['report.php'] = 'Отчёт';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?> — Кофейня «Зерно»</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar navbar-expand navbar-dark app-navbar">
    <div class="container">
        <a class="navbar-brand" href="sales.php"><span class="brand-mark">З</span> Зерно</a>
        <ul class="navbar-nav me-auto">
            <?php foreach ($menu as $file => $name): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $file === $page ? 'active' : '' ?>" href="<?= $file ?>"><?= $name ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
        <span class="navbar-text me-3">
            <?= htmlspecialchars($_SESSION['fio']) ?>
            <span class="role"><?= isAdmin() ? 'управляющий' : 'бариста' ?></span>
        </span>
        <a class="btn btn-outline-light btn-sm" href="logout.php">Выход</a>
    </div>
</nav>
<div class="container my-4">
