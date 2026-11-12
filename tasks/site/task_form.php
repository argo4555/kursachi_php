<?php
// Форма задачи: общая для task_add.php и task_edit.php.
// Перед подключением задать $title, $task, $errors, $users, $statuses
?>
<h3 class="mb-3"><?= $title ?></h3>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <b>Задача не сохранена:</b>
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="panel" method="post" style="max-width: 760px">
    <div class="mb-3">
        <label class="form-label" for="title">Название *</label>
        <input class="form-control" id="title" name="title" maxlength="255" value="<?= htmlspecialchars($task['title']) ?>">
    </div>
    <div class="mb-3">
        <label class="form-label" for="description">Описание</label>
        <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($task['description']) ?></textarea>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label" for="executor_id">Исполнитель *</label>
            <select class="form-select" id="executor_id" name="executor_id">
                <option value="0">— выберите сотрудника —</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $u['id'] == $task['executor_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['fio']) ?> (<?= htmlspecialchars($u['role_name']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="deadline">Срок выполнения *</label>
            <input class="form-control" type="datetime-local" id="deadline" name="deadline"
                   value="<?= $task['deadline'] ? date('Y-m-d\TH:i', strtotime($task['deadline'])) : '' ?>">
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="form-label">Приоритет *</div>
            <?php foreach ($priorities as $i => $p): ?>
                <input type="radio" class="btn-check" name="priority" id="p<?= $i ?>" value="<?= $p ?>" <?= $p === $task['priority'] ? 'checked' : '' ?>>
                <label class="btn btn-outline-secondary btn-sm" for="p<?= $i ?>"><span class="prio prio-<?= $i ?>"></span> <?= $p ?></label>
            <?php endforeach; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="status_id">Статус</label>
            <select class="form-select" id="status_id" name="status_id">
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $s['id'] == $task['status_id'] ? 'selected' : '' ?>><?= $s['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <button class="btn btn-primary">Сохранить</button>
    <a class="btn btn-light" href="tasks.php">Отмена</a>
</form>
