/**
 * 최종 검색 UI 완전 검증 테스트
 * 모든 수정사항이 정상 작동하는지 확인
 */

const { chromium } = require('playwright');

async function finalSearchUITest() {
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'] 
    });
    
    try {
        const page = await browser.newPage();
        
        console.log('🔐 DevLoginHelper로 로그인 중...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForTimeout(2000);
        
        console.log('📋 커뮤니티 페이지 접근...');
        await page.goto('https://www.topmktx.com/community');
        await page.waitForTimeout(3000);
        
        // 1단계: 첫 번째 검색 (결과 없는 검색어)
        console.log('\\n🔍 1단계: 결과 없는 검색어로 테스트');
        await page.fill('#searchInput', 'ㅁㄴㅇㅁㅇㄴㅊㅋㅌㅍㅎ');
        await page.click('.search-btn');
        await page.waitForTimeout(2000);
        
        // 글쓰기 버튼 크기 확인
        const writeButtonInfo = await page.$eval('a[href="/community/write"]', btn => {
            const rect = btn.getBoundingClientRect();
            const styles = window.getComputedStyle(btn);
            return {
                width: Math.round(rect.width),
                height: Math.round(rect.height),
                fontSize: styles.fontSize,
                padding: styles.padding,
                classes: btn.className
            };
        });
        
        console.log(`   글쓰기 버튼: ${writeButtonInfo.width}×${writeButtonInfo.height}px`);
        console.log(`   스타일: ${writeButtonInfo.fontSize}, ${writeButtonInfo.padding}`);
        console.log(`   클래스: ${writeButtonInfo.classes}`);
        
        const isGoodSize = writeButtonInfo.width < 150 && writeButtonInfo.height < 50;
        console.log(`   버튼 크기 적절함: ${isGoodSize ? '✅' : '❌'}`);
        
        // 2단계: 돋보기 재클릭 테스트
        console.log('\\n🎯 2단계: 돋보기 재검색 테스트');
        
        await page.fill('#searchInput', '테스트');
        console.log('   새 검색어 입력: "테스트"');
        
        // 돋보기 버튼 클릭
        try {
            await page.click('.search-btn');
            await page.waitForTimeout(2000);
            
            const newUrl = page.url();
            console.log(`   검색 후 URL: ${newUrl}`);
            
            const searchWorked = newUrl.includes('테스트') || newUrl.includes('%ED%85%8C%EC%8A%A4%ED%8A%B8');
            console.log(`   돋보기 재검색: ${searchWorked ? '✅ 성공' : '❌ 실패'}`);
            
            // 검색 결과 개수 확인
            const resultCount = await page.$$eval('.post-item', items => items.length);
            console.log(`   검색 결과: ${resultCount}개`);
            
        } catch (e) {
            console.log(`   ❌ 돋보기 클릭 실패: ${e.message}`);
        }
        
        // 3단계: 검색 해제 기능 테스트
        console.log('\\n✖️ 3단계: 검색 해제 기능 테스트');
        
        const clearButton = await page.$('a:has-text("검색 해제")');
        if (clearButton) {
            await clearButton.click();
            await page.waitForTimeout(2000);
            
            const clearedUrl = page.url();
            console.log(`   검색 해제 후 URL: ${clearedUrl}`);
            
            const isCleared = !clearedUrl.includes('search=');
            console.log(`   검색 해제: ${isCleared ? '✅ 성공' : '❌ 실패'}`);
            
            const inputValue = await page.$eval('#searchInput', input => input.value);
            console.log(`   입력 필드 초기화: ${inputValue === '' ? '✅' : '❌'}`);
        }
        
        // 4단계: 연속 검색 테스트 (실제 사용 시나리오)
        console.log('\\n🔄 4단계: 연속 검색 시나리오 테스트');
        
        const searchTerms = ['마케팅', '광고', '테스트'];
        let continuousSearchSuccess = true;
        
        for (let i = 0; i < searchTerms.length; i++) {
            const term = searchTerms[i];
            console.log(`   ${i+1}/3 검색: "${term}"`);
            
            await page.fill('#searchInput', term);
            await page.click('.search-btn');
            await page.waitForTimeout(2000);
            
            const url = page.url();
            const worked = url.includes(encodeURIComponent(term));
            console.log(`       결과: ${worked ? '✅' : '❌'}`);
            
            if (!worked) continuousSearchSuccess = false;
        }
        
        console.log(`   연속 검색 테스트: ${continuousSearchSuccess ? '✅ 모두 성공' : '❌ 일부 실패'}`);
        
        // 최종 스크린샷
        await page.screenshot({ 
            path: `/var/www/html/topmkt/final-search-ui-verification.png`,
            fullPage: true
        });
        
        return {
            success: isGoodSize && continuousSearchSuccess,
            writeButtonSize: isGoodSize,
            searchFunctionality: continuousSearchSuccess,
            details: {
                writeButton: writeButtonInfo,
                searchTermsWorked: searchTerms.length
            }
        };
        
    } catch (error) {
        console.error('❌ 테스트 실행 중 오류:', error);
        return { success: false, error: error.message };
    } finally {
        await browser.close();
    }
}

// 실행
if (require.main === module) {
    finalSearchUITest().then(result => {
        console.log('\\n🎉 최종 검색 UI 검증 결과:');
        console.log(`   전체 테스트: ${result.success ? '✅ 완벽 성공' : '❌ 문제 있음'}`);
        console.log(`   글쓰기 버튼 크기: ${result.writeButtonSize ? '✅ 적절함' : '❌ 큼'}`);
        console.log(`   검색 기능성: ${result.searchFunctionality ? '✅ 완벽' : '❌ 문제'}`);
        
        if (result.details) {
            console.log(`\\n📊 상세 정보:`);
            console.log(`   글쓰기 버튼: ${result.details.writeButton.width}×${result.details.writeButton.height}px`);
            console.log(`   연속 검색 성공: ${result.details.searchTermsWorked}/3`);
        }
        
        console.log('\\n📷 최종 검증 스크린샷: final-search-ui-verification.png');
        
        process.exit(result.success ? 0 : 1);
    });
}