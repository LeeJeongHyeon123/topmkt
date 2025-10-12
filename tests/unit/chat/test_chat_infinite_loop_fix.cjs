const { chromium } = require('playwright');

async function testChatInfiniteLoopFix() {
    console.log('🧪 채팅 페이지 무한 루프 수정사항 테스트 시작...');

    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    try {
        const page = await browser.newPage();

        // 콘솔 로그 캡처
        const consoleLogs = [];
        page.on('console', msg => {
            const text = msg.text();
            consoleLogs.push(text);
            console.log(`📝 콘솔: ${text}`);
        });

        // 채팅 페이지 이동
        console.log('🌐 채팅 페이지로 이동 중...');
        await page.goto('https://www.topmktx.com/chat', {
            waitUntil: 'domcontentloaded',
            timeout: 30000
        });

        // 10초간 대기하면서 콘솔 로그 모니터링
        console.log('⏱️ 10초간 무한 루프 발생 여부 모니터링...');
        await page.waitForTimeout(10000);

        // 무한 루프 패턴 검사
        const infiniteLoopCount = consoleLogs.filter(log =>
            log.includes('🔄 사용자 4 프로필 정보 로드 시작')
        ).length;

        const skipCount = consoleLogs.filter(log =>
            log.includes('🚫 사용자 4는 탈퇴한 회원으로 프로필 로드 스킵')
        ).length;

        console.log('\n📊 테스트 결과:');
        console.log(`- 프로필 로드 시도 횟수: ${infiniteLoopCount}`);
        console.log(`- 탈퇴 회원 스킵 횟수: ${skipCount}`);

        if (infiniteLoopCount <= 2) {
            console.log('✅ 무한 루프 해결 성공! (로드 시도 2회 이하)');
        } else {
            console.log(`❌ 무한 루프 여전히 발생 중 (${infiniteLoopCount}회 로드 시도)`);
        }

        if (skipCount > 0) {
            console.log('✅ 탈퇴 회원 스킵 로직 정상 작동');
        }

        // 스크린샷 저장
        await page.screenshot({
            path: 'chat-infinite-loop-fix-test.png',
            fullPage: true
        });
        console.log('📸 스크린샷 저장: chat-infinite-loop-fix-test.png');

    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error.message);
    } finally {
        await browser.close();
        console.log('🏁 테스트 완료');
    }
}

testChatInfiniteLoopFix();