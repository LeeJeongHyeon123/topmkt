/**
 * 검색 결과가 있는 경우의 UI 테스트
 * 실제 검색어로 결과가 나오는 경우 확인
 */

const { chromium } = require('playwright');

async function testSearchWithResults() {
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'] 
    });
    
    try {
        const page = await browser.newPage();
        
        console.log('🔐 DevLoginHelper로 로그인 중...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForTimeout(2000);
        
        // 커뮤니티 페이지로 이동
        console.log('📋 커뮤니티 페이지 접근...');
        await page.goto('https://www.topmktx.com/community');
        await page.waitForTimeout(3000);
        
        // 실제 검색어로 검색 (결과가 있을 가능성이 높은 검색어)
        console.log('🔍 실제 검색어로 검색 실행...');
        const searchTerms = ['테스트', '마케팅', '안녕', '게시'];
        
        for (const term of searchTerms) {
            console.log(`\\n검색어: "${term}"`);
            
            // 검색어 입력
            await page.fill('#searchInput', term);
            await page.selectOption('#searchFilter', 'all');
            
            // 검색 실행
            await page.click('.search-btn');
            await page.waitForTimeout(3000);
            
            // 검색 결과 확인
            const searchResults = await page.$eval('.post-list', container => {
                const posts = container.querySelectorAll('.post-item');
                const emptyState = container.querySelector('.empty-state');
                
                return {
                    hasResults: posts.length > 0,
                    resultCount: posts.length,
                    hasEmptyState: !!emptyState
                };
            });
            
            console.log(`   결과: ${searchResults.hasResults ? `${searchResults.resultCount}개 발견` : '결과 없음'}`);
            
            if (searchResults.hasResults) {
                // 검색 결과가 있는 경우 글쓰기 버튼 상태 확인
                const writeButtonExists = await page.$('a[href="/community/write"]');
                const writeButtonInfo = writeButtonExists ? await page.$eval('a[href="/community/write"]', btn => ({
                    text: btn.textContent.trim(),
                    classes: btn.className,
                    width: Math.round(btn.getBoundingClientRect().width),
                    height: Math.round(btn.getBoundingClientRect().height)
                })) : null;
                
                console.log(`   글쓰기 버튼: ${writeButtonInfo ? `"${writeButtonInfo.text}" (${writeButtonInfo.classes})` : '없음'}`);
                
                // 검색 입력 필드 상태 확인 (1.5초 후)
                await page.waitForTimeout(1500);
                
                const inputState = await page.$eval('#searchInput', input => {
                    const styles = window.getComputedStyle(input);
                    return {
                        borderColor: styles.borderColor,
                        backgroundColor: styles.backgroundColor
                    };
                });
                
                const isNormalized = inputState.borderColor === 'rgb(226, 232, 240)' && 
                                   inputState.backgroundColor === 'rgb(255, 255, 255)';
                
                console.log(`   입력 필드 상태 정상화: ${isNormalized ? '✅' : '❌'}`);
                
                // 스크린샷 촬영
                await page.screenshot({ 
                    path: `/var/www/html/topmkt/search-results-${term}.png`,
                    fullPage: true
                });
                
                console.log(`   스크린샷: search-results-${term}.png`);
                
                // 첫 번째 성공적인 검색에서 중단
                return {
                    success: true,
                    searchTerm: term,
                    resultCount: searchResults.resultCount,
                    inputNormalized: isNormalized,
                    writeButton: writeButtonInfo
                };
            }
            
            // 다음 검색을 위해 잠시 대기
            await page.waitForTimeout(1000);
        }
        
        console.log('\\n⚠️ 모든 검색어에서 결과를 찾지 못했습니다.');
        return {
            success: false,
            message: '검색 결과 없음'
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
    testSearchWithResults().then(result => {
        console.log('\\n🎉 검색 결과 있는 경우 테스트 결과:');
        console.log(`   테스트 성공: ${result.success ? '✅' : '❌'}`);
        
        if (result.success) {
            console.log(`   검색어: "${result.searchTerm}"`);
            console.log(`   결과 개수: ${result.resultCount}개`);
            console.log(`   입력 상태 정상화: ${result.inputNormalized ? '✅' : '❌'}`);
            if (result.writeButton) {
                console.log(`   글쓰기 버튼: "${result.writeButton.text}" (${result.writeButton.classes})`);
            }
        }
        
        process.exit(result.success ? 0 : 1);
    });
}