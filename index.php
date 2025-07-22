<?php
/**
 * 탑마케팅 메인 리디렉션 파일
 * 모든 요청을 public/index.php로 전달합니다.
 */

// 상대 경로 설정
define('ROOT_PATH', __DIR__);
define('PUBLIC_PATH', ROOT_PATH . '/public');

// public/index.php로 요청 전달
require_once PUBLIC_PATH . '/index.php'; 