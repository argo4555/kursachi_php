<?php
require 'auth.php';
require 'db.php';
onlyRoles(['admin']);

// Удаление ошибочно заведённой карточки. Пристроенных животных удалить нельзя:
// на них ссылается запись о передаче (внешний ключ ON DELETE RESTRICT).
// Ветеринарные записи удаляются вместе с карточкой (ON DELETE CASCADE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM adoptions WHERE animal_id = ?');
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        header("Location: animal_view.php?id=$id&error=adopted");
        exit;
    }
    $pdo->prepare('DELETE FROM animals WHERE id = ?')->execute([$id]);
    header('Location: animals.php?deleted=1');
    exit;
}
header('Location: animals.php');
