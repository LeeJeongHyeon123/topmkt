const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('🔍 캘린더 뷰에서 사이드바 확인 시작...');

    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');

    // 직접 캘린더 뷰 URL로 이동
    await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=calendar', {
      waitUntil: 'networkidle',
      timeout: 15000
    });

    console.log('✅ 캘린더 뷰 페이지 로딩 완료');

    // 캘린더 뷰 스크린샷
    await page.screenshot({
      path: 'calendar-view-sidebar-test.png',
      fullPage: true
    });

    // 캘린더 뷰에서 사이드바 상태 분석
    const calendarViewStatus = await page.evaluate(() => {
      const sidebar = document.querySelector('.events-sidebar');
      const layout = document.querySelector('.events-layout');
      const calendarView = document.querySelector('.calendar-view');
      const listView = document.querySelector('.list-view');

      const getCurrentView = () => {
        if (calendarView && window.getComputedStyle(calendarView).display !== 'none') {
          return 'calendar';
        } else if (listView && window.getComputedStyle(listView).display !== 'none') {
          return 'list';
        }
        return 'unknown';
      };

      if (!sidebar || !layout) {
        return { error: 'Elements not found' };
      }

      const sidebarRect = sidebar.getBoundingClientRect();
      const layoutRect = layout.getBoundingClientRect();
      const viewportWidth = window.innerWidth;

      // 사이드바 내용 확인
      const sidebarSections = sidebar.querySelectorAll('.sidebar-section');
      const todaySection = sidebar.querySelector('.sidebar-section:first-child');
      const upcomingSection = sidebar.querySelector('.sidebar-section:last-child');

      return {
        timestamp: new Date().toLocaleString('ko-KR'),
        currentURL: window.location.href,
        currentView: getCurrentView(),
        viewport: { width: viewportWidth, height: window.innerHeight },
        sidebar: {
          exists: true,
          visible: sidebarRect.x >= 0 && sidebarRect.x + sidebarRect.width <= viewportWidth,
          position: { x: sidebarRect.x, y: sidebarRect.y },
          size: { width: sidebarRect.width, height: sidebarRect.height },
          computedStyles: {
            width: window.getComputedStyle(sidebar).width,
            display: window.getComputedStyle(sidebar).display,
            visibility: window.getComputedStyle(sidebar).visibility,
            position: window.getComputedStyle(sidebar).position
          }
        },
        layout: {
          position: { x: layoutRect.x, y: layoutRect.y },
          size: { width: layoutRect.width, height: layoutRect.height },
          gridColumns: window.getComputedStyle(layout).gridTemplateColumns,
          display: window.getComputedStyle(layout).display
        },
        sidebarContent: {
          sectionsCount: sidebarSections.length,
          todayEvents: todaySection ? {
            title: todaySection.querySelector('h3')?.textContent || 'no title',
            hasContent: todaySection.querySelector('.sidebar-event-item') ? true : false,
            emptyMessage: todaySection.querySelector('.empty-sidebar')?.textContent || 'no empty message'
          } : null,
          upcomingEvents: upcomingSection ? {
            title: upcomingSection.querySelector('h3')?.textContent || 'no title',
            hasContent: upcomingSection.querySelector('.sidebar-event-item') ? true : false,
            eventCount: upcomingSection.querySelectorAll('.sidebar-event-item').length
          } : null
        },
        userExperience: {
          canSeeSidebar: sidebarRect.x >= 0 && sidebarRect.x + sidebarRect.width <= viewportWidth,
          sidebarRightEdge: sidebarRect.x + sidebarRect.width,
          viewportRightEdge: viewportWidth,
          isCompletelyVisible: sidebarRect.x >= 0 && sidebarRect.x + sidebarRect.width <= viewportWidth,
          percentageVisible: Math.max(0, Math.min(100,
            ((Math.min(sidebarRect.x + sidebarRect.width, viewportWidth) - Math.max(sidebarRect.x, 0)) / sidebarRect.width) * 100
          ))
        }
      };
    });

    console.log('📊 캘린더 뷰 사이드바 상태:');
    console.log(JSON.stringify(calendarViewStatus, null, 2));

    console.log('\n🎯 캘린더 뷰에서 사용자가 보는 것:');
    if (calendarViewStatus.userExperience) {
      console.log(`현재 뷰: ${calendarViewStatus.currentView}`);
      console.log(`사이드바 보임: ${calendarViewStatus.userExperience.canSeeSidebar ? 'YES' : 'NO'}`);
      console.log(`가시성 비율: ${calendarViewStatus.userExperience.percentageVisible.toFixed(1)}%`);
      console.log(`사이드바 위치: x=${calendarViewStatus.sidebar.position.x}`);
      console.log(`완전히 보임: ${calendarViewStatus.userExperience.isCompletelyVisible ? 'YES' : 'NO'}`);
    }

    console.log('\n📝 사이드바 내용 (캘린더 뷰):');
    if (calendarViewStatus.sidebarContent) {
      console.log(`섹션 수: ${calendarViewStatus.sidebarContent.sectionsCount}`);
      if (calendarViewStatus.sidebarContent.todayEvents) {
        console.log(`오늘의 행사: "${calendarViewStatus.sidebarContent.todayEvents.title}"`);
        console.log(`오늘 행사 있음: ${calendarViewStatus.sidebarContent.todayEvents.hasContent}`);
      }
      if (calendarViewStatus.sidebarContent.upcomingEvents) {
        console.log(`다가오는 행사: "${calendarViewStatus.sidebarContent.upcomingEvents.title}"`);
        console.log(`다가오는 행사 개수: ${calendarViewStatus.sidebarContent.upcomingEvents.eventCount}개`);
      }
    }

  } catch (error) {
    console.error('❌ 오류 발생:', error.message);
  } finally {
    await browser.close();
    console.log('\n✅ 캘린더 뷰 사이드바 확인 완료. calendar-view-sidebar-test.png 생성됨');
  }
})();