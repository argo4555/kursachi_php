<?php
require 'auth.php';
require 'db.php';

// Параметры отбора. Статус по умолчанию — все, кто сейчас в приюте
$q = trim($_GET['q'] ?? '');
$kind = $_GET['species'] ?? '';
$status = $_GET['status'] ?? 'now';
$sex = $_GET['sex'] ?? '';

$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(name LIKE ? OR breed LIKE ? OR color LIKE ?)';
    array_push($params, "%$q%", "%$q%", "%$q%");
}
if (in_array($kind, $species, true)) {
    $where[] = 'species = ?';
    $params[] = $kind;
}
if ($status === 'now') {
    $where[] = "status <> 'Пристроен'";
} elseif (isset($statuses[$status])) {
    $where[] = 'status = ?';
    $params[] = $status;
}
if (in_array($sex, ['Самец', 'Самка'], true)) {
    $where[] = 'sex = ?';
    $params[] = $sex;
}

$sql = 'SELECT * FROM animals';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY arrival_date DESC, id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$animals = $stmt->fetchAll();

$title = 'Животные';
require 'header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Животные</h3>
    <?php if (isAdmin()): ?>
        <a class="btn btn-accent" href="animal_add.php">+ Поступление</a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Карточка животного удалена</div>
<?php endif; ?>

<form class="panel row g-2 align-items-end mb-3" method="get">
    <div class="col-md-3">
        <label class="form-label" for="q">Поиск</label>
        <input class="form-control" id="q" name="q" placeholder="Кличка, порода, окрас" value="<?= htmlspecialchars($q) ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label" for="species">Вид</label>
        <select class="form-select" id="species" name="species">
            <option value="">Все</option>
            <?php foreach ($species as $s): ?>
                <option <?= $s === $kind ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="status">Статус</label>
        <select class="form-select" id="status" name="status">
            <option value="now" <?= $status === 'now' ? 'selected' : '' ?>>Сейчас в приюте</option>
            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Все, включая пристроенных</option>
            <?php foreach ($statuses as $name => $class): ?>
                <option <?= $name === $status ? 'selected' : '' ?>><?= $name ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="sex">Пол</label>
        <select class="form-select" id="sex" name="sex">
            <option value="">Любой</option>
            <option <?= $sex === 'Самец' ? 'selected' : '' ?>>Самец</option>
            <option <?= $sex === 'Самка' ? 'selected' : '' ?>>Самка</option>
        </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-teal flex-grow-1">Найти</button>
        <a class="btn btn-light" href="animals.php" title="Сбросить">✕</a>
    </div>
</form>

<p class="text-muted small">Найдено: <?= count($animals) ?></p>
<div class="pet-grid">
    <?php foreach ($animals as $a) { require 'animal_card.php'; } ?>
</div>
<?php if (!$animals): ?>
    <div class="panel text-center text-muted py-5">Животных не найдено</div>
<?php endif; ?>
<?php require 'footer.php'; ?>
