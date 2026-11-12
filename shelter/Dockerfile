# Курсовой проект на PHP + MySQL для Railway
FROM php:8.3-apache

# Драйвер MySQL для PDO и московское время
RUN docker-php-ext-install pdo_mysql \
 && ln -snf /usr/share/zoneinfo/Europe/Moscow /etc/localtime \
 && echo "date.timezone=Europe/Moscow" > /usr/local/etc/php/conf.d/timezone.ini \
 && echo "ServerName localhost" > /etc/apache2/conf-enabled/servername.conf

# Файлы сайта
COPY site/ /var/www/html/

# SQL-скрипт и создание базы при запуске
COPY sql/ /app/sql/
COPY init.php /app/init.php
COPY start.sh /start.sh
RUN sed -i 's/\r$//' /start.sh && chmod +x /start.sh

CMD ["/start.sh"]
