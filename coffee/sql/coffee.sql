-- База данных учёта продаж кофейни «Зерно»
-- Выполнить в phpMyAdmin: вкладка «Импорт» или «SQL»

CREATE DATABASE IF NOT EXISTS coffee CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE coffee;

DROP TABLE IF EXISTS sale_items;
DROP TABLE IF EXISTS sales;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

-- Сотрудники: управляющий (admin) и бариста (barista)
CREATE TABLE users (
    id_user  INT AUTO_INCREMENT PRIMARY KEY,
    login    VARCHAR(50)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fio      VARCHAR(100) NOT NULL,
    role     ENUM('admin', 'barista') NOT NULL DEFAULT 'barista'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Товары меню
CREATE TABLE products (
    id_product INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    category   ENUM('Кофе', 'Чай', 'Напитки', 'Выпечка', 'Десерты') NOT NULL,
    volume     VARCHAR(20)  NOT NULL,
    price      DECIMAL(8,2) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Продажи (чеки)
CREATE TABLE sales (
    id_sale   INT AUTO_INCREMENT PRIMARY KEY,
    date_sale DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_user   INT NOT NULL,
    payment   ENUM('Наличные', 'Карта', 'СБП') NOT NULL,
    total     DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users (id_user)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Позиции чека
CREATE TABLE sale_items (
    id_item    INT AUTO_INCREMENT PRIMARY KEY,
    id_sale    INT NOT NULL,
    id_product INT NOT NULL,
    quantity   INT NOT NULL,
    price      DECIMAL(8,2) NOT NULL,
    FOREIGN KEY (id_sale) REFERENCES sales (id_sale) ON DELETE CASCADE,
    FOREIGN KEY (id_product) REFERENCES products (id_product)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Пароли: admin — 1234, barista — 1111, barista2 — 2222
INSERT INTO users (login, password, fio, role) VALUES
('admin', '$2y$10$MVYx14AC5JFShiA77P2eTOqdSrkGn2TbzO1O/V8r87OkOpBwIaTQa', 'Ковалёва Марина Сергеевна', 'admin'),
('barista', '$2y$10$6JZjAlJ5U9E.ce70EMHViuuflqOXTJ1TALR0hb2iXUaNbpp6KU6xG', 'Лебедев Артём Игоревич', 'barista'),
('barista2', '$2y$10$8wa77jd9gqO3JFrtS.pA/eFx/B7vZSzi/u/35fAIO0LoHbZFDDtaG', 'Никитина Полина Андреевна', 'barista');

INSERT INTO products (name, category, volume, price) VALUES
('Эспрессо', 'Кофе', '40 мл', 140),
('Американо', 'Кофе', '300 мл', 180),
('Капучино', 'Кофе', '300 мл', 230),
('Латте', 'Кофе', '400 мл', 250),
('Раф ванильный', 'Кофе', '300 мл', 280),
('Флэт уайт', 'Кофе', '250 мл', 240),
('Чай чёрный с чабрецом', 'Чай', '400 мл', 170),
('Чай зелёный жасминовый', 'Чай', '400 мл', 170),
('Какао с маршмеллоу', 'Напитки', '300 мл', 220),
('Лимонад клубничный', 'Напитки', '400 мл', 210),
('Круассан классический', 'Выпечка', '80 г', 150),
('Синнабон', 'Выпечка', '120 г', 190),
('Маффин шоколадный', 'Выпечка', '100 г', 160),
('Чизкейк Нью-Йорк', 'Десерты', '130 г', 260),
('Тирамису', 'Десерты', '120 г', 280),
('Макарон, 3 шт.', 'Десерты', '45 г', 210);

-- Продажи за последние 7 дней (даты считаются от дня импорта)
INSERT INTO sales (date_sale, id_user, payment, total) VALUES
(TIMESTAMP(CURDATE() - INTERVAL 6 DAY, '12:40:00'), 2, 'Наличные', 1140),
(TIMESTAMP(CURDATE() - INTERVAL 6 DAY, '15:28:00'), 3, 'Наличные', 180),
(TIMESTAMP(CURDATE() - INTERVAL 6 DAY, '15:35:00'), 3, 'СБП', 530),
(TIMESTAMP(CURDATE() - INTERVAL 5 DAY, '09:03:00'), 2, 'СБП', 470),
(TIMESTAMP(CURDATE() - INTERVAL 5 DAY, '09:17:00'), 1, 'Карта', 500),
(TIMESTAMP(CURDATE() - INTERVAL 5 DAY, '13:01:00'), 3, 'СБП', 500),
(TIMESTAMP(CURDATE() - INTERVAL 5 DAY, '17:27:00'), 1, 'Наличные', 560),
(TIMESTAMP(CURDATE() - INTERVAL 5 DAY, '18:44:00'), 1, 'Карта', 190),
(TIMESTAMP(CURDATE() - INTERVAL 4 DAY, '09:45:00'), 3, 'Карта', 520),
(TIMESTAMP(CURDATE() - INTERVAL 4 DAY, '11:23:00'), 3, 'СБП', 720),
(TIMESTAMP(CURDATE() - INTERVAL 4 DAY, '15:07:00'), 1, 'СБП', 280),
(TIMESTAMP(CURDATE() - INTERVAL 3 DAY, '10:55:00'), 3, 'Карта', 620),
(TIMESTAMP(CURDATE() - INTERVAL 3 DAY, '11:51:00'), 3, 'СБП', 280),
(TIMESTAMP(CURDATE() - INTERVAL 3 DAY, '15:14:00'), 2, 'Карта', 560),
(TIMESTAMP(CURDATE() - INTERVAL 3 DAY, '18:16:00'), 3, 'СБП', 710),
(TIMESTAMP(CURDATE() - INTERVAL 2 DAY, '08:00:00'), 2, 'Карта', 440),
(TIMESTAMP(CURDATE() - INTERVAL 2 DAY, '14:39:00'), 2, 'Карта', 500),
(TIMESTAMP(CURDATE() - INTERVAL 2 DAY, '16:38:00'), 1, 'Наличные', 690),
(TIMESTAMP(CURDATE() - INTERVAL 1 DAY, '09:52:00'), 2, 'Карта', 250),
(TIMESTAMP(CURDATE() - INTERVAL 1 DAY, '12:19:00'), 3, 'Наличные', 480),
(TIMESTAMP(CURDATE() - INTERVAL 1 DAY, '15:28:00'), 2, 'СБП', 250),
(TIMESTAMP(CURDATE() - INTERVAL 1 DAY, '16:52:00'), 3, 'Карта', 680),
(TIMESTAMP(CURDATE() - INTERVAL 1 DAY, '18:16:00'), 1, 'Карта', 560),
(TIMESTAMP(CURDATE() - INTERVAL 0 DAY, '08:28:00'), 2, 'Карта', 630),
(TIMESTAMP(CURDATE() - INTERVAL 0 DAY, '08:35:00'), 2, 'Карта', 230),
(TIMESTAMP(CURDATE() - INTERVAL 0 DAY, '10:06:00'), 3, 'Карта', 280);

INSERT INTO sale_items (id_sale, id_product, quantity, price) VALUES
(1, 2, 2, 180),
(1, 3, 2, 230),
(1, 13, 2, 160),
(2, 2, 1, 180),
(3, 4, 1, 250),
(3, 5, 1, 280),
(4, 3, 1, 230),
(4, 6, 1, 240),
(5, 4, 2, 250),
(6, 4, 2, 250),
(7, 13, 2, 160),
(7, 6, 1, 240),
(8, 12, 1, 190),
(9, 9, 1, 220),
(9, 11, 2, 150),
(10, 5, 2, 280),
(10, 13, 1, 160),
(11, 1, 2, 140),
(12, 12, 2, 190),
(12, 6, 1, 240),
(13, 5, 1, 280),
(14, 15, 2, 280),
(15, 3, 2, 230),
(15, 4, 1, 250),
(16, 2, 1, 180),
(16, 14, 1, 260),
(17, 9, 1, 220),
(17, 15, 1, 280),
(18, 14, 2, 260),
(18, 7, 1, 170),
(19, 4, 1, 250),
(20, 3, 1, 230),
(20, 4, 1, 250),
(21, 4, 1, 250),
(22, 1, 2, 140),
(22, 11, 1, 150),
(22, 4, 1, 250),
(23, 5, 2, 280),
(24, 3, 2, 230),
(24, 8, 1, 170),
(25, 3, 1, 230),
(26, 5, 1, 280);
