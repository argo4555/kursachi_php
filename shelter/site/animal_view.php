<?php
require 'auth.php';
require 'db.php';
require 'animal_check.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM animals WHERE id = ?');
$stmt->execute([$id]);
$a = $stmt->fetch();
if (!$a) {
    header('Location: animals.php');
    exit;
}

$errors = [];
$record = ['record_date' => date('Y-m-d'), 'type' => 'Осмотр', 'description' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && canTreat() && $a['status'] !== 'Пристроен') {
    // Добавление ветеринарной записи
    if (isset($_POST['add_record'])) {
        $record = [
            'record_date' => $_POST['record_date'] ?? '',
            'type'        => $_POST['type'] ?? '',
            'description' => trim($_POST['description'] ?? ''),
        ];
        if (!isDate($record['record_date']) || $record['record_date'] > date('Y-m-d') || $record['record_date'] < $a['arrival_date']) {
            $errors[] = 'Дата записи должна быть не раньше поступления и не позже сегодняшнего дня';
        }
        if (!in_array($record['type'], $recordTypes, true)) {
            $errors[] = 'Выберите вид записи';
        }
        if ($record['description'] === '') {
            $errors[] = 'Опишите процедуру или результат осмотра';
        }
        if (!$errors) {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO medical_records (animal_id, record_date, type, description, vet_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$id, $record['record_date'], $record['type'], $record['description'], $_SESSION['user_id']]);
            // После стерилизации отметка в карточке ставится автоматически
            if ($record['type'] === 'Стерилизация') {
                $pdo->prepare('UPDATE animals SET sterilized = 1 WHERE id = ?')->execute([$id]);
            }
            $pdo->commit();
            header("Location: animal_view.php?id=$id&record=1");
            exit;
        }
    }
    // Смена статуса: карантин, лечение или в приюте
    if (isset($_POST['set_status']) && in_array($_POST['status'], ['В приюте', 'На карантине', 'На лечении'], true)) {
        $pdo->prepare('UPDATE animals SET status = ? WHERE id = ?')->execute([$_POST['status'], $id]);
        header("Location: animal_view.php?id=$id&changed=1");
        exit;
    }
}

