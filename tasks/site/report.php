<?php
require 'auth.php';
require 'db.php';
onlyRoles([1, 2]);

// Период отчёта по сроку выполнения задач: по умолчанию текущий месяц
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-t');
$error = '';
if (!strtotime($from) || !strtotime($to) || $from > $to) {
    $error = 'Неверно указан период: дата начала должна быть не позже даты окончания';
    $from = date('Y-m-01');
    $to = date('Y-m-t');
}

// Выполнение задач по каждому исполнителю за период
$stmt = $pdo->prepare("SELECT u.fio, r.name AS role_name,
                              COUNT(*) AS total,
                              SUM(t.status_id = 2) AS done,
                              SUM(t.status_id = 1 AND t.deadline >= NOW()) AS work,
                              SUM(t.status_id = 3 OR (t.status_id = 1 AND t.deadline < NOW())) AS failed
                       FROM tasks t
                       JOIN users u ON u.id = t.executor_id
                       JOIN roles r ON r.id = u.role_id
                       WHERE DATE(t.deadline) BETWEEN ? AND ? AND t.status_id <> 4
                       GROUP BY u.id, u.fio, r.name
                       ORDER BY done DESC, u.fio");
$stmt->execute([$from, $to]);
$rows = $stmt->fetchAll();

$total = array_sum(array_column($rows, 'total'));
$done = array_sum(array_column($rows, 'done'));
$failed = array_sum(array_column($rows, 'failed'));

$title = 'Отчёт';
require 'header.php';
?>
<h3 class="mb-3">Отчёт о выполнении задач</h3>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form class="panel filters row g-2 align-items-end mb-3" method="get">
    <div class="col-auto">
        <label class="form-label" for="from">Срок с</label>
        <input class="form-control" type="date" id="from" name="from" value="<?= htmlspecialchars($from) ?>">
    </div>
    <div class="col-auto">
        <label class="form-label" for="to">по</label>
        <input class="form-control" type="date" id="to" name="to" value="<?= htmlspecialchars($to) ?>">
    </div>
    <div class="col-auto"><button class="btn btn-outline-primary">Сформировать</button></div>
    <div class="col-auto"><button class="btn btn-light" type="button" onclick="window.print()">Печать</button></div>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="stat"><span>Задач за период</span><b><?= $total ?></b></div></div>
    <div class="col-md-4"><div class="stat stat-done"><span>Выполнено</span><b><?= $done ?></b></div></div>
    <div class="col-md-4"><div class="stat stat-overdue"><span>Не выполнено и просрочено</span><b><?= $failed ?></b></div></div>
</div>

<div class="table-responsive panel p-0">
    <table class="table align-middle mb-0 task-table">
        <thead><tr><th>Исполнитель</th><th class="text-center">Всего</th><th class="text-center">Выполнено</th>
            <th class="text-center">В работе</th><th class="text-center">Не выполнено</th><th style="width: 28%">Выполнение</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): $pct = round($row['done'] / $row['total'] * 100); ?>
            <tr>
                <td><?= htmlspecialchars($row['fio']) ?><small class="d-block text-muted"><?= htmlspecialchars($row['role_name']) ?></small></td>
                <td class="text-center"><?= $row['total'] ?></td>
                <td class="text-center text-success fw-semibold"><?= $row['done'] ?></td>
                <td class="text-center"><?= $row['work'] ?></td>
                <td class="text-center <?= $row['failed'] ? 'text-danger fw-semibold' : 'text-muted' ?>"><?= $row['failed'] ?></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1"><div class="progress-bar" style="width: <?= $pct ?>%"></div></div>
                        <span class="small text-muted"><?= $pct ?>%</span>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">За период задач нет</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require 'footer.php'; ?>
