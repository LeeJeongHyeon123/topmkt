import { chromium } from 'playwright';

(async () => {
    console.log('🚀 채팅 페이지 실제 테스트 시작...\n');

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1920, height: 1080 }
    });
    const page = await context.newPage();

    const consoleErrors = [];
    const consoleWarnings = [];
    const networkRequests = [];

    // 콘솔 리스너
    page.on('console', msg => {
        const text = msg.text();
        if (msg.type() === 'error') {
            consoleErrors.push(text);
        } else if (msg.type() === 'warning') {
            consoleWarnings.push(text);
        }
    });

    // 네트워크 리스너
    page.on('request', request => {
        if (request.url().includes('/api/users/')) {
            networkRequests.push({
                url: request.url(),
                time: Date.now()
            });
        }
    });

    try {
        // 1. DevLoginHelper로 로그인
        console.log('1️⃣  DevLoginHelper로 로그인 중 (user_id=4)...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
            waitUntil: 'networkidle',
            timeout: 30000
        });
        await page.waitForTimeout(2000);
        console.log('   ✅ 로그인 완료\n');

        // 2. 채팅 페이지 접속
        console.log('2️⃣  채팅 페이지 접속 중...');
        const startTime = Date.now();

        await page.goto('https://www.topmktx.com/chat', {
            waitUntil: 'networkidle',
            timeout: 60000
        });

        const loadTime = Date.now() - startTime;
        console.log(`   ✅ 페이지 로드 완료 (${loadTime}ms)\n`);

        // 3. 페이지 렌더링 확인
        console.log('3️⃣  페이지 렌더링 확인 중...');
        await page.waitForSelector('#chatRoomsList', { timeout: 10000 });
        console.log('   ✅ 채팅방 목록 컨테이너 발견\n');

        // 4. 10초 대기하며 무한 루프 확인
        console.log('4️⃣  10초간 무한 루프 발생 여부 확인 중...');
        const beforeCount = networkRequests.length;
        await page.waitForTimeout(10000);
        const afterCount = networkRequests.length;

        // 5. 결과 분석
        console.log('\n📊 테스트 결과:');
        console.log('━'.repeat(60));

        // 채팅방 개수
        const chatRoomCount = await page.locator('.chat-room-item').count();
        console.log(`\n채팅방 개수: ${chatRoomCount}개`);

        // API 호출 횟수
        console.log(`\nAPI 호출 횟수 (/api/users/*): ${networkRequests.length}회`);
        if (networkRequests.length > 20) {
            console.log('   ⚠️  경고: API 호출이 너무 많습니다! (무한 루프 의심)');
            console.log('\n   최근 10개 API 호출:');
            networkRequests.slice(-10).forEach((req, i) => {
                const userId = req.url.split('/').slice(-2, -1)[0];
                console.log(`   ${i + 1}. user_id=${userId}`);
            });
        } else {
            console.log('   ✅ API 호출 횟수 정상');
        }

        // 10초 동안 추가 호출 확인
        const callsDuring10s = afterCount - beforeCount;
        console.log(`\n10초 동안 추가 API 호출: ${callsDuring10s}회`);
        if (callsDuring10s > 10) {
            console.log('   ⚠️  경고: 계속 API를 호출하고 있습니다! (무한 루프)');
        } else {
            console.log('   ✅ API 호출 멈춤 (무한 루프 없음)');
        }

        // 콘솔 에러
        console.log(`\n콘솔 에러: ${consoleErrors.length}개`);
        if (consoleErrors.length > 0) {
            console.log('   ⚠️  에러 메시지:');
            consoleErrors.slice(0, 5).forEach((err, i) => {
                console.log(`   ${i + 1}. ${err.substring(0, 100)}`);
            });
        } else {
            console.log('   ✅ 에러 없음');
        }

        // 콘솔 경고
        console.log(`\n콘솔 경고: ${consoleWarnings.length}개`);
        if (consoleWarnings.length > 10) {
            console.log('   ⚠️  경고가 많습니다');
        } else {
            console.log('   ✅ 경고 정상 수준');
        }

        // 스크린샷
        console.log('\n5️⃣  스크린샷 저장 중...');
        await page.screenshot({ path: '/tmp/chat-page-test.png', fullPage: true });
        console.log('   ✅ 스크린샷: /tmp/chat-page-test.png');

        // 최종 판정
        console.log('\n' + '━'.repeat(60));
        console.log('\n🎯 최종 판정:');

        const isSuccess =
            loadTime < 30000 &&
            networkRequests.length < 50 &&
            callsDuring10s < 10 &&
            consoleErrors.length === 0;

        if (isSuccess) {
            console.log('✅ 테스트 통과! 채팅 페이지가 정상적으로 동작합니다.');
            console.log('\n상세 결과:');
            console.log(`  - 페이지 로드: ${loadTime}ms (기준: < 30000ms)`);
            console.log(`  - 총 API 호출: ${networkRequests.length}회 (기준: < 50회)`);
            console.log(`  - 10초간 추가 호출: ${callsDuring10s}회 (기준: < 10회)`);
            console.log(`  - 콘솔 에러: ${consoleErrors.length}개 (기준: 0개)`);
        } else {
            console.log('❌ 테스트 실패! 문제가 감지되었습니다:');
            if (loadTime >= 30000) {
                console.log(`  - 페이지 로딩이 너무 느립니다: ${loadTime}ms`);
            }
            if (networkRequests.length >= 50) {
                console.log(`  - API 호출이 너무 많습니다: ${networkRequests.length}회`);
            }
            if (callsDuring10s >= 10) {
                console.log(`  - 10초간 계속 API 호출: ${callsDuring10s}회`);
            }
            if (consoleErrors.length > 0) {
                console.log(`  - 콘솔 에러 발생: ${consoleErrors.length}개`);
            }
        }

        console.log('');

        await browser.close();
        process.exit(isSuccess ? 0 : 1);

    } catch (error) {
        console.error('\n❌ 테스트 실패:', error.message);
        await page.screenshot({ path: '/tmp/chat-page-error.png', fullPage: true });
        console.log('   에러 스크린샷: /tmp/chat-page-error.png');
        await browser.close();
        process.exit(1);
    }
})();
