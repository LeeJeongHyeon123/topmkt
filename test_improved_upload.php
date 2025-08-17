<?php
/**
 * 🚀 개선된 이미지 업로드 API 테스트
 */

// 세션 시작
session_start();

// 테스트 사용자 설정 (관리자)
$_SESSION['user_id'] = 4;
$_SESSION['user'] = [
    'id' => 4,
    'role' => 'ROLE_ADMIN'
];

// CSRF 토큰 생성
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

echo "🚀 개선된 이미지 업로드 API 테스트\n";
echo "===========================\n\n";

echo "📋 세션 정보:\n";
echo "- 사용자 ID: " . ($_SESSION['user_id'] ?? 'N/A') . "\n";
echo "- CSRF 토큰: " . substr($_SESSION['csrf_token'], 0, 16) . "...\n\n";

// 테스트 이미지 생성
$testImageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
$testImagePath = '/tmp/test_quill_improved.png';
file_put_contents($testImagePath, $testImageData);

// $_FILES 시뮬레이션
$_FILES['image'] = [
    'name' => 'test_quill_improved.png',
    'type' => 'image/png',
    'tmp_name' => $testImagePath,
    'error' => 0,
    'size' => strlen($testImageData)
];

// $_POST 데이터 설정
$_POST['csrf_token'] = $_SESSION['csrf_token'];
$_POST['upload_type'] = 'notices';
$_POST['is_quill_upload'] = 'true';

echo "📤 업로드 요청 정보:\n";
echo "- 파일명: " . $_FILES['image']['name'] . "\n";
echo "- 파일 크기: " . $_FILES['image']['size'] . " bytes\n";
echo "- 업로드 타입: " . $_POST['upload_type'] . "\n";
echo "- Quill 업로드: " . $_POST['is_quill_upload'] . "\n";
echo "- CSRF 토큰: " . substr($_POST['csrf_token'], 0, 16) . "...\n\n";

// HTTP 헤더 시뮬레이션
$_SERVER['HTTP_REFERER'] = 'https://www.topmktx.com/notices/write';
$_SERVER['REQUEST_METHOD'] = 'POST';

// 필요한 상수와 경로 설정
define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

// MediaController 테스트
try {
    require_once SRC_PATH . '/controllers/MediaController.php';
    
    echo "🔧 MediaController 호출 중...\n";
    
    ob_start();
    $controller = new MediaController();
    $result = $controller->uploadImage();
    $output = ob_get_clean();
    
    echo "📋 컨트롤러 출력:\n";
    echo $output . "\n\n";
    
    // 최근 PHP 에러 로그 확인
    echo "📄 PHP 에러 로그 (최근 10줄):\n";
    $logFile = '/var/www/html/topmkt/logs/php_errors.log';
    if (file_exists($logFile)) {
        $lines = file($logFile);
        $recentLines = array_slice($lines, -10);
        foreach ($recentLines as $line) {
            echo $line;
        }
    } else {
        echo "로그 파일 없음\n";
    }
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}

// 정리
unlink($testImagePath);

echo "\n✅ 테스트 완료\n";
?>