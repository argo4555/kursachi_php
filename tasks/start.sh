#!/bin/sh
# Запуск контейнера на Railway

# PHP работает в Apache только в режиме prefork: отключаем остальные режимы (MPM),
# иначе Apache не стартует с ошибкой «More than one MPM loaded»
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
[ -e /etc/apache2/mods-enabled/mpm_prefork.load ] || a2enmod mpm_prefork >/dev/null

# Apache слушает порт, который выдаёт Railway (переменная PORT)
PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Создаём базу данных проекта, если её ещё нет
php /app/init.php

exec apache2-foreground
