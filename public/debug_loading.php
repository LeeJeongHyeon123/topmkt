<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로딩 UI 디버그</title>
    <style>
        body { font-family: 'Noto Sans KR', sans-serif; padding: 20px; }
        .debug-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn.danger { background: #dc3545; }
        .btn.danger:hover { background: #c82333; }
        .console-log { background: #000; color: #0f0; padding: 15px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🚀 로딩 UI 디버그 도구</h1>
    
    <div class="debug-box">
        <h2>📊 현재 상태</h2>
        <p><strong>현재 날짜:</strong> <span id="currentDate"></span></p>
        <p><strong>저장된 마지막 방문 날짜:</strong> <span id="lastVisitDate"></span></p>
        <p><strong>오늘 첫 방문 여부:</strong> <span id="isFirstVisit"></span></p>
    </div>
    
    <div class="debug-box">
        <h2>🔧 제어 도구</h2>
        <button class="btn danger" onclick="clearLocalStorage()">localStorage 초기화 (다음 방문시 로딩 표시)</button>
        <button class="btn" onclick="setYesterdayDate()">어제 날짜로 설정 (즉시 로딩 표시)</button>
        <button class="btn" onclick="testLoadingUI()">로딩 UI 강제 테스트</button>
        <button class="btn" onclick="refreshStatus()">상태 새로고침</button>
    </div>
    
    <div class="debug-box">
        <h2>📝 콘솔 로그</h2>
        <div id="consoleLog" class="console-log"></div>
    </div>
    
    <div class="debug-box">
        <h2>🎯 해결책</h2>
        <p><strong>문제:</strong> localStorage에 오늘 날짜가 이미 저장되어 있어서 로딩 UI가 표시되지 않음</p>
        <p><strong>해결:</strong></p>
        <ol>
            <li><strong>즉시 테스트:</strong> "어제 날짜로 설정" 버튼 클릭 후 페이지 새로고침</li>
            <li><strong>완전 초기화:</strong> "localStorage 초기화" 버튼 클릭</li>
            <li><strong>강제 테스트:</strong> "로딩 UI 강제 테스트" 버튼으로 로딩 애니메이션 확인</li>
        </ol>
    </div>

    <script>
        // 콘솔 로그 캡처
        const originalLog = console.log;
        const logElement = document.getElementById('consoleLog');
        
        console.log = function(...args) {
            originalLog.apply(console, args);
            logElement.innerHTML += args.join(' ') + '\n';
            logElement.scrollTop = logElement.scrollHeight;
        };
        
        // 상태 업데이트
        function refreshStatus() {
            const today = new Date().toDateString();
            const lastVisitDate = localStorage.getItem('topMarketing_lastVisitDate');
            const isFirstVisit = lastVisitDate !== today;
            
            document.getElementById('currentDate').textContent = today;
            document.getElementById('lastVisitDate').textContent = lastVisitDate || '없음';
            document.getElementById('isFirstVisit').textContent = isFirstVisit ? '예 (로딩 표시됨)' : '아니오 (로딩 건너뜀)';
            
            console.log(`📊 현재 상태 - 오늘: ${today}, 저장된 날짜: ${lastVisitDate}, 첫 방문: ${isFirstVisit}`);
        }
        
        // localStorage 초기화
        function clearLocalStorage() {
            localStorage.removeItem('topMarketing_lastVisitDate');
            localStorage.removeItem('topMarketing_visitCount');
            console.log('🗑️ localStorage 초기화 완료');
            refreshStatus();
            alert('localStorage가 초기화되었습니다. 다음 페이지 방문시 로딩 UI가 표시됩니다.');
        }
        
        // 어제 날짜로 설정
        function setYesterdayDate() {
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            const yesterdayString = yesterday.toDateString();
            
            localStorage.setItem('topMarketing_lastVisitDate', yesterdayString);
            console.log(`📅 어제 날짜로 설정: ${yesterdayString}`);
            refreshStatus();
            alert('어제 날짜로 설정되었습니다. 페이지를 새로고침하면 로딩 UI가 표시됩니다.');
        }
        
        // 로딩 UI 강제 테스트
        function testLoadingUI() {
            console.log('🚀 로딩 UI 강제 테스트 시작');
            
            // 임시로 어제 날짜 설정
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            localStorage.setItem('topMarketing_lastVisitDate', yesterday.toDateString());
            
            // TopMarketingLoader가 있다면 강제 실행
            if (typeof window.TopMarketingLoader !== 'undefined') {
                window.TopMarketingLoader.init();
                console.log('🚀 기존 로더 강제 실행');
            } else {
                // 새 창에서 테스트
                window.open('/', '_blank');
                console.log('🚀 새 창에서 테스트 (로딩 UI 확인)');
            }
        }
        
        // 초기 상태 로드
        refreshStatus();
        
        // 5초마다 상태 업데이트
        setInterval(refreshStatus, 5000);
    </script>
</body>
</html>