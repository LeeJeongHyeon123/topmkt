/**
 * 커뮤니티 검색 UI 문제 재현 테스트
 * 1. 검색 후 돋보기가 사라지지 않는 문제
 * 2. 글쓰기 버튼 크기가 비정상적으로 커지는 문제
 */

const { chromium } = require('playwright');

async function testSearchUIIssues() {
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'] 
    });
    
    try {
        const page = await browser.newPage();
        
        console.log('🔐 DevLoginHelper로 로그인 중...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForTimeout(2000);
        
        // 1단계: 검색 전 상태 확인
        console.log('📋 1단계: 검색 전 상태 확인...');
        await page.goto('https://www.topmktx.com/community');
        await page.waitForTimeout(3000);
        
        // 검색 전 상태 스크린샷
        await page.screenshot({ 
            path: `/var/www/html/topmkt/before-search-ui.png`,
            fullPage: true
        });
        
        // 검색 전 글쓰기 버튼 크기 측정
        const beforeSearchWriteBtn = await page.$eval('a[href="/community/write"], a[href*="login"]', btn => {
            const rect = btn.getBoundingClientRect();
            return {
                width: Math.round(rect.width),
                height: Math.round(rect.height),
                text: btn.textContent.trim(),
                classes: btn.className
            };
        });
        
        console.log(`   검색 전 글쓰기 버튼: "${beforeSearchWriteBtn.text}" (${beforeSearchWriteBtn.width}×${beforeSearchWriteBtn.height}px)`);
        console.log(`   클래스: ${beforeSearchWriteBtn.classes}`);
        
        // 검색 버튼 상태 확인
        const searchBtnBefore = await page.$eval('.search-btn', btn => {
            const rect = btn.getBoundingClientRect();
            const styles = window.getComputedStyle(btn);
            return {
                width: Math.round(rect.width),
                height: Math.round(rect.height),
                visible: btn.offsetWidth > 0 && btn.offsetHeight > 0,
                display: styles.display,
                opacity: styles.opacity,
                backgroundColor: styles.backgroundColor
            };
        });
        
        console.log(`   검색 전 돋보기 버튼: ${searchBtnBefore.width}×${searchBtnBefore.height}px, 표시: ${searchBtnBefore.visible}`);
        console.log(`   스타일: display=${searchBtnBefore.display}, opacity=${searchBtnBefore.opacity}`);
        
        // 2단계: 검색 실행
        console.log('\\n🔍 2단계: 검색 실행...');
        
        // 검색어 입력 (문제 URL과 동일한 검색어)
        await page.fill('#searchInput', 'ㅂㄴㅇㅂㅇㄴ');
        await page.selectOption('#searchFilter', 'all');
        
        // 검색 실행 (버튼 클릭)
        await page.click('.search-btn');
        await page.waitForTimeout(3000);
        
        // 현재 URL 확인
        const currentUrl = page.url();
        console.log(`   검색 후 URL: ${currentUrl}`);
        
        // 3단계: 검색 후 상태 확인
        console.log('\\n📊 3단계: 검색 후 상태 확인...');
        
        // 검색 후 스크린샷
        await page.screenshot({ 
            path: `/var/www/html/topmkt/after-search-ui.png`,
            fullPage: true
        });
        
        // 검색 후 글쓰기 버튼 크기 측정
        const afterSearchWriteBtn = await page.$eval('a[href="/community/write"], a[href*="login"]', btn => {
            const rect = btn.getBoundingClientRect();
            const styles = window.getComputedStyle(btn);
            return {
                width: Math.round(rect.width),
                height: Math.round(rect.height),
                text: btn.textContent.trim(),
                classes: btn.className,
                fontSize: styles.fontSize,
                padding: styles.padding
            };
        });
        
        console.log(`   검색 후 글쓰기 버튼: "${afterSearchWriteBtn.text}" (${afterSearchWriteBtn.width}×${afterSearchWriteBtn.height}px)`);
        console.log(`   클래스: ${afterSearchWriteBtn.classes}`);
        console.log(`   폰트: ${afterSearchWriteBtn.fontSize}, 패딩: ${afterSearchWriteBtn.padding}`);
        
        // 검색 후 돋보기 버튼 상태
        const searchBtnAfter = await page.$eval('.search-btn', btn => {
            const rect = btn.getBoundingClientRect();
            const styles = window.getComputedStyle(btn);
            return {
                width: Math.round(rect.width),
                height: Math.round(rect.height),
                visible: btn.offsetWidth > 0 && btn.offsetHeight > 0,
                display: styles.display,
                opacity: styles.opacity,
                backgroundColor: styles.backgroundColor,
                transform: styles.transform
            };
        });
        
        console.log(`   검색 후 돋보기 버튼: ${searchBtnAfter.width}×${searchBtnAfter.height}px, 표시: ${searchBtnAfter.visible}`);
        console.log(`   스타일: display=${searchBtnAfter.display}, opacity=${searchBtnAfter.opacity}`);
        console.log(`   변형: ${searchBtnAfter.transform}`);
        
        // 검색 입력 필드 상태 확인
        const searchInputState = await page.$eval('#searchInput', input => {
            const styles = window.getComputedStyle(input);
            return {
                value: input.value,
                borderColor: styles.borderColor,
                backgroundColor: styles.backgroundColor,
                focused: document.activeElement === input
            };
        });
        
        console.log(`   검색 입력: "${searchInputState.value}"`);
        console.log(`   테두리: ${searchInputState.borderColor}, 배경: ${searchInputState.backgroundColor}`);
        
        // 4단계: 문제 분석
        console.log('\\n🔍 4단계: 문제 분석...');
        
        // 글쓰기 버튼 크기 변화 확인
        const widthDiff = afterSearchWriteBtn.width - beforeSearchWriteBtn.width;
        const heightDiff = afterSearchWriteBtn.height - beforeSearchWriteBtn.height;
        
        const hasButtonSizeIssue = Math.abs(widthDiff) > 10 || Math.abs(heightDiff) > 5;
        
        console.log(`   글쓰기 버튼 크기 변화: ${widthDiff > 0 ? '+' : ''}${widthDiff}px (너비), ${heightDiff > 0 ? '+' : ''}${heightDiff}px (높이)`);
        console.log(`   버튼 크기 문제: ${hasButtonSizeIssue ? '❌ 있음' : '✅ 없음'}`);
        
        // 돋보기 상태 문제 확인
        const hasSearchButtonIssue = searchInputState.borderColor !== 'rgb(226, 232, 240)' || 
                                    searchInputState.backgroundColor !== 'rgb(255, 255, 255)';
        
        console.log(`   돋보기/검색 상태 문제: ${hasSearchButtonIssue ? '❌ 있음' : '✅ 없음'}`);
        
        // 5단계: 검색 해제 버튼 테스트
        console.log('\\n✖️ 5단계: 검색 해제 기능 테스트...');
        
        const clearButton = await page.$('a:has-text("검색 해제")');
        if (clearButton) {
            console.log('   검색 해제 버튼 발견 - 클릭 테스트');
            await clearButton.click();
            await page.waitForTimeout(2000);
            
            const finalUrl = page.url();
            console.log(`   검색 해제 후 URL: ${finalUrl}`);
            
            // 검색 해제 후 상태 확인
            const resetSearchInput = await page.$eval('#searchInput', input => ({
                value: input.value,
                borderColor: window.getComputedStyle(input).borderColor,
                backgroundColor: window.getComputedStyle(input).backgroundColor
            }));
            
            console.log(`   초기화된 검색 입력: "${resetSearchInput.value}"`);
            console.log(`   초기화 후 스타일: ${resetSearchInput.borderColor}, ${resetSearchInput.backgroundColor}`);
            
            const isProperlyReset = resetSearchInput.value === '' && 
                                   resetSearchInput.borderColor === 'rgb(226, 232, 240)';
            console.log(`   검색 상태 초기화: ${isProperlyReset ? '✅ 정상' : '❌ 문제'}`);
        } else {
            console.log('   검색 해제 버튼 없음 (검색어가 없을 때 정상)');
        }
        
        return {
            success: !hasButtonSizeIssue && !hasSearchButtonIssue,
            issues: {
                buttonSizeIssue: hasButtonSizeIssue,
                searchStateIssue: hasSearchButtonIssue
            },
            beforeSearch: {
                writeButton: beforeSearchWriteBtn,
                searchButton: searchBtnBefore
            },
            afterSearch: {
                writeButton: afterSearchWriteBtn,
                searchButton: searchBtnAfter,
                inputState: searchInputState
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
    testSearchUIIssues().then(result => {
        console.log('\\n🎉 최종 테스트 결과:');
        console.log(`   전체 상태: ${result.success ? '✅ 문제 없음' : '❌ 문제 발견'}`);
        
        if (result.issues) {
            console.log(`   글쓰기 버튼 크기 문제: ${result.issues.buttonSizeIssue ? '❌' : '✅'}`);
            console.log(`   검색 상태 관리 문제: ${result.issues.searchStateIssue ? '❌' : '✅'}`);
        }
        
        console.log('\\n📷 생성된 스크린샷:');
        console.log('   - before-search-ui.png (검색 전)');
        console.log('   - after-search-ui.png (검색 후)');
        
        process.exit(result.success ? 0 : 1);
    });
}