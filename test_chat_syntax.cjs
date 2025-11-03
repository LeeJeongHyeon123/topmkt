const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const context = await browser.newContext({
        viewport: { width: 1920, height: 1080 }
    });

    const page = await context.newPage();

    // 모든 콘솔 메시지 수집
    const allLogs = [];
    page.on('console', msg => {
        const type = msg.type();
        const text = msg.text();
        allLogs.push({ type, text, timestamp: Date.now() });
        console.log(`[${type.toUpperCase()}] ${text}`);
    });

    // JavaScript 에러 수집
    const jsErrors = [];
    page.on('pageerror', error => {
        jsErrors.push({
            message: error.message,
            stack: error.stack,
            timestamp: Date.now()
        });
        console.log('\n' + '='.repeat(80));
        console.log('🚨 JAVASCRIPT ERROR DETECTED!');
        console.log('='.repeat(80));
        console.log('Message:', error.message);
        console.log('Stack:', error.stack);
        console.log('='.repeat(80) + '\n');
    });

    // 네트워크 실패
    const networkFailures = [];
    page.on('requestfailed', request => {
        networkFailures.push({
            url: request.url(),
            failure: request.failure().errorText
        });
        console.log(`[NET FAIL] ${request.url()} - ${request.failure().errorText}`);
    });

    try {
        console.log('='.repeat(80));
        console.log('채팅 페이지 JavaScript 문법 에러 테스트');
        console.log('='.repeat(80));
        console.log('DevLoginHelper 로그인 시도...\n');

        // 로그인 시도
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=14', {
            waitUntil: 'networkidle',
            timeout: 30000
        });

        await page.waitForTimeout(3000);

        console.log('\n채팅 페이지 이동 중...\n');

        // 채팅 페이지 접속
        const startTime = Date.now();
        await page.goto('https://www.topmktx.com/chat', {
            waitUntil: 'domcontentloaded',
            timeout: 10000
        }).catch(e => {
            console.log(`⚠️ 페이지 로드 타임아웃: ${e.message}`);
        });

        const loadTime = Date.now() - startTime;
        console.log(`\n페이지 DOMContentLoaded: ${loadTime}ms\n`);

        // 5초 대기
        console.log('JavaScript 실행 5초 대기...\n');
        await page.waitForTimeout(5000);

        // 페이지 상태 확인
        const pageState = await page.evaluate(() => {
            return {
                url: window.location.href,
                title: document.title,
                hasFirebase: typeof firebase !== 'undefined',
                hasChatRoomsList: !!document.getElementById('chatRoomsList'),
                hasDatabase: typeof database !== 'undefined',
                hasCurrentUserId: typeof currentUserId !== 'undefined',
                bodyClass: document.body.className,
                scriptCount: document.querySelectorAll('script').length
            };
        }).catch(e => {
            console.log('❌ page.evaluate 실패:', e.message);
            return null;
        });

        console.log('\n' + '='.repeat(80));
        console.log('페이지 상태');
        console.log('='.repeat(80));
        if (pageState) {
            console.log('URL:', pageState.url);
            console.log('Title:', pageState.title);
            console.log('Firebase 로드:', pageState.hasFirebase);
            console.log('chatRoomsList 존재:', pageState.hasChatRoomsList);
            console.log('database 변수:', pageState.hasDatabase);
            console.log('currentUserId 변수:', pageState.hasCurrentUserId);
            console.log('Script 태그 수:', pageState.scriptCount);
        }

        // 스크린샷
        await page.screenshot({
            path: '/var/www/html/topmkt/temp/screenshots/chat_syntax_test.png',
            fullPage: true
        });

        console.log('\n' + '='.repeat(80));
        console.log('최종 결과');
        console.log('='.repeat(80));
        console.log(`JavaScript 에러: ${jsErrors.length}개`);
        console.log(`네트워크 실패: ${networkFailures.length}개`);
        console.log(`전체 로그: ${allLogs.length}개`);

        if (jsErrors.length > 0) {
            console.log('\n🚨 발견된 JavaScript 에러:');
            jsErrors.forEach((err, i) => {
                console.log(`\n[${i + 1}] ${err.message}`);
                if (err.stack) {
                    console.log('스택:', err.stack.split('\n').slice(0, 5).join('\n'));
                }
            });
        }

        if (networkFailures.length > 0) {
            console.log('\n❌ 네트워크 실패:');
            networkFailures.forEach((fail, i) => {
                console.log(`[${i + 1}] ${fail.url}`);
                console.log(`    ${fail.failure}`);
            });
        }

        console.log('\n📸 스크린샷: /var/www/html/topmkt/temp/screenshots/chat_syntax_test.png');

    } catch (error) {
        console.error('\n❌ 테스트 실패:', error.message);
        console.error(error.stack);
    } finally {
        await browser.close();
    }
})();
