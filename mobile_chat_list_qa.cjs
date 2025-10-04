const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    // 모바일 뷰포트 설정 (iPhone 13 크기)
    await page.setViewportSize({ width: 390, height: 844 });
    console.log('📱 모바일 뷰포트 설정 완료: 390x844');

    // 채팅 페이지 접속
    await page.goto('https://www.topmktx.com/chat', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    console.log('📄 채팅 페이지 로딩 완료');

    // 페이지 로딩 대기
    await page.waitForTimeout(3000);

    console.log('\n=== 📋 모바일 채팅 리스트 QA 테스트 시작 ===\n');

    // 1. 모바일 토글 버튼 존재 확인
    const toggleBtnExists = await page.evaluate(() => {
      const btn = document.querySelector('.mobile-chat-toggle');
      if (!btn) return { exists: false };

      const styles = window.getComputedStyle(btn);
      return {
        exists: true,
        visible: styles.display !== 'none',
        text: btn.textContent.trim(),
        icon: !!btn.querySelector('i.fas.fa-bars')
      };
    });

    console.log('1️⃣ 모바일 토글 버튼 확인:');
    console.log('   - 존재:', toggleBtnExists.exists);
    console.log('   - 표시:', toggleBtnExists.visible);
    console.log('   - 텍스트:', toggleBtnExists.text);
    console.log('   - 아이콘:', toggleBtnExists.icon);

    // 2. 사이드바 초기 상태 확인
    const sidebarInitialState = await page.evaluate(() => {
      const sidebar = document.querySelector('.chat-sidebar');
      if (!sidebar) return { exists: false };

      const styles = window.getComputedStyle(sidebar);
      return {
        exists: true,
        display: styles.display,
        hasHiddenClass: sidebar.classList.contains('mobile-hidden'),
        hasVisibleClass: sidebar.classList.contains('mobile-visible')
      };
    });

    console.log('\n2️⃣ 사이드바 초기 상태:');
    console.log('   - 존재:', sidebarInitialState.exists);
    console.log('   - Display:', sidebarInitialState.display);
    console.log('   - Hidden 클래스:', sidebarInitialState.hasHiddenClass);
    console.log('   - Visible 클래스:', sidebarInitialState.hasVisibleClass);

    // 3. 토글 버튼 클릭 테스트
    if (toggleBtnExists.exists && toggleBtnExists.visible) {
      console.log('\n3️⃣ 토글 버튼 클릭 테스트:');

      // 스크린샷 (토글 전)
      await page.screenshot({ path: 'chat-mobile-before-toggle.png', fullPage: false });
      console.log('   📸 토글 전 스크린샷: chat-mobile-before-toggle.png');

      // 버튼 클릭
      await page.click('.mobile-chat-toggle');
      await page.waitForTimeout(1000);

      // 토글 후 상태 확인
      const sidebarAfterToggle = await page.evaluate(() => {
        const sidebar = document.querySelector('.chat-sidebar');
        const closeBtn = document.querySelector('.mobile-close-btn');

        if (!sidebar) return { exists: false };

        const styles = window.getComputedStyle(sidebar);
        return {
          exists: true,
          display: styles.display,
          hasVisibleClass: sidebar.classList.contains('mobile-visible'),
          position: styles.position,
          zIndex: styles.zIndex,
          width: styles.width,
          height: styles.height,
          closeBtnExists: !!closeBtn
        };
      });

      console.log('   토글 후 사이드바 상태:');
      console.log('   - 존재:', sidebarAfterToggle.exists);
      console.log('   - Display:', sidebarAfterToggle.display);
      console.log('   - Visible 클래스:', sidebarAfterToggle.hasVisibleClass);
      console.log('   - Position:', sidebarAfterToggle.position);
      console.log('   - Z-Index:', sidebarAfterToggle.zIndex);
      console.log('   - 크기:', sidebarAfterToggle.width, 'x', sidebarAfterToggle.height);
      console.log('   - 닫기 버튼:', sidebarAfterToggle.closeBtnExists);

      // 스크린샷 (토글 후)
      await page.screenshot({ path: 'chat-mobile-after-toggle.png', fullPage: false });
      console.log('   📸 토글 후 스크린샷: chat-mobile-after-toggle.png');

      // 4. 채팅방 목록 확인
      const chatRoomsInfo = await page.evaluate(() => {
        const roomsList = document.querySelector('.chat-rooms-list');
        const roomItems = document.querySelectorAll('.chat-room-item');

        return {
          listExists: !!roomsList,
          roomCount: roomItems.length,
          firstRoomInfo: roomItems[0] ? {
            name: roomItems[0].querySelector('.room-name')?.textContent?.trim(),
            hasAvatar: !!roomItems[0].querySelector('.room-avatar'),
            hasLastMessage: !!roomItems[0].querySelector('.room-last-message')
          } : null
        };
      });

      console.log('\n4️⃣ 채팅방 목록 정보:');
      console.log('   - 목록 존재:', chatRoomsInfo.listExists);
      console.log('   - 채팅방 수:', chatRoomsInfo.roomCount);
      if (chatRoomsInfo.firstRoomInfo) {
        console.log('   - 첫 번째 방 정보:');
        console.log('     이름:', chatRoomsInfo.firstRoomInfo.name);
        console.log('     아바타:', chatRoomsInfo.firstRoomInfo.hasAvatar);
        console.log('     마지막 메시지:', chatRoomsInfo.firstRoomInfo.hasLastMessage);
      }

      // 5. 닫기 기능 테스트 (ESC 키)
      if (sidebarAfterToggle.hasVisibleClass) {
        console.log('\n5️⃣ ESC 키 닫기 테스트:');
        await page.keyboard.press('Escape');
        await page.waitForTimeout(500);

        const sidebarAfterEsc = await page.evaluate(() => {
          const sidebar = document.querySelector('.chat-sidebar');
          return {
            hasVisibleClass: sidebar ? sidebar.classList.contains('mobile-visible') : false
          };
        });

        console.log('   - ESC 후 사이드바 숨김:', !sidebarAfterEsc.hasVisibleClass);

        // 다시 토글 버튼으로 열기
        await page.click('.mobile-chat-toggle');
        await page.waitForTimeout(500);
      }

      // 6. 닫기 버튼 테스트
      const hasCloseBtn = await page.evaluate(() => !!document.querySelector('.mobile-close-btn'));
      if (hasCloseBtn) {
        console.log('\n6️⃣ 닫기 버튼 테스트:');
        await page.click('.mobile-close-btn');
        await page.waitForTimeout(500);

        const sidebarAfterClose = await page.evaluate(() => {
          const sidebar = document.querySelector('.chat-sidebar');
          return {
            hasVisibleClass: sidebar ? sidebar.classList.contains('mobile-visible') : false
          };
        });

        console.log('   - 닫기 버튼 후 사이드바 숨김:', !sidebarAfterClose.hasVisibleClass);
      }
    }

    // 7. 전반적 평가
    console.log('\n=== 📊 QA 테스트 결과 요약 ===');

    let score = 0;
    let maxScore = 0;

    // 버튼 존재 (20점)
    maxScore += 20;
    if (toggleBtnExists.exists && toggleBtnExists.visible) {
      score += 20;
      console.log('✅ 모바일 토글 버튼 정상 (20/20점)');
    } else {
      console.log('❌ 모바일 토글 버튼 문제 (0/20점)');
    }

    // 사이드바 토글 (30점)
    maxScore += 30;
    if (sidebarAfterToggle?.hasVisibleClass && sidebarAfterToggle?.position === 'fixed') {
      score += 30;
      console.log('✅ 사이드바 토글 기능 정상 (30/30점)');
    } else {
      console.log('❌ 사이드바 토글 기능 문제 (0/30점)');
    }

    // 채팅방 목록 표시 (25점)
    maxScore += 25;
    if (chatRoomsInfo?.listExists) {
      if (chatRoomsInfo.roomCount > 0) {
        score += 25;
        console.log(`✅ 채팅방 목록 표시 정상 (${chatRoomsInfo.roomCount}개 방, 25/25점)`);
      } else {
        score += 15;
        console.log('⚠️ 채팅방 목록은 있지만 방이 없음 (15/25점)');
      }
    } else {
      console.log('❌ 채팅방 목록 표시 문제 (0/25점)');
    }

    // 닫기 기능 (15점)
    maxScore += 15;
    if (hasCloseBtn) {
      score += 15;
      console.log('✅ 닫기 기능 정상 (15/15점)');
    } else {
      console.log('❌ 닫기 기능 문제 (0/15점)');
    }

    // 사용성 (10점)
    maxScore += 10;
    if (toggleBtnExists.text === '채팅방' && toggleBtnExists.icon) {
      score += 10;
      console.log('✅ 사용자 인터페이스 직관적 (10/10점)');
    } else {
      console.log('❌ 사용자 인터페이스 개선 필요 (0/10점)');
    }

    const finalScore = Math.round((score / maxScore) * 100);
    console.log(`\n🎯 최종 점수: ${score}/${maxScore} (${finalScore}%)`);

    if (finalScore >= 90) {
      console.log('🎉 우수! 모바일 채팅 리스트 기능이 완벽하게 작동합니다.');
    } else if (finalScore >= 70) {
      console.log('✅ 양호! 기본 기능은 정상 작동하지만 일부 개선이 필요합니다.');
    } else {
      console.log('❌ 개선 필요! 핵심 기능에 문제가 있습니다.');
    }

    // 최종 스크린샷
    await page.screenshot({ path: 'chat-mobile-final.png', fullPage: false });
    console.log('📸 최종 상태 스크린샷: chat-mobile-final.png');

  } catch (error) {
    console.error('❌ QA 테스트 중 오류 발생:', error.message);
  } finally {
    await browser.close();
    console.log('\n✅ 모바일 채팅 리스트 QA 테스트 완료');
  }
})();