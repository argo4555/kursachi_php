<?php
require 'auth.php';
require 'db.php';

// Количество животных по статусам
$counts = $pdo->query('SELECT status, COUNT(*) AS cnt FROM animals GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
$inShelter = ($counts['В приюте'] ?? 0) + ($counts['На карантине'] ?? 0) + ($counts['На лечении'] ?? 0);

// Пристроено за последние 30 дней
$adopted30 = $pdo->query('SELECT COUNT(*) FROM adoptions WHERE adoption_date >= CURDATE() - INTERVAL 30 DAY')->fetchColumn();

// Недавно поступившие и животные, которые дольше всех ждут хозяина
$recent = $pdo->query("SELECT * FROM animals WHERE status <> 'Пристроен' ORDER BY arrival_date DESC LIMIT 4")->fetchAll();
$longest = $pdo->query("SELECT * FROM animals WHERE status = 'В приюте' ORDER BY arrival_date LIMIT 4")->fetchAll();

$title = 'Главная';
require 'header.php';
?>
<h3 class="mb-4">Здравствуйте, <?= htmlspecialchars(explode(' ', $_SESSION['fio'])[1] ?? '') ?>!</h3>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="stat"><span>Сейчас в приюте</span><b><?= $inShelter ?></b></div></div>
    <div class="col-6 col-lg-3"><div class="stat stat-quar"><span>На карантине</span><b><?= $counts['На карантине'] ?? 0 ?></b></div></div>
    <div class="col-6 col-lg-3"><div class="stat stat-treat"><span>На лечении</span><b><?= $counts['На лечении'] ?? 0 ?></b></div></div>
    <div class="col-6 col-lg-3"><div class="stat stat-adopted"><span>Обрели дом за 30 дней</span><b><?= $adopted30 ?></b></div></div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0">Недавно поступили</h5>
    <a href="animals.php" class="small">Все животные →</a>
</div>
<div class="pet-grid mb-4">
    <?php foreach ($recent as $a) { require 'animal_card.php'; } ?>
</div>

<h5 class="mb-2">Дольше всех ждут хозяина</h5>
<div class="pet-grid">
    <?php foreach ($longest as $a) { require 'animal_card.php'; } ?>
</div>
<?php require 'footer.php'; ?>
