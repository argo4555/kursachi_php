<?php
require 'auth.php';
require 'db.php';
require 'animal_check.php';
onlyRoles(['admin']);

// Передать можно только здоровое животное со статусом «В приюте»
$id = (int)($_GET['animal'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM animals WHERE id = ? AND status = 'В приюте'");
$stmt->execute([$id]);
$a = $stmt->fetch();
if (!$a) {
    header("Location: animal_view.php?id=$id");
    exit;
}

$adopters = $pdo->query('SELECT id, fio, phone FROM adopters ORDER BY fio')->fetchAll();
$errors = [];
$f = ['adopter_id' => 0, 'fio' => '', 'phone' => '', 'address' => '', 'adoption_date' => date('Y-m-d'), 'note' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = [
        'adopter_id'    => (int)($_POST['adopter_id'] ?? 0),
        'fio'           => trim($_POST['fio'] ?? ''),
        'phone'         => trim($_POST['phone'] ?? ''),
        'address'       => trim($_POST['address'] ?? ''),
        'adoption_date' => $_POST['adoption_date'] ?? '',
        'note'          => trim($_POST['note'] ?? ''),
    ];
    // Хозяин выбирается из списка или вводится новый
    if ($f['adopter_id'] > 0) {
        if (!in_array($f['adopter_id'], array_column($adopters, 'id'))) {
            $errors[] = 'Выбранный хозяин не найден';
        }
    } else {
        if (count(preg_split('/\s+/u', $f['fio'])) < 2) {
            $errors[] = 'Укажите фамилию и имя нового хозяина';
        }
        if (!preg_match('/^\+?[0-9()\- ]{10,20}$/', $f['phone'])) {
            $errors[] = 'Телефон указан неверно, пример: +7 (916) 123-45-67';
        }
        if ($f['address'] === '') {
            $errors[] = 'Укажите адрес проживания';
        }
    }
    if (!isDate($f['adoption_date']) || $f['adoption_date'] > date('Y-m-d') || $f['adoption_date'] < $a['arrival_date']) {
        $errors[] = 'Дата передачи должна быть не раньше поступления и не позже сегодняшнего дня';
    }

    if (!$errors) {
        // Хозяин, запись о передаче и новый статус животного сохраняются в одной транзакции
        $pdo->beginTransaction();
        $adopterId = $f['adopter_id'];
        if ($adopterId === 0) {
            $stmt = $pdo->prepare('INSERT INTO adopters (fio, phone, address) VALUES (?, ?, ?)');
            $stmt->execute([$f['fio'], $f['phone'], $f['address']]);
            $adopterId = $pdo->lastInsertId();
        }
        $stmt = $pdo->prepare('INSERT INTO adoptions (animal_id, adopter_id, adoption_date, user_id, note) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$id, $adopterId, $f['adoption_date'], $_SESSION['user_id'], $f['note']]);
        $pdo->prepare("UPDATE animals SET status = 'Пристроен' WHERE id = ?")->execute([$id]);
        $pdo->commit();

        header("Location: animal_view.php?id=$id&adopted=1");
        exit;
    }
}

$title = 'Передача животного';
require 'header.php';
?>
<h3 class="mb-3">Передача животного новому хозяину</h3>

<?php if ($errors): ?>
    <div class="alert alert-danger"><b>Передача не оформлена:</b><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="panel text-center">
            <div class="pet-photo big pet-<?= array_search($a['species'], $species) ?>"><?= petIcon($a['species']) ?></div>
            <h4 class="mt-3 mb-1"><?= htmlspecialchars($a['name']) ?></h4>
            <p class="text-muted mb-0"><?= $a['species'] ?>, <?= mb_strtolower($a['sex']) ?>, <?= age($a['birth_date']) ?></p>
        </div>
    </div>
    <div class="col-lg-8">
        <form class="panel" method="post">
            <div class="mb-3">
                <label class="form-label" for="adopter_id">Хозяин</label>
                <select class="form-select" id="adopter_id" name="adopter_id">
                    <option value="0">— новый хозяин (заполнить ниже) —</option>
                    <?php foreach ($adopters as $o): ?>
                        <option value="<?= $o['id'] ?>" <?= $o['id'] == $f['adopter_id'] ? 'selected' : '' ?>><?= htmlspecialchars($o['fio']) ?>, <?= htmlspecialchars($o['phone']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <fieldset class="new-owner mb-3" id="new-owner">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="fio">ФИО *</label>
                        <input class="form-control" id="fio" name="fio" value="<?= htmlspecialchars($f['fio']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Телефон *</label>
                        <input class="form-control" id="phone" name="phone" placeholder="+7 (916) 123-45-67" value="<?= htmlspecialchars($f['phone']) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="address">Адрес *</label>
                        <input class="form-control" id="address" name="address" value="<?= htmlspecialchars($f['address']) ?>">
                    </div>
                </div>
            </fieldset>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label" for="adoption_date">Дата передачи *</label>
                    <input class="form-control" type="date" id="adoption_date" name="adoption_date" value="<?= htmlspecialchars($f['adoption_date']) ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="note">Примечание</label>
                    <input class="form-control" id="note" name="note" placeholder="Например: договор № 15, визит через месяц" value="<?= htmlspecialchars($f['note']) ?>">
                </div>
            </div>
            <button class="btn btn-accent">Оформить передачу</button>
            <a class="btn btn-light" href="animal_view.php?id=<?= $id ?>">Отмена</a>
        </form>
    </div>
</div>

<script>
// Поля нового хозяина скрываются, если хозяин выбран из списка
const select = document.getElementById('adopter_id');
function toggleOwner() {
    document.getElementById('new-owner').hidden = select.value !== '0';
}
select.addEventListener('change', toggleOwner);
toggleOwner();
</script>
<?php require 'footer.php'; ?>
