FROM php:8.5.13-apache-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates curl libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    libzip-dev libonig-dev libxml2-dev libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd mysqli pdo_mysql mbstring zip \
    && missing="$(php -r 'foreach (["curl","dom","simplexml","xml"] as $ext) { if (!extension_loaded($ext)) echo "$ext "; }')" \
    && if [ -n "$missing" ]; then docker-php-ext-install -j"$(nproc)" $missing; fi \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /opt/project
RUN mkdir -p .runtime/gnuboard5 \
    && curl -fsSL https://codeload.github.com/gnuboard/gnuboard5/tar.gz/29d4070701715ab39e2ccec664661ff9584a4569 \
       | tar -xz --strip-components=1 -C .runtime/gnuboard5
COPY sample sample
COPY sample-erp sample-erp
COPY theme theme
COPY scripts scripts
COPY deploy deploy
RUN ln -s ../../sample .runtime/gnuboard5/sample \
    && ln -s ../../../theme/uram .runtime/gnuboard5/theme/uram \
    && mkdir -p .runtime/gnuboard5/data \
    && php deploy/configure-build.php \
    && php scripts/check-php.php \
    && a2enmod rewrite headers setenvif \
    && cp deploy/apache.conf /etc/apache2/sites-available/000-default.conf \
    && cp deploy/php.ini /usr/local/etc/php/conf.d/99-gnuboard.ini \
    && chmod 755 deploy/entrypoint.sh
# Keep Apache current even when the pinned PHP base image contains older packages.
RUN apt-get update \
    && apt-get install -y --no-install-recommends --only-upgrade apache2 apache2-bin apache2-data apache2-utils \
    && apache_version="$(apache2 -v | sed -n 's|^Server version: Apache/\([^ ]*\).*|\1|p')" \
    && dpkg --compare-versions "$apache_version" ge 2.4.71 \
    && rm -rf /var/lib/apt/lists/*
EXPOSE 80
ENTRYPOINT ["/opt/project/deploy/entrypoint.sh"]
CMD ["apache2-foreground"]
