const { chromium } = require('playwright');

(async () => {
    console.log('🔍 채팅 알림 프로필 이미지 디버깅 시작...\n');

    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();

    try {
        // 1. 홈페이지 접속
        await page.goto('https://www.topmktx.com');
        await page.waitForLoadState('networkidle');

        // 2. 콘솔 로그 캡처
        page.on('console', msg => {
            const text = msg.text();
            if (text.includes('채팅 알림') || text.includes('🔔') || text.includes('profileImage')) {
                console.log(`[브라우저 콘솔] ${text}`);
            }
        });

        // 3. 테스트 함수 실행 및 결과 확인
        console.log('✅ 테스트 알림 생성 중...\n');
        
        const result = await page.evaluate(() => {
            // 테스트 함수 실행
            if (typeof testChatNotification === 'function') {
                testChatNotification();
            }
            
            // 잠시 대기 후 DOM 확인
            return new Promise((resolve) => {
                setTimeout(() => {
                    const alert = document.querySelector('.alert.chat-notification');
                    if (!alert) {
                        resolve({ error: '알림 DOM을 찾을 수 없음' });
                        return;
                    }

                    const profileImage = alert.querySelector('.chat-profile-image');
                    const img = profileImage?.querySelector('img');
                    const icon = profileImage?.querySelector('i.fas.fa-user');
                    const alertText = alert.querySelector('.alert-text');
                    const strong = alertText?.querySelector('strong');
                    const span = alertText?.querySelector('span');
                    const time = alertText?.querySelector('.chat-time');

                    resolve({
                        알림존재: !!alert,
                        프로필컨테이너존재: !!profileImage,
                        이미지태그존재: !!img,
                        아이콘존재: !!icon,
                        이미지src: img?.src || 'N/A',
                        이미지alt: img?.alt || 'N/A',
                        발신자명: strong?.textContent || 'N/A',
                        메시지: span?.textContent || 'N/A',
                        시간: time?.textContent || 'N/A',
                        프로필컨테이너HTML: profileImage?.outerHTML?.substring(0, 200) || 'N/A',
                        전체HTML: alert.outerHTML.substring(0, 500)
                    });
                }, 500);
            });
        });

        console.log('📊 채팅 알림 DOM 분석 결과:\n');
        console.log(JSON.stringify(result, null, 2));

        // 4. 스크린샷
        await page.screenshot({ path: 'chat-notification-debug.png', fullPage: true });
        console.log('\n📸 스크린샷 저장: chat-notification-debug.png');

        // 5. CSS 스타일 확인
        const styles = await page.evaluate(() => {
            const alert = document.querySelector('.alert.chat-notification');
            const profileImage = alert?.querySelector('.chat-profile-image');
            const img = profileImage?.querySelector('img');

            return {
                알림display: alert ? window.getComputedStyle(alert).display : 'N/A',
                알림opacity: alert ? window.getComputedStyle(alert).opacity : 'N/A',
                프로필width: profileImage ? window.getComputedStyle(profileImage).width : 'N/A',
                프로필height: profileImage ? window.getComputedStyle(profileImage).height : 'N/A',
                이미지width: img ? window.getComputedStyle(img).width : 'N/A',
                이미지height: img ? window.getComputedStyle(img).height : 'N/A',
                이미지display: img ? window.getComputedStyle(img).display : 'N/A'
            };
        });

        console.log('\n🎨 CSS 스타일 분석:\n');
        console.log(JSON.stringify(styles, null, 2));

    } catch (error) {
        console.error('❌ 오류:', error.message);
    } finally {
        await browser.close();
    }
})();
