<?php
/**
 * CLI에서 업로드 설정 확인
 */

require_once '/var/www/html/topmkt/src/config/database.php';
require_once '/var/www/html/topmkt/src/config/upload.php';

echo "=== TOPMKT 업로드 설정 확인 ===\n\n";

echo "📋 공통 설정:\n";
echo "- 최대 파일 크기: " . UploadConfig::getMaxFileSizeMB() . "MB\n";
echo "- 허용 이미지 확장자: " . implode(', ', UploadConfig::ALLOWED_IMAGE_EXTENSIONS) . "\n";
echo "- 허용 문서 확장자: " . implode(', ', UploadConfig::ALLOWED_DOCUMENT_EXTENSIONS) . "\n\n";

echo "🔧 PHP 설정:\n";
echo "- upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "- post_max_size: " . ini_get('post_max_size') . "\n";
echo "- memory_limit: " . ini_get('memory_limit') . "\n\n";

echo "✅ 테스트 결과:\n";
$uploadOk = ini_get('upload_max_filesize') >= '30M';
$postOk = ini_get('post_max_size') >= '50M';
$memoryOk = ini_get('memory_limit') >= '256M' || ini_get('memory_limit') == '-1';

echo "- PHP upload_max_filesize: " . ($uploadOk ? "✅ OK" : "❌ FAIL") . "\n";
echo "- PHP post_max_size: " . ($postOk ? "✅ OK" : "❌ FAIL") . "\n";
echo "- PHP memory_limit: " . ($memoryOk ? "✅ OK" : "❌ FAIL") . "\n";

$allOk = $uploadOk && $postOk && $memoryOk;
echo "\n🎯 종합 결과: " . ($allOk ? "✅ 모든 설정 정상" : "❌ 일부 설정 문제") . "\n";

if ($allOk) {
    echo "\n🚀 30MB 이하 파일 업로드가 정상적으로 작동할 것입니다!\n";
} else {
    echo "\n⚠️ 일부 PHP 설정을 확인해주세요.\n";
}
?>