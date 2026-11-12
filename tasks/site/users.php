<?php
require 'auth.php';
require 'db.php';
onlyRoles([1]);

$errors = [];
$form = ['login' => '', 'fio' => '', 'role_id' => 3];

// Добавление сотрудника
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = [
        'login'   => trim($_POST['login'] ?? ''),
        'fio'     => trim($_POST['fio'] ?? ''),
        'role_id' => (int)($_POST['role_id'] ?? 0),
    ];
    $pass = $_POST['password'] ?? '';

    if (!preg_match('/^[a-z0-9_]{3,30}$/i', $form['login'])) {
        $errors[] = 'Логин: от 3 до 30 латинских букв, цифр или знаков «_»';
    }
    if ($form['fio'] === '') {
        $errors[] = 'Укажите ФИО сотрудника';
    }
    if (mb_strlen($pass) < 4) {
        $errors[] = 'Пароль должен быть не короче 4 символов';
    }
    if ($form['role_id'] < 1 || $form['role_id'] > 4) {
        $errors[] = 'Выберите роль';
    }
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE login = ?');
    $stmt->execute([$form['login']]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'Сотрудник с таким логином уже есть';
    }

    if (!$errors) {
        // В базе хранится только хеш пароля
        $stmt = $pdo->prepare('INSERT INTO users (login, password_hash, fio, role_id) VALUES (?, ?, ?, ?)');
        $stmt->execute([$form['login'], password_hash($pass, PASSWORD_DEFAULT), $form['fio'], $form['role_id']]);
        header('Location: users.php?added=1');
        exit;
    }
}

// Сотрудники и их загрузка: задачи в работе и просроченные
$users = $pdo->query("SELECT u.id, u.login, u.fio, r.name AS role_name,
                             SUM(t.status_id = 1) AS work,
                             SUM(t.status_id = 1 AND t.deadline < NOW()) AS overdue
                      FROM users u
                      JOIN roles r ON r.id = u.role_id
                      LEFT JOIN tasks t ON t.executor_id = u.id
                      GROUP BY u.id, u.login, u.fio, r.name, u.role_id
                      ORDER BY u.role_id, u.fio")->fetchAll();
$roles = $pdo->query('SELECT * FROM roles ORDER BY id')->fetchAll();

$title = 'Сотрудники';
require 'header.php';
?>
<h3 class="mb-3">Сотрудники</h3>

<?php if (isset($_GET['added'])): ?>
    <div class="alert alert-success">Сотрудник добавлен</div>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="table-responsive panel p-0 mb-4">
    <table class="table align-middle mb-0 task-table">
        <thead><tr><th>№</th><th>ФИО</th><th>Логин</th><th>Роль</th><th class="text-center">В работе</th><th class="text-center">Просрочено</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td class="text-muted"><?= $u['id'] ?></td>
                <td><a href="tasks.php?executor=<?= $u['id'] ?>"><?= htmlspecialchars($u['fio']) ?></a></td>
                <td><?= htmlspecialchars($u['login']) ?></td>
                <td><?= htmlspecialchars($u['role_name']) ?></td>
                <td class="text-center"><?= (int)$u['work'] ?></td>
                <td class="text-center <?= $u['overdue'] ? 'text-danger fw-semibold' : 'text-muted' ?>"><?= (int)$u['overdue'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<form class="panel" method="post">
    <h5 class="mb-3">Добавить сотрудника</h5>
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label" for="fio">ФИО *</label>
            <input class="form-control" id="fio" name="fio" value="<?= htmlspecialchars($form['fio']) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label" for="login">Логин *</label>
            <input class="form-control" id="login" name="login" value="<?= htmlspecialchars($form['login']) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label" for="password">Пароль *</label>
            <input class="form-control" type="password" id="password" name="password">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="role_id">Роль *</label>
            <select class="form-select" id="role_id" name="role_id">
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= $r['id'] == $form['role_id'] ? 'selected' : '' ?>><?= $r['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Добавить</button></div>
    </div>
</form>
<?php require 'footer.php'; ?>
