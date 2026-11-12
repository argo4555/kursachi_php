<?php
require 'auth.php';
require 'db.php';

// Параметры отбора
$q = trim($_GET['q'] ?? '');
$executor = (int)($_GET['executor'] ?? 0);
$status = $_GET['status'] ?? '';
$priority = $_GET['priority'] ?? '';

// Офисные работники и колл-центр видят только свои задачи
if (!canManage()) {
    $executor = (int)$_SESSION['user_id'];
}

$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(t.title LIKE ? OR t.description LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($executor > 0) {
    $where[] = 't.executor_id = ?';
    $params[] = $executor;
}
if ($status === 'overdue') {
    $where[] = 't.status_id = 1 AND t.deadline < NOW()';
} elseif ($status !== '') {
    $where[] = 't.status_id = ?';
    $params[] = (int)$status;
}
if (in_array($priority, $priorities, true)) {
    $where[] = 't.priority = ?';
    $params[] = $priority;
}

$sql = $taskSql;
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
// Сначала задачи в работе по возрастанию срока, затем остальные
$sql .= ' ORDER BY t.status_id <> 1, t.deadline IS NULL, t.deadline, t.id';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Списки для полей отбора
$users = $pdo->query('SELECT id, fio FROM users ORDER BY fio')->fetchAll();
$statuses = $pdo->query('SELECT * FROM task_statuses ORDER BY id')->fetchAll();

$title = canManage() ? 'Все задачи' : 'Мои задачи';
require 'header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= $title ?></h3>
    <?php if (canManage()): ?>
        <a class="btn btn-primary" href="task_add.php">+ Новая задача</a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">Задача сохранена</div>
<?php endif; ?>

<form class="panel filters row g-2 align-items-end mb-3" method="get">
    <div class="col-md-3">
        <label class="form-label" for="q">Поиск</label>
        <input class="form-control" id="q" name="q" placeholder="Название или описание" value="<?= htmlspecialchars($q) ?>">
    </div>
    <?php if (canManage()): ?>
        <div class="col-md-3">
            <label class="form-label" for="executor">Исполнитель</label>
            <select class="form-select" id="executor" name="executor">
                <option value="0">Все</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $u['id'] == $executor ? 'selected' : '' ?>><?= htmlspecialchars($u['fio']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
    <div class="col-md-2">
        <label class="form-label" for="status">Статус</label>
        <select class="form-select" id="status" name="status">
            <option value="">Все</option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= $s['id'] ?>" <?= (string)$s['id'] === $status ? 'selected' : '' ?>><?= $s['name'] ?></option>
            <?php endforeach; ?>
            <option value="overdue" <?= $status === 'overdue' ? 'selected' : '' ?>>Просрочено</option>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="priority">Приоритет</label>
        <select class="form-select" id="priority" name="priority">
            <option value="">Любой</option>
            <?php foreach ($priorities as $p): ?>
                <option <?= $p === $priority ? 'selected' : '' ?>><?= $p ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-outline-primary flex-grow-1">Найти</button>
        <a class="btn btn-light" href="tasks.php" title="Сбросить">✕</a>
    </div>
</form>

<div class="table-responsive panel p-0">
    <table class="table table-hover align-middle mb-0 task-table">
        <thead>
            <tr><th>№</th><th>Задача</th><th>Исполнитель</th><th>Приоритет</th><th>Срок</th><th>Статус</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($tasks as $task): [$state, $class] = taskState($task); $id = $task['id']; ?>
            <tr>
                <td class="text-muted"><?= $id ?></td>
                <td>
                    <a class="task-link" href="task_view.php?id=<?= $id ?>"><?= htmlspecialchars($task['title']) ?></a>
                    <small class="d-block text-muted">поставил(а): <?= htmlspecialchars(shortFio($task['author'])) ?></small>
                </td>
                <td class="text-nowrap"><?= htmlspecialchars(shortFio($task['executor'])) ?></td>
                <td class="text-nowrap"><span class="prio prio-<?= array_search($task['priority'], $priorities) ?>"></span> <?= $task['priority'] ?></td>
                <td class="text-nowrap <?= $class === 'st-overdue' ? 'text-danger fw-semibold' : '' ?>"><?= dt($task['deadline']) ?></td>
                <td><span class="badge-st <?= $class ?>"><?= $state ?></span></td>
                <td class="text-nowrap text-end">
                    <?php if (!canManage()): ?>
                        <a class="btn btn-sm btn-outline-primary" href="task_view.php?id=<?= $id ?>">Открыть</a>
                    <?php endif; ?>
                    <?php if (canManage()): ?>
                        <a class="btn btn-sm btn-outline-primary" href="task_edit.php?id=<?= $id ?>">Изменить</a>
                    <?php endif; ?>
                    <?php if (isBoss()): ?>
                        <form class="d-inline" method="post" action="task_delete.php" onsubmit="return confirm('Удалить задачу № <?= $id ?>?')">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <button class="btn btn-sm btn-outline-danger">Удалить</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$tasks): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">Задач не найдено</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<p class="text-muted small mt-2">Найдено задач: <?= count($tasks) ?></p>
<?php require 'footer.php'; ?>
