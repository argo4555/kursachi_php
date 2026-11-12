-- База данных учёта задач проектной команды «Умпа-Лумпа»
-- Выполнить в phpMyAdmin: вкладка «Импорт» или «SQL»

CREATE DATABASE IF NOT EXISTS task_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE task_manager;
SET NAMES utf8mb4;

DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS task_statuses;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

-- Роли сотрудников
CREATE TABLE roles (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Сотрудники
CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    login         VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    fio           VARCHAR(100) NOT NULL,
    role_id       INT UNSIGNED NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Справочник статусов задач
CREATE TABLE task_statuses (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Задачи
CREATE TABLE tasks (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    description TEXT,
    author_id   INT UNSIGNED NOT NULL,
    executor_id INT UNSIGNED NOT NULL,
    status_id   INT UNSIGNED NOT NULL DEFAULT 1,
    priority    ENUM('Высокий', 'Средний', 'Низкий') NOT NULL DEFAULT 'Средний',
    deadline    DATETIME NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE RESTRICT,
    FOREIGN KEY (executor_id) REFERENCES users (id) ON DELETE RESTRICT,
    FOREIGN KEY (status_id) REFERENCES task_statuses (id) ON DELETE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO roles (id, name) VALUES
(1, 'Начальник проекта'), (2, 'Секретарь'), (3, 'Офисный работник'), (4, 'Работник колл-центра');

INSERT INTO task_statuses (id, name) VALUES
(1, 'В работе'), (2, 'Выполнено'), (3, 'Не выполнено'), (4, 'Отменено');

-- Пароли: admin — 12345, остальные сотрудники — 1111
INSERT INTO users (login, password_hash, fio, role_id) VALUES
('admin', '$2y$10$0L1jxx7ZsvMxHZDXIPTdzurMICu.7aYLEK9KwcAnMJeGWPTF9q/.6', 'Гриднев Павел Андреевич', 1),
('secretary', '$2y$10$fMGoQTzw8ONDOdCLxwB.fuXdN5qBvWOYVJ2B7rS85wNDFD0Jfoiw2', 'Белова Ксения Игоревна', 2),
('worker1', '$2y$10$0J8EOBrojml.mdekWA4taeHwPHz/u76jO8/L36Bh1XVJtkO9FOsIq', 'Сорокин Дмитрий Олегович', 3),
('worker2', '$2y$10$kxNxE/7fpyae.lNdaNgY/OF4g9b8qx3hDRPBi8q0Z8xCqEEcWntI.', 'Яковлева Анна Сергеевна', 3),
('call1', '$2y$10$aF6BPcz7oCntstZMQEMoQ.X0LM6uPGIUstgauwGq8dj8GkcsuODNW', 'Мельников Игорь Петрович', 4),
('call2', '$2y$10$9jgXo.byvCC0uou4M1fhLuvpEuynYpJ39cRAimNwHwY2ll1HVedsm', 'Захарова Ольга Викторовна', 4);

-- Задачи: сроки и даты создания отсчитываются от дня импорта скрипта
INSERT INTO tasks (title, description, author_id, executor_id, status_id, priority, deadline, created_at) VALUES
('Сверка технического задания с заказчиком', 'Сверить разделы 2–4 ТЗ с замечаниями заказчика из письма от понедельника, составить список расхождений.', 1, 3, 1, 'Высокий', TIMESTAMP(CURDATE() + INTERVAL 1 DAY, '18:00:00'), TIMESTAMP(CURDATE() - INTERVAL 3 DAY, '09:30:00')),
('Подготовить договор с ООО «Вектор»', 'Подготовить договор на оказание услуг по типовой форме, согласовать с юристом.', 1, 2, 2, 'Высокий', TIMESTAMP(CURDATE() + INTERVAL -2 DAY, '17:00:00'), TIMESTAMP(CURDATE() - INTERVAL 6 DAY, '09:30:00')),
('Обзвон клиентов по акции ноября', 'Обзвонить клиентов из списка «Постоянные» и рассказать о скидке 15 % до конца месяца.', 2, 5, 1, 'Средний', TIMESTAMP(CURDATE() + INTERVAL -1 DAY, '17:00:00'), TIMESTAMP(CURDATE() - INTERVAL 4 DAY, '09:30:00')),
('Обновить скрипт разговора для колл-центра', 'Добавить в скрипт ответы на частые вопросы о сроках доставки.', 1, 6, 1, 'Средний', TIMESTAMP(CURDATE() + INTERVAL 3 DAY, '18:00:00'), TIMESTAMP(CURDATE() - INTERVAL 2 DAY, '09:30:00')),
('Собрать отчёт по обращениям за октябрь', 'Количество обращений по темам, среднее время ответа.', 2, 6, 2, 'Средний', TIMESTAMP(CURDATE() + INTERVAL -5 DAY, '18:00:00'), TIMESTAMP(CURDATE() - INTERVAL 9 DAY, '09:30:00')),
('Оформить командировку в Казань', 'Билеты, гостиница и командировочное удостоверение для П. А. Гриднева.', 1, 2, 1, 'Низкий', TIMESTAMP(CURDATE() + INTERVAL 5 DAY, '12:00:00'), TIMESTAMP(CURDATE() - INTERVAL 1 DAY, '09:30:00')),
('Подготовить презентацию для совещания', 'Итоги квартала: выполненные задачи, загрузка отделов, планы.', 1, 4, 1, 'Высокий', TIMESTAMP(CURDATE() + INTERVAL 2 DAY, '10:00:00'), TIMESTAMP(CURDATE() - INTERVAL 2 DAY, '09:30:00')),
('Внести правки в смету проекта', 'Учесть удорожание материалов и пересчитать итоговую сумму.', 1, 3, 1, 'Высокий', TIMESTAMP(CURDATE() + INTERVAL -2 DAY, '18:00:00'), TIMESTAMP(CURDATE() - INTERVAL 5 DAY, '09:30:00')),
('Заказать канцелярию для офиса', 'Бумага А4, ручки, папки-регистраторы по заявке отделов.', 2, 4, 2, 'Низкий', TIMESTAMP(CURDATE() + INTERVAL -3 DAY, '16:00:00'), TIMESTAMP(CURDATE() - INTERVAL 7 DAY, '09:30:00')),
('Провести опрос удовлетворённости клиентов', 'Опросить не менее 50 клиентов по анкете из пяти вопросов.', 2, 5, 3, 'Средний', TIMESTAMP(CURDATE() + INTERVAL -4 DAY, '18:00:00'), TIMESTAMP(CURDATE() - INTERVAL 10 DAY, '09:30:00')),
('Настроить рассылку напоминаний клиентам', 'Задача отменена: рассылку берёт на себя подрядчик.', 1, 3, 4, 'Низкий', TIMESTAMP(CURDATE() + INTERVAL -1 DAY, '18:00:00'), TIMESTAMP(CURDATE() - INTERVAL 8 DAY, '09:30:00')),
('Обработать входящие заявки с сайта', 'Связаться с каждым, кто оставил заявку, и внести результат в таблицу.', 2, 6, 1, 'Высокий', TIMESTAMP(CURDATE() + INTERVAL 0 DAY, '19:00:00'), TIMESTAMP(CURDATE() - INTERVAL 1 DAY, '09:30:00')),
('Составить протокол планёрки', 'Протокол планёрки отдела с перечнем поручений и сроками.', 1, 2, 2, 'Средний', TIMESTAMP(CURDATE() + INTERVAL -1 DAY, '12:00:00'), TIMESTAMP(CURDATE() - INTERVAL 2 DAY, '09:30:00')),
('Проверить базу контактов на дубли', 'Найти и объединить повторяющиеся контакты клиентов.', 2, 5, 1, 'Низкий', TIMESTAMP(CURDATE() + INTERVAL 6 DAY, '18:00:00'), TIMESTAMP(CURDATE() - INTERVAL 1 DAY, '09:30:00')),
('Согласовать макет буклета', 'Получить согласование макета у начальника проекта и отправить в типографию.', 1, 4, 1, 'Средний', TIMESTAMP(CURDATE() + INTERVAL -1 DAY, '12:00:00'), TIMESTAMP(CURDATE() - INTERVAL 4 DAY, '09:30:00')),
('Подготовить квартальный план работ', 'План по отделам с ответственными и контрольными датами.', 1, 3, 1, 'Средний', TIMESTAMP(CURDATE() + INTERVAL 8 DAY, '18:00:00'), TIMESTAMP(CURDATE() - INTERVAL 1 DAY, '09:30:00')),
('Ответить на претензию клиента № 245', 'Подготовить письменный ответ на претензию о сроках выполнения работ.', 2, 6, 2, 'Высокий', TIMESTAMP(CURDATE() + INTERVAL -6 DAY, '15:00:00'), TIMESTAMP(CURDATE() - INTERVAL 8 DAY, '09:30:00')),
('Перенести архив документов в облако', 'Отсканировать и загрузить договоры за 2025 год.', 1, 4, 3, 'Низкий', TIMESTAMP(CURDATE() + INTERVAL -7 DAY, '18:00:00'), TIMESTAMP(CURDATE() - INTERVAL 14 DAY, '09:30:00')),
('Обучить нового оператора колл-центра', 'Провести вводное обучение: скрипты, CRM, правила общения.', 1, 5, 1, 'Средний', TIMESTAMP(CURDATE() + INTERVAL 4 DAY, '11:00:00'), TIMESTAMP(CURDATE() - INTERVAL 2 DAY, '09:30:00'));
