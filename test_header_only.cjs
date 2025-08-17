const { chromium } = require('playwright');

async function testHeaderOnly() {
  console.log('🔍 헤더 영역만 집중 분석...');
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  try {
    const context = await browser.newContext({
      viewport: { width: 390, height: 844 },
      userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1'
    });
    
    const page = await context.newPage();
    
    // DevLogin 후 메인 페이지
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.waitForTimeout(2000);
    await page.goto('https://www.topmktx.com/');
    await page.waitForTimeout(3000);
    
    // 헤더 영역 분석
    console.log('📋 헤더 구조 분석:');
    
    // 로고 확인
    const logo = await page.locator('.logo, [alt*="로고"], h1, .brand').count();
    console.log(`🏷️  로고/브랜드: ${logo}개`);
    
    // 우측 상단 요소들 확인
    const topRight = await page.locator('.header-right, .navbar-right, .top-right').count();
    console.log(`📍 우측 상단 컨테이너: ${topRight}개`);
    
    // 프로필 이미지 정확한 위치
    const profileImg = page.locator('[src*="profile"], [alt*="프로필"]');
    const profileCount = await profileImg.count();
    console.log(`👤 프로필 이미지: ${profileCount}개`);
    
    if (profileCount > 0) {
      const boundingBox = await profileImg.first().boundingBox();
      console.log(`   위치: x=${boundingBox?.x}, y=${boundingBox?.y}`);
      console.log(`   크기: ${boundingBox?.width}x${boundingBox?.height}`);
    }
    
    // 햄버거 관련 요소 세밀 검사
    const hamElements = [
      { name: '3줄 아이콘', selector: '.fa-bars, .icon-bars, [class*="bars"]' },
      { name: '메뉴 버튼', selector: 'button[class*="menu"], .menu-btn' },
      { name: '토글 버튼', selector: '.toggle, .hamburger' },
      { name: '모바일 메뉴', selector: '.mobile-menu-btn, .mobile-toggle' }
    ];
    
    console.log('\n🔍 햄버거 메뉴 세밀 검사:');
    for (const element of hamElements) {
      const count = await page.locator(element.selector).count();
      if (count > 0) {
        const visible = await page.locator(element.selector).first().isVisible();
        console.log(`   ${element.name}: ${count}개 (보임: ${visible})`);
      } else {
        console.log(`   ${element.name}: 없음 ✅`);
      }
    }
    
    // 헤더만 정확히 촬영 (메뉴 열지 않고)
    await page.screenshot({ 
      path: 'header_clean.png',
      clip: { x: 0, y: 0, width: 390, height: 80 }
    });
    
    console.log('\n✅ 깔끔한 헤더 스크린샷 저장됨!');
    
  } catch (error) {
    console.error('❌ 오류:', error);
  } finally {
    await browser.close();
  }
}

testHeaderOnly();