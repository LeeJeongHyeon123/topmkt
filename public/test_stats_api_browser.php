<?php
/**
 * 브라우저에서 통계 API 호출 시뮬레이션 테스트
 */
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>통계 API 브라우저 테스트</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .error { background: #ffebee; border: 1px solid #f44336; }
        .success { background: #e8f5e9; border: 1px solid #4caf50; }
        pre { overflow-x: auto; }
    </style>
</head>
<body>
    <h1>📊 통계 API 브라우저 테스트</h1>
    <p>현재 시간: <?= date('Y-m-d H:i:s') ?></p>
    
    <button onclick="testStatsAPI()">통계 API 테스트 실행</button>
    <div id="result-container"></div>

    <script>
    async function testStatsAPI() {
        const resultContainer = document.getElementById('result-container');
        resultContainer.innerHTML = '<p>⏳ 테스트 진행 중...</p>';
        
        try {
            console.log('📡 통계 API 호출 시작...');
            
            // 1단계: API 호출
            const response = await fetch('/admin/getUserStats', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            console.log('📡 응답 상태:', response.status);
            console.log('📡 응답 헤더:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            // 2단계: JSON 파싱
            const data = await response.json();
            console.log('📊 받은 데이터:', data);
            
            // 3단계: 데이터 구조 검증
            if (!data) {
                throw new Error('응답 데이터가 없습니다');
            }
            
            if (!data.success) {
                throw new Error(data.error || '알 수 없는 오류');
            }
            
            if (!data.stats) {
                throw new Error('통계 데이터가 없습니다');
            }
            
            // 4단계: 각 필드 접근 테스트
            const stats = data.stats;
            const requiredFields = ['total_users', 'today_signups', 'active_users'];
            
            for (const field of requiredFields) {
                if (stats[field] === undefined) {
                    throw new Error(`필수 필드 '${field}'가 없습니다`);
                }
                console.log(`✅ ${field}: ${stats[field]}`);
            }
            
            // 5단계: 성공 표시
            resultContainer.innerHTML = `
                <div class="result success">
                    <h3>✅ 테스트 성공!</h3>
                    <h4>응답 데이터:</h4>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                    
                    <h4>통계 필드 검증:</h4>
                    <ul>
                        <li><strong>총 회원 수:</strong> ${stats.total_users}</li>
                        <li><strong>오늘 신규 가입:</strong> ${stats.today_signups}</li>
                        <li><strong>활성 회원:</strong> ${stats.active_users}</li>
                    </ul>
                    
                    <h4>브라우저 호환성:</h4>
                    <ul>
                        <li>✅ fetch API 지원</li>
                        <li>✅ async/await 지원</li>
                        <li>✅ JSON 파싱 성공</li>
                        <li>✅ 객체 속성 접근 성공</li>
                    </ul>
                </div>
            `;
            
        } catch (error) {
            console.error('❌ 오류 발생:', error);
            
            resultContainer.innerHTML = `
                <div class="result error">
                    <h3>❌ 테스트 실패</h3>
                    <p><strong>오류:</strong> ${error.message}</p>
                    
                    <h4>디버깅 정보:</h4>
                    <ul>
                        <li><strong>오류 타입:</strong> ${error.constructor.name}</li>
                        <li><strong>현재 URL:</strong> ${window.location.href}</li>
                        <li><strong>API URL:</strong> ${window.location.origin}/admin/getUserStats</li>
                        <li><strong>브라우저:</strong> ${navigator.userAgent}</li>
                    </ul>
                    
                    <details>
                        <summary>전체 오류 정보</summary>
                        <pre>${error.stack || error.toString()}</pre>
                    </details>
                </div>
            `;
        }
    }
    
    // 페이지 로드 시 자동 실행
    document.addEventListener('DOMContentLoaded', function() {
        console.log('📄 페이지 로드 완료, 자동 테스트 시작');
        testStatsAPI();
    });
    </script>
</body>
</html>