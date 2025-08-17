const { chromium } = require('playwright');

async function testHamburgerMenuRemoval() {
  console.log('🔍 햄버거 메뉴 제거 확인 테스트 시작...');
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  try {
    const context = await browser.newContext({
      viewport: { width: 390, height: 844 }, // 모바일 뷰포트
      userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1'
    });
    
    const page = await context.newPage();
    
    // 1. DevLogin Helper로 사용자 ID 4 로그인
    console.log('1. DevLogin Helper로 사용자 ID 4 로그인...');
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.waitForTimeout(2000);
    
    // 로그인 완료 확인
    const loginSuccess = await page.locator('body').textContent();
    if (loginSuccess.includes('로그인 성공') || loginSuccess.includes('이미 로그인')) {
      console.log('✅ 로그인 성공');
    }
    
    // 2. 메인 페이지로 이동
    console.log('2. 메인 페이지로 이동...');
    await page.goto('https://www.topmktx.com/');
    await page.waitForTimeout(3000);
    
    // 3. 헤더 영역 분석
    console.log('3. 헤더 영역 분석 중...');
    
    // 헤더 요소들 찾기
    const header = page.locator('header, .header, nav, .navbar');
    const headerExists = await header.count() > 0;
    console.log(`헤더 요소 존재: ${headerExists}`);
    
    // 햄버거 메뉴 버튼 확인 (다양한 선택자로)
    const hamburgerSelectors = [
      '.hamburger',
      '.menu-toggle', 
      '.burger-menu',
      '.navbar-toggle',
      '.mobile-menu-toggle',
      '[class*="hamburger"]',
      '[class*="burger"]',
      '[class*="menu-toggle"]',
      'button[aria-label*="menu"]',
      '.fa-bars',
      '.menu-icon'
    ];
    
    let hamburgerFound = false;
    let hamburgerInfo = [];
    
    for (const selector of hamburgerSelectors) {
      const elements = await page.locator(selector).count();
      if (elements > 0) {
        hamburgerFound = true;
        const isVisible = await page.locator(selector).first().isVisible();
        hamburgerInfo.push(`${selector}: ${elements}개 (보이는지: ${isVisible})`);
      }
    }
    
    console.log(`🍔 햄버거 메뉴 버튼 발견: ${hamburgerFound}`);
    if (hamburgerInfo.length > 0) {
      hamburgerInfo.forEach(info => console.log(`   - ${info}`));
    }
    
    // 프로필 이미지 확인
    const profileSelectors = [
      '.profile-image',
      '.user-profile',
      '.profile-pic',
      '[src*="profile"]',
      '[alt*="프로필"]',
      '.avatar'
    ];
    
    let profileFound = false;
    let profileInfo = [];
    
    for (const selector of profileSelectors) {
      const elements = await page.locator(selector).count();
      if (elements > 0) {
        profileFound = true;
        const isVisible = await page.locator(selector).first().isVisible();
        profileInfo.push(`${selector}: ${elements}개 (보이는지: ${isVisible})`);
      }
    }
    
    console.log(`👤 프로필 이미지 발견: ${profileFound}`);
    if (profileInfo.length > 0) {
      profileInfo.forEach(info => console.log(`   - ${info}`));
    }
    
    // 4. 프로필 이미지 클릭 테스트 (있다면)
    if (profileFound) {
      console.log('4. 프로필 이미지 클릭 테스트...');
      try {
        // 첫 번째 프로필 이미지 클릭
        await page.locator('.profile-image, .user-profile, .profile-pic, [src*="profile"], [alt*="프로필"], .avatar').first().click();
        await page.waitForTimeout(1000);
        
        // 메뉴가 나타났는지 확인
        const menuVisible = await page.locator('.dropdown-menu, .profile-menu, .user-menu, [class*="menu"]').isVisible();
        console.log(`   프로필 메뉴 표시: ${menuVisible}`);
      } catch (error) {
        console.log(`   프로필 클릭 실패: ${error.message}`);
      }
    }
    
    // 5. 헤더 스크린샷 촬영
    console.log('5. 헤더 스크린샷 촬영...');
    await page.screenshot({ 
      path: 'hamburger_removal_test_mobile.png',
      fullPage: false,
      clip: { x: 0, y: 0, width: 390, height: 200 } // 헤더 영역만
    });
    
    // 전체 페이지 스크린샷도 촬영
    await page.screenshot({ 
      path: 'hamburger_removal_test_full.png',
      fullPage: true
    });
    
    console.log('📸 스크린샷 저장 완료');
    
    // 6. 결과 요약
    console.log('\n📊 테스트 결과 요약:');
    console.log(`🍔 햄버거 메뉴 버튼: ${hamburgerFound ? '발견됨 ❌' : '제거됨 ✅'}`);
    console.log(`👤 프로필 이미지: ${profileFound ? '존재함 ✅' : '없음 ❌'}`);
    
    if (!hamburgerFound && profileFound) {
      console.log('🎉 SUCCESS: 햄버거 메뉴가 제거되고 프로필 이미지만 남아있음!');
      console.log('🎨 UI가 더 깔끔해졌습니다.');
    } else if (hamburgerFound) {
      console.log('⚠️  WARNING: 햄버거 메뉴 버튼이 여전히 존재합니다.');
    } else {
      console.log('🤔 INFO: 헤더 구조를 다시 확인이 필요합니다.');
    }
    
  } catch (error) {
    console.error('❌ 테스트 실행 오류:', error);
  } finally {
    await browser.close();
  }
}

// 테스트 실행
testHamburgerMenuRemoval();