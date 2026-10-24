#!/bin/sh
# WordPress 开发环境初始化脚本（容器内执行）
# 敏感密码一律从环境变量读取：WP_DB_PASSWORD（未设置时使用开发占位，生产必须注入）
set -e

WP_DB_PASSWORD="${WP_DB_PASSWORD:-wp_dev_change_me}"
WP_DB_NAME="${WP_DB_NAME:-wordpress}"
WP_DB_USER="${WP_DB_USER:-wp}"

echo "== [1/6] 初始化 MariaDB 数据目录 =="
if [ ! -d /var/lib/mysql/mysql ]; then
    mariadb-install-db --user=mysql --datadir=/var/lib/mysql --auth-root-authentication-method=normal >/dev/null 2>&1 || \
    mariadb-install-db --user=mysql --datadir=/var/lib/mysql >/dev/null 2>&1
    echo "  -> 数据目录已初始化"
else
    echo "  -> 数据目录已存在，跳过"
fi

echo "== [2/6] 启动 MariaDB =="
if ! mysqladmin ping >/dev/null 2>&1; then
    /usr/bin/mariadbd --user=mysql --datadir=/var/lib/mysql --skip-networking=0 >/var/log/mariadbd.log 2>&1 &
    for i in $(seq 1 40); do
        sleep 1
        if mysqladmin ping >/dev/null 2>&1; then break; fi
    done
fi
mysqladmin ping 2>/dev/null && echo "  -> MariaDB 已就绪" || { echo "  !! MariaDB 启动失败"; tail -20 /var/log/mariadbd.log; exit 1; }

echo "== [3/6] 创建 WordPress 数据库与用户 =="
mysql <<SQL
CREATE DATABASE IF NOT EXISTS ${WP_DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${WP_DB_USER}'@'localhost' IDENTIFIED BY '${WP_DB_PASSWORD}';
CREATE USER IF NOT EXISTS '${WP_DB_USER}'@'127.0.0.1' IDENTIFIED BY '${WP_DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${WP_DB_NAME}.* TO '${WP_DB_USER}'@'localhost';
GRANT ALL PRIVILEGES ON ${WP_DB_NAME}.* TO '${WP_DB_USER}'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL
echo "  -> 数据库 ${WP_DB_NAME} / 用户 ${WP_DB_USER} 已创建"

echo "== [4/6] 生成 wp-config.php（环境变量版模板）=="
cd /var/www/localhost/htdocs
if [ ! -f wp-config.php ]; then
    cp wp-config-sample.php wp-config.php
    echo "  -> wp-config.php 已由环境变量版模板生成（密码/盐值在运行时从环境变量读取）"
else
    echo "  -> wp-config.php 已存在，跳过"
fi

echo "== [5/6] 配置 Apache（rewrite + AllowOverride + PHP 模块）=="
sed -i 's|^#LoadModule rewrite_module|LoadModule rewrite_module|' /etc/apache2/httpd.conf
sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/httpd.conf
grep -q "IncludeOptional conf.d" /etc/apache2/httpd.conf || echo "IncludeOptional conf.d/*.conf" >> /etc/apache2/httpd.conf
# 容器镜像入口会以前台方式接管 httpd；独立脚本场景才在此启动
if [ "$WP_NO_HTTPD" != "1" ]; then
    if ! pgrep -x httpd >/dev/null 2>&1; then
        httpd -k start >/var/log/httpd_start.log 2>&1 || httpd >/var/log/httpd_start.log 2>&1 || true
    fi
    sleep 1
    pgrep -x httpd >/dev/null && echo "  -> Apache 已启动" || echo "  !! Apache 启动失败，见 /var/log/httpd_start.log"
else
    echo "  -> Apache 配置完成（由镜像入口前台接管）"
fi

echo "== [6/6] 目录权限（开发环境放开 wp-content）=="
chmod -R 777 /var/www/localhost/htdocs/wp-content 2>/dev/null || true
echo "  -> wp-content 可写"

echo ""
echo "=========================================="
echo " 环境初始化完成。下一步：通过 curl POST 完成 WordPress 安装"
echo " 站点地址: http://localhost:8080"
echo "=========================================="
