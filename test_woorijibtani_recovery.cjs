const { chromium } = require('playwright');

async function testWoorijibtaniRecovery() {
    console.log('🧪 우리집탄이 계정 복구 테스트 시작...');

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

        // 5초 대기
        await page.waitForTimeout(5000);

        // 사용자 4 관련 로그 확인
        const user4Logs = consoleLogs.filter(log => log.includes('사용자 4'));
        const deletedLogs = consoleLogs.filter(log => log.includes('탈퇴한 회원'));
        const woorijibtaniLogs = consoleLogs.filter(log => log.includes('우리집탄이'));

        console.log('\n📊 복구 테스트 결과:');
        console.log(`- 사용자 4 관련 로그: ${user4Logs.length}개`);
        console.log(`- "탈퇴한 회원" 로그: ${deletedLogs.length}개`);
        console.log(`- "우리집탄이" 로그: ${woorijibtaniLogs.length}개`);

        if (deletedLogs.length === 0) {
            console.log('✅ "탈퇴한 회원" 표시 없음 - 복구 성공!');
        } else {
            console.log('❌ 여전히 "탈퇴한 회원"으로 표시됨');
        }

        // API 직접 테스트
        console.log('\n🔗 사용자 4 API 직접 테스트...');
        const response = await page.evaluate(async () => {
            try {
                const response = await fetch('/api/user/4');
                const data = await response.json();
                return data;
            } catch (error) {
                return { error: error.message };
            }
        });

        console.log('📡 API 응답:', JSON.stringify(response, null, 2));

        // 스크린샷 저장
        await page.screenshot({
            path: 'woorijibtani-recovery-test.png',
            fullPage: true
        });
        console.log('📸 스크린샷 저장: woorijibtani-recovery-test.png');

    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error.message);
    } finally {
        await browser.close();
        console.log('🏁 테스트 완료');
    }
}

testWoorijibtaniRecovery();