#!/bin/sh
# WordPress 开发环境一键启动脚本
# 用途：podman 虚拟机重启后，恢复 wp-b2b 容器及容器内服务
set -e

echo "== 1/3 启动 wp-b2b 容器 =="
if ! podman ps --format "{{.Names}}" | grep -q "^wp-b2b$"; then
    podman start wp-b2b
    echo "  容器已启动"
else
    echo "  容器已在运行"
fi

sleep 2

echo "== 2/3 启动容器内 MariaDB =="
podman exec wp-b2b sh -c '
    mkdir -p /run/mysqld && chown mysql:mysql /run/mysqld
    if ! mysqladmin ping >/dev/null 2>&1; then
        /usr/bin/mariadbd --user=mysql --datadir=/var/lib/mysql >/var/log/mariadbd.log 2>&1 &
        for i in $(seq 1 30); do sleep 1; mysqladmin ping >/dev/null 2>&1 && break; done
    fi
    mysqladmin ping >/dev/null 2>&1 && echo "  MariaDB OK" || { echo "  MariaDB 启动失败"; tail -5 /var/log/mariadbd.log; exit 1; }
'

echo "== 3/3 启动 Apache =="
podman exec wp-b2b sh -c '
    if ! pgrep -x httpd >/dev/null 2>&1; then
        httpd -k start >/dev/null 2>&1 || httpd >/dev/null 2>&1 || true
    fi
    sleep 1
    pgrep -x httpd >/dev/null && echo "  Apache OK"
'

echo ""
echo "=========================================="
echo "  网站前台: http://localhost:8080"
echo "  管理后台: http://localhost:8080/wp-login.php"
echo "=========================================="

echo "== 4/4 恢复开发邮件落盘（sendmail + 权限） =="
podman exec wp-b2b sh -c '
    mkdir -p /var/mail
    cat > /usr/sbin/sendmail <<"SHEOF"
#!/bin/sh
# Dev-only sendmail: append incoming mail to /var/mail/outgoing.log and succeed.
mkdir -p /var/mail
touch /var/mail/outgoing.log
chmod 666 /var/mail/outgoing.log 2>/dev/null
cat >> /var/mail/outgoing.log
echo "----" >> /var/mail/outgoing.log
exit 0
SHEOF
    chmod 755 /usr/sbin/sendmail
    chmod 777 /var/mail
    touch /var/mail/outgoing.log && chmod 666 /var/mail/outgoing.log
    echo "  mail sink OK"
'
