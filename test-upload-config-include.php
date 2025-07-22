<?php
// 업로드 설정 include 테스트 페이지
?>
<!DOCTYPE html>
<html>
<head>
    <title>업로드 설정 테스트</title>
</head>
<body>
    <h1>🧪 업로드 설정 Include 테스트</h1>
    
    <h2>1. 업로드 설정 로드</h2>
    <div id="config-status">로딩 중...</div>
    
    <h2>2. 76.8KB 파일 크기 검증</h2>
    <div id="validation-result">검증 중...</div>
    
    <!-- 업로드 설정 Include -->
    <?php include '/var/www/html/topmkt/src/views/includes/upload-config.js.php'; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 설정 로드 확인
        if (typeof window.TOPMKT_UPLOAD_CONFIG !== 'undefined') {
            document.getElementById('config-status').innerHTML = 
                '✅ 설정 로드 성공<br>' +
                '최대 파일 크기: ' + window.TOPMKT_UPLOAD_CONFIG.maxFileSize + ' bytes (' + 
                window.TOPMKT_UPLOAD_CONFIG.maxFileSizeMB + 'MB)';
        } else {
            document.getElementById('config-status').innerHTML = '❌ 설정 로드 실패';
        }
        
        // 76.8KB 검증 테스트
        if (typeof window.validateFileSize === 'function') {
            const testSize = 78657; // 76.8KB
            const result = window.validateFileSize(testSize);
            document.getElementById('validation-result').innerHTML = 
                '파일 크기: ' + testSize + ' bytes<br>' +
                '검증 결과: ' + (result ? '✅ 통과' : '❌ 실패');
        } else {
            document.getElementById('validation-result').innerHTML = '❌ validateFileSize 함수 없음';
        }
    });
    </script>
</body>
</html>