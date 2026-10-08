FROM hyperf/hyperf:8.4-alpine-v3.21-swoole
LABEL maintainer="Hyperf Developers <group@hyperf.io>" version="1.0" license="MIT" app.name="Hyperf"

ARG timezone
ENV TIMEZONE=${timezone:-"Asia/Shanghai"} \
    APP_ENV=dev \
    SCAN_CACHEABLE=false

RUN set -ex \
    && php -v \
    && php -m \
    && php --ri swoole \
    && cd /etc/php* \
    && { \
        echo "upload_max_filesize=128M"; \
        echo "post_max_size=128M"; \
        echo "memory_limit=1G"; \
        echo "date.timezone=${TIMEZONE}"; \
    } | tee conf.d/99_overrides.ini \
    && ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "${TIMEZONE}" > /etc/timezone \
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man

WORKDIR /opt/www

COPY . /opt/www

EXPOSE 9501

ENTRYPOINT ["php", "/opt/www/bin/hyperf.php", "start"]