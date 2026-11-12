<?php
// Начало страницы: подключение стилей и боковое меню. Перед подключением задать $title
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?> — Умпа-Лумпа</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="layout">
    <?php require 'menu.php'; ?>
    <main class="content">
        <div class="topbar d-lg-none">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="offcanvas" data-bs-target="#sidebar">☰ Меню</button>
            <b>Умпа-Лумпа</b>
        </div>
