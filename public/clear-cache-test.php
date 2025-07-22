<?php
/**
 * 캐시 클리어 및 커뮤니티 테스트
 */

// OPcache 클리어 (가능한 경우)
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p>✅ OPcache 초기화됨</p>";
} else {
    echo "<p>ℹ️ OPcache 사용하지 않음</p>";
}

// 에러 표시 활성화
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

echo "<h1>캐시 클리어 후 커뮤니티 테스트</h1>";

try {
    // WebLogger 직접 테스트
    require_once SRC_PATH . '/helpers/WebLogger.php';
    
    WebLogger::init();
    WebLogger::info('캐시 클리어 테스트 로그');
    
    echo "<p>✅ WebLogger 테스트 성공</p>";
    
    // 실제 커뮤니티 URL 호출 테스트
    $community_url = 'https://www.topmktx.com/community';
    
    $context = stream_context_create([
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: Mozilla/5.0\r\n",
            "timeout" => 10
        ]
    ]);
    
    echo "<p>🔄 커뮤니티 URL 직접 호출 테스트...</p>";
    
    $response = @file_get_contents($community_url, false, $context);
    
    if ($response === false) {
        $error = error_get_last();
        echo "<p>❌ 커뮤니티 페이지 호출 실패</p>";
        echo "<p>오류: " . ($error['message'] ?? 'Unknown error') . "</p>";
        
        // HTTP 응답 헤더 확인
        if (isset($http_response_header)) {
            echo "<p>응답 헤더:</p>";
            echo "<pre>" . implode("\n", $http_response_header) . "</pre>";
        }
    } else {
        echo "<p>✅ 커뮤니티 페이지 호출 성공</p>";
        echo "<p>응답 크기: " . strlen($response) . " bytes</p>";
        
        // 에러 메시지 체크
        if (strpos($response, 'Fatal error') !== false) {
            echo "<p>❌ 응답에 Fatal error 포함됨</p>";
            $start = strpos($response, 'Fatal error');
            echo "<pre>" . htmlspecialchars(substr($response, $start, 500)) . "</pre>";
        } elseif (strpos($response, '시스템에 심각한 문제') !== false) {
            echo "<p>❌ 시스템 오류 메시지 포함됨</p>";
        } else {
            echo "<p>✅ 정상적인 응답으로 보임</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ 예외 발생: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #333; }
</style>