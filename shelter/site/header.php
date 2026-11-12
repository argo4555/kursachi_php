<?php
// Начало страницы и меню навигации. Перед подключением задать $title
$page = basename($_SERVER['PHP_SELF']);
$menu = ['index.php' => 'Главная', 'animals.php' => 'Животные', 'adoptions.php' => 'Передачи'];
if (isAdmin()) {
    $menu['report.php'] = 'Отчёт';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?> — Лапа помощи</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark app-navbar">
    <div class="container">
        <a class="navbar-brand" href="index.php"><span class="paw">🐾</span> Лапа помощи</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <?php foreach ($menu as $file => $name): ?>
                    <li class="nav-item"><a class="nav-link <?= $file === $page ? 'active' : '' ?>" href="<?= $file ?>"><?= $name ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php if (isAdmin()): ?>
                <a class="btn btn-accent btn-sm me-3" href="animal_add.php">+ Поступление</a>
            <?php endif; ?>
            <span class="navbar-text me-3 user-info">
                <?= htmlspecialchars($_SESSION['fio']) ?><small><?= $roleNames[$_SESSION['role']] ?></small>
            </span>
            <a class="btn btn-outline-light btn-sm" href="logout.php">Выйти</a>
        </div>
    </div>
</nav>
<div class="container my-4">
