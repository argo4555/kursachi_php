<?php
// Начало страницы и меню навигации. Перед подключением задать $title
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?> — ЕдаРядом</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar navbar-expand navbar-dark app-navbar">
    <div class="container">
        <a class="navbar-brand" href="orders.php">ЕдаРядом</a>
        <ul class="navbar-nav me-auto">
            <li class="nav-item"><a class="nav-link" href="orders.php">Заказы</a></li>
            <?php if (isAdmin()): ?>
                <li class="nav-item"><a class="nav-link" href="dishes.php">Блюда</a></li>
                <li class="nav-item"><a class="nav-link" href="couriers.php">Курьеры</a></li>
            <?php endif; ?>
        </ul>
        <span class="navbar-text me-3"><?= htmlspecialchars($_SESSION['fio']) ?></span>
        <a class="btn btn-outline-light btn-sm" href="logout.php">Выход</a>
    </div>
</nav>
<div class="container mt-4">
