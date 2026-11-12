<?php
require 'auth.php';
require 'db.php';
require 'animal_check.php';
onlyRoles(['admin']);

$errors = [];
// Новое животное по умолчанию попадает на карантин
$a = ['name' => '', 'species' => '', 'breed' => '', 'sex' => '', 'birth_date' => '', 'color' => '',
      'arrival_date' => date('Y-m-d'), 'cage' => '', 'status' => 'На карантине', 'sterilized' => 0, 'description' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = readAnimal();
    $errors = checkAnimal($a, $species);

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO animals (name, species, breed, sex, birth_date, color, arrival_date, cage, status, sterilized, description)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$a['name'], $a['species'], $a['breed'], $a['sex'], $a['birth_date'], $a['color'],
                        $a['arrival_date'], $a['cage'], $a['status'], $a['sterilized'], $a['description']]);
        header('Location: animal_view.php?id=' . $pdo->lastInsertId() . '&saved=1');
        exit;
    }
}

$title = 'Поступление животного';
require 'header.php';
require 'animal_form.php';
require 'footer.php';
