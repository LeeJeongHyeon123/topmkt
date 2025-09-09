/**
 * 커뮤니티 수정 모드 버튼 순서 테스트
 * 삭제 → 취소 → 수정하기 순서 확인
 */

const { chromium } = require('playwright');

async function testEditModeButtonOrder() {
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'] 
    });
    
    try {
        const page = await browser.newPage();
        
        console.log('🔐 DevLoginHelper로 로그인 중...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForTimeout(2000);
        
        console.log('📝 커뮤니티 작성 페이지로 이동...');
        await page.goto('https://www.topmktx.com/community/write');
        await page.waitForTimeout(2000);
        
        // 테스트용 게시글 작성
        console.log('📝 테스트 게시글 작성 중...');
        await page.fill('#title', '버튼 순서 테스트용 게시글');
        await page.click('.ql-editor');
        await page.type('.ql-editor', '수정 모드 버튼 순서 테스트를 위한 게시글입니다.');
        
        // 작성하기 버튼 클릭
        await page.click('#submitBtn');
        await page.waitForTimeout(3000);
        
        // 현재 URL에서 게시글 ID 추출
        const currentUrl = page.url();
        console.log(`📍 작성된 게시글 URL: ${currentUrl}`);
        
        // URL에서 게시글 ID 추출
        const postIdMatch = currentUrl.match(/\/posts\/(\d+)/);
        if (!postIdMatch) {
            throw new Error('게시글 ID를 추출할 수 없습니다.');
        }
        
        const postId = postIdMatch[1];
        console.log(`📝 게시글 ID: ${postId}`);
        
        // 수정 페이지로 직접 이동
        console.log('✏️ 수정 페이지로 직접 이동 중...');
        await page.goto(`https://www.topmktx.com/community/edit/${postId}`);
        await page.waitForTimeout(5000);
        
        // 페이지가 완전히 로드될 때까지 기다림
        await page.waitForSelector('.form-buttons', { timeout: 10000 });
        
        // 페이지 로드 확인
        const pageTitle = await page.textContent('h1');
        console.log(`📝 페이지 제목: ${pageTitle}`);
        
        // 버튼 존재 및 순서 확인
        console.log('🎯 수정 모드 버튼 순서 분석...');
        
        const buttons = await page.$$eval('.form-buttons button', buttons => {
            return buttons.map((btn, index) => {
                const rect = btn.getBoundingClientRect();
                const text = btn.textContent.trim();
                const id = btn.id;
                const classes = btn.className;
                
                return {
                    index,
                    text,
                    id,
                    classes,
                    position: {
                        left: Math.round(rect.left),
                        right: Math.round(rect.right),
                        width: Math.round(rect.width),
                        center: Math.round(rect.left + rect.width / 2)
                    },
                    visible: btn.offsetWidth > 0 && btn.offsetHeight > 0
                };
            });
        });
        
        console.log('📊 버튼 배치 결과:');
        buttons.forEach((btn, idx) => {
            const position = idx === 0 ? '좌측' : idx === buttons.length - 1 ? '우측' : '중앙';
            console.log(`  ${position}: "${btn.text}" (ID: ${btn.id}) - 위치: ${btn.position.left}px~${btn.position.right}px`);
        });
        
        // 정확한 버튼 순서 검증
        const expectedOrder = ['🗑️ 삭제', '❌ 취소', '수정하기'];
        const actualOrder = buttons.map(btn => btn.text);
        
        console.log('\n✅ 버튼 순서 검증:');
        console.log(`   기대 순서: [${expectedOrder.join(', ')}]`);
        console.log(`   실제 순서: [${actualOrder.join(', ')}]`);
        
        // 위치별 정렬로 다시 한 번 확인
        const sortedByPosition = [...buttons].sort((a, b) => a.position.center - b.position.center);
        console.log('\n📍 위치별 정렬 결과:');
        sortedByPosition.forEach((btn, idx) => {
            const position = idx === 0 ? '좌측' : idx === sortedByPosition.length - 1 ? '우측' : '중앙';
            console.log(`   ${position}: "${btn.text}" (${btn.position.center}px)`);
        });
        
        // 스크린샷 촬영
        await page.setViewportSize({ width: 1200, height: 800 });
        await page.screenshot({ 
            path: `/var/www/html/topmkt/edit-mode-button-order-test.png`,
            fullPage: false
        });
        
        console.log('\n🎉 테스트 완료!');
        console.log('   스크린샷: edit-mode-button-order-test.png');
        
        // 검증 결과
        const isCorrectOrder = (
            sortedByPosition[0].text.includes('삭제') && 
            sortedByPosition[1].text.includes('취소') && 
            sortedByPosition[2].text.includes('수정')
        );
        
        if (isCorrectOrder) {
            console.log('✅ SUCCESS: 수정 모드 버튼 순서가 올바릅니다!');
            console.log('   - 삭제 버튼이 좌측에 위치');
            console.log('   - 취소 버튼이 중앙에 위치');
            console.log('   - 수정하기 버튼이 우측에 위치');
        } else {
            console.log('❌ FAILED: 버튼 순서를 다시 확인해주세요.');
        }
        
        return {
            success: isCorrectOrder,
            buttonOrder: actualOrder,
            buttons: buttons
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
    testEditModeButtonOrder().then(result => {
        console.log('\n📊 최종 테스트 결과:');
        console.log(`   수정 모드 버튼 순서: ${result.success ? '✅ 올바름' : '❌ 수정 필요'}`);
        process.exit(result.success ? 0 : 1);
    });
}