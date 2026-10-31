# ============================================================
# 外贸 B2B 网站（WordPress + Apache + PHP8.3 + MariaDB）镜像
# 构建：docker build -t wp-b2b-site .
# 运行：docker run -d -p 8080:80 -e WP_DB_PASSWORD=<密码> wp-b2b-site
# ============================================================
FROM alpine:3.20

# apk 源切换（默认官方源，全球可达；本地中国网络构建时传 --build-arg APK_MIRROR=https://mirrors.cloud.tencent.com）
ARG APK_MIRROR=""
# 安装 Apache/PHP8.3/MariaDB
RUN if [ -n "$APK_MIRROR" ]; then \
        sed -i "s#dl-cdn.alpinelinux.org#$APK_MIRROR#g" /etc/apk/repositories; \
    fi \
    && apk add --no-cache \
    apache2 \
    apache2-ssl \
    php83 \
    php83-apache2 \
    php83-mysqli \
    php83-pdo \
    php83-pdo_mysql \
    php83-mbstring \
    php83-xml \
    php83-curl \
    php83-gd \
    php83-zip \
    php83-session \
    mariadb \
    mariadb-client \
    bash \
    curl

# 站点源码（wp-config.php 与上传内容由 .dockerignore 排除，启动时由模板生成）
COPY wordpress /var/www/localhost/htdocs
COPY wp-setup.sh /usr/local/bin/wp-setup.sh
COPY docker-entrypoint.sh /entrypoint.sh

RUN chmod +x /usr/local/bin/wp-setup.sh /entrypoint.sh \
    && mkdir -p /var/run/mysqld && chown mysql:mysql /var/run/mysqld

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
