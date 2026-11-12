<?php
// Форма карточки животного: общая для animal_add.php и animal_edit.php.
// Перед подключением задать $title, $a, $errors
?>
<h3 class="mb-3"><?= $title ?></h3>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <b>Карточка не сохранена:</b>
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?><li><?= $error ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="panel" method="post" style="max-width: 820px">
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label" for="name">Кличка *</label>
            <input class="form-control" id="name" name="name" maxlength="50" value="<?= htmlspecialchars($a['name']) ?>">
        </div>
        <div class="col-md-4">
            <div class="form-label">Вид *</div>
            <?php foreach ($species as $i => $s): ?>
                <input type="radio" class="btn-check" name="species" id="sp<?= $i ?>" value="<?= $s ?>" <?= $s === $a['species'] ? 'checked' : '' ?>>
                <label class="btn btn-outline-secondary btn-sm" for="sp<?= $i ?>"><?= petIcon($s) ?> <?= $s ?></label>
            <?php endforeach; ?>
        </div>
        <div class="col-md-4">
            <div class="form-label">Пол *</div>
            <?php foreach (['Самец' => '♂', 'Самка' => '♀'] as $s => $icon): ?>
                <input type="radio" class="btn-check" name="sex" id="sex-<?= $icon ?>" value="<?= $s ?>" <?= $s === $a['sex'] ? 'checked' : '' ?>>
                <label class="btn btn-outline-secondary btn-sm" for="sex-<?= $icon ?>"><?= $icon ?> <?= $s ?></label>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label" for="breed">Порода *</label>
            <input class="form-control" id="breed" name="breed" placeholder="метис" value="<?= htmlspecialchars($a['breed']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="color">Окрас *</label>
            <input class="form-control" id="color" name="color" value="<?= htmlspecialchars($a['color']) ?>">
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <label class="form-label" for="birth_date">Дата рождения *</label>
            <input class="form-control" type="date" id="birth_date" name="birth_date" value="<?= htmlspecialchars($a['birth_date']) ?>">
            <div class="form-text">примерная</div>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="arrival_date">Дата поступления *</label>
            <input class="form-control" type="date" id="arrival_date" name="arrival_date" value="<?= htmlspecialchars($a['arrival_date']) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label" for="cage">Вольер *</label>
            <input class="form-control" id="cage" name="cage" placeholder="В-1" value="<?= htmlspecialchars($a['cage']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="status">Статус *</label>
            <select class="form-select" id="status" name="status">
                <?php foreach (['На карантине', 'В приюте', 'На лечении'] as $s): ?>
                    <option <?= $s === $a['status'] ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="sterilized" name="sterilized" <?= $a['sterilized'] ? 'checked' : '' ?>>
        <label class="form-check-label" for="sterilized">Стерилизован(а)</label>
    </div>
    <div class="mb-4">
        <label class="form-label" for="description">Описание и история</label>
        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Где найден, характер, особенности"><?= htmlspecialchars($a['description']) ?></textarea>
    </div>
    <button class="btn btn-accent">Сохранить</button>
    <a class="btn btn-light" href="<?= isset($id) ? "animal_view.php?id=$id" : 'animals.php' ?>">Отмена</a>
</form>
