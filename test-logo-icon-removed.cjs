const { chromium } = require('playwright');

async function testLogoIconRemoved() {
    console.log('🔍 보라색 로고 아이콘 제거 확인 테스트');
    
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
        await page.waitForSelector('header', { timeout: 10000 });
        await page.waitForTimeout(3000);
        
        // 2. 페이지 내 모든 로고 관련 요소 확인
        const logoAnalysis = await page.evaluate(() => {
            const results = {
                commonHeader: {},
                formHeader: {},
                logoIconElements: []
            };
            
            // 공통 헤더의 로고 확인
            const commonHeaderLogo = document.querySelector('.main-header .logo-link');
            const commonHeaderText = document.querySelector('.main-header .logo-text');
            
            if (commonHeaderLogo && commonHeaderText) {
                const logoStyles = window.getComputedStyle(commonHeaderLogo);
                const textStyles = window.getComputedStyle(commonHeaderText);
                
                results.commonHeader = {
                    exists: true,
                    logoColor: logoStyles.color,
                    textColor: textStyles.color,
                    textContent: commonHeaderText.textContent?.trim() || ''
                };
            } else {
                results.commonHeader = { exists: false };
            }
            
            // 폼 헤더의 로고 확인
            const formHeaderLogo = document.querySelector('.form-header .brand-logo');
            const formHeaderText = document.querySelector('.form-header .brand-name');
            
            if (formHeaderLogo && formHeaderText) {
                const logoStyles = window.getComputedStyle(formHeaderLogo);
                const textStyles = window.getComputedStyle(formHeaderText);
                
                results.formHeader = {
                    exists: true,
                    logoColor: logoStyles.color,
                    textColor: textStyles.color,
                    textContent: formHeaderText.textContent?.trim() || ''
                };
            } else {
                results.formHeader = { exists: false };
            }
            
            // .logo-icon 요소가 여전히 존재하는지 확인
            const logoIconElements = document.querySelectorAll('.logo-icon');
            results.logoIconElements = Array.from(logoIconElements).map((el, idx) => {
                const styles = window.getComputedStyle(el);
                return {
                    index: idx,
                    exists: true,
                    display: styles.display,
                    visibility: styles.visibility,
                    backgroundColor: styles.backgroundColor,
                    width: styles.width,
                    height: styles.height
                };
            });
            
            return results;
        });
        
        console.log('📊 로고 분석 결과:');
        
        // 3. 공통 헤더 로고 상태
        if (logoAnalysis.commonHeader.exists) {
            console.log('✅ 공통 헤더 로고:');
            console.log(`   텍스트: "${logoAnalysis.commonHeader.textContent}"`);
            console.log(`   색상: ${logoAnalysis.commonHeader.textColor}`);
        } else {
            console.log('❌ 공통 헤더 로고를 찾을 수 없습니다');
        }
        
        // 4. 폼 헤더 로고 상태
        if (logoAnalysis.formHeader.exists) {
            console.log('✅ 폼 헤더 로고:');
            console.log(`   텍스트: "${logoAnalysis.formHeader.textContent}"`);
            console.log(`   색상: ${logoAnalysis.formHeader.textColor}`);
        } else {
            console.log('❌ 폼 헤더 로고를 찾을 수 없습니다');
        }
        
        // 5. 보라색 아이콘 요소 확인
        console.log(`🎨 보라색 아이콘 요소 개수: ${logoAnalysis.logoIconElements.length}`);
        
        if (logoAnalysis.logoIconElements.length === 0) {
            console.log('✅ 보라색 로고 아이콘이 완전히 제거되었습니다!');
        } else {
            console.log('❌ 아직 보라색 로고 아이콘 요소가 남아있습니다:');
            logoAnalysis.logoIconElements.forEach((el, idx) => {
                console.log(`   ${idx + 1}. 표시: ${el.display}, 가시성: ${el.visibility}, 배경색: ${el.backgroundColor}`);
            });
        }
        
        // 6. 스크린샷 촬영
        console.log('📸 수정 후 스크린샷 촬영...');
        
        // 전체 페이지
        await page.screenshot({ 
            path: 'logo-icon-removed-full-page.png',
            fullPage: true
        });
        
        // 헤더 부분만 클로즈업
        await page.screenshot({ 
            path: 'logo-icon-removed-header-only.png',
            fullPage: false,
            clip: { x: 0, y: 0, width: 1920, height: 200 }
        });
        
        // 폼 헤더 부분만 클로즈업
        try {
            const formHeaderBounds = await page.locator('.form-header').boundingBox();
            if (formHeaderBounds) {
                await page.screenshot({ 
                    path: 'logo-icon-removed-form-header.png',
                    fullPage: false,
                    clip: {
                        x: formHeaderBounds.x,
                        y: formHeaderBounds.y,
                        width: formHeaderBounds.width,
                        height: formHeaderBounds.height + 50
                    }
                });
            }
        } catch (e) {
            console.log('폼 헤더 스크린샷 실패, 무시됨');
        }
        
        console.log('✅ 로고 아이콘 제거 테스트 완료');
        
        return {
            commonHeaderExists: logoAnalysis.commonHeader.exists,
            formHeaderExists: logoAnalysis.formHeader.exists,
            logoIconCount: logoAnalysis.logoIconElements.length,
            isIconRemoved: logoAnalysis.logoIconElements.length === 0,
            commonHeaderText: logoAnalysis.commonHeader.textContent || '',
            formHeaderText: logoAnalysis.formHeader.textContent || ''
        };
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testLogoIconRemoved()
    .then(results => {
        console.log('\n📊 최종 로고 아이콘 제거 테스트 결과:');
        console.log(`공통 헤더: ${results.commonHeaderExists ? '✅ 존재' : '❌ 없음'} - "${results.commonHeaderText}"`);
        console.log(`폼 헤더: ${results.formHeaderExists ? '✅ 존재' : '❌ 없음'} - "${results.formHeaderText}"`);
        console.log(`보라색 아이콘 개수: ${results.logoIconCount}개`);
        console.log(`아이콘 제거 완료: ${results.isIconRemoved ? '✅ 성공' : '❌ 실패'}`);
        
        if (results.isIconRemoved) {
            console.log('\n🎉 보라색 로고 아이콘이 성공적으로 제거되었습니다!');
            console.log('이제 비밀번호 찾기 페이지의 로고가 공통 헤더와 일치합니다.');
        } else {
            console.log('\n⚠️ 일부 보라색 아이콘 요소가 여전히 남아있을 수 있습니다.');
        }
    })
    .catch(error => {
        console.error('💥 테스트 실행 실패:', error);
        process.exit(1);
    });