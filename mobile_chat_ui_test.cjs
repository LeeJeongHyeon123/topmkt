const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('=== 🧠 Ultra Think 모바일 채팅 UI 테스트 ===\n');

    // 모바일 뷰포트 설정 (iPhone 13)
    await page.setViewportSize({ width: 390, height: 844 });
    console.log('📱 모바일 뷰포트 설정 완료: 390x844');

    // 1. DevLoginHelper로 로그인
    console.log('\n1️⃣ DevLoginHelper로 로그인 중...');
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    await page.waitForTimeout(2000);

    const loginStatus = await page.evaluate(() => {
      return {
        url: window.location.href,
        hasLoginSuccess: document.body.textContent.includes('로그인 성공') || document.body.textContent.includes('우리집탄이'),
        bodyText: document.body.textContent.substring(0, 200)
      };
    });

    console.log('   로그인 상태:', loginStatus.hasLoginSuccess ? '✅ 성공' : '❌ 실패');
    console.log('   현재 URL:', loginStatus.url);

    // 2. 채팅 페이지로 이동
    console.log('\n2️⃣ 채팅 페이지로 이동...');
    await page.goto('https://www.topmktx.com/chat', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    await page.waitForTimeout(3000);

    // 3. 모바일 채팅 UI 초기 상태 확인
    console.log('\n3️⃣ 모바일 채팅 UI 초기 상태 확인...');
    const initialState = await page.evaluate(() => {
      const chatLayout = document.querySelector('.chat-layout');
      const chatSidebar = document.querySelector('.chat-sidebar');
      const chatMain = document.querySelector('.chat-main');
      const mobileToggleBtn = document.querySelector('.mobile-chat-toggle');
      const roomsList = document.querySelector('.chat-rooms-list');
      const chatWelcome = document.getElementById('chatWelcome');

      const sidebarStyles = chatSidebar ? window.getComputedStyle(chatSidebar) : null;
      const mainStyles = chatMain ? window.getComputedStyle(chatMain) : null;
      const toggleStyles = mobileToggleBtn ? window.getComputedStyle(mobileToggleBtn) : null;

      return {
        hasChatLayout: !!chatLayout,
        sidebar: {
          exists: !!chatSidebar,
          display: sidebarStyles?.display || 'none',
          visible: sidebarStyles?.display !== 'none'
        },
        main: {
          exists: !!chatMain,
          display: mainStyles?.display || 'none',
          visible: mainStyles?.display !== 'none'
        },
        toggleBtn: {
          exists: !!mobileToggleBtn,
          display: toggleStyles?.display || 'none',
          visible: toggleStyles?.display !== 'none'
        },
        roomsList: {
          exists: !!roomsList,
          hasRooms: roomsList ? roomsList.children.length > 1 : false // 로딩 제외
        },
        hasWelcomeMessage: !!chatWelcome && chatWelcome.style.display !== 'none'
      };
    });

    console.log('   📊 초기 UI 상태:');
    console.log('   - 채팅 사이드바 표시:', initialState.sidebar.visible ? '✅ 보임' : '❌ 숨김');
    console.log('   - 채팅 메인 표시:', initialState.main.visible ? '⚠️ 보임 (문제!)' : '✅ 숨김 (정상)');
    console.log('   - 토글 버튼 표시:', initialState.toggleBtn.visible ? '⚠️ 보임 (불필요!)' : '✅ 숨김 (정상)');
    console.log('   - 채팅방 목록:', initialState.roomsList.hasRooms ? '✅ 있음' : '❌ 없음');

    // 스크린샷 (초기 상태)
    await page.screenshot({ path: 'mobile-chat-ui-initial.png', fullPage: false });
    console.log('   📸 초기 상태 스크린샷: mobile-chat-ui-initial.png');

    // 4. 채팅방 목록 확인 및 선택 테스트
    console.log('\n4️⃣ 채팅방 선택 테스트...');

    // 채팅방 로딩 대기
    await page.waitForTimeout(5000);

    const roomsInfo = await page.evaluate(() => {
      const roomItems = document.querySelectorAll('.chat-room-item');

      return {
        count: roomItems.length,
        rooms: Array.from(roomItems).slice(0, 3).map((item, index) => ({
          index,
          roomId: item.getAttribute('data-room-id'),
          name: item.querySelector('.room-name')?.textContent?.trim() || '',
          hasClick: typeof item.onclick === 'function' || item.getAttribute('onclick')
        }))
      };
    });

    console.log(`   발견된 채팅방: ${roomsInfo.count}개`);

    if (roomsInfo.count > 0) {
      const firstRoom = roomsInfo.rooms[0];
      console.log(`   첫 번째 채팅방: "${firstRoom.name}" (ID: ${firstRoom.roomId})`);

      // 첫 번째 채팅방 클릭
      await page.click('.chat-room-item');
      await page.waitForTimeout(2000);

      // 채팅방 선택 후 상태 확인
      const afterSelectState = await page.evaluate(() => {
        const chatLayout = document.querySelector('.chat-layout');
        const chatSidebar = document.querySelector('.chat-sidebar');
        const chatMain = document.querySelector('.chat-main');
        const backBtn = document.querySelector('.mobile-back-btn');
        const activeChatArea = document.getElementById('activeChatArea');

        const sidebarStyles = chatSidebar ? window.getComputedStyle(chatSidebar) : null;
        const mainStyles = chatMain ? window.getComputedStyle(chatMain) : null;
        const backBtnStyles = backBtn ? window.getComputedStyle(backBtn) : null;

        return {
          hasChatActiveClass: chatLayout ? chatLayout.classList.contains('chat-active') : false,
          sidebar: {
            display: sidebarStyles?.display || 'none',
            visible: sidebarStyles?.display !== 'none'
          },
          main: {
            display: mainStyles?.display || 'none',
            visible: mainStyles?.display !== 'none'
          },
          backBtn: {
            exists: !!backBtn,
            display: backBtnStyles?.display || 'none',
            visible: backBtnStyles?.display !== 'none'
          },
          activeChatVisible: activeChatArea ? activeChatArea.style.display !== 'none' : false
        };
      });

      console.log('   🔄 채팅방 선택 후 상태:');
      console.log('   - chat-active 클래스:', afterSelectState.hasChatActiveClass ? '✅ 적용됨' : '❌ 미적용');
      console.log('   - 사이드바 표시:', afterSelectState.sidebar.visible ? '⚠️ 보임 (숨겨져야 함!)' : '✅ 숨김');
      console.log('   - 채팅창 표시:', afterSelectState.main.visible ? '✅ 보임' : '❌ 숨김');
      console.log('   - 뒤로가기 버튼:', afterSelectState.backBtn.visible ? '✅ 보임' : '❌ 숨김');
      console.log('   - 활성 채팅 영역:', afterSelectState.activeChatVisible ? '✅ 활성화' : '❌ 비활성화');

      // 스크린샷 (채팅방 선택 후)
      await page.screenshot({ path: 'mobile-chat-ui-after-select.png', fullPage: false });
      console.log('   📸 채팅방 선택 후: mobile-chat-ui-after-select.png');

      // 5. 뒤로가기 버튼 테스트
      if (afterSelectState.backBtn.exists && afterSelectState.backBtn.visible) {
        console.log('\n5️⃣ 뒤로가기 버튼 테스트...');

        await page.click('.mobile-back-btn');
        await page.waitForTimeout(2000);

        const afterBackState = await page.evaluate(() => {
          const chatLayout = document.querySelector('.chat-layout');
          const chatSidebar = document.querySelector('.chat-sidebar');
          const chatMain = document.querySelector('.chat-main');

          const sidebarStyles = chatSidebar ? window.getComputedStyle(chatSidebar) : null;
          const mainStyles = chatMain ? window.getComputedStyle(chatMain) : null;

          return {
            hasChatActiveClass: chatLayout ? chatLayout.classList.contains('chat-active') : false,
            sidebar: {
              display: sidebarStyles?.display || 'none',
              visible: sidebarStyles?.display !== 'none'
            },
            main: {
              display: mainStyles?.display || 'none',
              visible: mainStyles?.display !== 'none'
            }
          };
        });

        console.log('   🔄 뒤로가기 후 상태:');
        console.log('   - chat-active 클래스:', afterBackState.hasChatActiveClass ? '⚠️ 아직 있음 (제거되어야 함!)' : '✅ 제거됨');
        console.log('   - 사이드바 표시:', afterBackState.sidebar.visible ? '✅ 보임' : '❌ 숨김');
        console.log('   - 채팅창 표시:', afterBackState.main.visible ? '⚠️ 보임 (숨겨져야 함!)' : '✅ 숨김');

        // 스크린샷 (뒤로가기 후)
        await page.screenshot({ path: 'mobile-chat-ui-after-back.png', fullPage: false });
        console.log('   📸 뒤로가기 후: mobile-chat-ui-after-back.png');
      } else {
        console.log('\n5️⃣ 뒤로가기 버튼이 없어서 테스트를 건너뜁니다.');
      }
    } else {
      console.log('   ⚠️ 채팅방이 없어서 선택 테스트를 건너뜁니다.');
    }

    // 6. 최종 평가
    console.log('\n=== 📊 최종 평가 ===');

    let score = 0;
    let maxScore = 100;

    // 기본 UI 상태 (30점)
    if (initialState.sidebar.visible && !initialState.main.visible) {
      score += 30;
      console.log('✅ 기본 UI 상태 정상 (30/30점)');
    } else {
      console.log('❌ 기본 UI 상태 문제 (0/30점)');
    }

    // 토글 버튼 제거 (20점)
    if (!initialState.toggleBtn.visible) {
      score += 20;
      console.log('✅ 토글 버튼 제거됨 (20/20점)');
    } else {
      console.log('❌ 토글 버튼 아직 보임 (0/20점)');
    }

    // 채팅방 목록 표시 (25점)
    if (initialState.roomsList.exists) {
      if (roomsInfo.count > 0) {
        score += 25;
        console.log(`✅ 채팅방 목록 정상 (${roomsInfo.count}개 방, 25/25점)`);
      } else {
        score += 15;
        console.log('⚠️ 채팅방 목록은 있지만 비어있음 (15/25점)');
      }
    } else {
      console.log('❌ 채팅방 목록 없음 (0/25점)');
    }

    // 채팅방 선택 기능 (15점)
    if (roomsInfo.count > 0 && afterSelectState?.hasChatActiveClass) {
      score += 15;
      console.log('✅ 채팅방 선택 기능 정상 (15/15점)');
    } else if (roomsInfo.count === 0) {
      console.log('⚠️ 채팅방이 없어서 선택 기능 테스트 불가 (10/15점)');
      score += 10;
    } else {
      console.log('❌ 채팅방 선택 기능 문제 (0/15점)');
    }

    // 뒤로가기 기능 (10점)
    if (afterBackState && !afterBackState.hasChatActiveClass) {
      score += 10;
      console.log('✅ 뒤로가기 기능 정상 (10/10점)');
    } else if (!afterSelectState?.backBtn.exists) {
      console.log('⚠️ 뒤로가기 버튼이 없음 (0/10점)');
    } else {
      console.log('❌ 뒤로가기 기능 문제 (0/10점)');
    }

    const finalScore = Math.round(score);
    console.log(`\n🎯 최종 점수: ${finalScore}/${maxScore}점 (${Math.round((finalScore/maxScore)*100)}%)`);

    if (finalScore >= 90) {
      console.log('🎉 우수! 모바일 채팅 UI가 완벽하게 개선되었습니다.');
    } else if (finalScore >= 70) {
      console.log('✅ 양호! 주요 기능은 정상 작동하지만 일부 개선이 필요합니다.');
    } else if (finalScore >= 50) {
      console.log('⚠️ 보통! 기본 기능은 작동하지만 여러 문제가 있습니다.');
    } else {
      console.log('❌ 개선 필요! 주요 기능에 문제가 있어 추가 작업이 필요합니다.');
    }

    // 최종 스크린샷
    await page.screenshot({ path: 'mobile-chat-ui-final.png', fullPage: false });
    console.log('📸 최종 스크린샷: mobile-chat-ui-final.png');

  } catch (error) {
    console.error('❌ 테스트 중 오류 발생:', error.message);
  } finally {
    await browser.close();
    console.log('\n✅ 모바일 채팅 UI 테스트 완료');
  }
})();