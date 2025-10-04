// React Native WebView 통신 브리지
(function () {
    'use strict';

    console.log('🚀 App Bridge 초기화');

    // 앱에서 메시지 받기
    window.addEventListener('message', function (event) {
        try {
            const data = JSON.parse(event.data);
            console.log('📨 앱에서 메시지 수신:', data);

            // 푸시 토큰 수신
            if (data.type === 'FCM_TOKEN') {
                console.log('📱 푸시 토큰:', data.token);

                // 로컬 스토리지에 저장
                localStorage.setItem('app_push_token', data.token);

                // 서버에 전송 (API가 준비되면 주석 해제)
                /*
                fetch('/api/save-push-token', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        token: data.token,
                        platform: 'mobile'
                    })
                })
                .then(res => res.json())
                .then(result => console.log('✅ 토큰 저장:', result))
                .catch(err => console.error('❌ 토큰 저장 실패:', err));
                */
            }

            // 알림 클릭 이벤트
            if (data.type === 'NOTIFICATION_CLICKED') {
                console.log('🔔 알림 클릭됨:', data);
            }
        } catch (e) {
            console.error('❌ 메시지 파싱 실패:', e);
        }
    });

    // 페이지 로드 완료 후 토큰 요청
    window.addEventListener('load', function () {
        if (window.ReactNativeWebView) {
            console.log('✅ React Native WebView 감지');

            // 앱에 푸시 토큰 요청
            window.ReactNativeWebView.postMessage(JSON.stringify({
                type: 'REQUEST_PUSH_TOKEN'
            }));
        } else {
            console.log('ℹ️ 일반 웹 브라우저에서 접속');
        }
    });

    // 앱인지 확인하는 헬퍼 함수
    window.isApp = function () {
        return typeof window.ReactNativeWebView !== 'undefined';
    };

    console.log('✅ App Bridge 준비 완료');
})();