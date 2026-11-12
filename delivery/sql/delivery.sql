-- База данных службы доставки еды «ЕдаРядом»
-- Выполнить в phpMyAdmin: вкладка «Импорт» или «SQL»

CREATE DATABASE IF NOT EXISTS delivery CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE delivery;

DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS dishes;
DROP TABLE IF EXISTS couriers;
DROP TABLE IF EXISTS users;

-- Сотрудники, которые входят в приложение
CREATE TABLE users (
    id_user  INT AUTO_INCREMENT PRIMARY KEY,
    login    VARCHAR(50)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fio      VARCHAR(100) NOT NULL,
    role     ENUM('admin', 'operator') NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Блюда (меню)
CREATE TABLE dishes (
    id_dish  INT AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(100) NOT NULL,
    category VARCHAR(50)  NOT NULL,
    price    DECIMAL(8,2) NOT NULL,
    weight   INT          NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Курьеры
CREATE TABLE couriers (
    id_courier INT AUTO_INCREMENT PRIMARY KEY,
    fio        VARCHAR(100) NOT NULL,
    phone      VARCHAR(20)  NOT NULL,
    transport  ENUM('Пеший', 'Велосипед', 'Автомобиль') NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Заказы
CREATE TABLE orders (
    id_order    INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    phone       VARCHAR(20)  NOT NULL,
    address     VARCHAR(200) NOT NULL,
    id_dish     INT          NOT NULL,
    quantity    INT          NOT NULL DEFAULT 1,
    id_courier  INT          NULL,
    date_create DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status      ENUM('Новый', 'Готовится', 'В пути', 'Доставлен', 'Отменён')
                NOT NULL DEFAULT 'Новый',
    FOREIGN KEY (id_dish)    REFERENCES dishes(id_dish),
    FOREIGN KEY (id_courier) REFERENCES couriers(id_courier)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Пароли: admin / 1234, operator / 1111 (хранятся в виде хеша password_hash)
INSERT INTO users (login, password, fio, role) VALUES
('admin',    '$2y$10$iUQ2/o/uoGK3F5Dit9/Nbu1IOHuCerPK5LCe2sJdIYAEuFV.qcliy', 'Соколова Анна Викторовна', 'admin'),
('operator', '$2y$10$ag2A7xtZG8t5wECSOkbU8edz.QjsIA9G5LABb7C0/hfI/W0SOeMNu', 'Морозов Денис Олегович',   'operator');

INSERT INTO dishes (name, category, price, weight) VALUES
('Пицца «Маргарита»',        'Пицца',   549.00, 450),
('Пицца «Пепперони»',        'Пицца',   629.00, 470),
('Ролл «Филадельфия»',       'Роллы',   489.00, 250),
('Ролл «Калифорния»',        'Роллы',   419.00, 240),
('Бургер классический',      'Бургеры', 359.00, 320),
('Чизбургер двойной',        'Бургеры', 429.00, 380),
('Салат «Цезарь» с курицей', 'Салаты',  389.00, 260),
('Борщ со сметаной',         'Супы',    299.00, 350),
('Паста «Карбонара»',        'Паста',   459.00, 330),
('Картофель фри',            'Закуски', 179.00, 150),
('Наггетсы куриные',         'Закуски', 249.00, 180),
('Морс клюквенный 0,5 л',    'Напитки', 149.00, 500);

INSERT INTO couriers (fio, phone, transport) VALUES
('Ахмедов Руслан Тимурович',  '+7 916 214-55-01', 'Автомобиль'),
('Белов Никита Андреевич',    '+7 925 318-40-12', 'Велосипед'),
('Григорьева Ольга Игоревна', '+7 903 771-23-45', 'Пеший'),
('Ким Артём Сергеевич',       '+7 977 102-66-30', 'Автомобиль'),
('Лаптев Иван Павлович',      '+7 926 455-09-87', 'Велосипед');

INSERT INTO orders (client_name, phone, address, id_dish, quantity, id_courier, date_create, status) VALUES
('Иванова Мария',     '+7 915 100-20-30', 'ул. Ленина, д. 12, кв. 45',         1, 2, 1,    NOW() - INTERVAL 3 DAY,    'Доставлен'),
('Петров Сергей',     '+7 926 222-33-44', 'пр. Мира, д. 5, кв. 8',             3, 1, 2,    NOW() - INTERVAL 3 DAY,    'Доставлен'),
('Смирнова Елена',    '+7 903 555-66-77', 'ул. Садовая, д. 21, кв. 102',       5, 3, 3,    NOW() - INTERVAL 2 DAY,    'Доставлен'),
('Кузнецов Алексей',  '+7 916 888-11-22', 'ул. Гагарина, д. 7, кв. 15',        2, 1, 4,    NOW() - INTERVAL 2 DAY,    'Доставлен'),
('Попова Анастасия',  '+7 925 444-55-66', 'ул. Школьная, д. 3, кв. 60',        7, 2, 5,    NOW() - INTERVAL 2 DAY,    'Отменён'),
('Васильев Дмитрий',  '+7 977 333-22-11', 'Цветной бул., д. 15, кв. 4',        9, 1, 1,    NOW() - INTERVAL 1 DAY,    'Доставлен'),
('Новикова Ирина',    '+7 903 777-88-99', 'ул. Пушкина, д. 10, кв. 33',        4, 2, 2,    NOW() - INTERVAL 1 DAY,    'Доставлен'),
('Фёдоров Павел',     '+7 916 121-34-56', 'ул. Молодёжная, д. 2, кв. 71',      6, 2, 4,    NOW() - INTERVAL 1 DAY,    'Доставлен'),
('Морозова Ксения',   '+7 926 909-80-70', 'ул. Лесная, д. 18, кв. 9',          8, 3, 3,    NOW() - INTERVAL 20 HOUR,  'Доставлен'),
('Волков Артур',      '+7 925 654-32-10', 'пр. Победы, д. 44, кв. 120',       10, 4, 5,    NOW() - INTERVAL 6 HOUR,   'Доставлен'),
('Алексеева Дарья',   '+7 903 246-80-24', 'ул. Советская, д. 9, кв. 27',       1, 1, 1,    NOW() - INTERVAL 3 HOUR,   'В пути'),
('Лебедев Михаил',    '+7 977 135-79-13', 'ул. Заводская, д. 31, кв. 5',      11, 2, 2,    NOW() - INTERVAL 2 HOUR,   'В пути'),
('Семёнова Ольга',    '+7 916 864-20-86', 'ул. Береговая, д. 6, кв. 88',       3, 2, 4,    NOW() - INTERVAL 90 MINUTE, 'Готовится'),
('Егоров Константин', '+7 926 975-31-97', 'ул. Полевая, д. 14, кв. 3',         2, 1, NULL, NOW() - INTERVAL 60 MINUTE, 'Готовится'),
('Павлова Виктория',  '+7 925 112-23-34', 'ул. Космонавтов, д. 25, кв. 54',    5, 2, NULL, NOW() - INTERVAL 30 MINUTE, 'Новый'),
('Козлов Андрей',     '+7 903 445-56-67', 'ул. Северная, д. 8, кв. 19',       12, 3, NULL, NOW() - INTERVAL 15 MINUTE, 'Новый'),
('Степанова Юлия',    '+7 977 778-89-90', 'ул. Южная, д. 40, кв. 66',          9, 2, NULL, NOW() - INTERVAL 5 MINUTE,  'Новый');
