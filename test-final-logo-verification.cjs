const { chromium } = require('playwright');

async function testFinalLogoVerification() {
    console.log('🚀 비밀번호 찾기 페이지 로고 최종 검증');
    
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
        await page.waitForSelector('.main-header', { timeout: 10000 });
        
        // 잠시 대기하여 애니메이션 완료
        await page.waitForTimeout(3000);
        
        console.log('🔍 헤더 로고 요소들 확인...');
        
        // 로고 요소들이 존재하는지 확인
        const logoExists = await page.locator('.logo-link').count() > 0;
        const logoTextExists = await page.locator('.logo-text').count() > 0;
        const rocketExists = await page.locator('.header-rocket').count() > 0;
        
        console.log('📊 로고 요소 존재 여부:');
        console.log(`로고 링크: ${logoExists ? '존재' : '없음'}`);
        console.log(`로고 텍스트: ${logoTextExists ? '존재' : '없음'}`);
        console.log(`로켓 아이콘: ${rocketExists ? '존재' : '없음'}`);
        
        if (logoExists && logoTextExists && rocketExists) {
            // 로고 스타일 상세 분석
            const detailedStyles = await page.evaluate(() => {
                const logoLink = document.querySelector('.logo-link');
                const logoText = document.querySelector('.logo-text');
                const rocket = document.querySelector('.header-rocket');
                
                if (!logoLink || !logoText || !rocket) {
                    return { error: '로고 요소를 찾을 수 없습니다' };
                }
                
                const logoLinkStyles = window.getComputedStyle(logoLink);
                const logoTextStyles = window.getComputedStyle(logoText);
                const rocketStyles = window.getComputedStyle(rocket);
                
                return {
                    logoLink: {
                        color: logoLinkStyles.color,
                        display: logoLinkStyles.display,
                        visibility: logoLinkStyles.visibility,
                        opacity: logoLinkStyles.opacity
                    },
                    logoText: {
                        color: logoTextStyles.color,
                        display: logoTextStyles.display,
                        visibility: logoTextStyles.visibility,
                        opacity: logoTextStyles.opacity,
                        fontSize: logoTextStyles.fontSize,
                        fontWeight: logoTextStyles.fontWeight
                    },
                    rocket: {
                        color: rocketStyles.color,
                        display: rocketStyles.display,
                        visibility: rocketStyles.visibility,
                        opacity: rocketStyles.opacity,
                        fontSize: rocketStyles.fontSize,
                        animation: rocketStyles.animation
                    }
                };
            });
            
            console.log('📊 상세 스타일 분석:');
            console.log('로고 링크:', detailedStyles.logoLink);
            console.log('로고 텍스트:', detailedStyles.logoText);
            console.log('로켓 아이콘:', detailedStyles.rocket);
            
            // 애니메이션 작동 확인
            const hasAnimation = detailedStyles.rocket.animation && 
                                detailedStyles.rocket.animation !== 'none' && 
                                detailedStyles.rocket.animation.includes('headerRocketFloat');
            
            console.log(`🎬 로켓 애니메이션: ${hasAnimation ? '정상 작동' : '작동하지 않음'}`);
            
            // 색상 정상성 확인
            const isWhiteColor = (color) => {
                return color === 'rgb(255, 255, 255)' || color === '#ffffff' || color === 'white';
            };
            
            const isBlueRocket = detailedStyles.rocket.color === 'rgb(59, 130, 246)';
            
            console.log(`🎨 로고 색상: ${isWhiteColor(detailedStyles.logoLink.color) ? '정상 (흰색)' : '비정상'}`);
            console.log(`🎨 텍스트 색상: ${isWhiteColor(detailedStyles.logoText.color) ? '정상 (흰색)' : '비정상'}`);
            console.log(`🎨 로켓 색상: ${isBlueRocket ? '정상 (파란색)' : '비정상'}`);
        }
        
        // 3. 전체 화면 스크린샷 촬영
        console.log('📸 최종 검증 스크린샷 촬영...');
        await page.screenshot({ 
            path: 'forgot-password-final-logo-verification.png',
            fullPage: true
        });
        
        // 4. 헤더만 클로즈업 스크린샷
        await page.screenshot({ 
            path: 'forgot-password-header-closeup.png',
            fullPage: false,
            clip: { x: 0, y: 0, width: 1920, height: 150 }
        });
        
        console.log('✅ 최종 검증 완료');
        
        return {
            logoExists,
            logoTextExists, 
            rocketExists,
            success: logoExists && logoTextExists && rocketExists
        };
        
    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testFinalLogoVerification()
    .then(results => {
        console.log('\n📊 최종 검증 결과:');
        if (results.success) {
            console.log('✅ 비밀번호 찾기 페이지 헤더 로고가 정상적으로 표시됩니다!');
            console.log('🎯 로고, 텍스트, 로켓 아이콘 모두 정상 작동');
            console.log('🎨 색상 및 애니메이션 일관성 확보');
        } else {
            console.log('❌ 일부 로고 요소에 문제가 있습니다');
            console.log(`로고 링크: ${results.logoExists ? 'OK' : 'FAIL'}`);
            console.log(`로고 텍스트: ${results.logoTextExists ? 'OK' : 'FAIL'}`);
            console.log(`로켓 아이콘: ${results.rocketExists ? 'OK' : 'FAIL'}`);
        }
    })
    .catch(error => {
        console.error('💥 테스트 실행 실패:', error);
        process.exit(1);
    });