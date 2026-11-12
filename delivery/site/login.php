<?php
session_start();
require 'db.php';

$error = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $stmt = $pdo->prepare('SELECT * FROM users WHERE login = ?');
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        // Запоминаем сотрудника в сессии
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['fio'] = $user['fio'];
        $_SESSION['role'] = $user['role'];
        header('Location: orders.php');
        exit;
    }
    $error = 'Неверный логин или пароль';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Вход — ЕдаРядом</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="login-box card">
    <div class="card-body">
        <h4 class="card-title mb-1">ЕдаРядом</h4>
        <p class="small-muted mb-3">Учёт заказов службы доставки</p>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label" for="login">Логин</label>
                <input class="form-control" id="login" name="login" value="<?= htmlspecialchars($login) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Пароль</label>
                <input class="form-control" id="password" name="password" type="password">
            </div>
            <button class="btn btn-primary w-100">Войти</button>
        </form>
    </div>
</div>
</body>
</html>
