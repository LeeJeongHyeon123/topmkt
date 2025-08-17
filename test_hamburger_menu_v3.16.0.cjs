/**
 * 탑마케팅 햄버거 메뉴 시스템 테스트 (v3.16.0)
 * 목표: 새로 구현한 햄버거 메뉴 시스템 종합 검증
 */

const { chromium } = require('playwright');

async function testHamburgerMenuSystem() {
    console.log('🚀 탑마케팅 햄버거 메뉴 시스템 테스트 시작 (v3.16.0)');
    console.log('📱 Ultra Think 7단계 체계적 테스트 방법 적용');
    
    const browser = await chromium.launch({ 
        headless: true,
        slowMo: 500  // 0.5초씩 진행
    });
    
    const context = await browser.newContext({
        viewport: { width: 390, height: 844 }, // iPhone 12 모바일 크기
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1'
    });
    
    const page = await context.newPage();
    
    try {
        // ===============================
        // 1단계: DevLogin Helper로 로그인
        // ===============================
        console.log('\n📋 1단계: DevLogin Helper로 사용자 ID 4 로그인');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForTimeout(2000);
        
        // 로그인 성공 확인
        const loginSuccess = await page.textContent('body');
        if (loginSuccess.includes('로그인 성공') || loginSuccess.includes('success')) {
            console.log('✅ 로그인 성공');
        } else {
            console.log('⚠️ 로그인 상태 확인 필요');
        }
        
        // ===============================
        // 2단계: 메인 페이지로 이동
        // ===============================
        console.log('\n📋 2단계: 메인 페이지로 이동 및 모바일 뷰 확인');
        await page.goto('https://www.topmktx.com/');
        await page.waitForSelector('header', { timeout: 10000 });
        await page.waitForTimeout(3000);
        
        // 모바일 뷰 스크린샷
        await page.screenshot({ 
            path: 'hamburger_menu_test_01_main_mobile.png',
            fullPage: false
        });
        console.log('📸 메인 페이지 모바일 뷰 스크린샷 저장');
        
        // ===============================
        // 3단계: 헤더 UI 요소 확인
        // ===============================
        console.log('\n📋 3단계: 새로운 헤더 UI 요소 검증');
        
        // 햄버거 메뉴 버튼 확인
        const hamburgerButton = await page.$('.hamburger-menu, .mobile-menu-toggle, [aria-label*="menu"], .menu-toggle');
        if (hamburgerButton) {
            console.log('✅ 햄버거 메뉴 버튼 발견');
            const isVisible = await hamburgerButton.isVisible();
            console.log(`   - 가시성: ${isVisible ? '표시됨' : '숨겨짐'}`);
            
            // 버튼 텍스트/아이콘 확인
            const buttonText = await hamburgerButton.textContent();
            console.log(`   - 버튼 내용: "${buttonText}"`);
        } else {
            console.log('❌ 햄버거 메뉴 버튼을 찾을 수 없음');
        }
        
        // 프로필 이미지 숨김 확인
        const profileElements = await page.$$('.profile-image, .user-profile, .profile-avatar');
        console.log(`🔍 프로필 관련 요소 ${profileElements.length}개 발견`);
        
        for (let i = 0; i < profileElements.length; i++) {
            const isVisible = await profileElements[i].isVisible();
            console.log(`   - 프로필 요소 ${i+1}: ${isVisible ? '표시됨' : '숨겨짐'}`);
        }
        
        // ===============================
        // 4단계: 햄버거 메뉴 클릭 테스트
        // ===============================
        console.log('\n📋 4단계: 햄버거 메뉴 클릭 및 모달 검증');
        
        // 다양한 선택자로 햄버거 버튼 찾기
        const possibleSelectors = [
            '.hamburger-menu',
            '.mobile-menu-toggle', 
            '.menu-toggle',
            '[aria-label*="menu"]',
            '.mobile-menu-btn',
            '.navbar-toggler',
            '#mobile-menu-button',
            'button[onclick*="menu"]',
            '.mobile-nav-toggle'
        ];
        
        let menuButton = null;
        for (const selector of possibleSelectors) {
            try {
                menuButton = await page.$(selector);
                if (menuButton && await menuButton.isVisible()) {
                    console.log(`✅ 햄버거 버튼 발견: ${selector}`);
                    break;
                }
            } catch (e) {
                // 선택자 없음, 계속 진행
            }
        }
        
        if (menuButton) {
            // 클릭 전 상태 스크린샷
            await page.screenshot({ 
                path: 'hamburger_menu_test_02_before_click.png',
                fullPage: false
            });
            
            console.log('🖱️ 햄버거 메뉴 버튼 클릭');
            await menuButton.click();
            await page.waitForTimeout(1500);
            
            // 클릭 후 상태 스크린샷
            await page.screenshot({ 
                path: 'hamburger_menu_test_03_after_click.png',
                fullPage: true
            });
            console.log('📸 햄버거 메뉴 클릭 후 스크린샷 저장');
            
        } else {
            console.log('❌ 햄버거 메뉴 버튼을 찾을 수 없어 수동으로 클릭 가능한 요소 검색');
            
            // 클릭 가능한 모든 요소 찾기
            const clickableElements = await page.$$('button, a, [onclick], .btn, [role="button"]');
            console.log(`🔍 클릭 가능한 요소 ${clickableElements.length}개 발견`);
            
            for (let i = 0; i < Math.min(5, clickableElements.length); i++) {
                const element = clickableElements[i];
                const text = await element.textContent();
                const tagName = await element.evaluate(el => el.tagName);
                console.log(`   - ${tagName}: "${text?.trim() || '(텍스트 없음)'}"`);
            }
        }
        
        // ===============================
        // 5단계: 모달 내용 검증
        // ===============================
        console.log('\n📋 5단계: 모달 내용 및 구조 검증');
        
        // 모달 요소 확인
        const modalSelectors = [
            '.modal',
            '.mobile-menu',
            '.menu-modal',
            '.overlay',
            '.mobile-nav',
            '.sidebar',
            '[role="dialog"]'
        ];
        
        let modalFound = false;
        for (const selector of modalSelectors) {
            const modal = await page.$(selector);
            if (modal && await modal.isVisible()) {
                console.log(`✅ 모달 발견: ${selector}`);
                modalFound = true;
                
                // 모달 내 프로필 헤더 확인
                const profileHeader = await modal.$('.profile-header, .user-info, .profile-section');
                if (profileHeader) {
                    console.log('✅ 프로필 헤더 섹션 발견');
                    const headerText = await profileHeader.textContent();
                    console.log(`   - 헤더 내용: "${headerText?.trim()}"`);
                } else {
                    console.log('⚠️ 프로필 헤더 섹션을 찾을 수 없음');
                }
                
                // 메뉴 항목들 확인
                const menuItems = await modal.$$('a, button, .menu-item');
                console.log(`🔗 모달 내 메뉴 항목 ${menuItems.length}개 발견`);
                
                for (let i = 0; i < Math.min(10, menuItems.length); i++) {
                    const item = menuItems[i];
                    const text = await item.textContent();
                    const href = await item.getAttribute('href');
                    console.log(`   - 메뉴 ${i+1}: "${text?.trim()}" ${href ? `(${href})` : ''}`);
                }
                
                break;
            }
        }
        
        if (!modalFound) {
            console.log('❌ 모달을 찾을 수 없음 - 전체 페이지 검색');
            
            // 페이지 전체에서 새로 나타난 요소 확인
            const allElements = await page.$$('*');
            console.log(`🔍 전체 DOM 요소 ${allElements.length}개 스캔 중...`);
        }
        
        // ===============================
        // 6단계: 메뉴 항목 작동 테스트
        // ===============================
        console.log('\n📋 6단계: 메뉴 항목 작동 테스트 (샘플링)');
        
        // 안전한 메뉴 항목들만 테스트 (페이지를 벗어나지 않는 것들)
        const safeMenuItems = await page.$$('a[href*="#"], button[onclick*="modal"], .menu-item[data-action]');
        
        if (safeMenuItems.length > 0) {
            console.log(`🔗 안전한 메뉴 항목 ${safeMenuItems.length}개 발견`);
            
            // 첫 번째 안전한 항목 테스트
            try {
                const firstItem = safeMenuItems[0];
                const text = await firstItem.textContent();
                console.log(`🖱️ 테스트 클릭: "${text?.trim()}"`);
                
                await firstItem.click();
                await page.waitForTimeout(1000);
                
                console.log('✅ 메뉴 항목 클릭 성공');
            } catch (e) {
                console.log(`⚠️ 메뉴 항목 클릭 중 오류: ${e.message}`);
            }
        } else {
            console.log('⚠️ 안전한 메뉴 항목을 찾을 수 없음');
        }
        
        // ===============================
        // 7단계: 최종 상태 스크린샷
        // ===============================
        console.log('\n📋 7단계: 최종 상태 스크린샷 및 종합 평가');
        
        await page.screenshot({ 
            path: 'hamburger_menu_test_04_final_state.png',
            fullPage: true
        });
        console.log('📸 최종 상태 풀페이지 스크린샷 저장');
        
        // 페이지 정보 수집
        const pageTitle = await page.title();
        const currentUrl = page.url();
        
        console.log('\n📊 테스트 완료 요약:');
        console.log(`   - 현재 페이지: ${pageTitle}`);
        console.log(`   - URL: ${currentUrl}`);
        console.log(`   - 뷰포트: 390x844 (모바일)`);
        console.log(`   - 스크린샷: 4개 저장됨`);
        
    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error.message);
        
        // 오류 상태 스크린샷
        await page.screenshot({ 
            path: 'hamburger_menu_test_error.png',
            fullPage: true
        });
        console.log('📸 오류 상태 스크린샷 저장');
    }
    
    console.log('\n⏰ 5초 후 브라우저 종료...');
    await page.waitForTimeout(5000);
    
    await browser.close();
    console.log('✅ 햄버거 메뉴 시스템 테스트 완료');
}

// 테스트 실행
testHamburgerMenuSystem().catch(console.error);