// Ветеринарная карта
$stmt = $pdo->prepare('SELECT m.*, u.fio AS vet FROM medical_records m JOIN users u ON u.id = m.vet_id
                       WHERE m.animal_id = ? ORDER BY m.record_date DESC, m.id DESC');
$stmt->execute([$id]);
$records = $stmt->fetchAll();

// Сведения о передаче новому хозяину
$stmt = $pdo->prepare('SELECT ad.*, o.fio, o.phone, o.address, u.fio AS manager
                       FROM adoptions ad JOIN adopters o ON o.id = ad.adopter_id JOIN users u ON u.id = ad.user_id
                       WHERE ad.animal_id = ?');
$stmt->execute([$id]);
$adoption = $stmt->fetch();

$vaccinated = in_array('Вакцинация', array_column($records, 'type'));
$days = (new DateTime($a['arrival_date']))->diff(new DateTime($adoption['adoption_date'] ?? 'today'))->days;

$title = $a['name'];
require 'header.php';
?>
<a class="small" href="animals.php">← К списку животных</a>

<?php if (isset($_GET['saved'])): ?><div class="alert alert-success mt-3">Карточка сохранена</div><?php endif; ?>
<?php if (isset($_GET['record'])): ?><div class="alert alert-success mt-3">Запись добавлена в ветеринарную карту</div><?php endif; ?>
<?php if (isset($_GET['changed'])): ?><div class="alert alert-success mt-3">Статус изменён</div><?php endif; ?>
<?php if (isset($_GET['adopted'])): ?><div class="alert alert-success mt-3">Передача оформлена. <?= htmlspecialchars($a['name']) ?> обрёл(а) дом!</div><?php endif; ?>
<?php if (isset($_GET['error'])): ?><div class="alert alert-danger mt-3">Нельзя удалить карточку пристроенного животного</div><?php endif; ?>

<div class="row g-4 mt-1">
    <div class="col-lg-4">
        <div class="panel text-center">
            <div class="pet-photo big pet-<?= array_search($a['species'], $species) ?>"><?= petIcon($a['species']) ?></div>
            <h3 class="mt-3 mb-1"><?= htmlspecialchars($a['name']) ?></h3>
            <span class="badge-st <?= $statuses[$a['status']] ?>"><?= $a['status'] ?></span>
            <dl class="pet-info text-start mt-4">
                <dt>Вид, пол</dt><dd><?= $a['species'] ?>, <?= mb_strtolower($a['sex']) ?></dd>
                <dt>Порода, окрас</dt><dd><?= htmlspecialchars($a['breed']) ?>, <?= htmlspecialchars($a['color']) ?></dd>
                <dt>Возраст</dt><dd><?= age($a['birth_date']) ?> <span class="text-muted">(род. <?= d($a['birth_date']) ?>)</span></dd>
                <dt>Поступил(а)</dt><dd><?= d($a['arrival_date']) ?> · <?= $days ?> дн. в приюте</dd>
                <?php if ($a['status'] !== 'Пристроен'): ?><dt>Вольер</dt><dd><?= htmlspecialchars($a['cage']) ?></dd><?php endif; ?>
                <dt>Здоровье</dt>
                <dd>
                    <span class="tag <?= $vaccinated ? 'tag-yes' : 'tag-no' ?>"><?= $vaccinated ? '✓ привит(а)' : '✗ не привит(а)' ?></span>
                    <span class="tag <?= $a['sterilized'] ? 'tag-yes' : 'tag-no' ?>"><?= $a['sterilized'] ? '✓ стерилизован(а)' : '✗ не стерилизован(а)' ?></span>
                </dd>
            </dl>
            <?php if (isAdmin() && $a['status'] === 'В приюте'): ?>
                <a class="btn btn-accent w-100 mb-2" href="adoption_add.php?animal=<?= $id ?>">Оформить передачу хозяину</a>
            <?php endif; ?>
            <?php if (isAdmin() && $a['status'] !== 'Пристроен'): ?>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary flex-grow-1" href="animal_edit.php?id=<?= $id ?>">Изменить</a>
                    <form method="post" action="animal_delete.php" onsubmit="return confirm('Удалить карточку и ветеринарную карту?')">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button class="btn btn-outline-danger">Удалить</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-8">
        <?php if ($a['description']): ?>
            <div class="panel mb-4"><h5>История</h5><p class="mb-0"><?= nl2br(htmlspecialchars($a['description'])) ?></p></div>
        <?php endif; ?>

        <?php if ($adoption): ?>
            <div class="panel adopted-box mb-4">
                <h5>🏠 Передан(а) новому хозяину <?= d($adoption['adoption_date']) ?></h5>
                <p class="mb-1"><b><?= htmlspecialchars($adoption['fio']) ?></b></p>
                <?php if (isAdmin()): ?>
                    <p class="mb-1"><?= htmlspecialchars($adoption['phone']) ?> · <?= htmlspecialchars($adoption['address']) ?></p>
                <?php endif; ?>
                <p class="small text-muted mb-0">Оформил(а): <?= htmlspecialchars($adoption['manager']) ?></p>
            </div>
        <?php endif; ?>

        <?php if (canTreat() && $a['status'] !== 'Пристроен'): ?>
            <form class="panel mb-4 d-flex gap-2 align-items-end" method="post">
                <div class="flex-grow-1">
                    <label class="form-label" for="status">Статус животного</label>
                    <select class="form-select" id="status" name="status">
                        <?php foreach (['В приюте', 'На карантине', 'На лечении'] as $s): ?>
                            <option <?= $s === $a['status'] ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-teal" name="set_status">Изменить статус</button>
            </form>
        <?php endif; ?>

        <div class="panel">
            <h5 class="mb-3">Ветеринарная карта</h5>
            <?php if ($errors): ?>
                <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <?php if (canTreat() && $a['status'] !== 'Пристроен'): ?>
                <form class="row g-2 mb-3 record-form" method="post">
                    <div class="col-md-3">
                        <input class="form-control" type="date" name="record_date" value="<?= htmlspecialchars($record['record_date']) ?>" title="Дата">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="type" title="Вид записи">
                            <?php foreach ($recordTypes as $t): ?>
                                <option <?= $t === $record['type'] ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input class="form-control" name="description" placeholder="Что сделано" value="<?= htmlspecialchars($record['description']) ?>">
                    </div>
                    <div class="col-md-2"><button class="btn btn-teal w-100" name="add_record">Добавить</button></div>
                </form>
            <?php endif; ?>
            <?php foreach ($records as $r): ?>
                <div class="record">
                    <span class="record-type rt-<?= array_search($r['type'], $recordTypes) ?>"><?= $r['type'] ?></span>
                    <div class="flex-grow-1">
                        <?= htmlspecialchars($r['description']) ?>
                        <small class="d-block text-muted"><?= d($r['record_date']) ?> · <?= htmlspecialchars($r['vet']) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!$records): ?><p class="text-muted mb-0">Записей пока нет</p><?php endif; ?>
        </div>
    </div>
</div>
<?php require 'footer.php'; ?>
