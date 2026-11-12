<?php
session_start();
require 'db.php';

$error = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $stmt = $pdo->prepare('SELECT * FROM users WHERE login = ?');
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'] ?? '', $user['password_hash'])) {
        // Запоминаем сотрудника в сессии
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fio'] = $user['fio'];
        $_SESSION['role'] = $user['role'];
        header('Location: index.php');
        exit;
    }
    $error = 'Неверный логин или пароль';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вход — Лапа помощи</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
<div class="login-card">
    <div class="login-pets">🐶 🐱 🐾</div>
    <h3 class="text-center mb-1">Лапа помощи</h3>
    <p class="text-center text-muted mb-4">Учёт животных в приюте</p>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label" for="login">Логин</label>
            <input class="form-control" id="login" name="login" value="<?= htmlspecialchars($login) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Пароль</label>
            <input class="form-control" id="password" name="password" type="password" required>
        </div>
        <button class="btn btn-accent w-100">Войти</button>
    </form>
</div>
</body>
</html>
