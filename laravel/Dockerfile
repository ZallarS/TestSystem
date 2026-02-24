FROM php:8.4-fpm-alpine

# Установка системных зависимостей и расширений PHP
RUN apk add --no-cache \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql zip bcmath

# Копирование бинарного файла Composer из официального образа
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Рабочая директория внутри контейнера
WORKDIR /var/www/html

# Копирование всего содержимого текущей папки (laravel) в контейнер
COPY . .

# Установка прав на папки, в которые Laravel будет писать
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Установка зависимостей Composer (без dev-пакетов, для продакшена)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Открытие порта 9000 (стандартный порт php-fpm)
EXPOSE 9000

# Команда запуска php-fpm
CMD ["php-fpm"]
