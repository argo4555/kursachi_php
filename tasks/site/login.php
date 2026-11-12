<?php
session_start();
require 'db.php';

$error = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $stmt = $pdo->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.login = ?');
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'] ?? '', $user['password_hash'])) {
        // Запоминаем сотрудника в сессии
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fio'] = $user['fio'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['role_name'] = $user['role_name'];
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
    <title>Вход — Умпа-Лумпа</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
<div class="login-card">
    <div class="brand dark">
        <span class="logo">УЛ</span>
        <div>Умпа-Лумпа<small>учёт задач проектной команды</small></div>
    </div>
    <h4 class="mt-4 mb-3">Вход в систему</h4>
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
        <button class="btn btn-primary w-100">Войти</button>
    </form>
</div>
</body>
</html>
