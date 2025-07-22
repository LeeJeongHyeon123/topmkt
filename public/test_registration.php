<?php
/**
 * 강의 신청 시스템 최종 테스트
 */
session_start();

// CSRF 토큰 생성
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token); ?>">
    <title>강의 신청 시스템 테스트</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .test-section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #007bff; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #0056b3; }
        #result { background: #fff; border: 1px solid #ddd; padding: 15px; margin-top: 20px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>🎯 강의 신청 시스템 최종 테스트</h1>
    
    <div class="test-section">
        <h2>📊 시스템 상태 확인</h2>
        <p class="info">이 테스트는 실제 웹 환경에서 강의 신청 시스템이 정상 작동하는지 확인합니다.</p>
        
        <h3>테스트 시나리오:</h3>
        <ol>
            <li>강의 167번 상태 조회</li>
            <li>이전 신청 데이터 조회 (자동완성용)</li>
            <li>강의 신청 제출 (실제 데이터 사용)</li>
            <li>신청 취소</li>
            <li>재신청 (이전 데이터 자동완성)</li>
        </ol>
        
        <button onclick="startTest()">🚀 전체 테스트 시작</button>
        <button onclick="testStatus()">📊 상태 조회만</button>
        <button onclick="clearResult()">🗑️ 결과 지우기</button>
    </div>
    
    <div id="result"></div>
    
    <script>
        let testResult = document.getElementById('result');
        
        function log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const prefix = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
            testResult.textContent += `[${timestamp}] ${prefix} ${message}\n`;
            testResult.scrollTop = testResult.scrollHeight;
        }
        
        function clearResult() {
            testResult.textContent = '';
        }
        
        async function getCsrfToken() {
            try {
                const response = await fetch('/api/csrf-token');
                if (response.ok) {
                    const data = await response.json();
                    return data.token;
                }
            } catch (error) {
                log('CSRF 토큰 조회 실패, 임시 토큰 사용', 'error');
            }
            
            // CSRF 토큰을 가져올 수 없으면 현재 페이지의 메타 태그에서 찾기
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            if (metaToken) {
                return metaToken.getAttribute('content');
            }
            
            // 실패시 임시 토큰 반환
            return 'test-csrf-token';
        }
        
        async function apiCall(url, options = {}) {
            try {
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers
                    }
                });
                
                const data = await response.json();
                return { success: response.ok, status: response.status, data };
            } catch (error) {
                return { success: false, error: error.message };
            }
        }
        
        async function testStatus() {
            log('강의 167번 상태 조회 시작...');
            
            const result = await apiCall('/api/lectures/167/registration-status');
            
            if (result.success) {
                log(`상태 조회 성공: ${result.data.message}`, 'success');
                log(`강의 정보: ${JSON.stringify(result.data.data.lecture_info, null, 2)}`);
                if (result.data.data.registration) {
                    log(`신청 정보: ${JSON.stringify(result.data.data.registration, null, 2)}`);
                } else {
                    log('현재 신청 내역 없음');
                }
            } else {
                log(`상태 조회 실패: ${result.error || result.data?.message}`, 'error');
                log(`응답 코드: ${result.status}`);
            }
        }
        
        async function testPreviousData() {
            log('이전 신청 데이터 조회 시작...');
            
            const result = await apiCall('/api/lectures/167/previous-registration');
            
            if (result.success) {
                log(`이전 데이터 조회 성공: ${result.data.message}`, 'success');
                log(`이전 신청 데이터: ${JSON.stringify(result.data.data, null, 2)}`);
                return result.data.data;
            } else {
                log(`이전 데이터 조회 실패: ${result.error || result.data?.message}`, 'error');
                return null;
            }
        }
        
        async function testRegistration() {
            log('강의 신청 테스트 시작...');
            
            const csrfToken = await getCsrfToken();
            log(`CSRF 토큰: ${csrfToken}`);
            
            const registrationData = {
                csrf_token: csrfToken,
                participant_name: '테스트 사용자',
                participant_email: 'test@example.com',
                participant_phone: '010-1234-5678',
                company_name: '테스트 회사',
                position: '테스트 직책',
                motivation: '강의 신청 시스템 테스트를 위한 신청입니다.',
                special_requests: '',
                how_did_you_know: 'website'
            };
            
            const result = await apiCall('/api/lectures/167/registration', {
                method: 'POST',
                body: JSON.stringify(registrationData)
            });
            
            if (result.success) {
                log(`신청 성공: ${result.data.message}`, 'success');
                log(`신청 ID: ${result.data.data.registration_id}`);
                return result.data.data.registration_id;
            } else {
                log(`신청 실패: ${result.error || result.data?.message}`, 'error');
                if (result.data?.data?.errors) {
                    log(`검증 오류: ${JSON.stringify(result.data.data.errors, null, 2)}`);
                }
                return null;
            }
        }
        
        async function testCancellation() {
            log('신청 취소 테스트 시작...');
            
            const csrfToken = await getCsrfToken();
            
            const result = await apiCall('/api/lectures/167/registration', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            if (result.success) {
                log(`취소 성공: ${result.data.message}`, 'success');
                return true;
            } else {
                log(`취소 실패: ${result.error || result.data?.message}`, 'error');
                return false;
            }
        }
        
        async function startTest() {
            log('=== 강의 신청 시스템 전체 테스트 시작 ===');
            log('');
            
            // 1. 상태 조회
            await testStatus();
            log('');
            
            // 2. 이전 데이터 조회
            const previousData = await testPreviousData();
            log('');
            
            // 3. 신청 테스트
            const registrationId = await testRegistration();
            log('');
            
            if (registrationId) {
                // 4. 취소 테스트
                const cancelled = await testCancellation();
                log('');
                
                if (cancelled) {
                    // 5. 재신청 테스트 (이전 데이터 사용)
                    log('재신청 테스트 시작 (이전 데이터 자동완성)...');
                    await testPreviousData(); // 취소 후 이전 데이터 확인
                    log('');
                }
            }
            
            log('=== 전체 테스트 완료 ===');
        }
    </script>
</body>
</html>