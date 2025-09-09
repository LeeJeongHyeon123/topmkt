const { chromium } = require('playwright');

async function testJsLoadingDebug() {
    console.log('🔍 JavaScript 로딩 및 폼 핸들러 디버깅');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 },
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });
        
        const page = await context.newPage();
        
        // 모든 콘솔 메시지 캡처
        const consoleMessages = [];
        page.on('console', msg => {
            consoleMessages.push({
                type: msg.type(),
                text: msg.text(),
                location: msg.location()
            });
        });
        
        // 네트워크 요청 모니터링
        const jsFiles = [];
        page.on('response', response => {
            if (response.url().endsWith('.js')) {
                jsFiles.push({
                    url: response.url(),
                    status: response.status(),
                    contentType: response.headers()['content-type']
                });
            }
        });
        
        console.log('📱 페이지 로딩...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('form', { timeout: 10000 });
        await page.waitForTimeout(5000); // JS 로딩 대기
        
        console.log('\n📋 JavaScript 파일 로딩 상태:');
        jsFiles.forEach((file, idx) => {
            console.log(`${idx + 1}. ${file.url.split('/').pop()} - HTTP ${file.status}`);
        });
        
        console.log('\n📝 콘솔 메시지:');
        consoleMessages.forEach((msg, idx) => {
            if (idx < 10) { // 처음 10개만
                console.log(`${idx + 1}. [${msg.type.toUpperCase()}] ${msg.text}`);
            }
        });
        
        // JavaScript 전역 객체 확인
        const jsStatus = await page.evaluate(() => {
            return {
                jquery: typeof $ !== 'undefined',
                forgotPasswordForm: typeof ForgotPasswordForm !== 'undefined',
                window: typeof window !== 'undefined',
                document: typeof document !== 'undefined',
                formElement: document.querySelector('form') !== null,
                submitButton: document.querySelector('.submit-button') !== null,
                phoneInput: document.querySelector('input[name="phone"]') !== null
            };
        });
        
        console.log('\n🔧 JavaScript 환경 상태:');
        Object.entries(jsStatus).forEach(([key, value]) => {
            console.log(`${key}: ${value ? '✅' : '❌'}`);
        });
        
        // 폼 제출 이벤트 리스너 확인
        const formEvents = await page.evaluate(() => {
            const form = document.querySelector('form');
            const submitBtn = document.querySelector('.submit-button');
            
            return {
                formHasSubmitEvent: form && form.onsubmit !== null,
                buttonHasClickEvent: submitBtn && submitBtn.onclick !== null,
                formAction: form ? form.action : null,
                formMethod: form ? form.method : null
            };
        });
        
        console.log('\n📋 폼 이벤트 상태:');
        Object.entries(formEvents).forEach(([key, value]) => {
            console.log(`${key}: ${value || 'null'}`);
        });
        
        // 수동 폼 제출 시도
        console.log('\n🧪 수동 폼 제출 시도...');
        
        try {
            await page.fill('input[name="phone"]', '123456789');
            
            // 클릭 이벤트 모니터링
            let clickEventFired = false;
            await page.exposeFunction('onClickEvent', () => {
                clickEventFired = true;
                console.log('🖱️ 클릭 이벤트 발생');
            });
            
            // 클릭 리스너 추가
            await page.evaluate(() => {
                const btn = document.querySelector('.submit-button');
                if (btn) {
                    btn.addEventListener('click', window.onClickEvent);
                }
            });
            
            await page.click('.submit-button');
            await page.waitForTimeout(2000);
            
            console.log(`클릭 이벤트 발생: ${clickEventFired ? '✅' : '❌'}`);
            
        } catch (e) {
            console.log(`폼 제출 중 오류: ${e.message}`);
        }
        
        return {
            jsFiles: jsFiles.length,
            consoleMessages: consoleMessages.length,
            jsStatus,
            formEvents
        };
        
    } catch (error) {
        console.error('❌ 디버깅 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testJsLoadingDebug()
    .then(results => {
        console.log('\n📊 디버깅 요약:');
        console.log(`JavaScript 파일: ${results.jsFiles}개 로딩`);
        console.log(`콘솔 메시지: ${results.consoleMessages}개`);
        console.log(`ForgotPasswordForm: ${results.jsStatus.forgotPasswordForm ? '✅ 로딩됨' : '❌ 로딩 안됨'}`);
        console.log(`폼 요소: ${results.jsStatus.formElement ? '✅ 존재' : '❌ 없음'}`);
        
        if (!results.jsStatus.forgotPasswordForm) {
            console.log('\n⚠️ ForgotPasswordForm 클래스가 로딩되지 않았습니다.');
            console.log('   - JavaScript 파일 로딩 문제일 수 있습니다.');
            console.log('   - 또는 클래스 정의에 오류가 있을 수 있습니다.');
        }
    })
    .catch(error => {
        console.error('💥 디버깅 실행 실패:', error);
        process.exit(1);
    });