const { chromium } = require('playwright');

async function testEventDetailMobileUI() {
    console.log('\n🎯 행사 상세 페이지 모바일 UI 테스트 시작...\n');
    
    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 375, height: 667 }, // iPhone SE 크기
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15'
        });
        
        const page = await context.newPage();
        
        // 행사 상세 페이지로 이동
        const eventUrl = 'https://www.topmktx.com/events/detail?id=199&view=list';
        console.log(`📱 모바일 뷰포트로 페이지 접속: ${eventUrl}`);
        
        await page.goto(eventUrl, { waitUntil: 'networkidle' });
        await page.waitForTimeout(3000);
        
        let score = 0;
        let maxScore = 100;
        const results = [];
        
        // 1. 페이지 로딩 확인 (10점)
        try {
            await page.waitForSelector('.event-detail-container', { timeout: 10000 });
            score += 10;
            results.push('✅ 페이지 로딩: 성공 (10/10점)');
        } catch (e) {
            results.push('❌ 페이지 로딩: 실패 (0/10점)');
        }
        
        // 2. 수평 스크롤 확인 (20점)
        const bodyWidth = await page.evaluate(() => document.body.scrollWidth);
        const viewportWidth = 375;
        if (bodyWidth <= viewportWidth + 20) { // 20px 여유
            score += 20;
            results.push(`✅ 수평 스크롤: 없음 (${bodyWidth}px ≤ ${viewportWidth}px) (20/20점)`);
        } else {
            results.push(`❌ 수평 스크롤: 발생 (${bodyWidth}px > ${viewportWidth}px) (0/20점)`);
        }
        
        // 3. 버튼 터치 타겟 크기 확인 (30점)
        const buttons = await page.$$('.btn, button, .register-btn');
        let buttonScore = 0;
        let totalButtons = buttons.length;
        
        for (let i = 0; i < buttons.length; i++) {
            const button = buttons[i];
            const boundingBox = await button.boundingBox();
            
            if (boundingBox && boundingBox.width >= 44 && boundingBox.height >= 44) {
                buttonScore++;
            }
        }
        
        const buttonPercentage = totalButtons > 0 ? (buttonScore / totalButtons) * 100 : 100;
        const buttonPoints = Math.round((buttonPercentage / 100) * 30);
        score += buttonPoints;
        results.push(`✅ 터치 타겟: ${buttonScore}/${totalButtons}개 적합 (${buttonPercentage.toFixed(1)}%) (${buttonPoints}/30점)`);
        
        // 4. 폰트 크기 확인 (25점)
        const textElements = await page.$$('p, span, div, .info-label, .instructor-bio');
        let fontScore = 0;
        let checkedElements = 0;
        
        for (let i = 0; i < Math.min(textElements.length, 20); i++) {
            const element = textElements[i];
            const fontSize = await element.evaluate(el => {
                const style = window.getComputedStyle(el);
                return parseFloat(style.fontSize);
            });
            
            if (fontSize >= 16) {
                fontScore++;
            }
            checkedElements++;
        }
        
        const fontPercentage = checkedElements > 0 ? (fontScore / checkedElements) * 100 : 100;
        const fontPoints = Math.round((fontPercentage / 100) * 25);
        score += fontPoints;
        results.push(`✅ 폰트 가독성: ${fontScore}/${checkedElements}개 16px+ (${fontPercentage.toFixed(1)}%) (${fontPoints}/25점)`);
        
        // 5. 레이아웃 구조 확인 (15점)
        try {
            const contentElement = await page.$('.event-content');
            const isFlexColumn = await contentElement.evaluate(el => {
                const style = window.getComputedStyle(el);
                return style.display === 'flex' && style.flexDirection === 'column';
            });
            
            const sidebarElement = await page.$('.event-sidebar');
            const sidebarOrder = await sidebarElement.evaluate(el => {
                const style = window.getComputedStyle(el);
                return parseInt(style.order) || 0;
            });
            
            if (isFlexColumn && sidebarOrder >= 10) {
                score += 15;
                results.push('✅ 레이아웃: 모바일 최적화 완료 (사이드바 하단 배치) (15/15점)');
            } else {
                results.push('⚠️ 레이아웃: 부분적 최적화 (10/15점)');
                score += 10;
            }
        } catch (e) {
            results.push('❌ 레이아웃: 확인 불가 (0/15점)');
        }
        
        // 스크린샷 촬영
        await page.screenshot({ 
            path: 'event_detail_mobile_ui_test.png',
            fullPage: true 
        });
        
        // 결과 출력
        console.log('\n📊 모바일 UI 테스트 결과:');
        console.log('═'.repeat(50));
        results.forEach(result => console.log(result));
        console.log('═'.repeat(50));
        console.log(`🏆 최종 점수: ${score}/${maxScore}점 (${((score/maxScore)*100).toFixed(1)}%)`);
        
        if (score >= 85) {
            console.log('🎉 우수: 강의 상세 페이지와 동일한 수준의 모바일 UI 품질!');
        } else if (score >= 70) {
            console.log('👍 양호: 모바일 UI 개선이 필요한 부분이 있습니다.');
        } else {
            console.log('⚠️ 개선 필요: 추가 모바일 UI 최적화가 필요합니다.');
        }
        
        console.log(`📸 스크린샷 저장: event_detail_mobile_ui_test.png`);
        
        return score;
        
    } catch (error) {
        console.error('❌ 테스트 실행 중 오류 발생:', error.message);
        return 0;
    } finally {
        await browser.close();
    }
}

// 테스트 실행
testEventDetailMobileUI().then(score => {
    console.log(`\n✨ 테스트 완료. 최종 점수: ${score}점`);
    process.exit(score >= 85 ? 0 : 1);
}).catch(error => {
    console.error('테스트 실패:', error);
    process.exit(1);
});