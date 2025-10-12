const { chromium } = require('playwright');

async function testProfileImageFix() {
    console.log('🧪 프로필 이미지 마이그레이션 후 테스트 시작...');

    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    try {
        const page = await browser.newPage();

        // 1. 홈페이지에서 헤더 프로필 이미지 확인
        console.log('🏠 홈페이지 헤더 프로필 이미지 테스트...');
        await page.goto('https://www.topmktx.com/', {
            waitUntil: 'domcontentloaded',
            timeout: 30000
        });
        await page.waitForTimeout(2000);

        // 2. 커뮤니티 페이지에서 게시글 프로필 이미지 확인
        console.log('💬 커뮤니티 페이지 프로필 이미지 테스트...');
        await page.goto('https://www.topmktx.com/community', {
            waitUntil: 'domcontentloaded',
            timeout: 30000
        });
        await page.waitForTimeout(2000);

        // 3. API 직접 테스트
        console.log('🔗 프로필 이미지 API 테스트...');
        const apiResponse = await page.evaluate(async () => {
            try {
                const response = await fetch('/api/users/4/profile-image');
                const data = await response.json();
                return data;
            } catch (error) {
                return { error: error.message };
            }
        });

        console.log('📡 API 응답:', JSON.stringify(apiResponse, null, 2));

        // 4. 프로필 이미지 실제 로딩 테스트
        const imageLoadTest = await page.evaluate(() => {
            return new Promise((resolve) => {
                const img = new Image();
                img.onload = () => resolve({ status: 'success', loaded: true });
                img.onerror = () => resolve({ status: 'error', loaded: false });
                img.src = '/assets/images/user-profile-urijibtani.jpg';

                // 5초 타임아웃
                setTimeout(() => resolve({ status: 'timeout', loaded: false }), 5000);
            });
        });

        console.log('🖼️ 이미지 로딩 테스트:', imageLoadTest);

        // 스크린샷 저장
        await page.screenshot({
            path: 'profile-image-fix-test.png',
            fullPage: true
        });
        console.log('📸 스크린샷 저장: profile-image-fix-test.png');

        // 결과 분석
        const hasProfileImage = apiResponse.data && apiResponse.data.profile_image;
        const hasThumbImage = apiResponse.data && apiResponse.data.thumb_image;
        const imageLoaded = imageLoadTest.status === 'success';

        console.log('\n📊 종합 테스트 결과:');
        console.log(`- API profile_image: ${hasProfileImage ? '✅' : '❌'} ${apiResponse.data?.profile_image || 'null'}`);
        console.log(`- API thumb_image: ${hasThumbImage ? '✅' : '❌'} ${apiResponse.data?.thumb_image || 'null'}`);
        console.log(`- 이미지 파일 로딩: ${imageLoaded ? '✅' : '❌'}`);

        if (hasProfileImage && hasThumbImage && imageLoaded) {
            console.log('🎉 프로필 이미지 마이그레이션 성공! 모든 테스트 통과');
        } else {
            console.log('❌ 일부 테스트 실패');
        }

    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error.message);
    } finally {
        await browser.close();
        console.log('🏁 테스트 완료');
    }
}

testProfileImageFix();