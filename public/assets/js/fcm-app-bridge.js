/**
 * FCM 앱 브릿지
 * 앱에서 호출할 FCM 토큰 등록 JavaScript 함수
 *
 * 작성일: 2025-10-16
 * 버전: 1.0.0
 */

/**
 * 앱에서 FCM 토큰 등록 (메인 함수)
 *
 * 이 함수는 앱의 WebView에서 호출됩니다.
 * 앱이 메인 페이지 진입 시마다 FCM 토큰을 전달하여 자동으로 등록/업데이트합니다.
 *
 * @param {string} fcmToken - FCM 토큰 (Firebase에서 생성)
 * @param {string} deviceType - 디바이스 타입 ('android' | 'ios' | 'web')
 * @param {string} deviceName - 디바이스 이름 (예: 'Samsung Galaxy S23')
 * @param {string} appVersion - 앱 버전 (예: '1.0.0')
 * @returns {Promise<object>} 등록 결과
 *
 * @example
 * // Android WebView에서 호출
 * webView.evaluateJavascript(
 *   "registerFCMTokenFromApp('token123', 'android', 'Galaxy S23', '1.0.0')",
 *   null
 * );
 *
 * // iOS WKWebView에서 호출
 * webView.evaluateJavaScript(
 *   "registerFCMTokenFromApp('token123', 'ios', 'iPhone 15 Pro', '1.0.0')"
 * );
 */
async function registerFCMTokenFromApp(fcmToken, deviceType, deviceName, appVersion) {
    try {
        // 1. 파라미터 검증
        if (!fcmToken || typeof fcmToken !== 'string' || fcmToken.trim() === '') {
            console.error('❌ FCM 토큰이 유효하지 않습니다');
            return {
                status: 'error',
                message: 'FCM 토큰이 유효하지 않습니다.'
            };
        }

        // 2. 로그인 확인 (클라이언트 측 빠른 검증)
        const userId = document.body.dataset.userId;
        if (!userId) {
            console.warn('⚠️ 로그인 필요 (클라이언트 측 확인)');
            return {
                status: 'error',
                message: '로그인이 필요합니다.',
                code: 'NOT_LOGGED_IN'
            };
        }

        // 3. CSRF 토큰 확인
        const csrfToken = window.CSRF_TOKEN;
        if (!csrfToken) {
            console.error('❌ CSRF 토큰을 찾을 수 없습니다');
            return {
                status: 'error',
                message: 'CSRF 토큰을 찾을 수 없습니다.',
                code: 'NO_CSRF_TOKEN'
            };
        }

        // 4. API 호출
        const response = await fetch('/api/fcm/tokens', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                csrf_token: csrfToken,
                fcm_token: fcmToken.trim(),
                device_type: deviceType || 'android',
                device_name: deviceName || 'Unknown Device',
                app_version: appVersion || '1.0.0'
            })
        });

        const result = await response.json();

        // 5. 결과 처리
        if (result.status === 'success') {
            const action = result.data?.action || 'unknown';
            const changed = result.data?.changed || false;

            return {
                status: 'success',
                message: result.message,
                action: action,
                changed: changed,
                data: result.data
            };
        } else {
            console.error('❌ FCM 토큰 등록 실패:', result.message);
            return result;
        }
    } catch (error) {
        console.error('❌ FCM 토큰 등록 오류:', error);
        return {
            status: 'error',
            message: error.message || '알 수 없는 오류가 발생했습니다.',
            code: 'EXCEPTION'
        };
    }
}

// 전역으로 노출 (앱에서 호출 가능하도록)
window.registerFCMTokenFromApp = registerFCMTokenFromApp;

// 개발 모드: 테스트 함수
if (window.location.hostname === 'localhost' ||
    window.location.hostname.includes('dev') ||
    window.location.hostname.includes('127.0.0.1')) {

    /**
     * 개발 환경 전용 테스트 함수
     * 브라우저 콘솔에서 window.testFCMRegistration() 실행
     */
    window.testFCMRegistration = function() {
        const testToken = 'test_fcm_token_' + Date.now();
        return registerFCMTokenFromApp(
            testToken,
            'web',
            'Chrome Browser (Test)',
            '1.0.0-test'
        );
    };
}
