<?php
/**
 * JavaScript 설정 테스트
 * 클라이언트 측 업로드 검증이 제대로 작동하는지 확인
 */

require_once '/var/www/html/topmkt/src/config/database.php';
require_once '/var/www/html/topmkt/src/config/upload.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>JavaScript 업로드 설정 테스트</title>
</head>
<body>
    <h1>JavaScript 업로드 설정 테스트</h1>
    
    <div id="test-results"></div>
    
    <!-- 공통 업로드 설정 로드 -->
    <?php include dirname(__DIR__) . '/src/views/includes/upload-config.js.php'; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const resultsDiv = document.getElementById('test-results');
        let testResults = [];
        
        // 1. 설정 로드 테스트
        testResults.push('<h2>📋 설정 로드 테스트</h2>');
        
        if (window.TOPMKT_UPLOAD_CONFIG) {
            testResults.push('✅ window.TOPMKT_UPLOAD_CONFIG 로드됨');
            testResults.push(`- 최대 크기: ${window.formatFileSize(window.TOPMKT_UPLOAD_CONFIG.maxFileSize)}`);
            testResults.push(`- 허용 확장자: ${window.TOPMKT_UPLOAD_CONFIG.allowedImageExtensions.join(', ')}`);
        } else {
            testResults.push('❌ window.TOPMKT_UPLOAD_CONFIG 로드 실패');
        }
        
        // 2. 함수 존재 테스트
        testResults.push('<h2>🔧 함수 존재 테스트</h2>');
        
        const functions = [
            'validateFileSize',
            'validateImageExtension', 
            'formatFileSize',
            'getFileSizeError'
        ];
        
        functions.forEach(funcName => {
            if (window[funcName] && typeof window[funcName] === 'function') {
                testResults.push(`✅ window.${funcName} 함수 존재`);
            } else {
                testResults.push(`❌ window.${funcName} 함수 없음`);
            }
        });
        
        // 3. 파일 크기 검증 테스트
        testResults.push('<h2>📏 파일 크기 검증 테스트</h2>');
        
        const testSizes = [
            { size: 5 * 1024 * 1024, name: '5MB', shouldPass: true },
            { size: 25 * 1024 * 1024, name: '25MB', shouldPass: true },
            { size: 30 * 1024 * 1024, name: '30MB', shouldPass: true },
            { size: 35 * 1024 * 1024, name: '35MB', shouldPass: false },
            { size: 50 * 1024 * 1024, name: '50MB', shouldPass: false }
        ];
        
        testSizes.forEach(test => {
            const result = window.validateFileSize && window.validateFileSize(test.size);
            const passed = result === test.shouldPass;
            const icon = passed ? '✅' : '❌';
            const status = test.shouldPass ? '통과 예상' : '실패 예상';
            const actual = result ? '통과' : '실패';
            
            testResults.push(`${icon} ${test.name} (${status}): ${actual}`);
        });
        
        // 4. 확장자 검증 테스트
        testResults.push('<h2>📄 확장자 검증 테스트</h2>');
        
        const testExtensions = [
            { ext: 'jpg', shouldPass: true },
            { ext: 'jpeg', shouldPass: true },
            { ext: 'png', shouldPass: true },
            { ext: 'gif', shouldPass: true },
            { ext: 'webp', shouldPass: true },
            { ext: 'bmp', shouldPass: false },
            { ext: 'tiff', shouldPass: false },
            { ext: 'exe', shouldPass: false }
        ];
        
        testExtensions.forEach(test => {
            const result = window.validateImageExtension && window.validateImageExtension(`test.${test.ext}`);
            const passed = result === test.shouldPass;
            const icon = passed ? '✅' : '❌';
            const status = test.shouldPass ? '허용 예상' : '거부 예상';
            const actual = result ? '허용' : '거부';
            
            testResults.push(`${icon} .${test.ext} (${status}): ${actual}`);
        });
        
        // 5. 오류 메시지 테스트
        testResults.push('<h2>💬 오류 메시지 테스트</h2>');
        
        if (window.getFileSizeError) {
            const errorMsg = window.getFileSizeError();
            testResults.push(`✅ 파일 크기 오류 메시지: "${errorMsg}"`);
        } else {
            testResults.push('❌ 오류 메시지 함수 없음');
        }
        
        // 결과 출력
        resultsDiv.innerHTML = testResults.join('<br>');
        
        // 최종 결과
        const allTestsPassed = testResults.filter(r => r.includes('❌')).length === 0;
        const finalResult = document.createElement('div');
        finalResult.style.marginTop = '20px';
        finalResult.style.padding = '10px';
        finalResult.style.border = '2px solid ' + (allTestsPassed ? 'green' : 'red');
        finalResult.style.backgroundColor = allTestsPassed ? '#e6ffe6' : '#ffe6e6';
        finalResult.innerHTML = `<h2>${allTestsPassed ? '🎉' : '⚠️'} 최종 결과: ${allTestsPassed ? '모든 테스트 통과' : '일부 테스트 실패'}</h2>`;
        
        resultsDiv.appendChild(finalResult);
    });
    </script>
</body>
</html>