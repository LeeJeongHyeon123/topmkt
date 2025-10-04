const { chromium } = require('playwright');

(async () => {
  console.log('🎯 사이드바 데이터 디버깅...');

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=calendar', {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    await page.waitForTimeout(2000);

    // 사이드바 요소 및 내용 분석
    const sidebarAnalysis = await page.evaluate(() => {
      const sidebar = document.querySelector('.events-sidebar');
      if (!sidebar) {
        return { error: 'events-sidebar 요소가 없음' };
      }

      const sidebarSections = document.querySelectorAll('.sidebar-section');
      const todaySection = document.querySelector('.sidebar-section:first-child');
      const upcomingSection = document.querySelector('.sidebar-section:last-child');

      const todayEvents = document.querySelectorAll('.today-events .sidebar-event-item');
      const upcomingEvents = document.querySelectorAll('.upcoming-events .sidebar-event-item');
      const emptyMessages = document.querySelectorAll('.empty-sidebar');

      return {
        sidebar: {
          exists: true,
          visible: window.getComputedStyle(sidebar).display !== 'none',
          opacity: window.getComputedStyle(sidebar).opacity,
          visibility: window.getComputedStyle(sidebar).visibility,
          width: window.getComputedStyle(sidebar).width,
          height: window.getComputedStyle(sidebar).height,
          innerHTML_length: sidebar.innerHTML.length,
          innerHTML_preview: sidebar.innerHTML.substring(0, 500) + '...'
        },
        sections: {
          count: sidebarSections.length,
          today_exists: !!todaySection,
          upcoming_exists: !!upcomingSection
        },
        events: {
          today_count: todayEvents.length,
          upcoming_count: upcomingEvents.length,
          empty_messages: Array.from(emptyMessages).map(el => el.textContent.trim())
        },
        content: {
          today_section_html: todaySection ? todaySection.innerHTML.substring(0, 300) + '...' : null,
          upcoming_section_html: upcomingSection ? upcomingSection.innerHTML.substring(0, 300) + '...' : null
        }
      };
    });

    console.log('\n🔍 사이드바 상태 분석:');
    console.log('=========================');

    if (sidebarAnalysis.error) {
      console.log('❌ 오류:', sidebarAnalysis.error);
      return;
    }

    console.log('\n📋 사이드바 기본 정보:');
    console.log(`- 존재: ${sidebarAnalysis.sidebar.exists}`);
    console.log(`- 표시: ${sidebarAnalysis.sidebar.visible}`);
    console.log(`- 투명도: ${sidebarAnalysis.sidebar.opacity}`);
    console.log(`- 가시성: ${sidebarAnalysis.sidebar.visibility}`);
    console.log(`- 너비: ${sidebarAnalysis.sidebar.width}`);
    console.log(`- 높이: ${sidebarAnalysis.sidebar.height}`);
    console.log(`- HTML 길이: ${sidebarAnalysis.sidebar.innerHTML_length}`);

    console.log('\n📊 섹션 정보:');
    console.log(`- 총 섹션 수: ${sidebarAnalysis.sections.count}`);
    console.log(`- 오늘 섹션 존재: ${sidebarAnalysis.sections.today_exists}`);
    console.log(`- 다가오는 섹션 존재: ${sidebarAnalysis.sections.upcoming_exists}`);

    console.log('\n🎯 이벤트 데이터:');
    console.log(`- 오늘의 행사 개수: ${sidebarAnalysis.events.today_count}`);
    console.log(`- 다가오는 행사 개수: ${sidebarAnalysis.events.upcoming_count}`);
    console.log(`- 빈 메시지들: ${JSON.stringify(sidebarAnalysis.events.empty_messages)}`);

    if (sidebarAnalysis.content.today_section_html) {
      console.log('\n📝 오늘 섹션 HTML:');
      console.log(sidebarAnalysis.content.today_section_html);
    }

    if (sidebarAnalysis.content.upcoming_section_html) {
      console.log('\n📝 다가오는 섹션 HTML:');
      console.log(sidebarAnalysis.content.upcoming_section_html);
    }

    console.log('\n📄 사이드바 HTML 미리보기:');
    console.log(sidebarAnalysis.sidebar.innerHTML_preview);

    // 스크린샷
    await page.screenshot({
      path: '/var/www/html/topmkt/sidebar-debug.png',
      fullPage: true
    });

    console.log('\n📸 스크린샷 저장: /var/www/html/topmkt/sidebar-debug.png');

  } catch (error) {
    console.error('❌ 디버깅 실패:', error.message);
  } finally {
    await browser.close();
  }
})();