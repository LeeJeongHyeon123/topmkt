const { chromium } = require('playwright');

(async () => {
  console.log('🔍 Ultra Think: 관리자 메뉴 실제 구현 상태 테스트');
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  const context = await browser.newContext({
    ignoreHTTPSErrors: true,
    viewport: { width: 1280, height: 800 }
  });
  
  const page = await context.newPage();
  
  try {
    console.log('🔑 관리자 로그인...');
    
    // DevLoginHelper로 관리자 로그인
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', { 
      waitUntil: 'networkidle',
      timeout: 30000
    });
    
    await page.waitForTimeout(3000);
    
    console.log('📊 각 관리자 메뉴 접근 테스트...');
    
    // 테스트할 메뉴들
    const menuTests = [
      // 현재 메뉴에 있는 것들
      { path: '/admin', name: '메인 대시보드', expect: 'exists' },
      { path: '/admin/users', name: '회원 목록', expect: 'exists' },
      { path: '/admin/corporate/pending', name: '기업회원 인증 대기', expect: 'exists' },
      { path: '/admin/corporate/list', name: '기업회원 목록', expect: 'exists' },
      { path: '/admin/posts', name: '게시글 관리', expect: 'missing' },
      { path: '/admin/comments', name: '댓글 관리', expect: 'missing' },
      { path: '/admin/settings', name: '사이트 설정', expect: 'missing' },
      { path: '/admin/logs', name: '시스템 로그', expect: 'missing' },
      { path: '/admin/backup', name: '백업 관리', expect: 'missing' },
      
      // 구현되었지만 메뉴에 없는 것들
      { path: '/admin/notices', name: '공지사항 관리', expect: 'hidden_gem' },
      { path: '/admin/lectures', name: '강의 관리', expect: 'hidden_gem' },
      { path: '/admin/events', name: '이벤트/행사 관리', expect: 'hidden_gem' },
      { path: '/admin/registrations', name: '강의신청 관리', expect: 'hidden_gem' }
    ];
    
    const results = [];
    
    for (const test of menuTests) {
      console.log(`🔗 테스트: ${test.name} (${test.path})`);
      
      try {
        const response = await page.goto(`https://www.topmktx.com${test.path}`, { 
          waitUntil: 'networkidle',
          timeout: 15000
        });
        
        const status = response.status();
        const url = page.url();
        const title = await page.title();
        
        let result = {
          name: test.name,
          path: test.path,
          expected: test.expect,
          status: status,
          final_url: url,
          title: title,
          working: status === 200 && !url.includes('/errors/')
        };
        
        if (result.working) {
          console.log(`   ✅ ${status} - 정상 작동`);
        } else {
          console.log(`   ❌ ${status} - 작동 안함`);
        }
        
        results.push(result);
        
      } catch (error) {
        console.log(`   ⚠️ 에러: ${error.message}`);
        results.push({
          name: test.name,
          path: test.path,
          expected: test.expect,
          status: 'ERROR',
          working: false,
          error: error.message
        });
      }
      
      await page.waitForTimeout(1000);
    }
    
    // 결과 분석
    console.log('\\n📊 테스트 결과 분석:');
    console.log('='.repeat(80));
    
    const working = results.filter(r => r.working);
    const broken = results.filter(r => !r.working);
    const hiddenGems = working.filter(r => r.expected === 'hidden_gem');
    const missingFeatures = broken.filter(r => r.expected === 'missing');
    
    console.log(`\\n✅ 정상 작동하는 메뉴: ${working.length}개`);
    working.forEach(r => {
      console.log(`   - ${r.name}: ${r.path}`);
    });
    
    console.log(`\\n❌ 작동하지 않는 메뉴: ${broken.length}개`);
    broken.forEach(r => {
      console.log(`   - ${r.name}: ${r.path} (${r.status})`);
    });
    
    console.log(`\\n💎 구현되었지만 메뉴에 숨겨진 기능: ${hiddenGems.length}개`);
    hiddenGems.forEach(r => {
      console.log(`   - ${r.name}: ${r.path} ← 메뉴에 추가 권장!`);
    });
    
    console.log(`\\n🚫 메뉴에 있지만 미구현된 기능: ${missingFeatures.length}개`);
    missingFeatures.forEach(r => {
      console.log(`   - ${r.name}: ${r.path} ← 메뉴에서 제거 또는 구현 필요`);
    });
    
    // JSON 결과 파일 저장 (참고용)
    console.log('\\n💾 상세 결과를 파일로 저장합니다...');
    console.log(JSON.stringify(results, null, 2));
    
  } catch (error) {
    console.log(`❌ 테스트 실패: ${error.message}`);
  } finally {
    await browser.close();
    console.log('🔚 테스트 완료');
  }
})();