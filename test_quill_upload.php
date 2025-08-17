<?php
/**
 * 🚀 Quill 이미지 업로드 API 테스트
 */

session_start();
require_once '/var/www/html/topmkt/src/config/bootstrap.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

// 관리자로 로그인 설정 (테스트용)
$_SESSION['user'] = [
    'id' => 4,
    'role' => 'ROLE_ADMIN',
    'corp_status' => 'approved'
];

// CSRF 토큰 생성
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 테스트 이미지 생성 (1x1 PNG)
$testImagePath = '/tmp/test_quill_image.png';
$imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
file_put_contents($testImagePath, $imageData);

// FormData 시뮬레이션
$_FILES['image'] = [
    'name' => 'test_quill_image.png',
    'type' => 'image/png',
    'tmp_name' => $testImagePath,
    'error' => 0,
    'size' => strlen($imageData)
];

$_POST['csrf_token'] = $_SESSION['csrf_token'];
$_POST['upload_type'] = 'notices';
$_POST['is_quill_upload'] = 'true';

echo "🚀 Quill 이미지 업로드 API 테스트\n";
echo "================================\n\n";

echo "📋 요청 정보:\n";
echo "- 파일명: " . $_FILES['image']['name'] . "\n";
echo "- 파일 크기: " . $_FILES['image']['size'] . " bytes\n";
echo "- CSRF 토큰: " . substr($_SESSION['csrf_token'], 0, 16) . "...\n";
echo "- 업로드 타입: " . $_POST['upload_type'] . "\n";
echo "- Quill 업로드: " . $_POST['is_quill_upload'] . "\n\n";

// MediaController 직접 호출
require_once SRC_PATH . '/controllers/MediaController.php';

ob_start();
$controller = new MediaController();
$result = $controller->uploadImage();
$output = ob_get_clean();

echo "📤 MediaController 응답:\n";
echo $output . "\n\n";

// 업로드된 파일 확인
$uploadDir = '/var/www/html/topmkt/public/assets/uploads/notices-content/2025/08/';
if (is_dir($uploadDir)) {
    $files = array_diff(scandir($uploadDir), array('.', '..'));
    echo "📁 업로드 디렉토리 파일들:\n";
    foreach ($files as $file) {
        $filePath = $uploadDir . $file;
        if (is_file($filePath)) {
            $fileTime = date('Y-m-d H:i:s', filemtime($filePath));
            $fileSize = filesize($filePath);
            echo "- $file (생성: $fileTime, 크기: $fileSize bytes)\n";
        }
    }
}

// 정리
unlink($testImagePath);

echo "\n✅ 테스트 완료\n";
?>