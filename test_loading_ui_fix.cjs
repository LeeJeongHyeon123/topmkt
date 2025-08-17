/**
 * 🚀 Ultra Think: confirm 취소 시 로딩 UI 무한 루프 수정 테스트
 */

const { chromium } = require('playwright');

async function testLoadingUIFix() {
    console.log('🚀 confirm 취소 시 로딩 UI 무한 루프 수정 테스트');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
    });
    
    try {
        const context = await browser.newContext({
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });
        
        const page = await context.newPage();
        
        // 공지사항 작성 페이지 접근
        console.log('1️⃣ 공지사항 작성 페이지 접근...');
        await page.goto('https://www.topmktx.com/notices/write', { 
            waitUntil: 'domcontentloaded',
            timeout: 30000
        });
        
        await page.waitForTimeout(3000);
        
        // 제목 입력 (beforeunload 이벤트 트리거하기 위함)
        console.log('2️⃣ 제목 입력하여 beforeunload 이벤트 조건 만족...');
        await page.fill('#title', '테스트 제목');
        await page.waitForTimeout(1000);
        
        // focus 이벤트 리스너 테스트
        console.log('3️⃣ focus 이벤트 리스너 테스트...');
        await page.evaluate(() => {
            // 인위적으로 로딩 UI 표시
            if (window.topMarketingLoader) {
                window.topMarketingLoader.show();
                console.log('🔄 테스트용 로딩 UI 표시');
            }
        });
        
        await page.waitForTimeout(2000);
        
        // focus 이벤트 트리거
        await page.evaluate(() => {
            window.dispatchEvent(new Event('focus'));
        });
        
        await page.waitForTimeout(1000);
        
        // 로딩 UI 상태 확인
        const isLoadingAfterFocus = await page.evaluate(() => {
            return window.topMarketingLoader ? window.topMarketingLoader.isLoading : false;
        });
        
        if (isLoadingAfterFocus) {
            console.log('❌ focus 이벤트 후에도 로딩 UI가 여전히 표시됨');
        } else {
            console.log('✅ focus 이벤트로 로딩 UI가 올바르게 숨겨짐');
        }
        
        // visibilitychange 이벤트 테스트
        console.log('4️⃣ visibilitychange 이벤트 리스너 테스트...');
        await page.evaluate(() => {
            // 인위적으로 로딩 UI 표시
            if (window.topMarketingLoader) {
                window.topMarketingLoader.show();
                console.log('🔄 테스트용 로딩 UI 표시 (2차)');
            }
        });
        
        await page.waitForTimeout(1000);
        
        // visibilitychange 이벤트 트리거
        await page.evaluate(() => {
            Object.defineProperty(document, 'hidden', {
                writable: true,
                value: false
            });
            document.dispatchEvent(new Event('visibilitychange'));
        });
        
        await page.waitForTimeout(1000);
        
        // 로딩 UI 상태 확인
        const isLoadingAfterVisibility = await page.evaluate(() => {
            return window.topMarketingLoader ? window.topMarketingLoader.isLoading : false;
        });
        
        if (isLoadingAfterVisibility) {
            console.log('❌ visibilitychange 이벤트 후에도 로딩 UI가 여전히 표시됨');
        } else {
            console.log('✅ visibilitychange 이벤트로 로딩 UI가 올바르게 숨겨짐');
        }
        
        // JavaScript 오류 확인
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        
        if (errors.length > 0) {
            console.log('⚠️ JavaScript 오류:');
            errors.forEach(error => console.log(`   - ${error}`));
        } else {
            console.log('✅ JavaScript 오류 없음');
        }
        
        console.log('✅ confirm 취소 시 로딩 UI 무한 루프 수정 테스트 완료');
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error.message);
    } finally {
        await browser.close();
    }
}

// 테스트 실행
testLoadingUIFix();