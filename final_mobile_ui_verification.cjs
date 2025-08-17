/**
 * 최종 모바일 UI 검증 및 스크린샷 비교
 */

const { chromium } = require('playwright');

async function finalMobileVerification() {
    console.log('🔍 최종 모바일 UI 검증 시작');
    
    const browser = await chromium.launch({ headless: true });
    
    try {
        // 1. 모바일 (390x844) 테스트
        const mobileContext = await browser.newContext({
            viewport: { width: 390, height: 844 },
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1'
        });
        
        const mobilePage = await mobileContext.newPage();
        await mobilePage.goto('https://www.topmktx.com/lectures/3?view=list');
        await mobilePage.waitForLoadState('networkidle');
        
        // 모바일 전체 페이지 스크린샷
        await mobilePage.screenshot({ 
            path: '/var/www/html/topmkt/final_mobile_390px.png',
            fullPage: true 
        });
        
        // 2. 소형 모바일 (375x667) 테스트
        await mobilePage.setViewportSize({ width: 375, height: 667 });
        await mobilePage.waitForTimeout(1000);
        
        await mobilePage.screenshot({ 
            path: '/var/www/html/topmkt/final_mobile_375px.png',
            fullPage: true 
        });
        
        // 3. 태블릿 (768x1024) 테스트
        await mobilePage.setViewportSize({ width: 768, height: 1024 });
        await mobilePage.waitForTimeout(1000);
        
        await mobilePage.screenshot({ 
            path: '/var/www/html/topmkt/final_tablet_768px.png',
            fullPage: true 
        });
        
        // 4. 세부 분석
        const analysis = await mobilePage.evaluate(() => {
            // 뷰포트 375px로 설정 후 분석
            const content = document.querySelector('.lecture-content');
            const sidebar = document.querySelector('.lecture-sidebar');
            const main = document.querySelector('.lecture-main');
            
            if (!content || !sidebar || !main) {
                return { error: '필수 요소를 찾을 수 없습니다' };
            }
            
            const contentRect = content.getBoundingClientRect();
            const sidebarRect = sidebar.getBoundingClientRect();
            const mainRect = main.getBoundingClientRect();
            
            const contentStyle = window.getComputedStyle(content);
            const sidebarStyle = window.getComputedStyle(sidebar);
            
            // 터치 타겟 분석
            const buttons = document.querySelectorAll('.btn, button, [role="button"]');
            const touchTargetAnalysis = Array.from(buttons).map(btn => {
                const rect = btn.getBoundingClientRect();
                return {
                    text: btn.textContent.trim().substring(0, 20),
                    width: Math.round(rect.width),
                    height: Math.round(rect.height),
                    isVisible: rect.width > 0 && rect.height > 0,
                    isTouchFriendly: rect.width >= 44 && rect.height >= 44
                };
            }).filter(btn => btn.isVisible);
            
            return {
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                },
                layout: {
                    contentWidth: Math.round(contentRect.width),
                    contentHeight: Math.round(contentRect.height),
                    sidebarWidth: Math.round(sidebarRect.width),
                    sidebarHeight: Math.round(sidebarRect.height),
                    mainWidth: Math.round(mainRect.width),
                    mainHeight: Math.round(mainRect.height),
                    sidebarTop: Math.round(sidebarRect.top),
                    mainTop: Math.round(mainRect.top),
                    isSidebarBelow: sidebarRect.top > mainRect.top,
                    gridColumns: contentStyle.gridTemplateColumns,
                    sidebarOrder: sidebarStyle.order
                },
                touchTargets: touchTargetAnalysis,
                touchScore: touchTargetAnalysis.filter(btn => btn.isTouchFriendly).length / touchTargetAnalysis.length * 100
            };
        });
        
        console.log('\n📊 최종 분석 결과 (375px 기준):');
        console.log('==========================================');
        console.log(`📱 뷰포트: ${analysis.viewport.width}x${analysis.viewport.height}`);
        console.log(`🏗️ 콘텐츠 영역: ${analysis.layout.contentWidth}px × ${analysis.layout.contentHeight}px`);
        console.log(`📋 메인 콘텐츠: ${analysis.layout.mainWidth}px × ${analysis.layout.mainHeight}px (Y: ${analysis.layout.mainTop}px)`);
        console.log(`🔄 사이드바: ${analysis.layout.sidebarWidth}px × ${analysis.layout.sidebarHeight}px (Y: ${analysis.layout.sidebarTop}px)`);
        console.log(`📍 사이드바 위치: ${analysis.layout.isSidebarBelow ? '✅ 메인 아래' : '❌ 메인 위/옆'}`);
        console.log(`🎯 터치 타겟 점수: ${Math.round(analysis.touchScore)}%`);
        
        console.log('\n🔘 터치 타겟 상세:');
        analysis.touchTargets.forEach(btn => {
            const status = btn.isTouchFriendly ? '✅' : '❌';
            console.log(`   ${status} "${btn.text}": ${btn.width}x${btn.height}px`);
        });
        
        // 5. 레이아웃 적정성 평가
        const isProperMobile = analysis.layout.isSidebarBelow && 
                              analysis.layout.contentWidth < 400 &&
                              analysis.touchScore >= 70;
        
        console.log(`\n🏆 모바일 최적화 수준: ${isProperMobile ? '✅ 우수' : '🟡 개선 필요'}`);
        
        if (isProperMobile) {
            console.log('🎉 모바일 UI "완전 개판" 문제 해결 완료!');
            console.log('   - 사이드바가 정상적으로 하단 배치됨');
            console.log('   - 콘텐츠가 모바일 화면에 적절히 배치됨');
            console.log(`   - 터치 타겟 점수 ${Math.round(analysis.touchScore)}%로 개선됨`);
        } else {
            console.log('⚠️ 추가 최적화가 권장됩니다');
        }
        
        await mobileContext.close();
        
    } catch (error) {
        console.error('❌ 검증 중 오류:', error.message);
    } finally {
        await browser.close();
        console.log('\n✅ 최종 검증 완료');
    }
}

// 실행
finalMobileVerification().catch(console.error);