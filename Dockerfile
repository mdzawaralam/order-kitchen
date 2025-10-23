FROM php:8.3-cli

# Install system dependencies and PostgreSQL driver
RUN set -eux; \
    # Create apt sources if missing and ensure HTTPS is available
    apt-get update || true; \
    apt-get install -y apt-transport-https ca-certificates curl gnupg; \
    echo "deb https://deb.debian.org/debian bookworm main contrib non-free" > /etc/apt/sources.list; \
    echo "deb https://security.debian.org/debian-security bookworm-security main contrib non-free" >> /etc/apt/sources.list; \
    echo "deb https://deb.debian.org/debian bookworm-updates main contrib non-free" >> /etc/apt/sources.list; \
    apt-get update; \
    apt-get install -y unzip git libpq-dev cron; \
    docker-php-ext-install pdo pdo_pgsql; \
    rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --no-progress --optimize-autoloader

# Cron job setup
COPY cronjob.txt /etc/cron.d/auto-complete-orders
RUN chmod 0644 /etc/cron.d/auto-complete-orders \
    && crontab /etc/cron.d/auto-complete-orders \
    && touch /var/log/cron.log

# Expose Symfony dev server port
EXPOSE 8000

# Start both cron and Symfony server
CMD ["sh", "-c", "cron && php -S 0.0.0.0:8000 -t public"]
