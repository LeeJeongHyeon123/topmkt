import { chromium } from 'playwright';

(async () => {
  console.log('🔍 모바일 햄버거 메뉴 디버깅 시작...');
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  try {
    const context = await browser.newContext({
      viewport: { width: 390, height: 844 },
      userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Mobile/15E148 Safari/604.1'
    });
    
    const page = await context.newPage();
    
    // 1. DevLogin Helper로 사용자 ID 4 로그인
    console.log('📱 1단계: DevLogin Helper로 로그인 중...');
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.waitForTimeout(2000);
    
    // 메인 페이지로 이동
    console.log('🏠 2단계: 메인 페이지로 이동 중...');
    await page.goto('https://www.topmktx.com/');
    await page.waitForTimeout(3000);
    
    // 현재 화면 스크린샷
    console.log('📷 현재 모바일 상태 스크린샷 촬영...');
    await page.screenshot({ 
      path: '/var/www/html/topmkt/mobile_header_current.png',
      fullPage: false
    });
    
    // 3. 헤더 구조 분석
    console.log('🔍 3단계: 헤더 구조 분석 중...');
    
    // 햄버거 메뉴 버튼 존재 확인
    const hamburgerExists = await page.locator('#mobile-menu-toggle').count();
    console.log(`🍔 햄버거 메뉴 버튼 존재: ${hamburgerExists > 0 ? 'YES' : 'NO'}`);
    
    if (hamburgerExists > 0) {
      const isVisible = await page.locator('#mobile-menu-toggle').isVisible();
      console.log(`👁️ 햄버거 메뉴 버튼 가시성: ${isVisible ? 'VISIBLE' : 'HIDDEN'}`);
      
      // CSS 스타일 확인
      const buttonStyles = await page.locator('#mobile-menu-toggle').evaluate(el => {
        const styles = window.getComputedStyle(el);
        return {
          display: styles.display,
          visibility: styles.visibility,
          opacity: styles.opacity,
          width: styles.width,
          height: styles.height
        };
      });
      console.log('🎨 햄버거 버튼 CSS 스타일:', buttonStyles);
    }
    
    // 헤더 내부 요소들 확인
    console.log('📋 4단계: 헤더 내부 요소 분석...');
    const headerHTML = await page.locator('header').innerHTML();
    console.log('📄 헤더 HTML 구조 (일부):');
    console.log(headerHTML.substring(0, 500) + '...');
    
    // 우측 요소들 확인
    const rightElements = await page.locator('header .header-right, header .nav-right').count();
    console.log(`➡️ 헤더 우측 요소들: ${rightElements}개`);
    
    // 프로필 이미지 확인
    const profileImageExists = await page.locator('.profile-image, .user-profile img').count();
    console.log(`👤 프로필 이미지 존재: ${profileImageExists > 0 ? 'YES' : 'NO'}`);
    
    // 5. 반응형 미디어 쿼리 확인
    console.log('📱 5단계: 반응형 CSS 상태 확인...');
    const mediaQueryActive = await page.evaluate(() => {
      const mediaQuery = window.matchMedia('(max-width: 768px)');
      return mediaQuery.matches;
    });
    console.log(`📱 모바일 미디어 쿼리 활성화: ${mediaQueryActive ? 'YES' : 'NO'}`);
    
    // 6. JavaScript 오류 확인
    console.log('⚠️ 6단계: JavaScript 오류 확인...');
    const errors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        errors.push(msg.text());
      }
    });
    
    // 페이지 새로고침하여 오류 캐치
    await page.reload();
    await page.waitForTimeout(2000);
    
    if (errors.length > 0) {
      console.log('❌ JavaScript 오류 발견:');
      errors.forEach(error => console.log(`  - ${error}`));
    } else {
      console.log('✅ JavaScript 오류 없음');
    }
    
    // 7. 네비게이션 메뉴 상태 확인
    console.log('🧭 7단계: 네비게이션 메뉴 상태 확인...');
    const mainNav = await page.locator('.main-nav, .nav-menu').count();
    console.log(`🧭 메인 네비게이션 존재: ${mainNav > 0 ? 'YES' : 'NO'}`);
    
    if (mainNav > 0) {
      const navVisible = await page.locator('.main-nav, .nav-menu').isVisible();
      console.log(`👁️ 메인 네비게이션 가시성: ${navVisible ? 'VISIBLE' : 'HIDDEN'}`);
    }
    
    // 8. 브라우저 개발자 도구에서 요소 검사
    console.log('🔧 8단계: 개발자 도구 시뮬레이션...');
    
    // 모든 버튼 요소 찾기
    const allButtons = await page.locator('button, .btn, [role="button"]').count();
    console.log(`🔘 전체 버튼 요소 개수: ${allButtons}개`);
    
    // 아이콘 요소들 찾기
    const iconElements = await page.locator('i[class*="fa"], .icon, [class*="menu"], [class*="hamburger"]').count();
    console.log(`🎯 아이콘 관련 요소 개수: ${iconElements}개`);
    
    // 최종 상태 스크린샷
    console.log('📷 최종 상태 스크린샷 촬영...');
    await page.screenshot({ 
      path: '/var/www/html/topmkt/mobile_header_analyzed.png',
      fullPage: true
    });
    
    console.log('✅ 모바일 햄버거 메뉴 분석 완료!');
    console.log('📁 생성된 스크린샷:');
    console.log('  - mobile_header_current.png (현재 상태)');
    console.log('  - mobile_header_analyzed.png (분석 후 전체)');
    
  } catch (error) {
    console.error('❌ 오류 발생:', error);
  } finally {
    await browser.close();
  }
})();