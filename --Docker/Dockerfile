FROM php:8.2-apache

# تثبيت التبعيات المطلوبة
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    gd \
    zip \
    mbstring \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# تفعيل mod_rewrite لـ Apache
RUN a2enmod rewrite

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تعيين مجلد العمل
WORKDIR /var/www/html

# نسخ ملفات المشروع
COPY . .

# نسخ ملفات composer من --Docker وتثبيت التبعيات
RUN cp /var/www/html/--Docker/composer.json /var/www/html/composer.json && \
    cp /var/www/html/--Docker/composer.lock /var/www/html/composer.lock 2>/dev/null || true

# نسخ sw.js من public إلى الجذر
RUN cp /var/www/html/public/sw.js /var/www/html/sw.js 2>/dev/null || true

# تثبيت تبعيات Composer
RUN git config --global --add safe.directory /var/www/html && \
    composer update --no-dev --optimize-autoloader --no-interaction

# إنشاء روابط رمزية للتوافق مع بنية المجلدات
RUN ln -sf /var/www/html/app /var/www/html/public/app && \
    ln -sf /var/www/html/vendor /var/www/html/public/vendor && \
    ln -sf /var/www/html/assets /var/www/html/public/assets 2>/dev/null || true

# تعيين الصلاحيات للمجلدات
RUN mkdir -p /var/www/html/storage \
    /var/www/html/app/storage \
    /var/www/html/public/uploads \
    /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/storage \
    && chmod -R 777 /var/www/html/app/storage \
    && chmod -R 777 /var/www/html/public/uploads \
    && chmod -R 777 /var/www/html/uploads

# تكوين Apache - استخدام public كمجلد التطبيق
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    <Directory /var/www/html>\n\
        Options FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# تعيين متغيرات البيئة الافتراضية
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

EXPOSE 80

CMD ["apache2-foreground"]
