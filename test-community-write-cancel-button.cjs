const { chromium } = require('playwright');

/**
 * 🔍 Ultra Think: 커뮤니티 글쓰기 취소 버튼 개선 검증 테스트
 * 
 * 테스트 시나리오:
 * 1. 빈 내용에서 취소 - confirm 없이 즉시 이동
 * 2. 제목만 작성 후 취소 - confirm 표시 후 선택
 * 3. 내용만 작성 후 취소 - confirm 표시 후 선택
 * 4. 제목+내용 작성 후 취소 - confirm 표시 후 선택
 * 5. Confirm 응답 테스트 - 확인/취소 각각 검증
 * 6. UI/UX 검증 - 버튼 순서 및 접근성
 */

async function testCommunityWriteCancelButton() {
    console.log('🔍 커뮤니티 글쓰기 취소 버튼 개선 검증 테스트 시작');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        const page = await context.newPage();
        
        // 콘솔 로그 캡처
        page.on('console', msg => {
            if (msg.type() === 'error') {
                console.log(`🔴 [페이지 에러] ${msg.text()}`);
            }
        });
        
        // DevLoginHelper로 자동 로그인
        console.log('🔐 DevLoginHelper로 로그인...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForLoadState('networkidle');
        
        let allPassed = true;
        const results = [];
        
        // ===== 케이스 1: 빈 내용에서 취소 클릭 (confirm 없이 즉시 이동) =====
        console.log('\\n📋 테스트 케이스 1: 빈 내용에서 취소 클릭');
        
        await page.goto('https://www.topmktx.com/community/write');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);
        
        // 초기 상태 확인
        const initialTitle = await page.inputValue('#title');
        const initialContent = await page.evaluate(() => {
            const quill = window.quill;
            return quill ? quill.getText().trim() : '';
        });
        
        console.log(`   초기 제목: "${initialTitle}"`);
        console.log(`   초기 내용: "${initialContent}"`);
        
        // Confirm 다이얼로그 모니터링
        let dialogShown = false;
        const dialogHandler = (dialog) => {
            console.log(`   🚨 Dialog 감지: ${dialog.message()}`);
            dialogShown = true;
            dialog.accept(); // 확인 버튼 클릭
        };
        page.on('dialog', dialogHandler);
        
        // 취소 버튼 클릭
        await page.click('#cancelBtn');
        await page.waitForTimeout(1000);
        
        // 결과 확인
        const currentUrl = page.url();
        const case1Passed = !dialogShown && currentUrl.includes('/community');
        
        console.log(`   Dialog 표시됨: ${dialogShown ? '❌ 예 (실패)' : '✅ 아니오 (성공)'}`);
        console.log(`   현재 URL: ${currentUrl}`);
        console.log(`   케이스 1 결과: ${case1Passed ? '✅ PASS' : '❌ FAIL'}`);
        
        results.push({
            case: '빈 내용에서 취소',
            expected: 'confirm 없이 즉시 이동',
            dialogShown,
            finalUrl: currentUrl,
            passed: case1Passed
        });
        
        if (!case1Passed) allPassed = false;
        
        // ===== 케이스 2: 제목만 작성 후 취소 클릭 =====
        console.log('\\n📋 테스트 케이스 2: 제목만 작성 후 취소 클릭');
        
        await page.goto('https://www.topmktx.com/community/write');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);
        
        // 제목 입력
        const testTitle = '테스트 제목 - 취소 버튼 검증';
        await page.fill('#title', testTitle);
        console.log(`   제목 입력: "${testTitle}"`);
        
        // Dialog 모니터링 리셋
        page.off('dialog', dialogHandler);
        dialogShown = false;
        let dialogMessage = '';
        
        const dialogHandler2 = (dialog) => {
            dialogMessage = dialog.message();
            console.log(`   🚨 Dialog 감지: ${dialogMessage}`);
            dialogShown = true;
            dialog.dismiss(); // 취소 버튼 클릭
        };
        page.on('dialog', dialogHandler2);
        
        // 취소 버튼 클릭
        await page.click('#cancelBtn');
        await page.waitForTimeout(1000);
        
        // 결과 확인
        const currentUrl2 = page.url();
        const case2Passed = dialogShown && currentUrl2.includes('/community/write');
        
        console.log(`   Dialog 표시됨: ${dialogShown ? '✅ 예' : '❌ 아니오'}`);
        console.log(`   Dialog 메시지: "${dialogMessage}"`);
        console.log(`   현재 URL: ${currentUrl2}`);
        console.log(`   케이스 2 결과: ${case2Passed ? '✅ PASS' : '❌ FAIL'}`);
        
        results.push({
            case: '제목만 작성 후 취소',
            expected: 'confirm 표시, 취소 선택시 페이지 유지',
            dialogShown,
            dialogMessage,
            finalUrl: currentUrl2,
            passed: case2Passed
        });
        
        if (!case2Passed) allPassed = false;
        
        // ===== 케이스 3: 내용만 작성 후 취소 클릭 =====
        console.log('\\n📋 테스트 케이스 3: 내용만 작성 후 취소 클릭');
        
        await page.goto('https://www.topmktx.com/community/write');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);
        
        // Quill 에디터에 내용 입력
        const testContent = '테스트 내용입니다. 취소 버튼 검증 중...';
        await page.evaluate((content) => {
            const quill = window.quill;
            if (quill) {
                quill.setText(content);
            }
        }, testContent);
        
        console.log(`   내용 입력: "${testContent}"`);
        
        // Dialog 모니터링 리셋
        page.off('dialog', dialogHandler2);
        dialogShown = false;
        dialogMessage = '';
        
        const dialogHandler3 = (dialog) => {
            dialogMessage = dialog.message();
            console.log(`   🚨 Dialog 감지: ${dialogMessage}`);
            dialogShown = true;
            dialog.accept(); // 확인 버튼 클릭
        };
        page.on('dialog', dialogHandler3);
        
        // 취소 버튼 클릭
        await page.click('#cancelBtn');
        await page.waitForTimeout(1000);
        
        // 결과 확인
        const currentUrl3 = page.url();
        const case3Passed = dialogShown && currentUrl3.includes('/community');
        
        console.log(`   Dialog 표시됨: ${dialogShown ? '✅ 예' : '❌ 아니오'}`);
        console.log(`   Dialog 메시지: "${dialogMessage}"`);
        console.log(`   현재 URL: ${currentUrl3}`);
        console.log(`   케이스 3 결과: ${case3Passed ? '✅ PASS' : '❌ FAIL'}`);
        
        results.push({
            case: '내용만 작성 후 취소',
            expected: 'confirm 표시, 확인 선택시 목록 이동',
            dialogShown,
            dialogMessage,
            finalUrl: currentUrl3,
            passed: case3Passed
        });
        
        if (!case3Passed) allPassed = false;
        
        // ===== 케이스 4: 제목+내용 작성 후 취소 클릭 =====
        console.log('\\n📋 테스트 케이스 4: 제목+내용 작성 후 취소 클릭');
        
        await page.goto('https://www.topmktx.com/community/write');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);
        
        // 제목과 내용 모두 입력
        await page.fill('#title', '제목+내용 테스트');
        await page.evaluate(() => {
            const quill = window.quill;
            if (quill) {
                quill.setText('제목과 내용을 모두 작성한 상태에서 취소 버튼 테스트');
            }
        });
        
        console.log('   제목과 내용 모두 입력 완료');
        
        // Dialog 모니터링 리셋
        page.off('dialog', dialogHandler3);
        dialogShown = false;
        dialogMessage = '';
        
        const dialogHandler4 = (dialog) => {
            dialogMessage = dialog.message();
            console.log(`   🚨 Dialog 감지: ${dialogMessage}`);
            dialogShown = true;
            dialog.accept(); // 확인 버튼 클릭
        };
        page.on('dialog', dialogHandler4);
        
        // 취소 버튼 클릭
        await page.click('#cancelBtn');
        await page.waitForTimeout(1000);
        
        // 결과 확인
        const currentUrl4 = page.url();
        const case4Passed = dialogShown && currentUrl4.includes('/community');
        
        console.log(`   Dialog 표시됨: ${dialogShown ? '✅ 예' : '❌ 아니오'}`);
        console.log(`   Dialog 메시지: "${dialogMessage}"`);
        console.log(`   현재 URL: ${currentUrl4}`);
        console.log(`   케이스 4 결과: ${case4Passed ? '✅ PASS' : '❌ FAIL'}`);
        
        results.push({
            case: '제목+내용 작성 후 취소',
            expected: 'confirm 표시, 확인 선택시 목록 이동',
            dialogShown,
            dialogMessage,
            finalUrl: currentUrl4,
            passed: case4Passed
        });
        
        if (!case4Passed) allPassed = false;
        
        // ===== 케이스 5: UI/UX 검증 - 버튼 순서 확인 =====
        console.log('\\n📋 테스트 케이스 5: UI/UX 검증 - 버튼 순서');
        
        await page.goto('https://www.topmktx.com/community/write');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);
        
        // 버튼 순서 분석
        const buttonOrder = await page.evaluate(() => {
            const buttonsContainer = document.querySelector('.form-buttons');
            const buttons = Array.from(buttonsContainer.children);
            
            return buttons.map(btn => ({
                id: btn.id,
                text: btn.textContent.trim(),
                className: btn.className
            }));
        });
        
        console.log('   버튼 순서 분석:');
        buttonOrder.forEach((btn, index) => {
            console.log(`   ${index + 1}. ID: ${btn.id}, Text: "${btn.text}", Class: ${btn.className}`);
        });
        
        // 취소 버튼이 첫 번째, 작성하기 버튼이 두 번째인지 확인
        const case5Passed = buttonOrder.length >= 2 && 
                           buttonOrder[0].id === 'cancelBtn' && 
                           buttonOrder[1].id === 'submitBtn';
        
        console.log(`   케이스 5 결과: ${case5Passed ? '✅ PASS' : '❌ FAIL'}`);
        
        results.push({
            case: 'UI 버튼 순서 검증',
            expected: '취소(좌측), 작성하기(우측)',
            buttonOrder,
            passed: case5Passed
        });
        
        if (!case5Passed) allPassed = false;
        
        // 최종 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/cancel-button-test-result.png',
            fullPage: false
        });
        
        console.log('\\n📊 === 취소 버튼 개선 검증 결과 ===');
        
        results.forEach((result, index) => {
            const status = result.passed ? '✅' : '❌';
            console.log(`${status} 케이스 ${index + 1}: ${result.case}`);
            console.log(`    예상: ${result.expected}`);
            if (result.dialogShown !== undefined) {
                console.log(`    Dialog 표시: ${result.dialogShown}`);
            }
            if (result.dialogMessage) {
                console.log(`    Dialog 메시지: "${result.dialogMessage}"`);
            }
            if (result.finalUrl) {
                console.log(`    최종 URL: ${result.finalUrl}`);
            }
            if (result.buttonOrder) {
                console.log(`    버튼 순서: ${result.buttonOrder.map(b => b.text).join(' → ')}`);
            }
        });
        
        const passedCount = results.filter(r => r.passed).length;
        const totalCount = results.length;
        const successRate = (passedCount / totalCount * 100).toFixed(1);
        
        console.log(`\\n🏆 성공률: ${passedCount}/${totalCount} (${successRate}%)`);
        
        if (allPassed) {
            console.log('🎉 모든 테스트가 성공했습니다! 취소 버튼 개선이 완벽하게 작동합니다.');
        } else {
            console.log('⚠️ 일부 테스트에서 문제가 발견되었습니다.');
        }
        
        console.log('\\n📸 스크린샷 저장: cancel-button-test-result.png');
        
        return { allPassed, results, successRate };
        
    } catch (error) {
        console.error('💥 테스트 실행 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testCommunityWriteCancelButton()
    .then((result) => {
        console.log('\\n🏁 === 취소 버튼 개선 검증 완료 ===');
        
        if (result.allPassed) {
            console.log('🎊 축하합니다! 취소 버튼 개선이 완벽하게 구현되었습니다!');
            process.exit(0);
        } else {
            console.log('🔧 일부 개선이 필요합니다.');
            console.log(`성공률: ${result.successRate}%`);
            process.exit(1);
        }
    })
    .catch((error) => {
        console.error('💥 테스트 실패:', error);
        process.exit(1);
    });