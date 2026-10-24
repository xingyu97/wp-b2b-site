#!/bin/sh
# 镜像入口：初始化环境（DB / wp-config / Apache 配置）后以前台方式启动 Apache
set -e

echo "[entrypoint] 开始初始化 WordPress 环境..."
WP_NO_HTTPD=1 /usr/local/bin/wp-setup.sh

echo "[entrypoint] 启动 Apache（前台）"
exec httpd -DFOREGROUND
