/**
 * 세션 유지를 위한 AJAX Keep-Alive
 * 10분마다 서버에 요청을 보내 세션을 갱신합니다
 */

(function() {
    // 10분마다 실행 (600000ms = 10분)
    const INTERVAL = 600000;
    
    function keepAlive() {
        fetch('/keep_alive.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('세션 갱신 성공:', new Date().toLocaleTimeString());
            }
        })
        .catch(error => {
            console.error('세션 갱신 실패:', error);
        });
    }
    
    // 페이지가 활성 상태일 때만 실행
    let intervalId = null;
    
    function startKeepAlive() {
        if (!intervalId) {
            intervalId = setInterval(keepAlive, INTERVAL);
            console.log('세션 유지 시작');
        }
    }
    
    function stopKeepAlive() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
            console.log('세션 유지 중지');
        }
    }
    
    // 페이지 가시성 API 사용
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopKeepAlive();
        } else {
            startKeepAlive();
        }
    });
    
    // 초기 시작
    if (!document.hidden) {
        startKeepAlive();
    }
})();