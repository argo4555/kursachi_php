<?php
// Чтение и проверка полей карточки животного (animal_add.php и animal_edit.php)

function readAnimal()
{
    return [
        'name'         => trim($_POST['name'] ?? ''),
        'species'      => $_POST['species'] ?? '',
        'breed'        => trim($_POST['breed'] ?? ''),
        'sex'          => $_POST['sex'] ?? '',
        'birth_date'   => $_POST['birth_date'] ?? '',
        'color'        => trim($_POST['color'] ?? ''),
        'arrival_date' => $_POST['arrival_date'] ?? '',
        'cage'         => trim($_POST['cage'] ?? ''),
        'status'       => $_POST['status'] ?? '',
        'sterilized'   => isset($_POST['sterilized']) ? 1 : 0,
        'description'  => trim($_POST['description'] ?? ''),
    ];
}

// Проверяет дату в формате ГГГГ-ММ-ДД
function isDate($value)
{
    $date = DateTime::createFromFormat('Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value;
}

// Возвращает массив ошибок; пустой массив — ошибок нет
function checkAnimal($a, $species)
{
    $errors = [];
    if ($a['name'] === '' || mb_strlen($a['name']) > 50) {
        $errors[] = 'Укажите кличку (не длиннее 50 символов)';
    }
    if (!in_array($a['species'], $species, true)) {
        $errors[] = 'Выберите вид животного';
    }
    if (!in_array($a['sex'], ['Самец', 'Самка'], true)) {
        $errors[] = 'Выберите пол';
    }
    if ($a['breed'] === '' || $a['color'] === '') {
        $errors[] = 'Укажите породу и окрас (для беспородных — «беспородный», «метис»)';
    }
    if (!isDate($a['birth_date']) || !isDate($a['arrival_date'])) {
        $errors[] = 'Укажите дату рождения и дату поступления';
    } elseif ($a['arrival_date'] > date('Y-m-d')) {
        $errors[] = 'Дата поступления не может быть в будущем';
    } elseif ($a['birth_date'] > $a['arrival_date']) {
        $errors[] = 'Дата рождения не может быть позже даты поступления';
    }
    if (!in_array($a['status'], ['В приюте', 'На карантине', 'На лечении'], true)) {
        $errors[] = 'Выберите статус';
    }
    if ($a['cage'] === '') {
        $errors[] = 'Укажите номер вольера';
    }
    return $errors;
}
