<?php
// Выводит хеш пароля для вставки в таблицу users: открыть hash.php?p=пароль
echo password_hash($_GET['p'] ?? '1234', PASSWORD_DEFAULT);
