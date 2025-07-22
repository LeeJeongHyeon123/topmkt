<?php
/**
 * 500 에러 실시간 디버깅
 */

// 에러 출력 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>500 에러 실시간 디버깅</h1>";
echo "<p>현재 시간: " . date('Y-m-d H:i:s') . "</p>";

try {
    // 기본 설정 확인
    echo "<h2>1. 기본 설정 확인</h2>";
    echo "<p>✅ PHP 버전: " . PHP_VERSION . "</p>";
    echo "<p>✅ 메모리 한도: " . ini_get('memory_limit') . "</p>";
    echo "<p>✅ 최대 실행 시간: " . ini_get('max_execution_time') . "초</p>";
    
    // 경로 설정 확인
    echo "<h2>2. 경로 설정 확인</h2>";
    define('ROOT_PATH', dirname(__DIR__));
    define('SRC_PATH', ROOT_PATH . '/src');
    define('CONFIG_PATH', SRC_PATH . '/config');
    
    echo "<p>✅ ROOT_PATH: " . ROOT_PATH . "</p>";
    echo "<p>✅ SRC_PATH: " . SRC_PATH . "</p>";
    echo "<p>✅ CONFIG_PATH: " . CONFIG_PATH . "</p>";
    
    // 필수 파일 존재 확인
    echo "<h2>3. 필수 파일 존재 확인</h2>";
    $requiredFiles = [
        CONFIG_PATH . '/config.php',
        CONFIG_PATH . '/database.php',
        CONFIG_PATH . '/routes.php',
        SRC_PATH . '/helpers/WebLogger.php',
        SRC_PATH . '/helpers/ResponseHelper.php',
        SRC_PATH . '/helpers/GlobalErrorHandler.php',
        SRC_PATH . '/helpers/LogAnalyzer.php'
    ];
    
    foreach ($requiredFiles as $file) {
        if (file_exists($file)) {
            echo "<p>✅ " . basename($file) . "</p>";
        } else {
            echo "<p>❌ " . basename($file) . " - 파일 없음</p>";
        }
    }
    
    // 설정 파일 로드 테스트
    echo "<h2>4. 설정 파일 로드 테스트</h2>";
    require_once CONFIG_PATH . '/config.php';
    echo "<p>✅ config.php 로드 완료</p>";
    
    require_once CONFIG_PATH . '/database.php';
    echo "<p>✅ database.php 로드 완료</p>";
    
    require_once CONFIG_PATH . '/routes.php';
    echo "<p>✅ routes.php 로드 완료</p>";
    
    // 헬퍼 클래스 로드 테스트
    echo "<h2>5. 헬퍼 클래스 로드 테스트</h2>";
    require_once SRC_PATH . '/helpers/WebLogger.php';
    echo "<p>✅ WebLogger 로드 완료</p>";
    
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    echo "<p>✅ ResponseHelper 로드 완료</p>";
    
    require_once SRC_PATH . '/helpers/GlobalErrorHandler.php';
    echo "<p>✅ GlobalErrorHandler 로드 완료</p>";
    
    require_once SRC_PATH . '/helpers/LogAnalyzer.php';
    echo "<p>✅ LogAnalyzer 로드 완료</p>";
    
    // 클래스 존재 확인
    echo "<h2>6. 클래스 존재 확인</h2>";
    $classes = ['WebLogger', 'ResponseHelper', 'GlobalErrorHandler', 'LogAnalyzer', 'LogLevel'];
    foreach ($classes as $class) {
        if (class_exists($class)) {
            echo "<p>✅ {$class} 클래스 존재</p>";
        } else {
            echo "<p>❌ {$class} 클래스 없음</p>";
        }
    }
    
    // 글로벌 에러 핸들러 등록 테스트
    echo "<h2>7. 글로벌 에러 핸들러 등록 테스트</h2>";
    GlobalErrorHandler::register();
    echo "<p>✅ 글로벌 에러 핸들러 등록 완료</p>";
    
    // 로깅 시스템 초기화 테스트
    echo "<h2>8. 로깅 시스템 초기화 테스트</h2>";
    WebLogger::init();
    echo "<p>✅ 로깅 시스템 초기화 완료</p>";
    
    // 로그 디렉토리 확인
    $logDir = '/workspace/logs/';
    if (is_dir($logDir)) {
        if (is_writable($logDir)) {
            echo "<p>✅ 로그 디렉토리 쓰기 가능: {$logDir}</p>";
        } else {
            echo "<p>❌ 로그 디렉토리 쓰기 불가: {$logDir}</p>";
        }
    } else {
        echo "<p>❌ 로그 디렉토리 없음: {$logDir}</p>";
    }
    
    // 세션 시작 테스트
    echo "<h2>9. 세션 시작 테스트</h2>";
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        echo "<p>✅ 세션 시작 완료</p>";
    } else {
        echo "<p>✅ 세션 이미 시작됨</p>";
    }
    
    // 기본 로그 테스트
    echo "<h2>10. 기본 로그 테스트</h2>";
    WebLogger::info('Debug test message', ['test' => true]);
    echo "<p>✅ 로그 기록 테스트 완료</p>";
    
    // 데이터베이스 연결 테스트
    echo "<h2>11. 데이터베이스 연결 테스트</h2>";
    try {
        $db = Database::getInstance();
        $result = $db->fetchOne("SELECT 1 as test");
        if ($result && $result['test'] == 1) {
            echo "<p>✅ 데이터베이스 연결 성공</p>";
        } else {
            echo "<p>❌ 데이터베이스 쿼리 실패</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ 데이터베이스 연결 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // Router 클래스 테스트
    echo "<h2>12. Router 클래스 테스트</h2>";
    if (class_exists('Router')) {
        echo "<p>✅ Router 클래스 존재</p>";
        
        // 라우터 인스턴스 생성 테스트
        try {
            $router = new Router();
            echo "<p>✅ Router 인스턴스 생성 성공</p>";
        } catch (Exception $e) {
            echo "<p>❌ Router 인스턴스 생성 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p>❌ Router 클래스 없음</p>";
    }
    
    echo "<h2>🎉 모든 테스트 완료!</h2>";
    echo "<p>500 에러가 발생한다면 위의 항목 중 실패한 부분을 확인하세요.</p>";
    
} catch (ParseError $e) {
    echo "<h2>❌ 파싱 에러</h2>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<p>메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Error $e) {
    echo "<h2>❌ Fatal Error</h2>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<p>메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Exception $e) {
    echo "<h2>❌ 예외 발생</h2>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<p>메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1 { color: #333; }
h2 { color: #666; border-bottom: 1px solid #ddd; }
p { margin: 5px 0; }
</style>