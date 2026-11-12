<?php
// Проверка данных продажи.
// $qty — количество по каждому товару (номер товара => количество), $prices — цены товаров из базы.
// Возвращает текст ошибки или пустую строку, если ошибок нет
function checkSale($qty, $payment, $prices, $payments)
{
    $items = 0;
    foreach ($qty as $id => $q) {
        $q = trim($q);
        if ($q === '') {
            continue;
        }
        if (!ctype_digit($q) || $q > 20) {
            return 'Количество товара должно быть целым числом от 0 до 20';
        }
        if ((int)$q === 0) {
            continue;
        }
        if (!isset($prices[$id])) {
            return 'Товар не найден в меню';
        }
        $items++;
    }
    if ($items === 0) {
        return 'Добавьте в чек хотя бы один товар';
    }
    if (!in_array($payment, $payments, true)) {
        return 'Выберите способ оплаты';
    }
    return '';
}
