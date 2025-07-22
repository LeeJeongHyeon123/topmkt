<?php
/**
 * 탑마케팅 애플리케이션 진입점 (안전 버전)
 * 
 * 모든 요청은 이 파일을 통해 처리됩니다.
 */

// 오류 표시
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // 상대 경로 설정
    define('ROOT_PATH', dirname(__DIR__));
    define('SRC_PATH', ROOT_PATH . '/src');
    define('CONFIG_PATH', SRC_PATH . '/config');

    // 설정 파일 로드
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    require_once CONFIG_PATH . '/routes.php';

    // 기본 세션 시작 (session.php 사용하지 않음)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 라우팅 처리
    $router = new Router();
    $router->dispatch();
    
} catch (Exception $e) {
    echo "<h1>오류 발생</h1>";
    echo "<p>오류 메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Error $e) {
    echo "<h1>치명적 오류 발생</h1>";
    echo "<p>오류 메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>