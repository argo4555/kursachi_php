-- База данных учёта животных приюта «Лапа помощи»
-- Выполнить в phpMyAdmin: вкладка «Импорт» или «SQL»

CREATE DATABASE IF NOT EXISTS shelter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shelter;
SET NAMES utf8mb4;

DROP TABLE IF EXISTS adoptions;
DROP TABLE IF EXISTS adopters;
DROP TABLE IF EXISTS medical_records;
DROP TABLE IF EXISTS animals;
DROP TABLE IF EXISTS users;

-- Сотрудники приюта
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    login         VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    fio           VARCHAR(100) NOT NULL,
    role          ENUM('admin', 'vet', 'volunteer') NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Животные
CREATE TABLE animals (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(50)  NOT NULL,
    species      ENUM('Собака', 'Кошка', 'Другое') NOT NULL,
    breed        VARCHAR(100) NOT NULL,
    sex          ENUM('Самец', 'Самка') NOT NULL,
    birth_date   DATE NOT NULL,
    color        VARCHAR(50)  NOT NULL,
    arrival_date DATE NOT NULL,
    cage         VARCHAR(10)  NOT NULL,
    status       ENUM('На карантине', 'В приюте', 'На лечении', 'Пристроен') NOT NULL DEFAULT 'На карантине',
    sterilized   TINYINT(1)   NOT NULL DEFAULT 0,
    description  TEXT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Ветеринарная карта: осмотры, прививки, лечение
CREATE TABLE medical_records (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    animal_id   INT NOT NULL,
    record_date DATE NOT NULL,
    type        ENUM('Осмотр', 'Вакцинация', 'Лечение', 'Стерилизация') NOT NULL,
    description VARCHAR(255) NOT NULL,
    vet_id      INT NOT NULL,
    FOREIGN KEY (animal_id) REFERENCES animals (id) ON DELETE CASCADE,
    FOREIGN KEY (vet_id) REFERENCES users (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Новые хозяева
CREATE TABLE adopters (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    fio     VARCHAR(100) NOT NULL,
    phone   VARCHAR(20)  NOT NULL,
    address VARCHAR(200) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Передача животных хозяевам (одно животное передаётся один раз)
CREATE TABLE adoptions (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    animal_id     INT NOT NULL UNIQUE,
    adopter_id    INT NOT NULL,
    adoption_date DATE NOT NULL,
    user_id       INT NOT NULL,
    note          VARCHAR(255),
    FOREIGN KEY (animal_id) REFERENCES animals (id) ON DELETE RESTRICT,
    FOREIGN KEY (adopter_id) REFERENCES adopters (id),
    FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Пароли: admin — 1234, vet и volunteer — 1111
INSERT INTO users (login, password_hash, fio, role) VALUES
('admin', '$2y$10$EU6XEVuivgd0YYW0jaFQkOD.X3Q9Z3ptEvCIK9xfw4c/IiwZSEQju', 'Кравцова Елена Викторовна', 'admin'),
('vet', '$2y$10$S2DcHGptAIRNH3PvW74Louq4tJi3aLked1.5RnVFYs0D1ba0b69Cu', 'Самойлов Артём Николаевич', 'vet'),
('volunteer', '$2y$10$dh.3IarQu29zdGBn.ToL5OphU57FmkiHqWt2fb59cJnfhQ25zovmC', 'Пчёлкина Дарья Олеговна', 'volunteer');

-- Даты отсчитываются от дня импорта скрипта
INSERT INTO animals (name, species, breed, sex, birth_date, color, arrival_date, cage, status, sterilized, description) VALUES
('Бублик', 'Собака', 'метис', 'Самец', CURDATE() - INTERVAL 30 MONTH, 'рыжий', CURDATE() - INTERVAL 120 DAY, 'В-1', 'В приюте', 1, 'Найден у торгового центра на Профсоюзной улице. Ласковый, ладит с детьми и другими собаками.'),
('Мася', 'Кошка', 'беспородная', 'Самка', CURDATE() - INTERVAL 18 MONTH, 'трёхцветная', CURDATE() - INTERVAL 95 DAY, 'К-2', 'В приюте', 1, 'Передана прежними хозяевами из-за переезда. Спокойная, приучена к лотку.'),
('Граф', 'Собака', 'метис немецкой овчарки', 'Самец', CURDATE() - INTERVAL 60 MONTH, 'чёрно-подпалый', CURDATE() - INTERVAL 200 DAY, 'В-3', 'В приюте', 1, 'Умный и послушный, знает команды «сидеть», «рядом». Подойдёт для частного дома.'),
('Тучка', 'Кошка', 'метис британской', 'Самка', CURDATE() - INTERVAL 36 MONTH, 'серая', CURDATE() - INTERVAL 40 DAY, 'К-1', 'Пристроен', 1, 'Найдена в подъезде жилого дома. Очень ласковая.'),
('Рыжик', 'Кошка', 'беспородный', 'Самец', CURDATE() - INTERVAL 6 MONTH, 'рыжий', CURDATE() - INTERVAL 12 DAY, 'К-4', 'На карантине', 0, 'Котёнок, найден на детской площадке.'),
('Альма', 'Собака', 'метис лайки', 'Самка', CURDATE() - INTERVAL 48 MONTH, 'бело-серая', CURDATE() - INTERVAL 150 DAY, 'В-2', 'Пристроен', 1, 'Активная, любит долгие прогулки.'),
('Пират', 'Собака', 'метис', 'Самец', CURDATE() - INTERVAL 24 MONTH, 'чёрный с белым', CURDATE() - INTERVAL 20 DAY, 'В-5', 'На лечении', 0, 'Поступил с травмой передней лапы после ДТП.'),
('Снежка', 'Кошка', 'метис турецкой ангоры', 'Самка', CURDATE() - INTERVAL 12 MONTH, 'белая', CURDATE() - INTERVAL 70 DAY, 'К-3', 'В приюте', 1, 'Пугливая, к новому дому привыкает постепенно.'),
('Барон', 'Собака', 'метис ротвейлера', 'Самец', CURDATE() - INTERVAL 72 MONTH, 'чёрный', CURDATE() - INTERVAL 260 DAY, 'В-4', 'В приюте', 1, 'Дольше всех в приюте. Нужен опытный хозяин и частный дом.'),
('Луна', 'Собака', 'метис', 'Самка', CURDATE() - INTERVAL 10 MONTH, 'палевая', CURDATE() - INTERVAL 8 DAY, 'В-6', 'На карантине', 0, 'Щенок, найдена волонтёрами в парке.'),
('Персик', 'Кошка', 'беспородный', 'Самец', CURDATE() - INTERVAL 30 MONTH, 'персиковый', CURDATE() - INTERVAL 110 DAY, 'К-5', 'Пристроен', 1, 'Общительный кот, любит внимание.'),
('Кнопка', 'Собака', 'метис той-терьера', 'Самка', CURDATE() - INTERVAL 20 MONTH, 'коричневая', CURDATE() - INTERVAL 55 DAY, 'В-7', 'Пристроен', 1, 'Маленькая и энергичная, подходит для квартиры.'),
('Дымок', 'Кошка', 'беспородный', 'Самец', CURDATE() - INTERVAL 48 MONTH, 'дымчатый', CURDATE() - INTERVAL 180 DAY, 'К-6', 'В приюте', 1, 'Спокойный взрослый кот, ладит с другими кошками.'),
('Шарик', 'Собака', 'метис', 'Самец', CURDATE() - INTERVAL 8 MONTH, 'белый с рыжим', CURDATE() - INTERVAL 30 DAY, 'В-8', 'В приюте', 0, 'Весёлый щенок, приучается к поводку.'),
('Ириска', 'Кошка', 'беспородная', 'Самка', CURDATE() - INTERVAL 4 MONTH, 'черепаховая', CURDATE() - INTERVAL 5 DAY, 'К-7', 'На карантине', 0, 'Котёнок, принесена жильцами соседнего дома.'),
('Кеша', 'Другое', 'волнистый попугай', 'Самец', CURDATE() - INTERVAL 14 MONTH, 'зелёный', CURDATE() - INTERVAL 25 DAY, 'П-1', 'В приюте', 0, 'Сдан хозяевами. Говорит несколько слов.');

INSERT INTO medical_records (animal_id, record_date, type, description, vet_id) VALUES
(1, CURDATE() - INTERVAL 120 DAY, 'Осмотр', 'Первичный осмотр: состояние удовлетворительное, обработка от паразитов', 2),
(1, CURDATE() - INTERVAL 106 DAY, 'Вакцинация', 'Вакцинация от бешенства и чумы плотоядных', 2),
(1, CURDATE() - INTERVAL 100 DAY, 'Стерилизация', 'Плановая стерилизация, послеоперационный период без осложнений', 2),
(1, CURDATE() - INTERVAL 30 DAY, 'Осмотр', 'Плановый осмотр: здоров(а), вес в норме', 1),
(2, CURDATE() - INTERVAL 95 DAY, 'Осмотр', 'Первичный осмотр: состояние удовлетворительное, обработка от паразитов', 2),
(2, CURDATE() - INTERVAL 81 DAY, 'Вакцинация', 'Вакцинация от бешенства и панлейкопении', 2),
(2, CURDATE() - INTERVAL 75 DAY, 'Стерилизация', 'Плановая стерилизация, послеоперационный период без осложнений', 2),
(2, CURDATE() - INTERVAL 30 DAY, 'Осмотр', 'Плановый осмотр: здоров(а), вес в норме', 2),
(3, CURDATE() - INTERVAL 200 DAY, 'Осмотр', 'Первичный осмотр: состояние удовлетворительное, обработка от паразитов', 2),
(3, CURDATE() - INTERVAL 186 DAY, 'Вакцинация', 'Вакцинация от бешенства и чумы плотоядных', 2),
(3, CURDATE() - INTERVAL 180 DAY, 'Стерилизация', 'Плановая стерилизация, послеоперационный период без осложнений', 2),
(3, CURDATE() - INTERVAL 30 DAY, 'Осмотр', 'Плановый осмотр: здоров(а), вес в норме', 1),
(4, CURDATE() - INTERVAL 40 DAY, 'Осмотр', 'Первичный осмотр: состояние удовлетворительное, обработка от паразитов', 2),
(4, CURDATE() - INTERVAL 26 DAY, 'Вакцинация', 'Вакцинация от бешенства и панлейкопении', 2),
(4, CURDATE() - INTERVAL 20 DAY, 'Стерилизация', 'Плановая стерилизация, послеоперационный период без осложнений', 2),
(5, CURDATE() - INTERVAL 12 DAY, 'Осмотр', 'Первичный осмотр: состояние удовлетворительное, обработка от паразитов', 2),
(6, CURDATE() - INTERVAL 150 DAY, 'Осмотр', 'Первичный осмотр: состояние удовлетворительное, обработка от паразитов', 2),
(6, CURDATE() - INTERVAL 136 DAY, 'Вакцинация', 'Вакцинация от бешенства и чумы плотоядных', 2),
(6, CURDATE() - INTERVAL 130 DAY, 'Стерилизация', 'Плановая стерилизация, послеоперационный период без осложнений', 2),
(7, CURDATE() - INTERVAL 20 DAY, 'Осмотр', 'Первичный осмотр: состояние удовлетворительное, обработка от паразитов', 2),
(7, CURDATE() - INTERVAL 6 DAY, 'Вакцинация', 'Вакцинация от бешенства и чумы плотоядных', 2),
(8, CURDATE() - INTERVAL 70 DAY, 'Осмотр', 'Первичный осмотр: состояние удовлетворительное, обработка от паразитов', 2),
(8, CURDATE() - INTERVAL 56 DAY, 'Вакцинация', 'Вакцинация от бешенства и панлейкопении', 2),
(8, CURDATE() - INTERVAL 50 DAY, 'Стерилизация', 'Плановая стерилизация, послеоперационный период без осложнений', 2),
(9, CURDATE() - INTERVAL 260 DAY, 'Осмотр', 'Первичный осмотр: состояние удовлетворительное, обработка от паразитов', 2),
(9, CURDATE() - INTERVAL 246 DAY, 'Вакцинация', 'Вакцинация от бешенства и чумы плотоядных', 2),
(9, CURDATE() - INTERVAL 240 DAY, 'Стерилизация', 'Плановая стерилизация, послеоперационный период без осложнений', 2),
(9, CURDATE() - INTERVAL 30 DAY, 'Осмотр', 'Плановый осмотр: здоров(а), вес в норме', 1),
(10, CURDATE() - INTERVAL 8 DAY, 'Осмотр', 'Первичный осмотр: состояние удовлетворительное, обработка от паразитов', 2),
(11, CURDATE() - INTERVAL 110 DAY, 'Осмотр', 'Первичный осмотр: состояние удовлетворительное, обработка от паразитов', 2),
(11, CURDATE() - INTERVAL 96 DAY, 'Вакцинация', 'Вакцинация от бешенства и панлейкопении', 2),
(11, CURDATE() - INTERVAL 90 DAY, 'Стерилизация', 'Плановая стерилизация, послеоперационный период без осложнений', 2),
(12, CURDATE() - INTERVAL 55 DAY, 'Осмотр', 'Первичный осмотр: состояние удовлетворительное, обработка от паразитов', 2),
(12, CURDATE() - INTERVAL 41 DAY, 'Вакцинация', 'Вакцинация от бешенства и чумы плотоядных', 2),
(12, CURDATE() - INTERVAL 35 DAY, 'Стерилизация', 'Плановая стерилизация, послеоперационный период без осложнений', 2),
(13, CURDATE() - INTERVAL 180 DAY, 'Осмотр', 'Первичный осмотр: состояние удовлетворительное, обработка от паразитов', 2),
(13, CURDATE() - INTERVAL 166 DAY, 'Вакцинация', 'Вакцинация от бешенства и панлейкопении', 2),
(13, CURDATE() - INTERVAL 160 DAY, 'Стерилизация', 'Плановая стерилизация, послеоперационный период без осложнений', 2),
(13, CURDATE() - INTERVAL 30 DAY, 'Осмотр', 'Плановый осмотр: здоров(а), вес в норме', 1),
(14, CURDATE() - INTERVAL 30 DAY, 'Осмотр', 'Первичный осмотр: состояние удовлетворительное, обработка от паразитов', 2),
(14, CURDATE() - INTERVAL 16 DAY, 'Вакцинация', 'Вакцинация от бешенства и чумы плотоядных', 2),
(15, CURDATE() - INTERVAL 5 DAY, 'Осмотр', 'Первичный осмотр: состояние удовлетворительное, обработка от паразитов', 2),
(16, CURDATE() - INTERVAL 25 DAY, 'Осмотр', 'Первичный осмотр: состояние удовлетворительное, обработка от паразитов', 2),
(7, CURDATE() - INTERVAL 19 DAY, 'Лечение', 'Перелом передней лапы: наложена шина, назначено обезболивание', 2),
(7, CURDATE() - INTERVAL 5 DAY, 'Осмотр', 'Контрольный рентген: кость срастается правильно', 2),
(16, CURDATE() - INTERVAL 15 DAY, 'Осмотр', 'Осмотр птицы: оперение в норме, клюв и когти подрезаны', 2);

INSERT INTO adopters (fio, phone, address) VALUES
('Соловьёва Мария Андреевна', '+7 (916) 245-18-30', 'г. Москва, ул. Новаторов, д. 8, кв. 41'),
('Ефимов Сергей Павлович', '+7 (903) 711-52-09', 'Московская обл., г. Видное, ул. Садовая, д. 3'),
('Галкина Ирина Юрьевна', '+7 (925) 380-64-17', 'г. Москва, Ленинский пр-т, д. 92, кв. 15'),
('Тарасов Николай Владимирович', '+7 (977) 126-90-44', 'г. Москва, ул. Обручева, д. 21, кв. 7');

INSERT INTO adoptions (animal_id, adopter_id, adoption_date, user_id, note) VALUES
(4, 1, CURDATE() - INTERVAL 18 DAY, 1, 'Договор № 14, контрольный визит через месяц'),
(6, 2, CURDATE() - INTERVAL 32 DAY, 1, 'Договор № 12, частный дом с участком'),
(11, 3, CURDATE() - INTERVAL 58 DAY, 1, 'Договор № 9'),
(12, 4, CURDATE() - INTERVAL 9 DAY, 1, 'Договор № 15, получены фото из нового дома');
