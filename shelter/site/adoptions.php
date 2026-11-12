<?php
require 'auth.php';
require 'db.php';

// Журнал передачи животных новым хозяевам за период (по умолчанию — с начала года)
$from = $_GET['from'] ?? date('Y-01-01');
$to = $_GET['to'] ?? date('Y-m-d');
if (!strtotime($from) || !strtotime($to) || $from > $to) {
    $from = date('Y-01-01');
    $to = date('Y-m-d');
}

$stmt = $pdo->prepare("SELECT ad.adoption_date, ad.note, a.id, a.name, a.species, a.arrival_date,
                              o.fio, o.phone, u.fio AS manager,
                              DATEDIFF(ad.adoption_date, a.arrival_date) AS days
                       FROM adoptions ad
                       JOIN animals a ON a.id = ad.animal_id
                       JOIN adopters o ON o.id = ad.adopter_id
                       JOIN users u ON u.id = ad.user_id
                       WHERE ad.adoption_date BETWEEN ? AND ?
                       ORDER BY ad.adoption_date DESC");
$stmt->execute([$from, $to]);
$rows = $stmt->fetchAll();

$title = 'Передачи';
require 'header.php';
?>
<h3 class="mb-3">Журнал передачи животных</h3>

<form class="panel row g-2 align-items-end mb-3" method="get">
    <div class="col-auto">
        <label class="form-label" for="from">С</label>
        <input class="form-control" type="date" id="from" name="from" value="<?= htmlspecialchars($from) ?>">
    </div>
    <div class="col-auto">
        <label class="form-label" for="to">По</label>
        <input class="form-control" type="date" id="to" name="to" value="<?= htmlspecialchars($to) ?>">
    </div>
    <div class="col-auto"><button class="btn btn-teal">Показать</button></div>
</form>

<div class="table-responsive panel p-0">
    <table class="table align-middle mb-0 app-table">
        <thead><tr><th>Дата</th><th>Животное</th><th>Новый хозяин</th><th class="text-center">Дней в приюте</th><th>Оформил(а)</th><th>Примечание</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td class="text-nowrap"><?= d($r['adoption_date']) ?></td>
                <td class="text-nowrap"><?= petIcon($r['species']) ?> <a href="animal_view.php?id=<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></a></td>
                <td><?= htmlspecialchars($r['fio']) ?><?php if (isAdmin()): ?><small class="d-block text-muted"><?= htmlspecialchars($r['phone']) ?></small><?php endif; ?></td>
                <td class="text-center"><?= $r['days'] ?></td>
                <td><?= htmlspecialchars($r['manager']) ?></td>
                <td class="small text-muted"><?= htmlspecialchars($r['note']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="6" class="text-center text-muted py-4">За период передач не было</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<p class="text-muted small mt-2">Передано животных: <?= count($rows) ?></p>
<?php require 'footer.php'; ?>
