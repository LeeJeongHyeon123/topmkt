const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('📱 모바일 시간 정렬 확인 테스트\n');

    // 모바일 뷰포트 설정 (iPhone 13)
    await page.setViewportSize({ width: 390, height: 844 });

    // DevLoginHelper로 로그인
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    await page.waitForTimeout(2000);

    // 채팅 페이지로 이동
    await page.goto('https://www.topmktx.com/chat', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    console.log('📄 모바일 채팅 페이지 로딩 완료');

    // 로딩 후 잠시 대기
    await page.waitForTimeout(5000);

    // 모바일에서 시간 정보 확인
    const mobileTimeCheck = await page.evaluate(() => {
      const roomTimes = document.querySelectorAll('.room-time');
      const roomMetas = document.querySelectorAll('.room-meta');

      const timeInfos = Array.from(roomTimes).map((time, index) => {
        const styles = window.getComputedStyle(time);
        const rect = time.getBoundingClientRect();
        const parentMeta = time.closest('.room-meta');
        const parentMetaStyles = parentMeta ? window.getComputedStyle(parentMeta) : null;

        return {
          index: index + 1,
          text: time.textContent.trim(),
          textAlign: styles.textAlign,
          whiteSpace: styles.whiteSpace,
          display: styles.display,
          visibility: styles.visibility,
          width: rect.width,
          height: rect.height,
          right: rect.right,
          parentMetaTextAlign: parentMetaStyles?.textAlign || 'none',
          parentMetaAlignItems: parentMetaStyles?.alignItems || 'none'
        };
      });

      return {
        roomCount: roomTimes.length,
        metaCount: roomMetas.length,
        timeInfos,
        viewport: {
          width: window.innerWidth,
          height: window.innerHeight
        }
      };
    });

    console.log(`📊 모바일 시간 정보 분석 (${mobileTimeCheck.viewport.width}x${mobileTimeCheck.viewport.height}):`);
    console.log(`   - 채팅방 수: ${mobileTimeCheck.roomCount}개`);
    console.log(`   - 시간 요소 수: ${mobileTimeCheck.timeInfos.length}개`);

    // 각 시간 정보 분석
    mobileTimeCheck.timeInfos.forEach(info => {
      const alignmentOK = info.textAlign === 'right';
      const noWrapOK = info.whiteSpace === 'nowrap';
      const visibleOK = info.display !== 'none' && info.visibility !== 'hidden';
      const hasText = info.text.length > 0;

      console.log(`\n   🕐 시간 ${info.index}:`);
      console.log(`       텍스트: "${info.text}"`);
      console.log(`       정렬: ${info.textAlign} ${alignmentOK ? '✅' : '❌'}`);
      console.log(`       줄바꿈: ${info.whiteSpace} ${noWrapOK ? '✅' : '❌'}`);
      console.log(`       표시상태: ${info.display}/${info.visibility} ${visibleOK ? '✅' : '❌'}`);
      console.log(`       내용유무: ${hasText ? '✅' : '❌'}`);
      console.log(`       부모 정렬: ${info.parentMetaTextAlign}/${info.parentMetaAlignItems}`);
    });

    // 스크린샷
    await page.screenshot({
      path: 'mobile-time-alignment.png',
      fullPage: false
    });
    console.log('\n📸 모바일 시간 정렬 스크린샷: mobile-time-alignment.png');

    // 문제 진단
    const issues = [];
    const successes = [];

    if (mobileTimeCheck.timeInfos.length === 0) {
      issues.push('시간 요소가 전혀 없음');
    } else {
      const rightAlignedCount = mobileTimeCheck.timeInfos.filter(t => t.textAlign === 'right').length;
      const noWrapCount = mobileTimeCheck.timeInfos.filter(t => t.whiteSpace === 'nowrap').length;
      const visibleCount = mobileTimeCheck.timeInfos.filter(t =>
        t.display !== 'none' && t.visibility !== 'hidden'
      ).length;
      const textCount = mobileTimeCheck.timeInfos.filter(t => t.text.length > 0).length;

      if (rightAlignedCount === mobileTimeCheck.timeInfos.length) {
        successes.push('모든 시간이 우측 정렬됨');
      } else {
        issues.push(`일부 시간이 우측 정렬되지 않음 (${rightAlignedCount}/${mobileTimeCheck.timeInfos.length})`);
      }

      if (noWrapCount === mobileTimeCheck.timeInfos.length) {
        successes.push('모든 시간이 줄바꿈 방지됨');
      } else {
        issues.push(`일부 시간이 줄바꿈 방지되지 않음 (${noWrapCount}/${mobileTimeCheck.timeInfos.length})`);
      }

      if (visibleCount === mobileTimeCheck.timeInfos.length) {
        successes.push('모든 시간이 표시됨');
      } else {
        issues.push(`일부 시간이 숨겨짐 (${visibleCount}/${mobileTimeCheck.timeInfos.length})`);
      }

      if (textCount === mobileTimeCheck.timeInfos.length) {
        successes.push('모든 시간에 내용이 있음');
      } else {
        issues.push(`일부 시간에 내용이 없음 (${textCount}/${mobileTimeCheck.timeInfos.length})`);
      }
    }

    console.log('\n=== 📊 모바일 시간 정렬 진단 ===');

    if (successes.length > 0) {
      console.log('✅ 성공 항목:');
      successes.forEach(success => console.log(`   - ${success}`));
    }

    if (issues.length > 0) {
      console.log('\n❌ 문제 항목:');
      issues.forEach(issue => console.log(`   - ${issue}`));
    }

    const successRate = successes.length / (successes.length + issues.length) * 100;
    console.log(`\n🎯 모바일 시간 정렬 성공률: ${Math.round(successRate)}%`);

    if (successRate >= 90) {
      console.log('🎉 모바일에서 시간 정렬이 완벽합니다!');
    } else if (successRate >= 70) {
      console.log('✅ 모바일에서 시간 정렬이 대체로 좋습니다.');
    } else {
      console.log('⚠️ 모바일에서 시간 정렬에 문제가 있습니다.');
    }

  } catch (error) {
    console.error('❌ 모바일 테스트 중 오류 발생:', error.message);
  } finally {
    await page.close();
    await browser.close();
    console.log('\n✅ 모바일 시간 정렬 확인 완료');
  }
})();