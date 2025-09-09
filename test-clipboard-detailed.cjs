/**
 * 실제 클립보드 동작 상세 테스트
 */

const { chromium } = require('playwright');

async function testClipboardDetailed() {
    const browser = await chromium.launch({ 
        headless: false, // 헤드리스 모드 비활성화로 실제 클립보드 테스트
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const page = await browser.newPage();
        
        // 클립보드 권한 허용
        const context = page.context();
        await context.grantPermissions(['clipboard-read', 'clipboard-write']);
        
        console.log('📄 Sample 페이지 접속 중...');
        await page.goto('https://www.topmktx.com/sample');
        await page.waitForTimeout(3000);
        
        const currentURL = await page.url();
        console.log('🌐 현재 페이지 URL:', currentURL);
        
        // JavaScript 함수들이 제대로 정의되어 있는지 확인
        const functionCheck = await page.evaluate(() => {
            return {
                copyURLBanner: typeof window.copyURLBanner === 'function',
                copyURL: typeof window.copyURL === 'function',
                clipboardAPI: !!navigator.clipboard
            };
        });
        
        console.log('\n🔍 함수 및 API 확인:');
        console.log('   copyURLBanner 함수:', functionCheck.copyURLBanner ? '✅ 정의됨' : '❌ 없음');
        console.log('   copyURL 함수:', functionCheck.copyURL ? '✅ 정의됨' : '❌ 없음');
        console.log('   Clipboard API:', functionCheck.clipboardAPI ? '✅ 지원됨' : '❌ 미지원');
        
        // 실제 클립보드 동작 테스트 (배너 버튼)
        console.log('\n🧪 배너 버튼 실제 클립보드 테스트...');
        
        // 클립보드 비우기
        await page.evaluate(() => navigator.clipboard.writeText(''));
        
        // 배너 버튼 클릭
        await page.click('.banner-copy-btn');
        await page.waitForTimeout(1000);
        
        // 실제 클립보드에서 읽기
        try {
            const clipboardContent = await page.evaluate(async () => {
                try {
                    return await navigator.clipboard.readText();
                } catch (e) {
                    return 'ERROR: ' + e.message;
                }
            });
            
            console.log('   배너 버튼 클립보드 내용:', clipboardContent);
            console.log('   URL 정확성:', clipboardContent === currentURL ? '✅ 정확' : '❌ 불일치');
        } catch (error) {
            console.log('   클립보드 읽기 오류:', error.message);
        }
        
        await page.waitForTimeout(3000); // 버튼 상태 변화 완료 대기
        
        // 하단 버튼 테스트
        console.log('\n🧪 하단 버튼 실제 클립보드 테스트...');
        
        // 하단으로 스크롤
        await page.evaluate(() => {
            document.querySelector('.copy-btn').scrollIntoView({ behavior: 'smooth' });
        });
        await page.waitForTimeout(1000);
        
        // 클립보드 비우기
        await page.evaluate(() => navigator.clipboard.writeText('test-clear'));
        
        // 하단 버튼 클릭
        await page.click('.copy-btn');
        await page.waitForTimeout(1000);
        
        // 실제 클립보드에서 읽기
        try {
            const clipboardContent2 = await page.evaluate(async () => {
                try {
                    return await navigator.clipboard.readText();
                } catch (e) {
                    return 'ERROR: ' + e.message;
                }
            });
            
            console.log('   하단 버튼 클립보드 내용:', clipboardContent2);
            console.log('   URL 정확성:', clipboardContent2 === currentURL ? '✅ 정확' : '❌ 불일치');
        } catch (error) {
            console.log('   클립보드 읽기 오류:', error.message);
        }
        
        // 함수 직접 호출 테스트
        console.log('\n🧪 함수 직접 호출 테스트...');
        
        const directTest = await page.evaluate(async (url) => {
            try {
                // 배너 함수 직접 호출
                if (typeof window.copyURLBanner === 'function') {
                    window.copyURLBanner();
                    await new Promise(resolve => setTimeout(resolve, 500));
                    const bannerResult = await navigator.clipboard.readText();
                    
                    return {
                        bannerFunction: true,
                        bannerResult: bannerResult,
                        bannerCorrect: bannerResult === url
                    };
                } else {
                    return { bannerFunction: false };
                }
            } catch (error) {
                return { error: error.message };
            }
        }, currentURL);
        
        console.log('   배너 함수 직접 호출:', directTest);
        
        return { currentURL, functionCheck, success: true };
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error);
        return { error: error.message };
    } finally {
        await page.waitForTimeout(2000); // 결과 확인을 위한 대기
        await browser.close();
    }
}

testClipboardDetailed().then(result => {
    console.log('\n🏁 상세 테스트 완료');
});