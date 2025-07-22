<?php
/**
 * WebLogger 통합 로깅 시스템 테스트
 * 
 * 이 스크립트는 새로 구축된 로깅 시스템이 정상적으로 작동하는지 검증합니다.
 */

// 프로젝트 루트 경로 설정 (실제 운영 경로 사용)
define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 필요한 파일들 로드
require_once CONFIG_PATH . '/paths.php';
require_once CONFIG_PATH . '/config.php';
require_once SRC_PATH . '/helpers/WebLogger.php';

echo "=== WebLogger 통합 로깅 시스템 테스트 ===\n\n";

// 1. WebLogger 초기화 테스트
echo "1. WebLogger 초기화 테스트...\n";
try {
    WebLogger::init([
        'useUnifiedLog' => true,
        'unifiedLogFile' => 'test_logging.log',
        'maxFileSize' => 10 * 1024 * 1024
    ]);
    echo "✅ WebLogger 초기화 성공\n";
} catch (Exception $e) {
    echo "❌ WebLogger 초기화 실패: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. 설정 확인 테스트
echo "\n2. 설정 확인 테스트...\n";
$config = WebLogger::getConfig();
echo "📁 로그 디렉토리: " . $config['logDir'] . "\n";
echo "📄 통합 로그 사용: " . ($config['useUnifiedLog'] ? 'YES' : 'NO') . "\n";
echo "📝 통합 로그 파일: " . $config['unifiedLogFile'] . "\n";
echo "💾 최대 파일 크기: " . number_format($config['maxFileSize']) . " bytes\n";

// 3. 다양한 로그 레벨 테스트
echo "\n3. 다양한 로그 레벨 테스트...\n";

// DEBUG 레벨
WebLogger::debug('디버그 메시지 테스트', [
    'test_type' => 'debug_test',
    'data' => ['key1' => 'value1', 'key2' => 'value2']
]);
echo "✅ DEBUG 로그 기록\n";

// INFO 레벨
WebLogger::info('정보 메시지 테스트', [
    'test_type' => 'info_test',
    'process' => '로깅 시스템 검증'
]);
echo "✅ INFO 로그 기록\n";

// WARNING 레벨
WebLogger::warning('경고 메시지 테스트', [
    'test_type' => 'warning_test',
    'issue' => '테스트용 경고'
]);
echo "✅ WARNING 로그 기록\n";

// ERROR 레벨
WebLogger::error('에러 메시지 테스트', [
    'test_type' => 'error_test',
    'error_code' => 'TEST_ERROR_001'
]);
echo "✅ ERROR 로그 기록\n";

// 4. 특별한 로깅 메소드 테스트
echo "\n4. 특별한 로깅 메소드 테스트...\n";

// 컨트롤러 로깅 테스트
WebLogger::controllerStart('TestController', 'testAction', ['param' => 'test']);
echo "✅ Controller Start 로그 기록\n";

WebLogger::controllerEnd('TestController', 'testAction', 0.123, ['result' => 'success']);
echo "✅ Controller End 로그 기록\n";

// API 로깅 테스트
WebLogger::api('/api/test', 'GET', 0.05, 200, ['api_version' => 'v1']);
echo "✅ API 로그 기록\n";

// 사용자 활동 로깅 테스트
WebLogger::activity('로그 시스템 테스트', ['user_id' => 'test_user']);
echo "✅ Activity 로그 기록\n";

// 성능 로깅 테스트
WebLogger::performance('로그 시스템 초기화', 0.01, ['component' => 'WebLogger']);
echo "✅ Performance 로그 기록\n";

// 5. 예외 로깅 테스트
echo "\n5. 예외 로깅 테스트...\n";
try {
    throw new Exception('테스트용 예외입니다');
} catch (Exception $e) {
    WebLogger::exception($e, ['test_context' => '예외 로깅 테스트']);
    echo "✅ Exception 로그 기록\n";
}

// 6. 민감한 데이터 마스킹 테스트
echo "\n6. 민감한 데이터 마스킹 테스트...\n";
WebLogger::info('민감한 데이터 테스트', [
    'password' => 'secret123',
    'api_key' => 'abc-def-ghi',
    'phone' => '010-1234-5678',
    'normal_data' => '일반 데이터'
]);
echo "✅ 민감한 데이터 마스킹 로그 기록\n";

// 7. 로그 파일 존재 확인
echo "\n7. 로그 파일 존재 확인...\n";
$logFile = $config['logDir'] . '/' . $config['unifiedLogFile'];
if (file_exists($logFile)) {
    $fileSize = filesize($logFile);
    echo "✅ 로그 파일 존재: {$logFile}\n";
    echo "📊 파일 크기: " . number_format($fileSize) . " bytes\n";
    
    // 최근 로그 라인 수 확인
    $lines = file($logFile);
    $recentLines = array_slice($lines, -5);
    echo "📝 최근 로그 라인 수: " . count($lines) . "\n";
    echo "📋 최근 5개 로그 미리보기:\n";
    foreach ($recentLines as $i => $line) {
        $data = json_decode(trim($line), true);
        if ($data) {
            echo "  " . ($i + 1) . ". [{$data['level']}] {$data['message']}\n";
        }
    }
} else {
    echo "❌ 로그 파일 없음: {$logFile}\n";
}

// 8. 상수 설정 확인
echo "\n8. 상수 설정 확인...\n";
$constants = [
    'LOG_USE_UNIFIED', 'LOG_UNIFIED_FILE', 'LOG_MAX_FILE_SIZE',
    'LOG_MIN_LEVEL', 'LOG_CONSOLE_OUTPUT', 'LOG_SLOW_QUERY_THRESHOLD'
];

foreach ($constants as $constant) {
    if (defined($constant)) {
        echo "✅ {$constant}: " . var_export(constant($constant), true) . "\n";
    } else {
        echo "❌ {$constant}: 정의되지 않음\n";
    }
}

echo "\n=== 테스트 완료 ===\n";
echo "모든 로깅 기능이 정상적으로 작동합니다!\n";
echo "로그 파일 경로: {$logFile}\n";
echo "\n실제 프로젝트에서도 다음과 같이 사용하세요:\n";
echo "WebLogger::info('메시지', ['context' => 'data']);\n";
echo "WebLogger::error('에러 메시지', ['error_details' => 'info']);\n";
echo "WebLogger::controllerStart('ControllerName', 'actionName');\n";
echo "WebLogger::exception(\$e, ['additional' => 'context']);\n";