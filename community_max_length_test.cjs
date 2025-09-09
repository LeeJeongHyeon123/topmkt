const { chromium } = require('playwright');

async function testCommunityMaxLength() {
    console.log('📝 커뮤니티 게시글 1만자 제한 기능 테스트 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        const page = await context.newPage();
        
        const testResults = [];
        
        // 1. 로그인 처리 (우리집탄이 계정 user_id=4 사용)
        console.log('🔐 로그인 처리...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForLoadState('networkidle');
        
        // 2. 게시글 작성 페이지로 이동
        console.log('📄 게시글 작성 페이지 접속...');
        await page.goto('https://www.topmktx.com/community/write');
        await page.waitForLoadState('networkidle');
        await page.waitForSelector('#quill-editor', { timeout: 10000 });
        
        // Quill 에디터 초기화 대기
        await page.waitForTimeout(2000);
        
        // === 테스트 1: 정상 범위 내 입력 (9,500자) ===
        console.log('\\n📝 테스트 1: 9,500자 정상 입력...');
        
        // 제목 입력
        await page.fill('#title', '1만자 제한 기능 테스트');
        
        // 9,500자 텍스트 생성
        const normalText = 'A'.repeat(9500);
        
        await page.evaluate((text) => {
            if (window.quill) {
                window.quill.setText(text);
            }
        }, normalText);
        
        await page.waitForTimeout(1000);
        
        const test1Result = await page.evaluate(() => {
            const counter = document.getElementById('contentCounter');
            const editorText = window.quill ? window.quill.getText() : '';
            
            return {
                textLength: editorText.length,
                counterText: counter ? counter.textContent : '',
                hasWarningClass: counter ? counter.classList.contains('warning') : false,
                hasErrorClass: counter ? counter.classList.contains('error') : false
            };
        });
        
        testResults.push({
            test: '정상 범위 내 입력 (9,500자)',
            expected: 9500,
            actual: test1Result.textLength,
            counterText: test1Result.counterText,
            hasWarning: test1Result.hasWarningClass,
            hasError: test1Result.hasErrorClass,
            passed: test1Result.textLength === 9500 && test1Result.hasWarningClass && !test1Result.hasErrorClass
        });
        
        // === 테스트 2: 정확히 10,000자 입력 ===
        console.log('\\n📝 테스트 2: 정확히 10,000자 입력...');
        
        const maxText = 'B'.repeat(10000);
        
        await page.evaluate((text) => {
            if (window.quill) {
                window.quill.setText(text);
            }
        }, maxText);
        
        await page.waitForTimeout(1000);
        
        const test2Result = await page.evaluate(() => {
            const counter = document.getElementById('contentCounter');
            const editorText = window.quill ? window.quill.getText() : '';
            
            return {
                textLength: editorText.length,
                counterText: counter ? counter.textContent : '',
                hasWarningClass: counter ? counter.classList.contains('warning') : false,
                hasErrorClass: counter ? counter.classList.contains('error') : false
            };
        });
        
        testResults.push({
            test: '정확히 10,000자 입력',
            expected: 10000,
            actual: test2Result.textLength,
            counterText: test2Result.counterText,
            hasWarning: test2Result.hasWarningClass,
            hasError: test2Result.hasErrorClass,
            passed: test2Result.textLength === 10000 && test2Result.hasErrorClass
        });
        
        // === 테스트 3: 10,000자 초과 입력 시도 (추가 타이핑) ===
        console.log('\\n📝 테스트 3: 10,000자 초과 추가 타이핑 시도...');
        
        // Quill 에디터에서 추가 문자 타이핑 시도
        const editorElement = await page.locator('.ql-editor').first();
        await editorElement.click();
        
        // 커서를 맨 끝으로 이동
        await page.keyboard.press('End');
        
        // 추가 문자 입력 시도
        await page.keyboard.type('추가텍스트입니다');
        await page.waitForTimeout(1000);
        
        const test3Result = await page.evaluate(() => {
            const counter = document.getElementById('contentCounter');
            const editorText = window.quill ? window.quill.getText() : '';
            const warningElement = document.querySelector('.max-length-warning');
            
            return {
                textLength: editorText.length,
                counterText: counter ? counter.textContent : '',
                hasErrorClass: counter ? counter.classList.contains('error') : false,
                warningShown: !!warningElement,
                warningText: warningElement ? warningElement.textContent : ''
            };
        });
        
        testResults.push({
            test: '10,000자 초과 추가 타이핑',
            expected: 10000,
            actual: test3Result.textLength,
            counterText: test3Result.counterText,
            hasError: test3Result.hasErrorClass,
            warningShown: test3Result.warningShown,
            warningText: test3Result.warningText,
            passed: test3Result.textLength === 10000 && test3Result.warningShown
        });
        
        // === 테스트 4: 복사-붙여넣기로 초과 입력 시도 ===
        console.log('\\n📝 테스트 4: 복사-붙여넣기로 초과 입력 시도...');
        
        // 현재 텍스트를 선택하고 더 긴 텍스트로 교체 시도
        const overText = 'C'.repeat(15000); // 15,000자
        
        await page.evaluate((text) => {
            if (window.quill) {
                // 전체 텍스트 선택
                window.quill.setSelection(0, window.quill.getLength());
                
                // 클립보드에 복사하는 대신 직접 텍스트 삽입 시도
                try {
                    window.quill.insertText(0, text);
                } catch (e) {
                    console.log('삽입 중 오류:', e);
                }
            }
        }, overText);
        
        await page.waitForTimeout(2000);
        
        const test4Result = await page.evaluate(() => {
            const counter = document.getElementById('contentCounter');
            const editorText = window.quill ? window.quill.getText() : '';
            const warningElement = document.querySelector('.max-length-warning');
            
            return {
                textLength: editorText.length,
                counterText: counter ? counter.textContent : '',
                hasErrorClass: counter ? counter.classList.contains('error') : false,
                warningShown: !!warningElement,
                actualText: editorText.substring(0, 50) + '...' // 처음 50자만
            };
        });
        
        testResults.push({
            test: '복사-붙여넣기 초과 입력',
            expected: 10000,
            actual: test4Result.textLength,
            counterText: test4Result.counterText,
            hasError: test4Result.hasErrorClass,
            warningShown: test4Result.warningShown,
            actualText: test4Result.actualText,
            passed: test4Result.textLength <= 10000
        });
        
        // === 테스트 5: 백스페이스 후 재입력 ===
        console.log('\\n📝 테스트 5: 백스페이스 후 재입력 가능 여부...');
        
        // 현재 텍스트 끝에서 10자 정도 삭제
        await editorElement.click();
        await page.keyboard.press('End');
        
        for (let i = 0; i < 50; i++) {
            await page.keyboard.press('Backspace');
        }
        
        await page.waitForTimeout(500);
        
        // 새로운 텍스트 입력 시도
        await page.keyboard.type('새로운텍스트입력테스트');
        await page.waitForTimeout(1000);
        
        const test5Result = await page.evaluate(() => {
            const counter = document.getElementById('contentCounter');
            const editorText = window.quill ? window.quill.getText() : '';
            
            return {
                textLength: editorText.length,
                counterText: counter ? counter.textContent : '',
                canInputAfterDelete: editorText.includes('새로운텍스트입력테스트'),
                hasErrorClass: counter ? counter.classList.contains('error') : false
            };
        });
        
        testResults.push({
            test: '백스페이스 후 재입력',
            actual: test5Result.textLength,
            counterText: test5Result.counterText,
            canReInput: test5Result.canInputAfterDelete,
            hasError: test5Result.hasErrorClass,
            passed: test5Result.canInputAfterDelete && test5Result.textLength <= 10000
        });
        
        // === 스크린샷 촬영 ===
        console.log('\\n📸 최종 스크린샷 촬영...');
        await page.screenshot({ 
            path: '/var/www/html/topmkt/community-max-length-test-result.png',
            fullPage: true 
        });
        
        // === 결과 출력 ===
        console.log('\\n🎯 === 1만자 제한 기능 테스트 결과 ===');
        
        let passedTests = 0;
        testResults.forEach((result, index) => {
            console.log(`\\n테스트 ${index + 1}: ${result.test}`);
            if (result.passed) {
                console.log('✅ PASS');
                passedTests++;
            } else {
                console.log('❌ FAIL');
                console.log('상세:', JSON.stringify(result, null, 2));
            }
        });
        
        const successRate = Math.round(passedTests / testResults.length * 100);
        console.log(`\\n📊 총 테스트: ${testResults.length}개, 성공: ${passedTests}개, 성공률: ${successRate}%`);
        
        if (successRate >= 80) {
            console.log('🎉 1만자 제한 기능이 정상적으로 작동합니다!');
        } else {
            console.log('⚠️ 일부 기능에서 문제가 발견되었습니다.');
        }
        
        return testResults;
        
    } catch (error) {
        console.error('❌ 테스트 실행 중 오류:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testCommunityMaxLength()
    .then((results) => {
        console.log('\\n✅ 1만자 제한 기능 테스트 완료!');
        
        const passedCount = results.filter(r => r.passed).length;
        const totalCount = results.length;
        
        console.log('\\n🏆 최종 결과 요약:');
        console.log(`   • 전체 테스트: ${totalCount}개`);
        console.log(`   • 성공 테스트: ${passedCount}개`);
        console.log(`   • 성공률: ${Math.round(passedCount/totalCount*100)}%`);
        
        if (passedCount === totalCount) {
            console.log('   • 상태: ✅ 완벽 성공');
            console.log('   • 사용자 요청사항 100% 달성');
        } else {
            console.log('   • 상태: ⚠️ 일부 개선 필요');
        }
        
        console.log('\\n📸 스크린샷: community-max-length-test-result.png');
    })
    .catch((error) => {
        console.error('💥 테스트 실패:', error);
        process.exit(1);
    });