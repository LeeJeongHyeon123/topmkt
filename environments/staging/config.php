<?php
/**
 * 탑마케팅 스테이징 환경 설정 파일
 */

// 기본 설정
define('APP_NAME', '탑마케팅 (Staging)');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'https://staging.topmktx.com');
define('APP_DEBUG', true);

// 타임존 설정
date_default_timezone_set('Asia/Seoul');

// 데이터베이스 설정
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'TOPMKT_staging');
define('DB_USER', 'topmkt_staging');
define('DB_PASS', 'staging_password_here');
define('DB_CHARSET', 'utf8mb4');

// 환경 설정
define('ENVIRONMENT', 'staging');
define('LOG_LEVEL', 'debug'); 