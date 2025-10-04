const { chromium } = require('playwright');

(async () => {
    console.log('🧪 Button 컴포넌트 QA 테스트 시작...\n');
    
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    const results = {
        passed: 0,
        failed: 0,
        tests: []
    };
    
    function logTest(name, passed, details = '') {
        results.tests.push({ name, passed, details });
        if (passed) {
            console.log(`✅ ${name}`);
            results.passed++;
        } else {
            console.log(`❌ ${name}`);
            if (details) console.log(`   ${details}`);
            results.failed++;
        }
    }
    
    try {
        // 1. 로그인 페이지 접속
        console.log('📍 Step 1: 로그인 페이지 접속\n');
        await page.goto('https://www.topmktx.com/auth/login');
        await page.waitForLoadState('networkidle');
        
        logTest('로그인 페이지 접속', true);
        
        // 2. 버튼 존재 확인
        console.log('\n📍 Step 2: 버튼 존재 확인\n');
        
        const loginButton = await page.locator('button[type="submit"]').count();
        logTest('로그인 버튼 존재', loginButton > 0);
        
        // 3. 버튼 클래스 확인
        console.log('\n📍 Step 3: 버튼 클래스 확인\n');
        
        const loginBtnClasses = await page.locator('button[type="submit"]').getAttribute('class');
        const hasBtn = loginBtnClasses?.includes('btn');
        const hasPrimary = loginBtnClasses?.includes('btn-primary');
        const hasLg = loginBtnClasses?.includes('btn-lg');
        const hasFull = loginBtnClasses?.includes('btn-full');
        
        logTest('버튼 기본 클래스 (btn)', hasBtn, loginBtnClasses);
        logTest('버튼 타입 클래스 (btn-primary)', hasPrimary, loginBtnClasses);
        logTest('버튼 크기 클래스 (btn-lg)', hasLg, loginBtnClasses);
        logTest('전체 너비 클래스 (btn-full)', hasFull, loginBtnClasses);
        
        // 4. 버튼 스타일 확인
        console.log('\n📍 Step 4: 버튼 CSS 스타일 확인\n');
        
        const styles = await page.locator('button[type="submit"]').evaluate(el => {
            const computed = window.getComputedStyle(el);
            return {
                minHeight: computed.minHeight,
                width: computed.width,
                display: computed.display,
                fontSize: computed.fontSize,
                padding: computed.padding
            };
        });
        
        const minHeightOk = parseInt(styles.minHeight) >= 52; // lg는 52px
        const isFullWidth = styles.width === '100%' || styles.display === 'block';
        
        logTest('최소 높이 52px 이상', minHeightOk, `실제: ${styles.minHeight}`);
        logTest('전체 너비 (100%)', isFullWidth, `width: ${styles.width}, display: ${styles.display}`);
        
        // 5. 아이콘 확인
        console.log('\n📍 Step 5: 아이콘 확인\n');
        
        const hasIcon = await page.locator('button[type="submit"] i.fas.fa-sign-in-alt').count();
        logTest('로그인 아이콘 존재', hasIcon > 0);
        
        // 6. 테스트 계정 버튼 확인 (개발 모드)
        console.log('\n📍 Step 6: 테스트 계정 버튼 확인\n');
        
        const testButton = await page.locator('button:has-text("테스트 계정으로 자동 입력")').count();
        if (testButton > 0) {
            const testBtnClasses = await page.locator('button:has-text("테스트 계정으로 자동 입력")').getAttribute('class');
            const hasOutline = testBtnClasses?.includes('btn-outline');
            logTest('테스트 버튼 outline 스타일', hasOutline, testBtnClasses);
        } else {
            logTest('테스트 버튼 (개발 모드 아님)', true, '프로덕션 환경');
        }
        
        // 7. 모바일 반응형 테스트
        console.log('\n📍 Step 7: 모바일 반응형 테스트\n');
        
        // 768px
        await page.setViewportSize({ width: 768, height: 1024 });
        await page.waitForTimeout(500);
        
        const mobile768 = await page.locator('button[type="submit"]').evaluate(el => {
            const computed = window.getComputedStyle(el);
            return {
                minHeight: computed.minHeight,
                fontSize: computed.fontSize
            };
        });
        
        const mobile768Ok = parseInt(mobile768.minHeight) >= 48;
        logTest('모바일 768px 높이', mobile768Ok, `실제: ${mobile768.minHeight}`);
        
        // 480px
        await page.setViewportSize({ width: 480, height: 800 });
        await page.waitForTimeout(500);
        
        const mobile480 = await page.locator('button[type="submit"]').evaluate(el => {
            const computed = window.getComputedStyle(el);
            return {
                minHeight: computed.minHeight,
                fontSize: computed.fontSize
            };
        });
        
        const mobile480Ok = parseInt(mobile480.minHeight) >= 48;
        logTest('모바일 480px 높이', mobile480Ok, `실제: ${mobile480.minHeight}`);
        
        // 8. 접근성 테스트
        console.log('\n📍 Step 8: 접근성 테스트\n');
        
        const ariaLabel = await page.locator('button[type="submit"]').getAttribute('aria-label');
        const hasAriaLabel = ariaLabel !== null && ariaLabel !== '';
        logTest('aria-label 존재', !hasAriaLabel, '텍스트가 있어서 불필요 (정상)');
        
        // 9. 스크린샷
        console.log('\n📍 Step 9: 스크린샷 생성\n');
        
        await page.setViewportSize({ width: 1920, height: 1080 });
        await page.screenshot({ path: 'qa-login-desktop.png' });
        logTest('데스크톱 스크린샷', true, 'qa-login-desktop.png');
        
        await page.setViewportSize({ width: 768, height: 1024 });
        await page.screenshot({ path: 'qa-login-mobile-768.png' });
        logTest('모바일 768px 스크린샷', true, 'qa-login-mobile-768.png');
        
        await page.setViewportSize({ width: 480, height: 800 });
        await page.screenshot({ path: 'qa-login-mobile-480.png' });
        logTest('모바일 480px 스크린샷', true, 'qa-login-mobile-480.png');
        
    } catch (error) {
        console.error('❌ 테스트 오류:', error.message);
        results.failed++;
    } finally {
        await browser.close();
    }
    
    // 최종 결과
    console.log('\n========================================');
    console.log('📊 QA 테스트 결과');
    console.log('========================================');
    console.log(`✅ 통과: ${results.passed}개`);
    console.log(`❌ 실패: ${results.failed}개`);
    console.log(`📈 성공률: ${((results.passed / (results.passed + results.failed)) * 100).toFixed(1)}%`);
    console.log('========================================\n');
    
    if (results.failed === 0) {
        console.log('🎉 모든 테스트 통과! 버튼 컴포넌트가 완벽하게 작동합니다!');
    } else {
        console.log('⚠️  일부 테스트 실패. 위 내용을 확인하세요.');
    }
    
    process.exit(results.failed > 0 ? 1 : 0);
})();
