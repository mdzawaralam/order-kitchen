FROM php:8.3-cli

# Install system dependencies and PostgreSQL driver
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libpq-dev \
    cron \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

WORKDIR /var/www/html

COPY . .

# Cron job setup
COPY cronjob.txt /etc/cron.d/auto-complete-orders
RUN chmod 0644 /etc/cron.d/auto-complete-orders \
    && crontab /etc/cron.d/auto-complete-orders \
    && touch /var/log/cron.log

# Keep container running and run cron
CMD ["sh", "-c", "cron && tail -f /var/log/cron.log"]
