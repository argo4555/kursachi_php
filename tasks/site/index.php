<?php
require 'auth.php';
require 'db.php';

// Начальник и секретарь видят все задачи, остальные сотрудники — только свои
$where = canManage() ? '' : ' WHERE t.executor_id = ' . (int)$_SESSION['user_id'];

// Количество задач по состояниям
$stats = $pdo->query("SELECT
        COUNT(*) AS total,
        SUM(t.status_id = 1 AND (t.deadline IS NULL OR t.deadline >= NOW())) AS work,
        SUM(t.status_id = 1 AND t.deadline < NOW()) AS overdue,
        SUM(t.status_id = 2) AS done
    FROM tasks t" . $where)->fetch();

// Ближайшие сроки: задачи в работе, сначала просроченные
$near = $pdo->query($taskSql . ($where ?: ' WHERE 1') . " AND t.status_id = 1
                     ORDER BY t.deadline LIMIT 6")->fetchAll();

$title = 'Главная';
require 'header.php';
?>
<h3 class="mb-1">Здравствуйте, <?= htmlspecialchars(explode(' ', $_SESSION['fio'])[1] ?? $_SESSION['fio']) ?>!</h3>
<p class="text-muted mb-4"><?= canManage() ? 'Сводка по задачам команды' : 'Сводка по вашим задачам' ?> на <?= date('d.m.Y') ?></p>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="stat"><span>Всего задач</span><b><?= (int)$stats['total'] ?></b></div></div>
    <div class="col-6 col-xl-3"><div class="stat stat-work"><span>В работе</span><b><?= (int)$stats['work'] ?></b></div></div>
    <div class="col-6 col-xl-3"><div class="stat stat-overdue"><span>Просрочено</span><b><?= (int)$stats['overdue'] ?></b></div></div>
    <div class="col-6 col-xl-3"><div class="stat stat-done"><span>Выполнено</span><b><?= (int)$stats['done'] ?></b></div></div>
</div>

<div class="panel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Ближайшие сроки</h5>
        <a href="tasks.php" class="small">Все задачи →</a>
    </div>
    <?php foreach ($near as $task): [$state, $class] = taskState($task); ?>
        <a class="near-row" href="task_view.php?id=<?= $task['id'] ?>">
            <span class="prio prio-<?= array_search($task['priority'], $priorities) ?>" title="<?= $task['priority'] ?> приоритет"></span>
            <span class="flex-grow-1">
                <b><?= htmlspecialchars($task['title']) ?></b>
                <small class="d-block text-muted"><?= htmlspecialchars($task['executor']) ?></small>
            </span>
            <span class="text-nowrap small <?= $class === 'st-overdue' ? 'text-danger fw-semibold' : 'text-muted' ?>"><?= dt($task['deadline']) ?></span>
            <span class="badge-st <?= $class ?>"><?= $state ?></span>
        </a>
    <?php endforeach; ?>
    <?php if (!$near): ?><p class="text-muted mb-0">Задач в работе нет</p><?php endif; ?>
</div>
<?php require 'footer.php'; ?>
