const { chromium } = require('playwright');

async function testArrowIconWhite() {
    console.log('🚀 로그인으로 돌아가기 버튼 화살표 아이콘 흰색 변경 테스트 시작');
    
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
        
        // 1. 비밀번호 찾기 페이지 로딩
        console.log('📱 비밀번호 찾기 페이지 로딩...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('.auth-navigation', { timeout: 10000 });
        
        // 2. 로그인으로 돌아가기 버튼 찾기
        console.log('🔍 로그인으로 돌아가기 버튼 확인...');
        const primaryNavLink = await page.locator('.nav-link.primary');
        const arrowIcon = await page.locator('.nav-link.primary .fa-arrow-left');
        const buttonText = await page.locator('.nav-link.primary span');
        
        // 3. 버튼과 아이콘 스타일 확인
        console.log('🎨 버튼 스타일 분석...');
        
        const buttonStyles = await primaryNavLink.evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                color: styles.color,
                backgroundColor: styles.backgroundColor,
                background: styles.background
            };
        });
        
        const arrowIconStyles = await arrowIcon.evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                color: styles.color,
                fontSize: styles.fontSize,
                display: styles.display
            };
        });
        
        const textStyles = await buttonText.evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                color: styles.color,
                fontWeight: styles.fontWeight
            };
        });
        
        console.log('📊 로그인 버튼 현재 스타일:');
        console.log('버튼 전체:', buttonStyles);
        console.log('화살표 아이콘:', arrowIconStyles);
        console.log('텍스트:', textStyles);
        
        // 4. 버튼 텍스트 확인
        const buttonTextContent = await primaryNavLink.textContent();
        console.log(`📝 버튼 텍스트: "${buttonTextContent?.trim()}"`);
        
        // 5. 화살표 아이콘이 흰색인지 확인
        const isArrowWhite = arrowIconStyles.color === 'rgb(255, 255, 255)' || 
                            arrowIconStyles.color === 'white' ||
                            arrowIconStyles.color === 'rgba(255, 255, 255, 1)';
        
        const isTextWhite = textStyles.color === 'rgb(255, 255, 255)' || 
                           textStyles.color === 'white' ||
                           textStyles.color === 'rgba(255, 255, 255, 1)';
        
        // 6. 호버 효과 테스트
        console.log('🖱️ 호버 효과 테스트...');
        await primaryNavLink.hover();
        await page.waitForTimeout(500);
        
        const hoverArrowStyles = await arrowIcon.evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                color: styles.color
            };
        });
        
        const isArrowWhiteOnHover = hoverArrowStyles.color === 'rgb(255, 255, 255)' || 
                                   hoverArrowStyles.color === 'white' ||
                                   hoverArrowStyles.color === 'rgba(255, 255, 255, 1)';
        
        // 7. 스크린샷 촬영
        console.log('📸 스크린샷 촬영...');
        await page.screenshot({ 
            path: 'login-button-arrow-white-test.png',
            fullPage: false,
            clip: { x: 0, y: 400, width: 1920, height: 400 }
        });
        
        console.log('✅ 테스트 완료 - 스크린샷 저장됨');
        
        return {
            buttonStyles,
            arrowIconStyles,
            textStyles,
            hoverArrowStyles,
            isArrowWhite,
            isTextWhite,
            isArrowWhiteOnHover,
            buttonText: buttonTextContent?.trim(),
            summary: isArrowWhite && isTextWhite ? 
                '✅ 화살표 아이콘과 텍스트 모두 흰색 적용 성공!' : 
                '❌ 화살표 아이콘 또는 텍스트 색상 문제 존재'
        };
        
    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testArrowIconWhite()
    .then(results => {
        console.log('\n📊 최종 화살표 아이콘 테스트 결과:');
        console.log(`${results.summary}`);
        console.log(`🔤 버튼 텍스트: "${results.buttonText}"`);
        console.log(`🎯 화살표 아이콘 색상: ${results.arrowIconStyles.color}`);
        console.log(`📝 텍스트 색상: ${results.textStyles.color}`);
        console.log(`🖱️ 호버 시 화살표 색상: ${results.hoverArrowStyles.color}`);
        console.log(`✅ 화살표 흰색: ${results.isArrowWhite ? '성공' : '실패'}`);
        console.log(`✅ 텍스트 흰색: ${results.isTextWhite ? '성공' : '실패'}`);
        console.log(`✅ 호버 시 화살표 흰색: ${results.isArrowWhiteOnHover ? '성공' : '실패'}`);
    })
    .catch(error => {
        console.error('💥 테스트 실행 실패:', error);
        process.exit(1);
    });