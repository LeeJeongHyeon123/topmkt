const { chromium } = require('playwright');

async function testProfileMenuFunction() {
  console.log('🔍 프로필 메뉴 기능 테스트...');
  
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
    
    console.log('1. 초기 헤더 상태 스크린샷...');
    await page.screenshot({ 
      path: 'header_before_click.png',
      clip: { x: 0, y: 0, width: 390, height: 80 }
    });
    
    // 프로필 이미지 클릭
    console.log('2. 프로필 이미지 클릭...');
    await page.locator('[src*="profile"], [alt*="프로필"]').first().click();
    await page.waitForTimeout(1000);
    
    console.log('3. 프로필 메뉴 열린 후 스크린샷...');
    await page.screenshot({ 
      path: 'profile_menu_opened.png',
      fullPage: true
    });
    
    // 메뉴 항목들 확인
    const menuItems = await page.locator('.user-menu a, .menu-section a').allTextContents();
    console.log('📋 메뉴 항목들:');
    menuItems.forEach((item, index) => {
      if (item.trim()) {
        console.log(`   ${index + 1}. ${item.trim()}`);
      }
    });
    
    // 메뉴 외부 클릭으로 닫기
    console.log('4. 메뉴 외부 클릭으로 닫기...');
    await page.click('body', { position: { x: 50, y: 50 } });
    await page.waitForTimeout(1000);
    
    console.log('5. 메뉴 닫힌 후 헤더 상태...');
    await page.screenshot({ 
      path: 'header_after_close.png',
      clip: { x: 0, y: 0, width: 390, height: 80 }
    });
    
    console.log('\n✅ 프로필 메뉴 기능 테스트 완료!');
    
  } catch (error) {
    console.error('❌ 오류:', error);
  } finally {
    await browser.close();
  }
}

testProfileMenuFunction();