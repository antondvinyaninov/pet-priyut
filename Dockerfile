# Используем официальный PHP 8.4 образ с Apache
FROM php:8.4-apache

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    nodejs \
    npm

# Очищаем кэш apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Устанавливаем PHP расширения
RUN docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd zip

# Включаем Apache mod_rewrite
RUN a2enmod rewrite

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Устанавливаем рабочую директорию
WORKDIR /var/www/html

# Копируем composer файлы
COPY composer.json composer.lock ./

# Устанавливаем зависимости Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Копируем package файлы
COPY package*.json ./

# Устанавливаем зависимости NPM
RUN npm ci

# Копируем все файлы проекта
COPY . .

# Собираем фронтенд
RUN npm run build

# Завершаем установку Composer (запускаем скрипты)
RUN composer dump-autoload --optimize

# Создаем директорию для SQLite базы данных
RUN mkdir -p /var/www/html/database && \
    touch /var/www/html/database/database.sqlite

# Устанавливаем права доступа
RUN chown -R www-data:www-data /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    /var/www/html/database

RUN chmod -R 775 /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    /var/www/html/database

# Создаем символическую ссылку для storage
RUN php artisan storage:link || true

# Настраиваем Apache для Laravel
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Добавляем конфигурацию Apache для Laravel
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/sites-available/000-default.conf

# Открываем порт 80
EXPOSE 80

# Запускаем Apache
CMD ["apache2-foreground"]
