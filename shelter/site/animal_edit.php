<?php
require 'auth.php';
require 'db.php';
require 'animal_check.php';
onlyRoles(['admin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM animals WHERE id = ?');
$stmt->execute([$id]);
$a = $stmt->fetch();
// Карточку пристроенного животного не изменяют: она остаётся в архиве
if (!$a || $a['status'] === 'Пристроен') {
    header("Location: animal_view.php?id=$id");
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = readAnimal();
    $errors = checkAnimal($a, $species);

    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE animals SET name = ?, species = ?, breed = ?, sex = ?, birth_date = ?, color = ?,
                               arrival_date = ?, cage = ?, status = ?, sterilized = ?, description = ? WHERE id = ?');
        $stmt->execute([$a['name'], $a['species'], $a['breed'], $a['sex'], $a['birth_date'], $a['color'],
                        $a['arrival_date'], $a['cage'], $a['status'], $a['sterilized'], $a['description'], $id]);
        header("Location: animal_view.php?id=$id&saved=1");
        exit;
    }
}

$title = 'Изменение карточки: ' . htmlspecialchars($a['name']);
require 'header.php';
require 'animal_form.php';
require 'footer.php';
