/**
 * 로그인 프로세스 디버깅 도구
 */

const { chromium } = require('playwright');

async function debugLogin() {
    console.log('🔍 로그인 프로세스 디버깅 시작');
    
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext();
    const page = await context.newPage();
    
    try {
        // 1. 로그인 페이지 접근
        console.log('📄 로그인 페이지 접근...');
        await page.goto('https://www.topmktx.com/auth/login', { 
            waitUntil: 'networkidle',
            timeout: 30000 
        });
        
        // 2. 페이지 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/login_page_debug.png',
            fullPage: true 
        });
        console.log('📸 로그인 페이지 스크린샷 저장');
        
        // 3. 폼 요소 확인
        const formElements = await page.evaluate(() => {
            const phoneInput = document.getElementById('phone');
            const passwordInput = document.getElementById('password');
            const submitButton = document.querySelector('button[type="submit"]');
            
            return {
                phoneInput: phoneInput ? { exists: true, type: phoneInput.type, name: phoneInput.name } : null,
                passwordInput: passwordInput ? { exists: true, type: passwordInput.type, name: passwordInput.name } : null,
                submitButton: submitButton ? { exists: true, text: submitButton.textContent } : null
            };
        });
        
        console.log('📋 폼 요소 확인:', formElements);
        
        // 4. 로그인 시도
        console.log('📝 로그인 정보 입력...');
        await page.fill('#phone', '010-2659-1346');
        await page.fill('#password', 'dnlszkem1!');
        
        // 입력 후 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/login_form_filled.png',
            fullPage: true 
        });
        console.log('📸 입력 완료 스크린샷 저장');
        
        // 5. 로그인 버튼 클릭
        console.log('🔘 로그인 버튼 클릭...');
        await page.click('button[type="submit"]');
        
        // 6. 응답 대기
        await page.waitForTimeout(5000);
        
        // 7. 결과 확인
        const currentUrl = page.url();
        const isLoginPage = currentUrl.includes('/auth/login');
        
        console.log('🔗 현재 URL:', currentUrl);
        console.log('🏠 로그인 페이지 여부:', isLoginPage);
        
        // 8. 에러 메시지 확인
        const errorMessage = await page.evaluate(() => {
            const errorDiv = document.querySelector('.alert-danger, .error-message, .alert-error');
            return errorDiv ? errorDiv.textContent.trim() : null;
        });
        
        if (errorMessage) {
            console.log('❌ 에러 메시지:', errorMessage);
        } else {
            console.log('📭 에러 메시지 없음');
        }
        
        // 9. 최종 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/login_result_debug.png',
            fullPage: true 
        });
        console.log('📸 로그인 결과 스크린샷 저장');
        
        // 10. 페이지 내용 확인
        const pageTitle = await page.title();
        console.log('📄 페이지 제목:', pageTitle);
        
        if (!isLoginPage) {
            console.log('✅ 로그인 성공으로 보임');
        } else {
            console.log('❌ 로그인 실패 - 여전히 로그인 페이지에 있음');
        }
        
    } catch (error) {
        console.error('❌ 디버깅 중 오류:', error.message);
        
        await page.screenshot({ 
            path: '/var/www/html/topmkt/login_debug_error.png',
            fullPage: true 
        });
        console.log('📸 오류 스크린샷 저장');
    } finally {
        await browser.close();
    }
}

debugLogin();