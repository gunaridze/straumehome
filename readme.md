# Документация по настройке VPS-сервера Hetzner под 1С-Битрикс
**Проект:** Перенос сайта с loriata.ru на домен **straumehome.com** **Дата:** 4 июля 2026 г.  
**Провайдер:** Hetzner Cloud  
**Сервер:** bitrix-server (IP: `167.233.54.24`)  
**Конфигурация:** CPX22 (3 vCPU, 4 GB RAM, 80 GB NVMe SSD)  
**ОС:** Ubuntu 24.04 LTS  

---

## 1. Авторизация и доступ (SSH)
Доступ к серверу настроен по публичному ключу `ed25519` без использования паролей.
* **Подключение через Терминал:**
  ```bash
  ssh root@167.233.54.24
  
2. Установка веб-стека (LAMP)
Обновлены системные репозитории, добавлен PPA-репозиторий для стабильных версий PHP и установлен стек ПО (Apache, MariaDB, PHP 8.2 с необходимыми модулями).

Bash
# Добавление репозитория PHP
add-apt-repository -y ppa:ondrej/php && apt update

# Установка пакетов
apt install -y apache2 mariadb-server php8.2 php8.2-mysql php8.2-mbstring php8.2-gd php8.2-xml php8.2-curl php8.2-zip php8.2-opcache php8.2-intl libapache2-mod-php8.2

3. Конфигурация PHP под требования Битрикса
Создан конфигурационный файл /etc/php/8.2/apache2/conf.d/99-bitrix.ini со специфическими лимитами и параметрами кэширования для «Монитора качества» Битрикса. Установлен часовой пояс Латвии.

Параметры:

Ini, TOML
memcached.sess_locking = Off
date.timezone = Europe/Riga
max_input_vars = 10000
memory_limit = 512M
max_execution_time = 300
post_max_size = 1024M
upload_max_filesize = 1024M
opcache.revalidate_freq = 0
opcache.max_accelerated_files = 100000

4. Настройка веб-сервера Apache
Включен модуль mod_rewrite для работы ЧПУ (человекопонятных URL), включена обработка файлов конфигурации сайтов .htaccess и перезапущен сервер.

Bash
# Включение mod_rewrite
a2enmod rewrite

# Разрешение AllowOverride All для папки /var/www/
sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Перезапуск веб-сервера
systemctl restart apache2
Корневая папка сайта: /var/www/html

5. База данных (MariaDB)
Создана база данных с кодировкой utf8mb4 для корректной поддержки эмодзи и спецсимволов, а также выделенный пользователь.

Имя БД: straumehome_db
Пользователь: straume_user
Пароль: 027)xl#yvzG@Tc
Хост: localhost
Кодировка: utf8mb4_unicode_ci

Команды создания (SQL):
SQL
CREATE DATABASE straumehome_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'straume_user'@'localhost' IDENTIFIED BY 'ВАШ_ПАРОЛЬ';
GRANT ALL PRIVILEGES ON straumehome_db.* TO 'straume_user'@'localhost';
FLUSH PRIVILEGES;


на локальном компьютере перенесённые файлы сайта и бд лежат здесь:
/Users/Gunars/Documents/Projects/straumehome
