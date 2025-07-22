<?php
/**
 * 탑마케팅 프로덕션 환경 설정 파일
 */

// 기본 설정
define('APP_NAME', '탑마케팅');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'https://www.topmktx.com');
define('APP_DEBUG', false);

// 타임존 설정
date_default_timezone_set('Asia/Seoul');

// 데이터베이스 설정
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'TOPMKT');
define('DB_USER', 'topmkt_user');
define('DB_PASS', 'secure_password_here');
define('DB_CHARSET', 'utf8mb4');

// 환경 설정
define('ENVIRONMENT', 'production');
define('LOG_LEVEL', 'error');

// 캐시 설정
define('ENABLE_CACHE', true);
define('CACHE_TIMEOUT', 3600); 