<?php
/**
 * 파일 업로드 유틸리티 통합 테스트 페이지
 * v3.64.0 - 업로드 함수 컴포넌트화 테스트
 */

define('SRC_PATH', dirname(__DIR__) . '/src');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>파일 업로드 유틸리티 통합 테스트</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
        }
        h1 {
            color: #667eea;
            font-size: 32px;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }
        .test-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
        }
        .test-section h2 {
            color: #333;
            font-size: 18px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
        }
        .status.pending {
            background: #fff3cd;
            color: #856404;
        }
        .test-item {
            background: white;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
        }
        .test-item:last-child {
            margin-bottom: 0;
        }
        .test-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .test-result {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #666;
            background: #f1f3f5;
            padding: 8px 12px;
            border-radius: 4px;
            word-break: break-all;
        }
        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 12px;
        }
        .upload-area:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        .upload-area input[type="file"] {
            display: none;
        }
        .result-log {
            background: #212529;
            color: #0f0;
            padding: 16px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            max-height: 200px;
            overflow-y: auto;
            margin-top: 12px;
        }
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 12px;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
    </style>

    <!-- Toast 시스템 -->
    <?php require_once SRC_PATH . '/views/includes/toast.js.php'; ?>

    <!-- 업로드 설정 (테스트 대상) -->
    <?php require_once SRC_PATH . '/views/includes/upload-config.js.php'; ?>
