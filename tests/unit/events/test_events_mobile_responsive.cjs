const { chromium } = require('playwright');

async function testEventsMobileResponsive() {
    console.log('🚀 행사일정 페이지 모바일 반응형 테스트 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const context = await browser.newContext();
    const page = await context.newPage();
    
    // 테스트할 뷰포트 정의
    const viewports = [
        { name: 'mobile', width: 375, height: 667, device: 'iPhone SE' },
        { name: 'tablet', width: 768, height: 1024, device: 'iPad' },
        { name: 'small', width: 320, height: 568, device: 'iPhone 5' }
    ];
    
    const testResults = {
        mobile: {},
        tablet: {},
        small: {}
    };
    
    try {
        for (const viewport of viewports) {
            console.log(`\n📱 테스트 중: ${viewport.device} (${viewport.width}x${viewport.height})`);
            
            // 뷰포트 설정
            await page.setViewportSize({ 
                width: viewport.width, 
                height: viewport.height 
            });
            
            // 행사일정 페이지 접속
            console.log('   🌐 행사일정 페이지 로딩...');
            await page.goto('https://www.topmktx.com/events', { 
                waitUntil: 'networkidle',
                timeout: 30000 
            });
            
            // 페이지 로딩 대기
            await page.waitForTimeout(3000);
            
            const result = testResults[viewport.name];
            
            // 1. 터치 타겟 크기 검사 (최소 44px)
            console.log('   ✋ 터치 타겟 크기 검사...');
            const touchTargets = await page.evaluate(() => {
                const buttons = document.querySelectorAll('button, a, input[type="button"], input[type="submit"], .btn');
                const targets = [];
                
                buttons.forEach((btn, index) => {
                    const rect = btn.getBoundingClientRect();
                    if (rect.width > 0 && rect.height > 0) {
                        targets.push({
                            element: btn.tagName + (btn.className ? '.' + btn.className.split(' ')[0] : ''),
                            width: Math.round(rect.width),
                            height: Math.round(rect.height),
                            isAccessible: rect.width >= 44 && rect.height >= 44
                        });
                    }
                });
                
                return targets;
            });
            
            result.touchTargets = touchTargets;
            result.touchTargetIssues = touchTargets.filter(t => !t.isAccessible);
            
            // 2. 폰트 크기 검사 (최소 16px for iOS zoom prevention)
            console.log('   📝 폰트 크기 검사...');
            const fontSizes = await page.evaluate(() => {
                const elements = document.querySelectorAll('input, select, textarea, p, span, div, h1, h2, h3, h4, h5, h6');
                const fonts = [];
                
                elements.forEach(el => {
                    const style = window.getComputedStyle(el);
                    const fontSize = parseFloat(style.fontSize);
                    
                    if (fontSize > 0) {
                        const tagName = el.tagName.toLowerCase();
                        const className = el.className || '';
                        
                        fonts.push({
                            element: `${tagName}${className ? '.' + className.split(' ')[0] : ''}`,
                            fontSize: Math.round(fontSize),
                            isAccessible: fontSize >= 16
                        });
                    }
                });
                
                // 중복 제거 및 그룹화
                const uniqueFonts = {};
                fonts.forEach(font => {
                    const key = `${font.element}_${font.fontSize}`;
                    if (!uniqueFonts[key]) {
                        uniqueFonts[key] = font;
                    }
                });
                
                return Object.values(uniqueFonts);
            });
            
            result.fontSizes = fontSizes;
            result.fontSizeIssues = fontSizes.filter(f => !f.isAccessible);
            
            // 3. 수평 스크롤 검사
            console.log('   ↔️ 수평 스크롤 검사...');
            const scrollInfo = await page.evaluate(() => {
                const body = document.body;
                const html = document.documentElement;
                
                return {
                    bodyScrollWidth: body.scrollWidth,
                    bodyClientWidth: body.clientWidth,
                    htmlScrollWidth: html.scrollWidth,
                    htmlClientWidth: html.clientWidth,
                    viewportWidth: window.innerWidth,
                    hasHorizontalScroll: body.scrollWidth > window.innerWidth || html.scrollWidth > window.innerWidth
                };
            });
            
            result.scrollInfo = scrollInfo;
            
            // 4. 캘린더 레이아웃 검사
            console.log('   📅 캘린더 레이아웃 검사...');
            const calendarInfo = await page.evaluate(() => {
                const calendar = document.querySelector('.calendar, #calendar, .event-calendar, table');
                const calendarContainer = document.querySelector('.calendar-container, .events-container');
                
                if (!calendar) return { exists: false };
                
                const rect = calendar.getBoundingClientRect();
                const containerRect = calendarContainer ? calendarContainer.getBoundingClientRect() : null;
                
                return {
                    exists: true,
                    width: Math.round(rect.width),
                    height: Math.round(rect.height),
                    containerWidth: containerRect ? Math.round(containerRect.width) : null,
                    isResponsive: rect.width <= window.innerWidth,
                    overflowsViewport: rect.width > window.innerWidth
                };
            });
            
            result.calendarInfo = calendarInfo;
            
            // 5. 네비게이션 버튼 검사
            console.log('   🧭 네비게이션 버튼 검사...');
            const navButtons = await page.evaluate(() => {
                const navSelectors = [
                    '.nav-button', '.navigation-btn', '.btn-nav',
                    '.prev', '.next', '.btn-prev', '.btn-next',
                    '[data-action="prev"]', '[data-action="next"]',
                    'button[onclick*="prev"]', 'button[onclick*="next"]'
                ];
                
                const buttons = [];
                navSelectors.forEach(selector => {
                    const elements = document.querySelectorAll(selector);
                    elements.forEach(btn => {
                        const rect = btn.getBoundingClientRect();
                        if (rect.width > 0 && rect.height > 0) {
                            buttons.push({
                                selector: selector,
                                text: btn.textContent.trim(),
                                width: Math.round(rect.width),
                                height: Math.round(rect.height),
                                isVisible: rect.width > 0 && rect.height > 0,
                                isAccessible: rect.width >= 44 && rect.height >= 44
                            });
                        }
                    });
                });
                
                return buttons;
            });
            
            result.navButtons = navButtons;
            result.navButtonIssues = navButtons.filter(btn => !btn.isAccessible);
            
            // 6. 인터랙티브 요소 검사
            console.log('   🖱️ 인터랙티브 요소 검사...');
            const interactiveElements = await page.evaluate(() => {
                const elements = document.querySelectorAll('a, button, input, select, textarea, [onclick], [role="button"]');
                const interactive = [];
                
                elements.forEach(el => {
                    const rect = el.getBoundingClientRect();
                    const style = window.getComputedStyle(el);
                    
                    if (rect.width > 0 && rect.height > 0) {
                        interactive.push({
                            tag: el.tagName.toLowerCase(),
                            type: el.type || '',
                            width: Math.round(rect.width),
                            height: Math.round(rect.height),
                            hasPointerEvents: style.pointerEvents !== 'none',
                            isAccessible: rect.width >= 44 && rect.height >= 44 && style.pointerEvents !== 'none'
                        });
                    }
                });
                
                return interactive;
            });
            
            result.interactiveElements = interactiveElements;
            result.interactiveIssues = interactiveElements.filter(el => !el.isAccessible);
            
            // 스크린샷 촬영
            console.log(`   📸 스크린샷 촬영: events_${viewport.name}_current.png`);
            await page.screenshot({ 
                path: `/var/www/html/topmkt/events_${viewport.name}_current.png`,
                fullPage: true 
            });
            
            console.log(`   ✅ ${viewport.device} 테스트 완료`);
        }
        
        // 결과 리포트 생성
        console.log('\n📊 모바일 반응형 테스트 결과 분석...');
        
        let report = `# 행사일정 페이지 모바일 반응형 테스트 리포트\n\n`;
        report += `**테스트 일시**: ${new Date().toLocaleString('ko-KR')}\n`;
        report += `**테스트 페이지**: /events\n`;
        report += `**테스트 뷰포트**: iPhone SE (375x667), iPad (768x1024), iPhone 5 (320x568)\n\n`;
        
        for (const [deviceName, result] of Object.entries(testResults)) {
            const deviceInfo = viewports.find(v => v.name === deviceName);
            
            report += `## ${deviceInfo.device} (${deviceInfo.width}x${deviceInfo.height})\n\n`;
            
            // 터치 타겟 분석
            report += `### 1. 터치 타겟 크기 분석\n`;
            report += `- **총 터치 타겟**: ${result.touchTargets.length}개\n`;
            report += `- **접근성 문제**: ${result.touchTargetIssues.length}개\n`;
            
            if (result.touchTargetIssues.length > 0) {
                report += `\n**문제 요소들**:\n`;
                result.touchTargetIssues.forEach(issue => {
                    report += `- ${issue.element}: ${issue.width}x${issue.height}px (최소 44x44px 필요)\n`;
                });
            } else {
                report += `- ✅ 모든 터치 타겟이 접근성 기준 충족\n`;
            }
            
            // 폰트 크기 분석
            report += `\n### 2. 폰트 크기 분석\n`;
            report += `- **총 폰트 요소**: ${result.fontSizes.length}개\n`;
            report += `- **iOS 줌 유발 요소**: ${result.fontSizeIssues.length}개\n`;
            
            if (result.fontSizeIssues.length > 0) {
                report += `\n**문제 요소들** (16px 미만):\n`;
                result.fontSizeIssues.slice(0, 10).forEach(issue => {
                    report += `- ${issue.element}: ${issue.fontSize}px\n`;
                });
                if (result.fontSizeIssues.length > 10) {
                    report += `- ... 및 ${result.fontSizeIssues.length - 10}개 추가\n`;
                }
            } else {
                report += `- ✅ 모든 폰트 크기가 iOS 줌 방지 기준 충족\n`;
            }
            
            // 수평 스크롤 분석
            report += `\n### 3. 수평 스크롤 분석\n`;
            report += `- **뷰포트 너비**: ${result.scrollInfo.viewportWidth}px\n`;
            report += `- **콘텐츠 너비**: ${result.scrollInfo.bodyScrollWidth}px\n`;
            report += `- **수평 스크롤**: ${result.scrollInfo.hasHorizontalScroll ? '❌ 발생' : '✅ 없음'}\n`;
            
            // 캘린더 레이아웃 분석
            report += `\n### 4. 캘린더 레이아웃 분석\n`;
            if (result.calendarInfo.exists) {
                report += `- **캘린더 크기**: ${result.calendarInfo.width}x${result.calendarInfo.height}px\n`;
                report += `- **반응형 적용**: ${result.calendarInfo.isResponsive ? '✅ 정상' : '❌ 문제'}\n`;
                report += `- **뷰포트 오버플로우**: ${result.calendarInfo.overflowsViewport ? '❌ 발생' : '✅ 없음'}\n`;
            } else {
                report += `- ❌ 캘린더 요소를 찾을 수 없음\n`;
            }
            
            // 네비게이션 버튼 분석
            report += `\n### 5. 네비게이션 버튼 분석\n`;
            report += `- **네비게이션 버튼**: ${result.navButtons.length}개\n`;
            report += `- **접근성 문제**: ${result.navButtonIssues.length}개\n`;
            
            if (result.navButtons.length > 0) {
                result.navButtons.forEach(btn => {
                    const status = btn.isAccessible ? '✅' : '❌';
                    report += `- ${status} ${btn.text || btn.selector}: ${btn.width}x${btn.height}px\n`;
                });
            } else {
                report += `- ⚠️ 네비게이션 버튼을 찾을 수 없음\n`;
            }
            
            // 전체 인터랙티브 요소 요약
            report += `\n### 6. 인터랙티브 요소 요약\n`;
            report += `- **총 인터랙티브 요소**: ${result.interactiveElements.length}개\n`;
            report += `- **접근성 문제**: ${result.interactiveIssues.length}개\n`;
            
            const accessibilityScore = result.interactiveElements.length > 0 
                ? Math.round((result.interactiveElements.length - result.interactiveIssues.length) / result.interactiveElements.length * 100)
                : 0;
            
            report += `- **접근성 점수**: ${accessibilityScore}%\n`;
            
            report += `\n---\n\n`;
        }
        
        // 종합 결론
        report += `## 종합 결론\n\n`;
        
        const totalIssues = Object.values(testResults).reduce((sum, result) => {
            return sum + result.touchTargetIssues.length + result.fontSizeIssues.length + 
                   (result.scrollInfo.hasHorizontalScroll ? 1 : 0) + 
                   (result.calendarInfo.exists && !result.calendarInfo.isResponsive ? 1 : 0) + 
                   result.navButtonIssues.length;
        }, 0);
        
        if (totalIssues === 0) {
            report += `✅ **모든 뷰포트에서 모바일 반응형이 우수합니다.**\n\n`;
        } else {
            report += `⚠️ **총 ${totalIssues}개의 개선이 필요한 사항이 발견되었습니다.**\n\n`;
        }
        
        report += `### 우선순위 개선 사항\n`;
        report += `1. **터치 타겟 크기**: 44x44px 미만인 버튼들을 확대\n`;
        report += `2. **폰트 크기**: 16px 미만인 입력 필드 폰트 크기 조정\n`;
        report += `3. **수평 스크롤**: 콘텐츠가 뷰포트를 벗어나는 문제 해결\n`;
        report += `4. **캘린더 반응형**: 캘린더가 화면에 맞게 조정되도록 개선\n\n`;
        
        report += `### 생성된 스크린샷\n`;
        report += `- events_mobile_current.png (iPhone SE)\n`;
        report += `- events_tablet_current.png (iPad)\n`;
        report += `- events_small_current.png (iPhone 5)\n\n`;
        
        console.log(report);
        
        // 리포트 저장
        require('fs').writeFileSync('/var/www/html/topmkt/events_mobile_responsive_report.md', report);
        console.log('\n📄 상세 리포트가 events_mobile_responsive_report.md 파일로 저장되었습니다.');
        
    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error);
        
        // 오류 스크린샷
        try {
            await page.screenshot({ 
                path: '/var/www/html/topmkt/events_error_screenshot.png',
                fullPage: true 
            });
            console.log('🚨 오류 스크린샷이 저장되었습니다: events_error_screenshot.png');
        } catch (screenshotError) {
            console.error('스크린샷 저장 실패:', screenshotError);
        }
    } finally {
        await browser.close();
    }
}

// 테스트 실행
testEventsMobileResponsive().then(() => {
    console.log('\n🎉 모바일 반응형 테스트 완료!');
}).catch(error => {
    console.error('🚨 테스트 실행 실패:', error);
    process.exit(1);
});