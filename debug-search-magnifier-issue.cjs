/**
 * 돋보기 아이콘이 사라지지 않는 문제 정확한 재현
 * 사용자가 지적한 실제 문제 파악
 */

const { chromium } = require('playwright');

async function debugSearchMagnifierIssue() {
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
        
        // 검색 전 상태 확인
        console.log('\\n🔍 1단계: 검색 전 상태 확인');
        const beforeSearch = await page.evaluate(() => {
            const searchBtn = document.querySelector('.search-btn');
            const searchInput = document.querySelector('#searchInput');
            
            if (!searchBtn || !searchInput) return null;
            
            const btnRect = searchBtn.getBoundingClientRect();
            const inputRect = searchInput.getBoundingClientRect();
            const btnStyles = window.getComputedStyle(searchBtn);
            const inputStyles = window.getComputedStyle(searchInput);
            
            return {
                searchBtn: {
                    visible: searchBtn.offsetWidth > 0 && searchBtn.offsetHeight > 0,
                    position: {
                        right: Math.round(btnRect.right),
                        top: Math.round(btnRect.top),
                        width: Math.round(btnRect.width),
                        height: Math.round(btnRect.height)
                    },
                    styles: {
                        position: btnStyles.position,
                        right: btnStyles.right,
                        top: btnStyles.top,
                        transform: btnStyles.transform,
                        zIndex: btnStyles.zIndex
                    }
                },
                searchInput: {
                    position: {
                        right: Math.round(inputRect.right),
                        top: Math.round(inputRect.top),
                        width: Math.round(inputRect.width),
                        height: Math.round(inputRect.height)
                    },
                    styles: {
                        paddingRight: inputStyles.paddingRight
                    }
                }
            };
        });
        
        if (beforeSearch) {
            console.log(`   돋보기 버튼 위치: right=${beforeSearch.searchBtn.position.right}px, top=${beforeSearch.searchBtn.position.top}px`);
            console.log(`   돋보기 버튼 크기: ${beforeSearch.searchBtn.position.width}×${beforeSearch.searchBtn.position.height}px`);
            console.log(`   CSS position: ${beforeSearch.searchBtn.styles.position}, right: ${beforeSearch.searchBtn.styles.right}`);
            console.log(`   입력 필드 위치: right=${beforeSearch.searchInput.position.right}px`);
            console.log(`   입력 필드 패딩: ${beforeSearch.searchInput.styles.paddingRight}`);
        }
        
        // 문제 URL로 직접 이동
        console.log('\\n🎯 2단계: 문제 URL로 직접 이동');
        await page.goto('https://www.topmktx.com/community?filter=all&search=%E3%85%81%E3%84%B4%E3%85%87%E3%85%81%E3%85%87%E3%84%B4');
        await page.waitForTimeout(3000);
        
        // 검색 후 상태 확인
        const afterSearch = await page.evaluate(() => {
            const searchBtn = document.querySelector('.search-btn');
            const searchInput = document.querySelector('#searchInput');
            
            if (!searchBtn || !searchInput) return null;
            
            const btnRect = searchBtn.getBoundingClientRect();
            const inputRect = searchInput.getBoundingClientRect();
            const btnStyles = window.getComputedStyle(searchBtn);
            
            return {
                searchBtn: {
                    visible: searchBtn.offsetWidth > 0 && searchBtn.offsetHeight > 0,
                    position: {
                        right: Math.round(btnRect.right),
                        top: Math.round(btnRect.top),
                        width: Math.round(btnRect.width),
                        height: Math.round(btnRect.height)
                    },
                    styles: {
                        position: btnStyles.position,
                        right: btnStyles.right,
                        top: btnStyles.top,
                        transform: btnStyles.transform,
                        zIndex: btnStyles.zIndex,
                        pointerEvents: btnStyles.pointerEvents
                    }
                },
                searchInput: {
                    value: searchInput.value,
                    focused: document.activeElement === searchInput,
                    position: {
                        right: Math.round(inputRect.right),
                        top: Math.round(inputRect.top)
                    }
                }
            };
        });
        
        console.log(`   검색어: "${afterSearch.searchInput.value}"`);
        console.log(`   돋보기 버튼 표시: ${afterSearch.searchBtn.visible ? '✅ 보임' : '❌ 안보임'}`);
        console.log(`   돋보기 버튼 위치: right=${afterSearch.searchBtn.position.right}px, top=${afterSearch.searchBtn.position.top}px`);
        console.log(`   변형(transform): ${afterSearch.searchBtn.styles.transform}`);
        console.log(`   포인터 이벤트: ${afterSearch.searchBtn.styles.pointerEvents}`);
        
        // 3단계: 실제 클릭 시도
        console.log('\\n🖱️ 3단계: 돋보기 버튼 클릭 시도');
        
        try {
            // 입력 필드 클리어
            await page.fill('#searchInput', '');
            await page.waitForTimeout(500);
            
            console.log('   입력 필드 클리어 완료');
            
            // 새로운 검색어 입력
            await page.fill('#searchInput', '새로운검색');
            await page.waitForTimeout(500);
            
            console.log('   새 검색어 입력: "새로운검색"');
            
            // 돋보기 버튼 클릭
            await page.click('.search-btn');
            await page.waitForTimeout(2000);
            
            const newUrl = page.url();
            console.log(`   클릭 후 URL: ${newUrl}`);
            
            const clickWorked = newUrl.includes('새로운검색') || newUrl.includes('%EC%83%88%EB%A1%9C%EC%9A%B4%EA%B2%80%EC%83%89');
            console.log(`   돋보기 클릭 동작: ${clickWorked ? '✅ 정상' : '❌ 문제'}`);
            
            if (!clickWorked) {
                // 돋보기 버튼의 클릭 가능성 분석
                const clickability = await page.evaluate(() => {
                    const btn = document.querySelector('.search-btn');
                    if (!btn) return { exists: false };
                    
                    const rect = btn.getBoundingClientRect();
                    const styles = window.getComputedStyle(btn);
                    
                    // 버튼 위에 다른 요소가 있는지 확인
                    const elementAtCenter = document.elementFromPoint(
                        rect.left + rect.width / 2,
                        rect.top + rect.height / 2
                    );
                    
                    return {
                        exists: true,
                        rect: {
                            left: rect.left,
                            top: rect.top,
                            right: rect.right,
                            bottom: rect.bottom,
                            width: rect.width,
                            height: rect.height
                        },
                        styles: {
                            pointerEvents: styles.pointerEvents,
                            zIndex: styles.zIndex,
                            visibility: styles.visibility,
                            display: styles.display
                        },
                        elementAtCenter: elementAtCenter ? elementAtCenter.tagName + (elementAtCenter.className ? '.' + elementAtCenter.className : '') : null,
                        isClickable: elementAtCenter === btn
                    };
                });
                
                console.log('   🔍 클릭 불가 원인 분석:');
                console.log(`   - 버튼 존재: ${clickability.exists ? '✅' : '❌'}`);
                if (clickability.exists) {
                    console.log(`   - 버튼 크기: ${clickability.rect.width}×${clickability.rect.height}px`);
                    console.log(`   - 포인터 이벤트: ${clickability.styles.pointerEvents}`);
                    console.log(`   - Z-Index: ${clickability.styles.zIndex}`);
                    console.log(`   - 중앙점의 요소: ${clickability.elementAtCenter}`);
                    console.log(`   - 실제 클릭 가능: ${clickability.isClickable ? '✅' : '❌'}`);
                }
            }
            
        } catch (clickError) {
            console.log(`   ❌ 클릭 오류: ${clickError.message}`);
        }
        
        // 스크린샷 촬영
        await page.screenshot({ 
            path: `/var/www/html/topmkt/magnifier-issue-debug.png`,
            fullPage: true
        });
        
        console.log('\\n📷 스크린샷 저장: magnifier-issue-debug.png');
        
        return {
            beforeSearch,
            afterSearch,
            issueIdentified: true
        };
        
    } catch (error) {
        console.error('❌ 디버그 실행 중 오류:', error);
        return { success: false, error: error.message };
    } finally {
        await browser.close();
    }
}

// 실행
if (require.main === module) {
    debugSearchMagnifierIssue().then(result => {
        console.log('\\n🎯 돋보기 문제 디버그 완료!');
    });
}