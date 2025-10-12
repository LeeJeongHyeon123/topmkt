<?php
/**
 * 테스트: 강의 신청 상태 메시지 표시 확인
 * URL: https://www.topmktx.com/test-lecture-status.php
 */

// 세션 시작
session_start();

// 기본 경로 설정
define('BASE_PATH', __DIR__);
define('SRC_PATH', BASE_PATH . '/src');

// 데이터베이스 연결
require_once SRC_PATH . '/config/database.php';

// 헤더 설정
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>강의 신청 상태 메시지 테스트</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; margin: 40px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .test-section { border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .status-message { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin: 15px 0; }
        .status-message.rejected { border-left: 4px solid #e53e3e; background: #fef5f5; }
        .status-content { display: flex; align-items: flex-start; gap: 12px; }
        .status-icon { color: #e53e3e; font-size: 1.2rem; margin-top: 2px; }
        .status-text { flex: 1; }
        .status-title { font-weight: 600; color: #2d3748; margin-bottom: 4px; }
        .status-description { color: #4a5568; font-size: 0.95rem; line-height: 1.4; }
        pre { background: #f7fafc; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 0.9rem; }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; }
        .btn-primary { background: #667eea; color: white; }
        .btn-secondary { background: #718096; color: white; }
        .highlight { background: #fff3cd; padding: 2px 6px; border-radius: 4px; }
        .error { color: #e53e3e; font-weight: 600; }
        .success { color: #38a169; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 강의 신청 상태 메시지 테스트</h1>
        
        <div class="test-section">
            <h2>📋 테스트 대상 정보</h2>
            <ul>
                <li><strong>사용자:</strong> 안계현 (ID: 5)</li>
                <li><strong>강의:</strong> ID 167</li>
                <li><strong>URL:</strong> <a href="https://www.topmktx.com/lectures/167" target="_blank">https://www.topmktx.com/lectures/167</a></li>
                <li><strong>문제:</strong> 거절 메시지가 표시되지 않음</li>
            </ul>
        </div>

        <div class="test-section">
            <h2>🗄️ 데이터베이스 확인</h2>
            <?php
            try {
                $stmt = $pdo->prepare("
                    SELECT 
                        lr.id, lr.user_id, lr.lecture_id, lr.status, 
                        lr.admin_notes, lr.processed_at, lr.created_at,
                        u.nickname, u.email,
                        l.title as lecture_title
                    FROM lecture_registrations lr 
                    JOIN users u ON lr.user_id = u.id 
                    JOIN lectures l ON lr.lecture_id = l.id
                    WHERE lr.lecture_id = 167 AND u.nickname = '안계현'
                ");
                $stmt->execute();
                $registration = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($registration) {
                    echo "<div class='success'>✅ 데이터베이스에서 신청 정보 찾음</div>";
                    echo "<pre>" . json_encode($registration, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                } else {
                    echo "<div class='error'>❌ 데이터베이스에서 신청 정보를 찾을 수 없음</div>";
                }
            } catch (Exception $e) {
                echo "<div class='error'>❌ 데이터베이스 오류: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
            ?>
        </div>

        <div class="test-section">
            <h2>🧪 상태 메시지 UI 시뮬레이션</h2>
            <p>실제 강의 페이지에서 표시되어야 하는 메시지:</p>
            
            <?php if (isset($registration) && $registration): ?>
            <div class="status-message rejected">
                <div class="status-content">
                    <div class="status-icon">
                        <i class="fas fa-times-circle">❌</i>
                    </div>
                    <div class="status-text">
                        <div class="status-title">신청이 거절되었습니다</div>
                        <div class="status-description"><?= htmlspecialchars($registration['admin_notes']) ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="test-section">
            <h2>🔧 API 테스트</h2>
            <p>신청 상태 확인 API 응답:</p>
            <button class="btn btn-primary" onclick="testAPI()">API 테스트 실행</button>
            <div id="api-result" style="margin-top: 15px;"></div>
        </div>

        <div class="test-section">
            <h2>🧭 문제 진단</h2>
            <h3>가능한 원인들:</h3>
            <ol>
                <li><span class="highlight">로그인 상태 문제</span> - 사용자가 로그인하지 않은 상태</li>
                <li><span class="highlight">JavaScript 실행 순서</span> - 함수가 로드되기 전에 호출</li>
                <li><span class="highlight">API 인증 실패</span> - 세션이 만료되었거나 CSRF 토큰 문제</li>
                <li><span class="highlight">DOM 요소 누락</span> - 상태 메시지 표시할 HTML 요소가 없음</li>
            </ol>

            <h3>해결 방법:</h3>
            <ol>
                <li><strong>로그인 확인:</strong> 사용자 "안계현"으로 로그인 필요</li>
                <li><strong>브라우저 개발자 도구:</strong> 콘솔에서 JavaScript 오류 확인</li>
                <li><strong>네트워크 탭:</strong> API 호출 및 응답 상태 확인</li>
                <li><strong>DOM 요소:</strong> #lecture-status-message 요소 존재 확인</li>
            </ol>
        </div>

        <div class="test-section">
            <h2>✅ 다음 단계</h2>
            <ol>
                <li>사용자 "안계현"으로 <a href="/login" target="_blank">로그인</a></li>
                <li><a href="https://www.topmktx.com/lectures/167" target="_blank">강의 167 페이지</a> 접속</li>
                <li>브라우저 개발자 도구(F12) 열기</li>
                <li>콘솔에서 다음 명령어 실행:
                    <pre>console.log('Status message element:', document.getElementById('lecture-status-message'));
console.log('Current user ID:', document.querySelector('meta[name="user-id"]')?.getAttribute('content'));</pre>
                </li>
                <li>네트워크 탭에서 <code>/api/lectures/167/registration-status</code> 호출 확인</li>
            </ol>
        </div>
    </div>

    <script>
    async function testAPI() {
        const resultDiv = document.getElementById('api-result');
        resultDiv.innerHTML = '<div style="color: #666;">API 호출 중...</div>';
        
        try {
            const response = await fetch('/api/lectures/167/registration-status', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            let html = `
                <h4>응답 상태: ${response.status}</h4>
                <pre>${JSON.stringify(data, null, 2)}</pre>
            `;
            
            if (response.status === 401) {
                html += '<div class="error">❌ 인증 필요: 사용자 "안계현"으로 로그인해주세요</div>';
            } else if (data.status === 'success') {
                html += '<div class="success">✅ API 정상 응답</div>';
            }
            
            resultDiv.innerHTML = html;
        } catch (error) {
            resultDiv.innerHTML = `<div class="error">❌ API 오류: ${error.message}</div>`;
        }
    }
    </script>
</body>
</html>