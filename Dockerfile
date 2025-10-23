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

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Cron job setup
COPY cronjob.txt /etc/cron.d/auto-complete-orders
RUN chmod 0644 /etc/cron.d/auto-complete-orders \
    && crontab /etc/cron.d/auto-complete-orders \
    && touch /var/log/cron.log

# Expose Symfony port
EXPOSE 8000

# ✅ Start both cron and Symfony server
CMD ["sh", "-c", "cron && php -S 0.0.0.0:8000 -t public"]
