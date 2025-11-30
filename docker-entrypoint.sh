#!/bin/bash
set -e

echo "Инициализация Laravel приложения..."

# Ожидание готовности базы данных
echo "Проверка подключения к базе данных..."
DB_HOST=${DB_HOST:-db}
DB_PORT=${DB_PORT:-3306}
DB_USERNAME=${DB_USERNAME:-hookah_user}
DB_PASSWORD=${DB_PASSWORD:-hookah_password}
DB_DATABASE=${DB_DATABASE:-hookah}

echo "Параметры подключения: host=${DB_HOST}, port=${DB_PORT}, user=${DB_USERNAME}, database=${DB_DATABASE}"

# Дополнительное ожидание после healthcheck MySQL (пользователь и БД могут создаваться асинхронно)
echo "Ожидание завершения инициализации MySQL..."
sleep 5

# Сначала проверяем подключение к MySQL серверу без указания базы данных
for i in {1..30}; do
    ERROR_OUTPUT=$(php -r "
    \$host = '${DB_HOST}';
    \$port = '${DB_PORT}';
    \$user = '${DB_USERNAME}';
    \$pass = '${DB_PASSWORD}';
    try {
        \$pdo = new PDO(\"mysql:host=\$host;port=\$port\", \$user, \$pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3
        ]);
        exit(0);
    } catch (PDOException \$e) {
        echo \$e->getMessage();
        exit(1);
    }
    " 2>&1)
    
    if [ $? -eq 0 ]; then
        echo "Подключение к MySQL серверу успешно!"
        break
    fi
    
    if [ $i -eq 30 ]; then
        echo "Ошибка: Не удалось подключиться к MySQL серверу за 60 секунд"
        echo "Последняя ошибка: $ERROR_OUTPUT"
        echo "Проверьте параметры подключения и убедитесь, что база данных запущена"
    else
        if [ $i -le 3 ] || [ $((i % 5)) -eq 0 ]; then
            echo "Ожидание подключения к MySQL серверу... ($i/30) - $ERROR_OUTPUT"
        else
            echo "Ожидание подключения к MySQL серверу... ($i/30)"
        fi
        sleep 2
    fi
done

# Теперь проверяем подключение к конкретной базе данных
for i in {1..10}; do
    DB_ERROR_OUTPUT=$(php -r "
    \$host = '${DB_HOST}';
    \$port = '${DB_PORT}';
    \$user = '${DB_USERNAME}';
    \$pass = '${DB_PASSWORD}';
    \$db = '${DB_DATABASE}';
    try {
        \$pdo = new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3
        ]);
        exit(0);
    } catch (PDOException \$e) {
        echo \$e->getMessage();
        exit(1);
    }
    " 2>&1)
    
    if [ $? -eq 0 ]; then
        echo "База данных '${DB_DATABASE}' доступна!"
        break
    fi
    
    if [ $i -eq 10 ]; then
        echo "Предупреждение: База данных '${DB_DATABASE}' может быть еще не создана"
        echo "Последняя ошибка: $DB_ERROR_OUTPUT"
        echo "Продолжаем работу - база данных будет создана при выполнении миграций"
    else
        if [ $i -le 3 ]; then
            echo "Ожидание создания базы данных '${DB_DATABASE}'... ($i/10) - $DB_ERROR_OUTPUT"
        else
            echo "Ожидание создания базы данных '${DB_DATABASE}'... ($i/10)"
        fi
        sleep 1
    fi
done

# Генерация ключа приложения, если его нет
if [ -z "$APP_KEY" ] || [ "$APP_KEY" == "" ]; then
    echo "Генерация ключа приложения..."
    php artisan key:generate --force || true
fi

# Запуск миграций
echo "Запуск миграций..."
php artisan migrate --force || echo "Миграции пропущены или уже выполнены"

# Очистка кеша
echo "Очистка кеша..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

# Запуск приложения
echo "Запуск сервера на http://0.0.0.0:8000"
exec php artisan serve --host=0.0.0.0 --port=8000

