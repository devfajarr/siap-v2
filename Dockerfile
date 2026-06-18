# =============================================================================
# SIAP - Sistem Informasi Akademik POLSA
# Dockerfile: FrankenPHP (Caddy + PHP 8.3 Runtime)
# =============================================================================
# Stage 1 (composer-deps) : Install PHP vendor — untuk menyediakan ziggy ke Vite
# Stage 2 (node-builder)  : Compile frontend assets (Vite + Tailwind + Vue 3)
# Stage 3 (final)         : FrankenPHP — serve Laravel app via Caddy worker mode
# =============================================================================

# -----------------------------------------------------------------------------
# Stage 1: PHP Vendor Dependencies
# -----------------------------------------------------------------------------
# resources/js/app.js mengimport "../../vendor/tightenco/ziggy" (Ziggy route helper).
# Import ini diresolved oleh Vite/Rollup saat build, sehingga vendor/tightenco/ziggy
# HARUS tersedia di stage node-builder meski bukan PHP runtime.
# Solusi: install composer di stage terpisah, lalu COPY hanya package ziggy-nya.
# -----------------------------------------------------------------------------
FROM composer:2 AS composer-deps

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs \
    --no-interaction

# -----------------------------------------------------------------------------
# Stage 2: Build Frontend Assets
# -----------------------------------------------------------------------------
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy dependency manifests terlebih dahulu untuk memanfaatkan layer cache Docker.
# Layer ini hanya direbuild jika package.json/lock berubah.
COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

# Ziggy membutuhkan vendor/tightenco/ziggy agar Vite dapat me-resolve import-nya.
# Hanya copy package ini (bukan seluruh vendor) agar node-builder tetap ringan.
COPY --from=composer-deps /app/vendor/tightenco ./vendor/tightenco

# Copy seluruh source untuk dicompile
COPY resources/ ./resources/
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY public/ ./public/

# Build production assets → output ke public/build/
RUN npm run build

# -----------------------------------------------------------------------------
# Stage 3: PHP Application (FrankenPHP)
# -----------------------------------------------------------------------------
FROM dunglas/frankenphp:latest-php8.3-alpine AS final

# ---------- System Dependencies ----------
RUN apk add --no-cache \
    supervisor \
    tzdata \
    postgresql-client \
    curl \
    bash \
    libpng-dev \
    libwebp-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev

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
    opcache \
    exif \
    mbstring \
    xml \
    ctype \
    fileinfo \
    tokenizer

# ---------- Composer ----------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------- PHP Configuration ----------
# Tingkatkan limits untuk sinkronisasi API Neofeeder, export Excel, dan upload file.
RUN echo "date.timezone=Asia/Jakarta" > /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "max_execution_time=600" >> /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "post_max_size=100M" >> /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "upload_max_filesize=100M" >> /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "opcache.save_comments=1" >> /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "opcache.jit=tracing" >> /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "opcache.jit_buffer_size=64M" >> /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "realpath_cache_size=4096K" >> /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "realpath_cache_ttl=600" >> /usr/local/etc/php/conf.d/99-custom.ini

# ---------- Timezone ----------
ENV TZ=Asia/Jakarta
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# ---------- Working Directory ----------
WORKDIR /app

# ---------- Install PHP Dependencies (production only) ----------
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-autoloader \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ---------- Application Source ----------
COPY . .

# ---------- Copy Compiled Frontend Assets ----------
COPY --from=node-builder /app/public/build ./public/build

# ---------- Generate Optimized Autoloader ----------
RUN composer dump-autoload --optimize --classmap-authoritative

# ---------- Laravel Storage & Cache Directories ----------
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# ---------- Supervisor Directories ----------
RUN mkdir -p /var/log/supervisor /var/run

# ---------- Copy Configuration Files ----------
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# ---------- FrankenPHP / Caddy Server Root ----------
# SERVER_NAME tidak menggunakan port default (80/443).
# Port 8000 untuk HTTP → akan di-proxy oleh web server VPS.
ENV SERVER_NAME=":8000"
ENV FRANKENPHP_CONFIG="worker ./public/index.php"

# ---------- Expose Ports ----------
# 8000 → HTTP (Laravel app via FrankenPHP/Caddy)
# 9001 → Supervisor XML-RPC (monitoring internal, tidak perlu di-expose ke host)
EXPOSE 8000

# ---------- Entrypoint ----------
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
