# =============================================================================
# SIAP - Sistem Informasi Akademik POLSA
# Dockerfile: FrankenPHP (Caddy + PHP Runtime)
# =============================================================================
# FrankenPHP mengeliminasi kebutuhan Nginx + PHP-FPM dengan menjalankan PHP
# langsung di dalam web server Caddy melalui worker mode, menghasilkan
# arsitektur single-process yang lebih efisien.
# =============================================================================

FROM dunglas/frankenphp:latest-php8.3

# ---------- PHP Extensions ----------
# install-php-extensions adalah utility bawaan image FrankenPHP
# yang menangani semua system dependencies secara otomatis.
RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    gd \
    zip \
    intl \
    bcmath \
    pcntl \
    opcache

# ---------- Composer ----------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ---------- Working Directory ----------
WORKDIR /app

# ---------- Application Files ----------
# Pada development, volume bind mount akan meng-override ini.
# Pada production, COPY memastikan image berisi kode aplikasi.
COPY . .

# ---------- Supervisor ----------
# Supervisor mengelola FrankenPHP dan Queue Worker sebagai child processes.
# Jika salah satu mati, Supervisor otomatis me-restart.
RUN apt-get update && apt-get install -y --no-install-recommends supervisor \
    && rm -rf /var/lib/apt/lists/* \
    && mkdir -p /var/log/supervisor

COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# ---------- Laravel Permissions ----------
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ---------- Timezone ----------
ENV TZ=Asia/Jakarta
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# ---------- PHP Configuration ----------
# Tingkatkan limits untuk sinkronisasi API Neofeeder dan upload file.
RUN echo "date.timezone=Asia/Jakarta" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_execution_time=600" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "memory_limit=1024M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size=100M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "upload_max_filesize=100M" >> /usr/local/etc/php/conf.d/custom.ini

# ---------- Caddy / FrankenPHP Server Root ----------
ENV SERVER_NAME=":80"

# ---------- Expose Ports ----------
EXPOSE 80 443

# ---------- Entrypoint ----------
# Supervisor sebagai PID 1, mengelola FrankenPHP + Queue Worker.
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
