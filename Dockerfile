FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    cron \
    unzip \
    git \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

WORKDIR /var/www/html

COPY . .

COPY cronjob.txt /etc/cron.d/auto-complete-orders
RUN chmod 0644 /etc/cron.d/auto-complete-orders \
    && crontab /etc/cron.d/auto-complete-orders \
    && chmod +x bin/console

CMD cron && php -S 0.0.0.0:8000 -t public
