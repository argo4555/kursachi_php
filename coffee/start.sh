#!/bin/sh
# Запуск контейнера: Apache слушает порт, который выдаёт Railway (переменная PORT)
PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Создаём базу данных проекта, если её ещё нет
php /app/init.php

exec apache2-foreground
