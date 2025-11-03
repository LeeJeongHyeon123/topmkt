import { chromium } from 'playwright';

(async () => {
    console.log('🔍 채팅 페이지 구조 확인 중...\n');

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1920, height: 1080 }
    });
    const page = await context.newPage();

    try {
        // 1. DevLoginHelper로 로그인
        console.log('1️⃣  로그인 중...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
            waitUntil: 'networkidle',
            timeout: 30000
        });
        await page.waitForTimeout(2000);
        console.log('   ✅ 로그인 완료\n');

        // 2. 채팅 페이지 접속
        console.log('2️⃣  채팅 페이지 접속 중...');
        await page.goto('https://www.topmktx.com/chat', {
            waitUntil: 'networkidle',
            timeout: 60000
        });
        console.log('   ✅ 페이지 로드 완료\n');

        // 3. 페이지 내용 확인
        console.log('3️⃣  페이지 HTML 구조 확인 중...');

        // body의 모든 id 속성 가져오기
        const allIds = await page.evaluate(() => {
            return Array.from(document.querySelectorAll('[id]')).map(el => el.id);
        });

        console.log(`   발견된 id 속성: ${allIds.length}개`);
        console.log(`   ID 목록 (상위 20개):`);
        allIds.slice(0, 20).forEach(id => console.log(`     - ${id}`));

        // 채팅 관련 요소 찾기
        console.log('\n4️⃣  채팅 관련 요소 찾기...');
        const chatElements = await page.evaluate(() => {
            const elements = [];
            document.querySelectorAll('[id*="chat" i], [class*="chat" i]').forEach(el => {
                if (el.id) elements.push(`#${el.id}`);
                if (el.className) elements.push(`.${el.className.split(' ')[0]}`);
            });
            return [...new Set(elements)].slice(0, 10);
        });
        console.log(`   채팅 관련 요소:`);
        chatElements.forEach(sel => console.log(`     - ${sel}`));

        // 스크린샷
        console.log('\n5️⃣  스크린샷 저장 중...');
        await page.screenshot({ path: '/tmp/chat-page-debug.png', fullPage: true });
        console.log('   ✅ 스크린샷: /tmp/chat-page-debug.png');

        // 페이지 제목 확인
        const title = await page.title();
        console.log(`\n📄 페이지 제목: "${title}"`);

        // URL 확인
        console.log(`🔗 현재 URL: ${page.url()}`);

        await browser.close();
        process.exit(0);

    } catch (error) {
        console.error('\n❌ 오류:', error.message);
        await page.screenshot({ path: '/tmp/chat-page-debug-error.png', fullPage: true });
        await browser.close();
        process.exit(1);
    }
})();
