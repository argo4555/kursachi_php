<?php
// Боковое меню. На узком экране сворачивается и открывается кнопкой «Меню»
$page = basename($_SERVER['PHP_SELF']);
$menu = ['index.php' => ['▦', 'Главная'], 'tasks.php' => ['☰', canManage() ? 'Все задачи' : 'Мои задачи']];
if (canManage()) {
    $menu['task_add.php'] = ['+', 'Новая задача'];
    $menu['report.php'] = ['▤', 'Отчёт'];
}
if (isBoss()) {
    $menu['users.php'] = ['☺', 'Сотрудники'];
}
?>
<aside class="sidebar offcanvas-lg offcanvas-start" id="sidebar">
    <div class="brand">
        <span class="logo">УЛ</span>
        <div>Умпа-Лумпа<small>учёт задач команды</small></div>
    </div>
    <nav class="side-nav">
        <?php foreach ($menu as $file => [$icon, $name]): ?>
            <a class="<?= $file === $page ? 'active' : '' ?>" href="<?= $file ?>"><span><?= $icon ?></span><?= $name ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="side-user">
        <b><?= htmlspecialchars($_SESSION['fio']) ?></b>
        <small><?= htmlspecialchars($_SESSION['role_name']) ?></small>
        <a class="btn btn-sm btn-outline-light mt-2" href="logout.php">Выйти</a>
    </div>
</aside>