</head>
<body>
    <div class="container">
        <h1>🚀 파일 업로드 유틸리티 통합 테스트</h1>
        <p class="subtitle">v3.64.0 - upload-config.js.php 컴포넌트화 검증</p>

        <!-- 1. 함수 존재 확인 -->
        <div class="test-section">
            <h2>1️⃣ 전역 함수 존재 확인</h2>
            <div class="test-item">
                <div class="test-label">window.formatFileSize</div>
                <div class="test-result" id="test-formatFileSize">테스트 대기 중...</div>
            </div>
            <div class="test-item">
                <div class="test-label">window.validateFileSize</div>
                <div class="test-result" id="test-validateFileSize">테스트 대기 중...</div>
            </div>
            <div class="test-item">
                <div class="test-label">window.validateImageExtension</div>
                <div class="test-result" id="test-validateImageExtension">테스트 대기 중...</div>
            </div>
            <div class="test-item">
                <div class="test-label">window.validateImageFile</div>
                <div class="test-result" id="test-validateImageFile">테스트 대기 중...</div>
            </div>
        </div>

        <!-- 2. formatFileSize 기능 테스트 -->
        <div class="test-section">
            <h2>2️⃣ formatFileSize() 기능 테스트</h2>
            <div class="test-item">
                <div class="test-label">다양한 파일 크기 포맷팅</div>
                <div class="test-result" id="test-formatFileSize-samples"></div>
            </div>
        </div>

        <!-- 3. validateFileSize 기능 테스트 -->
        <div class="test-section">
            <h2>3️⃣ validateFileSize() 기능 테스트</h2>
            <div class="test-item">
                <div class="test-label">30MB 제한 검증</div>
                <div class="test-result" id="test-validateFileSize-samples"></div>
            </div>
        </div>

        <!-- 4. validateImageExtension 기능 테스트 -->
        <div class="test-section">
            <h2>4️⃣ validateImageExtension() 기능 테스트</h2>
            <div class="test-item">
                <div class="test-label">이미지 확장자 검증</div>
                <div class="test-result" id="test-validateImageExtension-samples"></div>
            </div>
        </div>

        <!-- 5. validateImageFile 실제 파일 테스트 -->
        <div class="test-section">
            <h2>5️⃣ validateImageFile() 실제 파일 테스트</h2>
            <div class="test-item">
                <div class="test-label">실제 파일 업로드 검증</div>
                <div class="upload-area" onclick="document.getElementById('testFileInput').click()">
                    <input type="file" id="testFileInput" accept="image/*">
                    <p>📁 클릭하여 이미지 파일 선택</p>
                    <p style="font-size: 12px; color: #999; margin-top: 8px;">JPG, PNG, GIF, WebP (최대 30MB)</p>
                </div>
                <div class="result-log" id="test-validateImageFile-result"></div>
            </div>
        </div>

        <button class="btn" onclick="runAllTests()">🔄 모든 테스트 다시 실행</button>
    </div>

    <script>
        // 페이지 로드 시 자동 테스트
        document.addEventListener('DOMContentLoaded', function() {
            runAllTests();
            setupFileUploadTest();
        });

        // 모든 테스트 실행
        function runAllTests() {
            testFunctionExistence();
            testFormatFileSize();
            testValidateFileSize();
            testValidateImageExtension();
        }

        // 1. 함수 존재 확인 테스트
        function testFunctionExistence() {
            const tests = [
                { id: 'test-formatFileSize', fn: 'formatFileSize' },
                { id: 'test-validateFileSize', fn: 'validateFileSize' },
                { id: 'test-validateImageExtension', fn: 'validateImageExtension' },
                { id: 'test-validateImageFile', fn: 'validateImageFile' }
            ];

            tests.forEach(test => {
                const exists = typeof window[test.fn] === 'function';
                const el = document.getElementById(test.id);
                el.textContent = exists
                    ? `✅ 함수 정상 정의됨 (typeof: ${typeof window[test.fn]})`
                    : `❌ 함수 정의 안 됨`;
                el.style.color = exists ? '#155724' : '#721c24';
            });
        }

        // 2. formatFileSize 기능 테스트
        function testFormatFileSize() {
            const samples = [
                0,
                1024,
                1048576,
                10485760,
                31457280, // 30MB
                52428800  // 50MB
            ];

            const results = samples.map(bytes => {
                const formatted = window.formatFileSize(bytes);
                return `${bytes.toLocaleString()} bytes → ${formatted}`;
            });

            document.getElementById('test-formatFileSize-samples').innerHTML =
                results.join('<br>');
        }

        // 3. validateFileSize 기능 테스트
        function testValidateFileSize() {
            const samples = [
                { size: 10485760, label: '10MB' },
                { size: 31457280, label: '30MB (정확히)' },
                { size: 31457279, label: '30MB - 1byte' },
                { size: 31457281, label: '30MB + 1byte' },
                { size: 52428800, label: '50MB' }
            ];

            const results = samples.map(sample => {
                const isValid = window.validateFileSize(sample.size);
                const icon = isValid ? '✅' : '❌';
                return `${icon} ${sample.label} (${window.formatFileSize(sample.size)}): ${isValid ? '허용' : '거부'}`;
            });

            document.getElementById('test-validateFileSize-samples').innerHTML =
                results.join('<br>');
        }

        // 4. validateImageExtension 기능 테스트
        function testValidateImageExtension() {
            const samples = [
                'photo.jpg',
                'image.jpeg',
                'icon.png',
                'animation.gif',
                'modern.webp',
                'document.pdf',
                'archive.zip',
                'video.mp4',
                'UPPERCASE.JPG',
                'mixed.JpEg'
            ];

            const results = samples.map(filename => {
                const isValid = window.validateImageExtension(filename);
                const icon = isValid ? '✅' : '❌';
                return `${icon} ${filename}: ${isValid ? '허용' : '거부'}`;
            });

            document.getElementById('test-validateImageExtension-samples').innerHTML =
                results.join('<br>');
        }

        // 5. 실제 파일 업로드 테스트
        function setupFileUploadTest() {
            const fileInput = document.getElementById('testFileInput');
            const resultLog = document.getElementById('test-validateImageFile-result');

            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                resultLog.innerHTML = '';

                function log(message, color = '#0f0') {
                    const line = document.createElement('div');
                    line.style.color = color;
                    line.textContent = message;
                    resultLog.appendChild(line);
                }

                log(`📁 파일 선택: ${file.name}`);
                log(`📏 파일 크기: ${window.formatFileSize(file.size)}`);
                log(`📄 MIME 타입: ${file.type}`);
                log('');

                // validateImageFile 테스트 (Toast 알림 발생)
                const isValid = window.validateImageFile(file);

                log(`🔍 validateImageFile() 결과: ${isValid}`, isValid ? '#0f0' : '#f00');
                log('');

                if (isValid) {
                    log('✅ 파일 검증 통과!', '#0f0');
                    log('   - 이미지 형식: 정상', '#0f0');
                    log('   - 파일 크기: 30MB 이하', '#0f0');
                } else {
                    log('❌ 파일 검증 실패!', '#f00');
                    log('   Toast 알림을 확인하세요.', '#ff0');
                }

                // 입력 초기화
                setTimeout(() => {
                    fileInput.value = '';
                }, 100);
            });
        }
    </script>
</body>
</html>
