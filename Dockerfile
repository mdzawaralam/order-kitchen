# -------------------------------------------------------
# Base PHP image
# -------------------------------------------------------
FROM php:8.3-cli

# -------------------------------------------------------
# Install system dependencies and PostgreSQL extensions
# -------------------------------------------------------
RUN set -eux; \
    # Ensure apt sources exist and HTTPS transport is available
    if [ ! -f /etc/apt/sources.list ]; then \
    echo "deb https://deb.debian.org/debian bookworm main contrib non-free" > /etc/apt/sources.list; \
    echo "deb https://security.debian.org/debian-security bookworm-security main contrib non-free" >> /etc/apt/sources.list; \
    echo "deb https://deb.debian.org/debian bookworm-updates main contrib non-free" >> /etc/apt/sources.list; \
    fi; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    unzip \
    git \
    libpq-dev \
    cron; \
    docker-php-ext-install pdo pdo_pgsql; \
    rm -rf /var/lib/apt/lists/*

# -------------------------------------------------------
# Install Composer globally (from official installer)
# -------------------------------------------------------
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

# -------------------------------------------------------
# Set working directory
# -------------------------------------------------------
WORKDIR /var/www/html

# -------------------------------------------------------
# Copy Composer files first for better caching
# -------------------------------------------------------
COPY composer.json composer.lock* ./

# -------------------------------------------------------
# Install PHP dependencies (optimized autoloader)
# -------------------------------------------------------
RUN composer install --no-interaction --no-progress --no-scripts --prefer-dist --optimize-autoloader

# -------------------------------------------------------
# Copy the rest of your project
# -------------------------------------------------------
COPY . .

# -------------------------------------------------------
# Cron job setup
# -------------------------------------------------------
COPY cronjob.txt /etc/cron.d/auto-complete-orders
RUN chmod 0644 /etc/cron.d/auto-complete-orders \
    && crontab /etc/cron.d/auto-complete-orders \
    && touch /var/log/cron.log

# -------------------------------------------------------
# Expose Symfony server port
# -------------------------------------------------------
EXPOSE 8000

# -------------------------------------------------------
# Start both cron and Symfony PHP server
# -------------------------------------------------------
CMD ["sh", "-c", "cron && php -S 0.0.0.0:8000 -t public"]
