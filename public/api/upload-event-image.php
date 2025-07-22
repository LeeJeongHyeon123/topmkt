<?php
/**
 * 이벤트 이미지 업로드 API
 */

// 경로 설정
define('ROOT_PATH', '/workspace/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';

// JSON 응답 헤더 설정
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseHelper::json(['success' => false, 'message' => 'POST 요청만 허용됩니다.'], 405);
    exit;
}

// 파일 업로드 확인
if (!isset($_FILES['event_image']) || $_FILES['event_image']['error'] !== UPLOAD_ERR_OK) {
    ResponseHelper::json(['success' => false, 'message' => '파일 업로드에 실패했습니다.'], 400);
    exit;
}

$file = $_FILES['event_image'];

// 파일 크기 검증 (5MB 제한)
$maxSize = 5 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    ResponseHelper::json(['success' => false, 'message' => '파일 크기는 5MB를 초과할 수 없습니다.'], 400);
    exit;
}

// 파일 타입 검증
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$fileType = mime_content_type($file['tmp_name']);

if (!in_array($fileType, $allowedTypes)) {
    ResponseHelper::json(['success' => false, 'message' => 'JPG, PNG, GIF, WebP 파일만 업로드 가능합니다.'], 400);
    exit;
}

// 업로드 디렉토리 생성
$uploadDir = ROOT_PATH . '/public/assets/uploads/events/' . date('Y/m');
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// 고유 파일명 생성
$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
$uploadPath = $uploadDir . '/' . $fileName;

// 파일 이동
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    // 웹 경로 생성
    $webPath = '/assets/uploads/events/' . date('Y/m') . '/' . $fileName;
    
    // 성공 응답
    ResponseHelper::json([
        'success' => true,
        'message' => '이미지가 성공적으로 업로드되었습니다.',
        'data' => [
            'url' => $webPath,
            'filename' => $fileName,
            'original_name' => $file['name'],
            'size' => $file['size'],
            'type' => $fileType
        ]
    ], 200);
} else {
    ResponseHelper::json(['success' => false, 'message' => '파일 저장에 실패했습니다.'], 500);
}
?>