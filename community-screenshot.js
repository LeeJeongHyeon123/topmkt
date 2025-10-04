import { chromium } from 'playwright';

(async () => {
  console.log('🎭 브라우저 시작 중...');
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  // 뷰포트 크기 설정
  await page.setViewportSize({ width: 1920, height: 1080 });

  console.log('🌐 페이지 이동 중...');
  const url = 'https://www.topmktx.com/community?filter=all&search=ㅁㄴㅇ';
  await page.goto(url, { waitUntil: 'networkidle' });

  // 페이지 로딩 대기
  await page.waitForTimeout(3000);

  console.log('📸 스크린샷 촬영 중...');
  await page.screenshot({
    path: '/var/www/html/topmkt/community-layout-screenshot.png',
    fullPage: true
  });

  console.log('✅ 스크린샷 촬영 완료: community-layout-screenshot.png');

  await browser.close();
})().catch(console.error);