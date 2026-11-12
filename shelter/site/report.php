<?php
require 'auth.php';
require 'db.php';
onlyRoles(['admin']);

// Период отчёта: по умолчанию с начала года по сегодня
$from = $_GET['from'] ?? date('Y-01-01');
$to = $_GET['to'] ?? date('Y-m-d');
$error = '';
if (!strtotime($from) || !strtotime($to) || $from > $to) {
    $error = 'Неверно указан период: дата начала должна быть не позже даты окончания';
    $from = date('Y-01-01');
    $to = date('Y-m-d');
}
$period = [$from, $to];

// Поступление и пристройство по видам животных
$stmt = $pdo->prepare("SELECT a.species,
                              SUM(a.arrival_date BETWEEN ? AND ?) AS arrived,
                              SUM(ad.adoption_date BETWEEN ? AND ?) AS adopted,
                              SUM(a.status <> 'Пристроен') AS now_in
                       FROM animals a
                       LEFT JOIN adoptions ad ON ad.animal_id = a.id
                       GROUP BY a.species
                       ORDER BY FIELD(a.species, 'Собака', 'Кошка', 'Другое')");
$stmt->execute([$from, $to, $from, $to]);
$bySpecies = $stmt->fetchAll();

// Средний срок пребывания в приюте до передачи хозяину
$stmt = $pdo->prepare('SELECT ROUND(AVG(DATEDIFF(ad.adoption_date, a.arrival_date))) FROM adoptions ad
                       JOIN animals a ON a.id = ad.animal_id WHERE ad.adoption_date BETWEEN ? AND ?');
$stmt->execute($period);
$avgDays = $stmt->fetchColumn();

// Ветеринарные процедуры за период по видам
$stmt = $pdo->prepare('SELECT type, COUNT(*) AS cnt FROM medical_records WHERE record_date BETWEEN ? AND ?
                       GROUP BY type ORDER BY cnt DESC');
$stmt->execute($period);
$procedures = $stmt->fetchAll();

$arrived = array_sum(array_column($bySpecies, 'arrived'));
$adopted = array_sum(array_column($bySpecies, 'adopted'));
$maxProc = $procedures ? max(array_column($procedures, 'cnt')) : 1;

$title = 'Отчёт';
require 'header.php';
?>
<h3 class="mb-3">Отчёт о работе приюта</h3>

<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<form class="panel row g-2 align-items-end mb-3 no-print" method="get">
    <div class="col-auto">
        <label class="form-label" for="from">С</label>
        <input class="form-control" type="date" id="from" name="from" value="<?= htmlspecialchars($from) ?>">
    </div>
    <div class="col-auto">
        <label class="form-label" for="to">По</label>
        <input class="form-control" type="date" id="to" name="to" value="<?= htmlspecialchars($to) ?>">
    </div>
    <div class="col-auto"><button class="btn btn-teal">Сформировать</button></div>
    <div class="col-auto"><button class="btn btn-light" type="button" onclick="window.print()">Печать</button></div>
</form>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="stat"><span>Поступило за период</span><b><?= $arrived ?></b></div></div>
    <div class="col-md-4"><div class="stat stat-adopted"><span>Обрели дом за период</span><b><?= $adopted ?></b></div></div>
    <div class="col-md-4"><div class="stat stat-quar"><span>Средний срок до передачи</span><b><?= $avgDays !== null ? $avgDays . ' дн.' : '—' ?></b></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="table-responsive panel p-0">
            <table class="table align-middle mb-0 app-table">
                <thead><tr><th>Вид</th><th class="text-center">Поступило</th><th class="text-center">Пристроено</th><th class="text-center">Сейчас в приюте</th></tr></thead>
                <tbody>
                <?php foreach ($bySpecies as $r): ?>
                    <tr>
                        <td><?= petIcon($r['species']) ?> <?= $r['species'] ?></td>
                        <td class="text-center"><?= (int)$r['arrived'] ?></td>
                        <td class="text-center fw-semibold text-teal"><?= (int)$r['adopted'] ?></td>
                        <td class="text-center"><?= (int)$r['now_in'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel">
            <h5 class="mb-3">Ветеринарные процедуры</h5>
            <?php foreach ($procedures as $p): ?>
                <div class="bar-row">
                    <span class="bar-label"><?= $p['type'] ?></span>
                    <div class="bar-track"><div class="bar" style="width: <?= round($p['cnt'] / $maxProc * 100) ?>%"></div></div>
                    <b><?= $p['cnt'] ?></b>
                </div>
            <?php endforeach; ?>
            <?php if (!$procedures): ?><p class="text-muted mb-0">За период процедур не было</p><?php endif; ?>
        </div>
    </div>
</div>
<?php require 'footer.php'; ?>
