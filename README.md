# 外贸 B2B 官网（WordPress 自研建站）

面向外贸 B2B 的官网：展示产品、应用场景并获取询盘。WordPress 自研（自定义内容类型 + Blocksy 主题 + 三语 EN/FR/ES 切换 + Google SEO + 后台询盘存档），全站运行在 Alpine + Apache + PHP 8.3 + MariaDB 的容器镜像中。

## 功能一览

- 产品 / 应用 / 技术文章三种自定义内容类型，产品逐个录入（每个产品带专属询盘表单）
- 三语内容体系：`/`（英文）、`/fr/`（法语）、`/es/`（西语），右上角语言切换（hover + 点击）
- Google SEO：Rank Math（Sitemap / Schema / robots / OG），对标关键词型标题结构
- 询盘管理：Contact Form 7 + Flamingo 后台存档 + 邮件通知
- 后台管理：wp-admin 完整后台（产品录入、页面、询盘、SEO）
- 安全：XML-RPC 关闭、后台文件编辑器禁用、评论全站关闭、2FA（WP 2FA 插件，管理员启用双重验证）

## 项目结构

```
├── wordpress/                  # WordPress 站点源码
│   ├── wp-config-sample.php    # 配置模板（环境变量版，复制为 wp-config.php 使用）
│   ├── wp-content/plugins/
│   │   ├── wp-b2b-custom/      # 自研插件（CPT/短代码/语言切换器/安全加固）
│   │   ├── contact-form-7/     # 询盘表单
│   │   ├── seo-by-rank-math/   # SEO
│   │   ├── flamingo/           # 询盘后台存档
│   │   └── wp-2fa/             # 管理员双重验证
│   └── wp-content/themes/
│       ├── blocksy/            # 当前主题
│       └── astra/              # 备用主题
├── docker/Dockerfile           # 容器镜像构建
├── docker-entrypoint.sh        # 镜像入口（初始化 + 启动 Apache）
├── wp-setup.sh                 # 环境初始化（DB / wp-config / Apache）
└── wp-start.sh                 # 本地容器一键恢复脚本
```

## 本地开发（mac + docker）

```sh
# 1. 启动容器并初始化（详见 wp-start.sh / wp-setup.sh）
./wp-start.sh

# 2. 站点
#    前台 http://localhost:8080
#    后台 http://localhost:8080/wp-login.php
```

## 构建并运行镜像

```sh
docker build -t wp-b2b-site .
docker run -d -p 8080:80 \
  -e WP_DB_PASSWORD=你的数据库密码 \
  --name wp-b2b wp-b2b-site
```

启动后访问 `http://localhost:8080` 完成 WordPress 安装向导即可（容器内已自动建库建用户）。

### 环境变量

| 变量 | 说明 | 默认 |
|---|---|---|
| `WP_DB_NAME` | 数据库名 | `wordpress` |
| `WP_DB_USER` | 数据库用户 | `wp` |
| `WP_DB_PASSWORD` | **数据库密码（生产必注入）** | `wp_dev_change_me`（占位） |
| `WP_DB_HOST` | 数据库主机 | `127.0.0.1` |
| `WP_AUTH_KEY` 等 8 组 | WordPress 密钥/盐值 | 随机生成（生产建议注入固定值） |

> 数据持久化：数据库数据保存在容器卷中，建议挂载 `-v wpdb:/var/lib/mysql`。

## 安全说明

- 真实 `wp-config.php` 从不入库（gitignore），仓库只含环境变量版模板
- 所有密码/密钥由环境变量注入，生产环境务必覆盖默认值
- 上线前：修改管理员密码、启用 2FA、配置真实 SMTP（方案指定 QQ 邮箱 + 授权码）、配置 CDN 与异地备份

## 方案约束（已确认）

- 不做在线支付/购物车；产品 50–500 个逐个录入（不批量导入）
- 三语内容完全独立（不自动翻译、不自动同步）；某语言内容缺失时不显示该语言入口
- 不编造工厂/认证/客户案例，均以真实素材为准
