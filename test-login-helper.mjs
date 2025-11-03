import { chromium } from 'playwright';

(async () => {
    console.log('🔍 DevLoginHelper 작동 확인 중...\n');

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1920, height: 1080 }
    });
    const page = await context.newPage();

    try {
        console.log('1️⃣  DevLoginHelper 접속 중...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
            waitUntil: 'networkidle',
            timeout: 30000
        });

        const url1 = page.url();
        const title1 = await page.title();
        console.log(`   URL: ${url1}`);
        console.log(`   제목: ${title1}`);

        // 페이지 내용 확인
        const bodyText = await page.evaluate(() => document.body.innerText);
        console.log(`   내용 (앞부분): ${bodyText.substring(0, 200)}`);

        // 스크린샷
        await page.screenshot({ path: '/tmp/login-helper.png' });
        console.log(`   스크린샷: /tmp/login-helper.png\n`);

        // 쿠키 확인
        const cookies = await context.cookies();
        console.log('2️⃣  쿠키 확인:');
        cookies.forEach(cookie => {
            console.log(`   - ${cookie.name}: ${cookie.value.substring(0, 20)}...`);
        });

        // 5초 대기 후 다시 확인
        console.log('\n3️⃣  5초 대기 후 상태 확인...');
        await page.waitForTimeout(5000);

        const url2 = page.url();
        const title2 = await page.title();
        console.log(`   URL: ${url2}`);
        console.log(`   제목: ${title2}`);

        // 이제 채팅 페이지로 이동
        console.log('\n4️⃣  채팅 페이지 접속 시도...');
        await page.goto('https://www.topmktx.com/chat', {
            waitUntil: 'networkidle',
            timeout: 30000
        });

        const finalUrl = page.url();
        const finalTitle = await page.title();
        console.log(`   최종 URL: ${finalUrl}`);
        console.log(`   최종 제목: ${finalTitle}`);

        if (finalUrl.includes('/auth/login')) {
            console.log('\n❌ 로그인 실패: 로그인 페이지로 리다이렉트됨');
        } else if (finalUrl.includes('/chat')) {
            console.log('\n✅ 로그인 성공: 채팅 페이지 접속 완료');
        }

        await page.screenshot({ path: '/tmp/chat-after-login.png' });
        console.log(`   스크린샷: /tmp/chat-after-login.png`);

        await browser.close();

    } catch (error) {
        console.error('\n❌ 오류:', error.message);
        await browser.close();
        process.exit(1);
    }
})();
