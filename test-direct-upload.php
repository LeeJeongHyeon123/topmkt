<?php
/**
 * 직접 업로드 테스트 스크립트
 * 실제 컨트롤러 로직을 시뮬레이션하여 업로드 검증
 */

require_once '/var/www/html/topmkt/src/config/database.php';
require_once '/var/www/html/topmkt/src/config/upload.php';

echo "=== 업로드 기능 직접 테스트 ===\n\n";

// 25MB 파일 테스트
$testFile = [
    'name' => 'test-25mb.jpg',
    'size' => 26214400, // 25MB
    'type' => 'image/jpeg',
    'tmp_name' => '/tmp/test-25mb.jpg'
];

echo "📁 테스트 파일 정보:\n";
echo "- 파일명: {$testFile['name']}\n";
echo "- 크기: " . number_format($testFile['size']) . " 바이트 (" . round($testFile['size']/1024/1024, 1) . "MB)\n";
echo "- 타입: {$testFile['type']}\n\n";

echo "🔍 업로드 설정 확인:\n";
echo "- 최대 허용 크기: " . UploadConfig::getMaxFileSizeMB() . "MB\n";
echo "- 허용 확장자: " . implode(', ', UploadConfig::ALLOWED_IMAGE_EXTENSIONS) . "\n\n";

echo "✅ 검증 테스트:\n";

// 파일 크기 검증
$sizeOk = UploadConfig::validateFileSize($testFile['size']);
echo "- 파일 크기 검증: " . ($sizeOk ? "✅ 통과" : "❌ 실패") . "\n";

// 확장자 검증 (파일명에서 확장자 추출)
$extension = pathinfo($testFile['name'], PATHINFO_EXTENSION);
$extensionOk = UploadConfig::validateImageExtension($extension);
echo "- 확장자 검증: " . ($extensionOk ? "✅ 통과" : "❌ 실패") . "\n";

// MIME 타입 검증
$mimeOk = UploadConfig::validateImageMimeType($testFile['type']);
echo "- MIME 타입 검증: " . ($mimeOk ? "✅ 통과" : "❌ 실패") . "\n";

$allOk = $sizeOk && $extensionOk && $mimeOk;
echo "\n🎯 종합 결과: " . ($allOk ? "✅ 업로드 가능" : "❌ 업로드 불가") . "\n";

if (!$allOk) {
    echo "\n❌ 실패 원인:\n";
    if (!$sizeOk) {
        echo "- 파일 크기가 " . UploadConfig::getMaxFileSizeMB() . "MB를 초과합니다.\n";
    }
    if (!$extensionOk) {
        echo "- 허용되지 않는 파일 확장자입니다.\n";
    }
    if (!$mimeOk) {
        echo "- 허용되지 않는 MIME 타입입니다.\n";
    }
}

// 31MB 파일 테스트 (실패해야 함)
echo "\n" . str_repeat("=", 50) . "\n";
echo "31MB 파일 테스트 (실패 예상)\n";
echo str_repeat("=", 50) . "\n";

$largeFile = [
    'name' => 'test-31mb.jpg',
    'size' => 32505856, // 31MB
    'type' => 'image/jpeg'
];

echo "📁 큰 파일 정보:\n";
echo "- 파일명: {$largeFile['name']}\n";
echo "- 크기: " . number_format($largeFile['size']) . " 바이트 (" . round($largeFile['size']/1024/1024, 1) . "MB)\n\n";

$largeSizeOk = UploadConfig::validateFileSize($largeFile['size']);
echo "✅ 31MB 파일 크기 검증: " . ($largeSizeOk ? "✅ 통과 (문제!)" : "❌ 실패 (정상)") . "\n";

if (!$largeSizeOk) {
    echo "👍 30MB 초과 파일이 정상적으로 거부되었습니다.\n";
} else {
    echo "⚠️ 문제: 30MB 초과 파일이 허용되었습니다!\n";
}

echo "\n🏁 테스트 완료\n";
?>