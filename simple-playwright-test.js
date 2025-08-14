#!/usr/bin/env node

/**
 * 간단한 Playwright 테스트
 */

import { chromium } from 'playwright';

async function testPlaywright() {
  console.log('🎭 Playwright 기본 테스트 시작...');
  
  try {
    // 헤드리스 브라우저 실행
    const browser = await chromium.launch({ 
      headless: true,
      args: ['--no-sandbox', '--disable-dev-shm-usage']
    });
    
    console.log('✅ 브라우저 실행 성공');
    
    // 새 페이지 생성
    const context = await browser.newContext({
      viewport: { width: 1920, height: 1080 }
    });
    const page = await context.newPage();
    
    console.log('✅ 새 페이지 생성 성공');
    
    // 테스트 페이지 이동
    await page.goto('https://www.topmktx.com');
    console.log('✅ 페이지 이동 성공');
    
    // 페이지 제목 확인
    const title = await page.title();
    console.log('📄 페이지 제목:', title);
    
    // 스크린샷 촬영
    await page.screenshot({ 
      path: '/var/www/html/topmkt/playwright-test.png',
      fullPage: false
    });
    console.log('📸 스크린샷 저장 성공: playwright-test.png');
    
    // 브라우저 종료
    await browser.close();
    console.log('✅ 브라우저 종료 완료');
    
    console.log('🎉 모든 테스트 통과!');
    
  } catch (error) {
    console.error('❌ 테스트 실패:', error.message);
    process.exit(1);
  }
}

testPlaywright();