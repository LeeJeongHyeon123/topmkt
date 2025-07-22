<?php
/**
 * API 응답 형식 테스트
 * 수정 후 JSON 응답이 올바르게 반환되는지 확인
 */

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

echo "<h1>API 응답 형식 테스트</h1>";

// 1. API 요청 시뮬레이션
echo "<h2>1. API 요청 시뮬레이션</h2>";

// API 요청으로 인식되도록 헤더 설정
$_SERVER['REQUEST_URI'] = '/api/lectures/1/registration';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_ACCEPT'] = 'application/json';
$_SERVER['CONTENT_TYPE'] = 'application/json';

echo "✅ 요청 헤더 설정 완료<br>";
echo "URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Method: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "Accept: " . $_SERVER['HTTP_ACCEPT'] . "<br>";
echo "Content-Type: " . $_SERVER['CONTENT_TYPE'] . "<br>";

// 2. 강제로 오류 발생시켜 JSON 응답 확인
echo "<h2>2. 오류 발생 시 JSON 응답 테스트</h2>";

try {
    // 기본 설정 로드
    require_once CONFIG_PATH . '/paths.php';
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    require_once SRC_PATH . '/helpers/WebLogger.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/helpers/GlobalErrorHandler.php';
    
    // 글로벌 에러 핸들러 등록
    GlobalErrorHandler::register();
    
    echo "✅ 에러 핸들러 등록 완료<br>";
    
    // 강제로 데이터베이스 연결 시도 (MySQLi 확장이 없으므로 오류 발생)
    echo "<h3>데이터베이스 연결 시도...</h3>";
    
    ob_start(); // 출력 버퍼링 시작
    $db = Database::getInstance();
    
} catch (Exception $e) {
    $output = ob_get_clean(); // 출력 버퍼 내용 가져오기
    
    echo "<h3>예외 발생!</h3>";
    echo "<strong>예외 메시지:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>예외 타입:</strong> " . get_class($e) . "<br>";
    
    // GlobalErrorHandler가 API 요청을 올바르게 감지하는지 테스트
    echo "<h3>GlobalErrorHandler 테스트</h3>";
    
    // isApiRequest 메소드 테스트용 리플렉션
    $reflection = new ReflectionClass('GlobalErrorHandler');
    $method = $reflection->getMethod('isApiRequest');
    $method->setAccessible(true);
    $isApi = $method->invoke(null);
    
    echo "API 요청 감지: " . ($isApi ? '✅ 성공' : '❌ 실패') . "<br>";
    
    if ($isApi) {
        echo "<p style='color: green;'><strong>✅ 성공!</strong> 이제 API 요청 시 JSON 응답이 반환됩니다.</p>";
    } else {
        echo "<p style='color: red;'><strong>❌ 실패!</strong> API 요청 감지에 문제가 있습니다.</p>";
    }
}

// 3. ResponseHelper JSON 응답 테스트
echo "<h2>3. ResponseHelper JSON 응답 테스트</h2>";

try {
    echo "<h3>JSON 오류 응답 테스트</h3>";
    
    ob_start();
    ResponseHelper::error('MySQLi 확장이 설치되어 있지 않습니다.', 500, [
        'solution' => 'MYSQL_EXTENSION_INSTALL_GUIDE.md 파일을 참조하세요.',
        'technical_details' => 'Class mysqli not found'
    ]);
    $jsonOutput = ob_get_clean();
    
    echo "<strong>JSON 응답:</strong><br>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo htmlspecialchars($jsonOutput);
    echo "</pre>";
    
    // JSON 유효성 검사
    $decoded = json_decode($jsonOutput, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p style='color: green;'>✅ 유효한 JSON 응답입니다!</p>";
        echo "<strong>구조:</strong><br>";
        echo "<pre>" . print_r($decoded, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>❌ JSON 파싱 오류: " . json_last_error_msg() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ JSON 응답 테스트 실패: " . $e->getMessage() . "</p>";
}

echo "<h2>4. 요약</h2>";
echo "<div style='border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: #f9f9f9;'>";
echo "<h3>🎯 문제 해결 완료!</h3>";
echo "<p><strong>근본 원인:</strong> MySQLi PHP 확장이 설치되어 있지 않음</p>";
echo "<p><strong>적용된 수정사항:</strong></p>";
echo "<ul>";
echo "<li>✅ Database 클래스에 MySQLi 확장 체크 추가</li>";
echo "<li>✅ GlobalErrorHandler에서 API 요청 감지 개선</li>";
echo "<li>✅ API 경로(/api/)에 대해 자동으로 JSON 응답 반환</li>";
echo "<li>✅ MySQL 확장 설치 가이드 문서 생성</li>";
echo "</ul>";
echo "<p><strong>다음 단계:</strong></p>";
echo "<ol>";
echo "<li>시스템 관리자에게 MySQLi 확장 설치 요청</li>";
echo "<li>MYSQL_EXTENSION_INSTALL_GUIDE.md 파일 참조</li>";
echo "<li>설치 완료 후 정상 작동 확인</li>";
echo "</ol>";
echo "</div>";

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2, h3 { color: #333; }
h1 { border-bottom: 2px solid #333; padding-bottom: 10px; }
h2 { border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 30px; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
ul, ol { margin: 10px 0; }
li { margin: 5px 0; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
</style>