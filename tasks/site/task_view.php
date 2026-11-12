<?php
require 'auth.php';
require 'db.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare($taskSql . ' WHERE t.id = ?');
$stmt->execute([$id]);
$task = $stmt->fetch();

// Сотрудник без прав руководителя может открыть только свою задачу
if (!$task || (!canManage() && $task['executor_id'] != $_SESSION['user_id'])) {
    header('Location: tasks.php');
    exit;
}

// Смена статуса: исполнитель отмечает выполнение, руководитель — любой статус
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = (int)($_POST['status_id'] ?? 0);
    $allowed = canManage() ? [1, 2, 3, 4] : [1, 2, 3];
    if (in_array($status, $allowed)) {
        $pdo->prepare('UPDATE tasks SET status_id = ? WHERE id = ?')->execute([$status, $id]);
    }
    header("Location: task_view.php?id=$id&changed=1");
    exit;
}

$statuses = $pdo->query('SELECT * FROM task_statuses ORDER BY id')->fetchAll();
[$state, $class] = taskState($task);

$title = 'Задача № ' . $id;
require 'header.php';
?>
<a class="small" href="tasks.php">← К списку задач</a>

<?php if (isset($_GET['changed'])): ?>
    <div class="alert alert-success mt-3">Статус задачи изменён</div>
<?php endif; ?>

<div class="row g-4 mt-1">
    <div class="col-lg-8">
        <div class="panel">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <h4 class="mb-2"><?= htmlspecialchars($task['title']) ?></h4>
                <span class="badge-st <?= $class ?>"><?= $state ?></span>
            </div>
            <p class="text-muted small">Задача № <?= $id ?> · создана <?= dt($task['created_at']) ?>
                <?= $task['updated_at'] ? ' · изменена ' . dt($task['updated_at']) : '' ?></p>
            <div class="description"><?= nl2br(htmlspecialchars($task['description'] ?: 'Описание не заполнено')) ?></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel">
            <dl class="task-info">
                <dt>Исполнитель</dt><dd><?= htmlspecialchars($task['executor']) ?></dd>
                <dt>Поставил(а)</dt><dd><?= htmlspecialchars($task['author']) ?></dd>
                <dt>Приоритет</dt><dd><span class="prio prio-<?= array_search($task['priority'], $priorities) ?>"></span> <?= $task['priority'] ?></dd>
                <dt>Срок</dt><dd class="<?= $class === 'st-overdue' ? 'text-danger fw-semibold' : '' ?>"><?= dt($task['deadline']) ?></dd>
            </dl>
            <form method="post" class="border-top pt-3">
                <label class="form-label" for="status_id">Изменить статус</label>
                <div class="d-flex gap-2">
                    <select class="form-select" id="status_id" name="status_id">
                        <?php foreach ($statuses as $s): if (!canManage() && $s['id'] == 4) continue; ?>
                            <option value="<?= $s['id'] ?>" <?= $s['id'] == $task['status_id'] ? 'selected' : '' ?>><?= $s['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary">OK</button>
                </div>
            </form>
            <?php if (canManage()): ?>
                <a class="btn btn-outline-primary w-100 mt-3" href="task_edit.php?id=<?= $id ?>">Изменить задачу</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require 'footer.php'; ?>
