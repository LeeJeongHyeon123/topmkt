/**
 * 커뮤니티 버튼 배치 개선 테스트
 * 작성하기(우측), 취소(좌측) 위치 변경 테스트
 */

const { chromium } = require('playwright');

async function testCommunityButtonLayout() {
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'] 
    });
    
    try {
        const page = await browser.newPage();
        
        // DevLoginHelper로 로그인
        console.log('🔐 DevLoginHelper로 로그인 중...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForTimeout(2000);
        
        // 커뮤니티 작성 페이지로 이동
        console.log('📝 커뮤니티 작성 페이지 이동 중...');
        await page.goto('https://www.topmktx.com/community/write');
        await page.waitForTimeout(3000);
        
        console.log('📊 버튼 배치 분석 중...');
        
        // 버튼 요소들 찾기
        const buttons = await page.$$eval('.form-buttons .btn', buttons => {
            return buttons.map((btn, index) => {
                const rect = btn.getBoundingClientRect();
                const text = btn.textContent.trim();
                const classes = btn.className;
                
                return {
                    index,
                    text,
                    classes,
                    position: {
                        left: Math.round(rect.left),
                        right: Math.round(rect.right),
                        width: Math.round(rect.width),
                        center: Math.round(rect.left + rect.width / 2)
                    }
                };
            });
        });
        
        console.log('🎯 버튼 배치 결과:');
        buttons.forEach((btn, idx) => {
            console.log(`  ${idx + 1}. "${btn.text}" - 위치: ${btn.position.left}px~${btn.position.right}px (중앙: ${btn.position.center}px)`);
        });
        
        // 버튼 순서 검증
        const buttonOrder = buttons.map(btn => {
            if (btn.text.includes('취소')) return 'cancel';
            if (btn.text.includes('삭제')) return 'delete';
            if (btn.text.includes('작성') || btn.text.includes('수정')) return 'submit';
            return 'unknown';
        });
        
        console.log('\n✅ 버튼 순서 분석:');
        console.log(`   실제 순서: [${buttonOrder.join(', ')}]`);
        console.log(`   기대 순서: [cancel, submit] 또는 [cancel, delete, submit]`);
        
        // 위치 기반 검증
        const sortedByPosition = [...buttons].sort((a, b) => a.position.center - b.position.center);
        
        console.log('\n📍 위치별 정렬 검증:');
        sortedByPosition.forEach((btn, idx) => {
            const position = idx === 0 ? '좌측' : idx === sortedByPosition.length - 1 ? '우측' : '중앙';
            console.log(`   ${position}: "${btn.text}"`);
        });
        
        // 스크린샷 촬영 (데스크톱)
        await page.setViewportSize({ width: 1200, height: 800 });
        await page.screenshot({ 
            path: `/var/www/html/topmkt/community-button-layout-desktop.png`,
            fullPage: false
        });
        
        // 모바일 버전 테스트
        console.log('\n📱 모바일 버전 테스트...');
        await page.setViewportSize({ width: 375, height: 667 });
        await page.waitForTimeout(1000);
        
        const mobileButtons = await page.$$eval('.form-buttons .btn', buttons => {
            return buttons.map(btn => {
                const rect = btn.getBoundingClientRect();
                const text = btn.textContent.trim();
                const style = window.getComputedStyle(btn);
                
                return {
                    text,
                    position: {
                        left: Math.round(rect.left),
                        right: Math.round(rect.right),
                        width: Math.round(rect.width)
                    },
                    styles: {
                        flex: style.flex,
                        minWidth: style.minWidth,
                        maxWidth: style.maxWidth,
                        fontSize: style.fontSize
                    }
                };
            });
        });
        
        console.log('📱 모바일 버튼 배치:');
        mobileButtons.forEach((btn, idx) => {
            console.log(`  ${idx + 1}. "${btn.text}" - 너비: ${btn.position.width}px, 폰트: ${btn.styles.fontSize}`);
        });
        
        // 모바일 스크린샷
        await page.screenshot({ 
            path: `/var/www/html/topmkt/community-button-layout-mobile.png`,
            fullPage: false
        });
        
        console.log('\n🎉 테스트 완료!');
        console.log('   데스크톱 스크린샷: community-button-layout-desktop.png');
        console.log('   모바일 스크린샷: community-button-layout-mobile.png');
        
        // 검증 결과
        const firstButton = sortedByPosition[0];
        const lastButton = sortedByPosition[sortedByPosition.length - 1];
        
        const isCorrectOrder = firstButton.text.includes('취소') && 
                             (lastButton.text.includes('작성') || lastButton.text.includes('수정'));
        
        if (isCorrectOrder) {
            console.log('✅ SUCCESS: 버튼 배치가 올바릅니다!');
            console.log('   - 취소 버튼이 좌측에 위치');
            console.log('   - 작성하기/수정하기 버튼이 우측에 위치');
        } else {
            console.log('❌ FAILED: 버튼 배치를 다시 확인해주세요.');
        }
        
        return {
            success: isCorrectOrder,
            buttonOrder,
            desktopButtons: buttons,
            mobileButtons
        };
        
    } catch (error) {
        console.error('❌ 테스트 실행 중 오류:', error);
        return { success: false, error: error.message };
    } finally {
        await browser.close();
    }
}

// 실행
if (require.main === module) {
    testCommunityButtonLayout().then(result => {
        process.exit(result.success ? 0 : 1);
    });
}