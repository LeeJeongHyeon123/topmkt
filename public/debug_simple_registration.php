<?php
/**
 * 간단한 강의 신청 오류 디버깅
 */

// 메모리 한계 늘리기
ini_set('memory_limit', '512M');

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

echo "🔧 간단한 강의 신청 오류 디버깅\n\n";

// 세션 시작
session_start();

try {
    // 기본 설정 로드
    require_once CONFIG_PATH . '/paths.php';
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    
    echo "✅ 시스템 로드 완료\n";
    
    // 로그인 상태 확인
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    echo "로그인 상태: " . ($isLoggedIn ? "로그인됨" : "로그인 안됨") . "\n";
    
    if (!$isLoggedIn) {
        echo "❌ 로그인이 필요합니다.\n";
        exit;
    }
    
    $userId = AuthMiddleware::getCurrentUserId();
    echo "사용자 ID: $userId\n";
    
    // 에러 로그 마지막 10줄만 확인
    echo "\n📋 최근 에러 로그:\n";
    $errorLogPath = '/var/www/html/topmkt/logs/topmkt_errors.log';
    if (file_exists($errorLogPath)) {
        $logContent = file_get_contents($errorLogPath);
        $recentLogs = array_slice(explode("\n", $logContent), -10);
        foreach ($recentLogs as $log) {
            if (trim($log)) {
                echo trim($log) . "\n";
            }
        }
    }
    
    // PHP 에러 로그도 확인
    echo "\n📋 PHP 에러 로그:\n";
    $phpErrorLog = '/var/log/php_errors.log';
    if (file_exists($phpErrorLog)) {
        $phpLogs = array_slice(explode("\n", file_get_contents($phpErrorLog)), -5);
        foreach ($phpLogs as $log) {
            if (trim($log)) {
                echo trim($log) . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . "\n";
    echo "라인: " . $e->getLine() . "\n";
}
?>