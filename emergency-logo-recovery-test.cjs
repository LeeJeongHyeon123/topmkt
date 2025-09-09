const { chromium } = require('playwright');

async function emergencyLogoRecoveryTest() {
    console.log('🚨 긴급 로고 복구 테스트 시작');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const testPages = [
        { name: '메인 페이지', url: 'https://www.topmktx.com/' },
        { name: '커뮤니티', url: 'https://www.topmktx.com/community' },
        { name: '강의 목록', url: 'https://www.topmktx.com/lectures' },
        { name: '비밀번호 찾기', url: 'https://www.topmktx.com/auth/forgot-password' }
    ];
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 },
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });
        
        const page = await context.newPage();
        const results = [];
        
        for (const testPage of testPages) {
            console.log(`\n🔍 ${testPage.name} 테스트 중...`);
            
            try {
                await page.goto(testPage.url, { timeout: 10000 });
                await page.waitForSelector('.main-header', { timeout: 5000 });
                await page.waitForTimeout(1000);
                
                // 로고 요소들 확인
                const logoStatus = await page.evaluate(() => {
                    const logoIcon = document.querySelector('.main-header .logo-icon');
                    const logoText = document.querySelector('.main-header .logo-text');
                    const headerRocket = document.querySelector('.main-header .header-rocket');
                    
                    return {
                        logoIcon: !!logoIcon,
                        logoText: !!logoText,
                        headerRocket: !!headerRocket,
                        logoTextContent: logoText ? logoText.textContent?.trim() : '',
                        rocketClass: headerRocket ? headerRocket.className : '',
                        rocketVisible: headerRocket ? window.getComputedStyle(headerRocket).visibility === 'visible' : false
                    };
                });
                
                results.push({
                    page: testPage.name,
                    url: testPage.url,
                    ...logoStatus,
                    success: logoStatus.logoIcon && logoStatus.logoText && logoStatus.headerRocket
                });
                
                console.log(`   로고 아이콘: ${logoStatus.logoIcon ? '✅' : '❌'}`);
                console.log(`   로고 텍스트: ${logoStatus.logoText ? '✅' : '❌'} - "${logoStatus.logoTextContent}"`);
                console.log(`   로켓 아이콘: ${logoStatus.headerRocket ? '✅' : '❌'} - ${logoStatus.rocketClass}`);
                console.log(`   로켓 가시성: ${logoStatus.rocketVisible ? '✅' : '❌'}`);
                console.log(`   전체 상태: ${logoStatus.logoIcon && logoStatus.logoText && logoStatus.headerRocket ? '✅ 정상' : '❌ 문제'}`);
                
            } catch (error) {
                console.log(`   ❌ 페이지 로딩 실패: ${error.message}`);
                results.push({
                    page: testPage.name,
                    url: testPage.url,
                    error: error.message,
                    success: false
                });
            }
        }
        
        // 비밀번호 찾기 페이지 특별 확인 (폼 헤더 보라색 아이콘 제거 확인)
        console.log(`\n🎯 비밀번호 찾기 페이지 폼 헤더 보라색 아이콘 확인...`);
        
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('.form-header', { timeout: 5000 });
        
        const formHeaderStatus = await page.evaluate(() => {
            const formLogoIcon = document.querySelectorAll('.form-header .logo-icon');
            const brandName = document.querySelector('.form-header .brand-name');
            
            return {
                formLogoIconCount: formLogoIcon.length,
                brandNameExists: !!brandName,
                brandNameText: brandName ? brandName.textContent?.trim() : ''
            };
        });
        
        console.log(`   폼 헤더 보라색 아이콘 개수: ${formHeaderStatus.formLogoIconCount} (0이어야 정상)`);
        console.log(`   브랜드명: ${formHeaderStatus.brandNameExists ? '✅' : '❌'} - "${formHeaderStatus.brandNameText}"`);
        console.log(`   보라색 아이콘 제거 상태: ${formHeaderStatus.formLogoIconCount === 0 ? '✅ 제거됨' : '❌ 아직 존재'}`);
        
        // 스크린샷 촬영
        for (let i = 0; i < testPages.length; i++) {
            await page.goto(testPages[i].url);
            await page.waitForSelector('.main-header', { timeout: 5000 });
            await page.screenshot({ 
                path: `emergency-recovery-${testPages[i].name.replace(/\s+/g, '-')}.png`,
                fullPage: false,
                clip: { x: 0, y: 0, width: 1920, height: 150 }
            });
        }
        
        console.log('\n✅ 긴급 로고 복구 테스트 완료');
        
        // 결과 요약
        const successCount = results.filter(r => r.success).length;
        console.log(`\n📊 테스트 결과 요약:`);
        console.log(`성공: ${successCount}/${results.length} 페이지`);
        console.log(`비밀번호 찾기 보라색 아이콘 제거: ${formHeaderStatus.formLogoIconCount === 0 ? '✅ 성공' : '❌ 실패'}`);
        
        return {
            results,
            totalPages: testPages.length,
            successPages: successCount,
            formIconRemoved: formHeaderStatus.formLogoIconCount === 0,
            allFixed: successCount === testPages.length && formHeaderStatus.formLogoIconCount === 0
        };
        
    } catch (error) {
        console.error('❌ 긴급 복구 테스트 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
emergencyLogoRecoveryTest()
    .then(results => {
        console.log('\n🚨 긴급 복구 최종 결과:');
        console.log(`전체 페이지 로고 복구: ${results.allFixed ? '✅ 완전 복구' : '❌ 부분 복구'}`);
        console.log(`성공률: ${results.successPages}/${results.totalPages} (${Math.round(results.successPages / results.totalPages * 100)}%)`);
        
        if (results.allFixed) {
            console.log('🎉 모든 페이지의 로고가 완전히 복구되었습니다!');
            console.log('   - 공통 헤더: 파란색 로켓 아이콘 + "탑마케팅" 텍스트 ✅');
            console.log('   - 비밀번호 찾기: 보라색 아이콘 제거, "탑마케팅" 텍스트만 ✅');
        } else {
            console.log('⚠️ 일부 페이지에 여전히 문제가 있을 수 있습니다.');
        }
    })
    .catch(error => {
        console.error('💥 긴급 복구 테스트 실행 실패:', error);
        process.exit(1);
    });