<?php
// 실제 이벤트 페이지 JavaScript 설정 디버깅

echo "🔍 실제 이벤트 페이지 디버깅\n\n";

// 현재 작업 디렉토리를 이벤트 뷰 위치로 설정
chdir('/var/www/html/topmkt/src/views/events');

echo "=== 1. include 경로 시뮬레이션 ===\n";
$includePath = '/var/www/html/topmkt/src/views/includes/upload-config.js.php';
echo "Include 경로: $includePath\n";
echo "파일 존재: " . (file_exists($includePath) ? "✅ YES" : "❌ NO") . "\n\n";

if (file_exists($includePath)) {
    echo "=== 2. include 파일 내용 캡처 ===\n";
    
    // 실제 PHP include 시뮬레이션
    ob_start();
    include $includePath;
    $jsOutput = ob_get_clean();
    
    echo "JavaScript 출력 (처음 300자):\n";
    echo "```javascript\n";
    echo substr($jsOutput, 0, 300) . "...\n";
    echo "```\n\n";
    
    // 핵심 설정값 확인
    echo "=== 3. 핵심 설정값 확인 ===\n";
    if (preg_match('/"maxFileSize":(\d+)/', $jsOutput, $matches)) {
        $maxFileSize = $matches[1];
        echo "✅ maxFileSize 발견: " . number_format($maxFileSize) . " bytes\n";
        echo "   = " . round($maxFileSize / 1024 / 1024, 1) . "MB\n";
        
        // 76.8KB 검증
        $testSize = 78657;
        $shouldPass = $testSize <= $maxFileSize;
        echo "✅ 76.8KB 검증: $testSize <= $maxFileSize = " . ($shouldPass ? "통과" : "실패") . "\n";
    } else {
        echo "❌ maxFileSize 없음\n";
    }
    
    if (strpos($jsOutput, 'validateFileSize') !== false) {
        echo "✅ validateFileSize 함수 있음\n";
    } else {
        echo "❌ validateFileSize 함수 없음\n";
    }
    
    if (strpos($jsOutput, 'TOPMKT_UPLOAD_CONFIG') !== false) {
        echo "✅ TOPMKT_UPLOAD_CONFIG 객체 있음\n";
    } else {
        echo "❌ TOPMKT_UPLOAD_CONFIG 객체 없음\n";
    }
}

echo "\n=== 4. MediaController 설정 확인 ===\n";
require_once '/var/www/html/topmkt/src/config/upload.php';
echo "UploadConfig::getMaxFileSize(): " . number_format(UploadConfig::getMaxFileSize()) . " bytes\n";
echo "UploadConfig::getMaxFileSizeMB(): " . UploadConfig::getMaxFileSizeMB() . "MB\n";

echo "\n✅ 디버깅 완료!\n";
?>