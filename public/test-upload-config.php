<?php
/**
 * 업로드 설정 테스트 페이지
 * 공통 설정이 제대로 적용되었는지 확인
 */

require_once dirname(__DIR__) . '/src/config/database.php';
require_once dirname(__DIR__) . '/src/config/upload.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TOPMKT 업로드 설정 테스트</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f8fafc;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: #059669; }
        .error { color: #dc2626; }
        .config-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .test-section {
            margin: 20px 0;
        }
        .test-file-input {
            margin: 10px 0;
            padding: 10px;
            border: 2px dashed #3b82f6;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
        }
        .test-result {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .test-result.success {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }
        .test-result.error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fca5a5;
        }
    </style>
</head>
<body>
    <h1>🚀 TOPMKT 업로드 설정 테스트</h1>
    
    <div class="card">
        <h2>📋 서버 설정 확인</h2>
        
        <div class="config-item">
            <span>최대 업로드 크기:</span>
            <span class="success"><?= UploadConfig::getMaxFileSizeMB() ?>MB</span>
        </div>
        
        <div class="config-item">
            <span>허용 이미지 확장자:</span>
            <span><?= implode(', ', UploadConfig::ALLOWED_IMAGE_EXTENSIONS) ?></span>
        </div>
        
        <div class="config-item">
            <span>허용 문서 확장자:</span>
            <span><?= implode(', ', UploadConfig::ALLOWED_DOCUMENT_EXTENSIONS) ?></span>
        </div>
        
        <div class="config-item">
            <span>PHP upload_max_filesize:</span>
            <span class="<?= ini_get('upload_max_filesize') >= '30M' ? 'success' : 'error' ?>">
                <?= ini_get('upload_max_filesize') ?>
            </span>
        </div>
        
        <div class="config-item">
            <span>PHP post_max_size:</span>
            <span class="<?= ini_get('post_max_size') >= '50M' ? 'success' : 'error' ?>">
                <?= ini_get('post_max_size') ?>
            </span>
        </div>
        
        <div class="config-item">
            <span>PHP memory_limit:</span>
            <span class="<?= ini_get('memory_limit') >= '256M' || ini_get('memory_limit') == '-1' ? 'success' : 'error' ?>">
                <?= ini_get('memory_limit') ?>
            </span>
        </div>
    </div>

    <div class="card">
        <h2>🧪 클라이언트 측 테스트</h2>
        
        <div class="test-section">
            <h3>파일 크기 검증 테스트</h3>
            <div class="test-file-input" onclick="document.getElementById('test-file').click()">
                📁 테스트 파일 선택하기
                <input type="file" id="test-file" style="display: none;">
            </div>
            <div id="test-result"></div>
        </div>
        
        <div class="test-section">
            <h3>🔍 JavaScript 설정 확인</h3>
            <div id="js-config"></div>
        </div>
    </div>

    <div class="card">
        <h2>📊 테스트 결과 요약</h2>
        <div id="summary"></div>
    </div>

    <!-- 공통 업로드 설정 로드 -->
    <?php include dirname(__DIR__) . '/src/views/includes/upload-config.js.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // JavaScript 설정 표시
        const jsConfigDiv = document.getElementById('js-config');
        if (window.TOPMKT_UPLOAD_CONFIG) {
            jsConfigDiv.innerHTML = `
                <div class="config-item">
                    <span>최대 파일 크기 (JS):</span>
                    <span class="success">${window.formatFileSize(window.TOPMKT_UPLOAD_CONFIG.maxFileSize)}</span>
                </div>
                <div class="config-item">
                    <span>허용 확장자 (JS):</span>
                    <span>${window.TOPMKT_UPLOAD_CONFIG.allowedImageExtensions.join(', ')}</span>
                </div>
                <div class="config-item">
                    <span>에러 메시지:</span>
                    <span>${window.TOPMKT_UPLOAD_CONFIG.errorMessages.file_too_large}</span>
                </div>
            `;
        } else {
            jsConfigDiv.innerHTML = '<span class="error">❌ JavaScript 설정 로드 실패</span>';
        }

        // 파일 테스트
        document.getElementById('test-file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const resultDiv = document.getElementById('test-result');
            
            if (!file) {
                resultDiv.innerHTML = '';
                return;
            }

            let testResults = [];

            // 파일 크기 테스트
            if (window.validateFileSize && window.validateFileSize(file.size)) {
                testResults.push('✅ 파일 크기 검증 통과');
            } else {
                testResults.push('❌ 파일 크기 검증 실패');
            }

            // 확장자 테스트 (이미지 파일인 경우)
            if (file.type.startsWith('image/')) {
                if (window.validateImageExtension && window.validateImageExtension(file.name)) {
                    testResults.push('✅ 이미지 확장자 검증 통과');
                } else {
                    testResults.push('❌ 이미지 확장자 검증 실패');
                }
            }

            const isSuccess = !testResults.some(result => result.includes('❌'));
            
            resultDiv.innerHTML = `
                <div class="test-result ${isSuccess ? 'success' : 'error'}">
                    <strong>파일: ${file.name}</strong><br>
                    크기: ${window.formatFileSize(file.size)}<br>
                    타입: ${file.type}<br><br>
                    ${testResults.join('<br>')}
                </div>
            `;
        });

        // 테스트 요약
        const summaryDiv = document.getElementById('summary');
        const phpUploadOk = '<?= ini_get("upload_max_filesize") >= "30M" ? "true" : "false" ?>' === 'true';
        const phpPostOk = '<?= ini_get("post_max_size") >= "50M" ? "true" : "false" ?>' === 'true';
        const phpMemoryOk = '<?= ini_get("memory_limit") >= "256M" || ini_get("memory_limit") == "-1" ? "true" : "false" ?>' === 'true';
        const jsConfigOk = !!window.TOPMKT_UPLOAD_CONFIG;

        const allOk = phpUploadOk && phpPostOk && phpMemoryOk && jsConfigOk;

        summaryDiv.innerHTML = `
            <div class="test-result ${allOk ? 'success' : 'error'}">
                <strong>${allOk ? '🎉 모든 테스트 통과!' : '⚠️ 일부 테스트 실패'}</strong><br><br>
                ${phpUploadOk ? '✅' : '❌'} PHP upload_max_filesize<br>
                ${phpPostOk ? '✅' : '❌'} PHP post_max_size<br>
                ${phpMemoryOk ? '✅' : '❌'} PHP memory_limit<br>
                ${jsConfigOk ? '✅' : '❌'} JavaScript 설정 로드<br><br>
                ${allOk ? '30MB 이하 파일 업로드가 정상적으로 작동할 것입니다.' : '일부 설정을 확인해주세요.'}
            </div>
        `;
    });
    </script>
</body>
</html>