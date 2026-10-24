<?php
/**
 * WordPress 基础配置模板（环境变量版）
 *
 * 所有敏感值（数据库密码、密钥盐值）一律从环境变量读取；
 * 未提供环境变量时，数据库密码使用安全占位（部署时务必注入真实值），
 * 密钥盐值自动随机生成（建议生产环境通过环境变量注入固定值）。
 *
 * 用法：
 *   本地/容器部署时，将此文件复制为 wp-config.php，并通过环境变量注入：
 *     WP_DB_NAME / WP_DB_USER / WP_DB_PASSWORD / WP_DB_HOST
 *     WP_AUTH_KEY / WP_SECURE_AUTH_KEY / WP_LOGGED_IN_KEY / WP_NONCE_KEY
 *     WP_AUTH_SALT / WP_SECURE_AUTH_SALT / WP_LOGGED_IN_SALT / WP_NONCE_SALT
 */

// ** 数据库设置 ** //
define( 'DB_NAME', getenv( 'WP_DB_NAME' ) ?: 'wordpress' );
define( 'DB_USER', getenv( 'WP_DB_USER' ) ?: 'wp' );
define( 'DB_PASSWORD', getenv( 'WP_DB_PASSWORD' ) ?: 'CHANGE_ME_IN_PRODUCTION' );
define( 'DB_HOST', getenv( 'WP_DB_HOST' ) ?: '127.0.0.1' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

// ** 密钥与盐值：环境变量优先，否则随机生成 ** //
$wpb2b_salt = function () {
	return bin2hex( random_bytes( 32 ) );
};
define( 'AUTH_KEY',         getenv( 'WP_AUTH_KEY' ) ?: $wpb2b_salt() );
define( 'SECURE_AUTH_KEY',  getenv( 'WP_SECURE_AUTH_KEY' ) ?: $wpb2b_salt() );
define( 'LOGGED_IN_KEY',    getenv( 'WP_LOGGED_IN_KEY' ) ?: $wpb2b_salt() );
define( 'NONCE_KEY',        getenv( 'WP_NONCE_KEY' ) ?: $wpb2b_salt() );
define( 'AUTH_SALT',        getenv( 'WP_AUTH_SALT' ) ?: $wpb2b_salt() );
define( 'SECURE_AUTH_SALT', getenv( 'WP_SECURE_AUTH_SALT' ) ?: $wpb2b_salt() );
define( 'LOGGED_IN_SALT',   getenv( 'WP_LOGGED_IN_SALT' ) ?: $wpb2b_salt() );
define( 'NONCE_SALT',       getenv( 'WP_NONCE_SALT' ) ?: $wpb2b_salt() );

/**#@-*/

/**
 * WordPress 数据库表前缀。
 */
$table_prefix = 'wp_';

/**
 * 开发者调试模式。
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

/** 后台禁用主题/插件文件编辑器（上线安全要求） */
define( 'DISALLOW_FILE_EDIT', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
