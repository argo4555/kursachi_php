<?php
// Карточка животного для списков. Перед подключением задать $a (строка из таблицы animals)
?>
<a class="pet-card" href="animal_view.php?id=<?= $a['id'] ?>">
    <div class="pet-photo pet-<?= array_search($a['species'], $species) ?>"><?= petIcon($a['species']) ?></div>
    <div class="pet-body">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <h5><?= htmlspecialchars($a['name']) ?></h5>
            <span class="badge-st <?= $statuses[$a['status']] ?>"><?= $a['status'] ?></span>
        </div>
        <div class="pet-meta"><?= $a['species'] ?> · <?= $a['sex'] === 'Самец' ? '♂' : '♀' ?> <?= mb_strtolower($a['sex']) ?> · <?= age($a['birth_date']) ?></div>
        <div class="pet-meta text-truncate"><?= htmlspecialchars($a['breed']) ?>, <?= htmlspecialchars($a['color']) ?></div>
        <div class="pet-foot">
            <?php if ($a['status'] === 'Пристроен'): ?>
                <span>Обрёл дом</span>
            <?php else: ?>
                <span>Вольер <?= htmlspecialchars($a['cage']) ?></span>
                <span><?= (new DateTime($a['arrival_date']))->diff(new DateTime('today'))->days ?> дн. в приюте</span>
            <?php endif; ?>
        </div>
    </div>
</a>
