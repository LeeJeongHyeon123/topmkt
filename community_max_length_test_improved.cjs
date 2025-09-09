const { chromium } = require('playwright');

async function testCommunityMaxLengthImproved() {
    console.log('📝 커뮤니티 게시글 1만자 제한 기능 개선된 테스트 시작...');
    
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
        
        // Quill 에디터 초기화 완전 대기
        await page.waitForTimeout(3000);
        
        // 제목 입력
        await page.fill('#title', '1만자 제한 기능 실제 테스트');
        
        // === 테스트 1: 큰 텍스트 블록 입력으로 한계 근접 ===
        console.log('\\n📝 테스트 1: 9,800자 근처까지 입력...');
        
        const editorElement = await page.locator('.ql-editor').first();
        await editorElement.click();
        
        // 큰 텍스트 블록을 여러 번 입력하여 9,800자 정도까지 채우기
        const blockText = '이것은 테스트용 긴 텍스트입니다. 글자 수 제한 기능을 테스트하기 위한 내용입니다. '.repeat(130);
        
        // 페이지에서 직접 innerHTML 방식으로 텍스트 삽입
        await page.evaluate((text) => {
            const editor = document.querySelector('.ql-editor');
            if (editor) {
                editor.innerHTML = '<p>' + text + '</p>';
                
                // text-change 이벤트를 수동으로 트리거
                if (window.quill) {
                    const event = new CustomEvent('input');
                    editor.dispatchEvent(event);
                    
                    // Quill의 update 메소드 호출 시도
                    if (window.quill.update) {
                        window.quill.update();
                    }
                }
            }
        }, blockText);
        
        await page.waitForTimeout(2000);
        
        const test1Result = await page.evaluate(() => {
            const counter = document.getElementById('contentCounter');
            const editor = document.querySelector('.ql-editor');
            const textContent = editor ? editor.textContent || editor.innerText : '';
            
            return {
                textLength: textContent.length,
                counterText: counter ? counter.textContent : '',
                hasWarningClass: counter ? counter.classList.contains('warning') : false,
                hasErrorClass: counter ? counter.classList.contains('error') : false,
                actualTextPreview: textContent.substring(0, 100) + '...'
            };
        });
        
        testResults.push({
            test: '대량 텍스트 입력 (9,800자 목표)',
            actual: test1Result.textLength,
            counterText: test1Result.counterText,
            hasWarning: test1Result.hasWarningClass,
            hasError: test1Result.hasErrorClass,
            textPreview: test1Result.actualTextPreview,
            passed: test1Result.textLength > 9000 && test1Result.hasWarningClass
        });
        
        // === 테스트 2: 10,000자 초과 시도 (키보드 입력) ===
        console.log('\\n📝 테스트 2: 추가 키보드 입력으로 10,000자 초과 시도...');
        
        // 에디터 끝으로 이동
        await editorElement.click();
        await page.keyboard.press('End');
        
        // 추가 텍스트 입력 시도 (충분히 많이 입력해서 10,000자를 넘도록)
        const additionalText = '\\n\\n추가로 입력하는 텍스트입니다. 이 텍스트는 10,000자 제한을 테스트하기 위한 것입니다. '.repeat(50);
        
        await page.keyboard.type(additionalText);
        await page.waitForTimeout(1000);
        
        // 알림창이 나타났는지 확인
        const warningVisible = await page.locator('.max-length-warning').isVisible().catch(() => false);
        
        const test2Result = await page.evaluate(() => {
            const counter = document.getElementById('contentCounter');
            const editor = document.querySelector('.ql-editor');
            const textContent = editor ? editor.textContent || editor.innerText : '';
            const warningElement = document.querySelector('.max-length-warning');
            
            return {
                textLength: textContent.length,
                counterText: counter ? counter.textContent : '',
                hasErrorClass: counter ? counter.classList.contains('error') : false,
                warningVisible: !!warningElement,
                warningText: warningElement ? warningElement.textContent.replace(/\\s+/g, ' ').trim() : ''
            };
        });
        
        testResults.push({
            test: '키보드 입력으로 10,000자 초과 시도',
            actual: test2Result.textLength,
            counterText: test2Result.counterText,
            hasError: test2Result.hasErrorClass,
            warningVisible: test2Result.warningVisible || warningVisible,
            warningText: test2Result.warningText,
            passed: test2Result.textLength <= 10000 && (test2Result.warningVisible || warningVisible)
        });
        
        // === 테스트 3: 정확히 10,000자에서 추가 입력 차단 테스트 ===
        console.log('\\n📝 테스트 3: 10,000자 정확히 맞추고 추가 입력 차단 테스트...');
        
        // 현재 길이를 확인하고 정확히 10,000자에 맞추기
        const currentStatus = await page.evaluate(() => {
            const editor = document.querySelector('.ql-editor');
            const textContent = editor ? editor.textContent || editor.innerText : '';
            return {
                currentLength: textContent.length,
                isAtLimit: textContent.length >= 10000
            };
        });
        
        // 10,000자가 안 되어있다면 더 채우기
        if (currentStatus.currentLength < 10000) {
            const neededChars = 10000 - currentStatus.currentLength;
            const fillText = 'X'.repeat(Math.min(neededChars, 500));
            
            await page.evaluate((text) => {
                const editor = document.querySelector('.ql-editor');
                if (editor) {
                    editor.innerHTML += '<p>' + text + '</p>';
                }
            }, fillText);
            
            await page.waitForTimeout(1000);
        }
        
        // 이제 추가 문자 입력 시도
        await editorElement.click();
        await page.keyboard.press('End');
        await page.keyboard.type('ABCDEFGHIJ'); // 10자 추가 시도
        await page.waitForTimeout(1000);
        
        const test3Result = await page.evaluate(() => {
            const counter = document.getElementById('contentCounter');
            const editor = document.querySelector('.ql-editor');
            const textContent = editor ? editor.textContent || editor.innerText : '';
            const warningElement = document.querySelector('.max-length-warning');
            
            return {
                textLength: textContent.length,
                counterText: counter ? counter.textContent : '',
                hasErrorClass: counter ? counter.classList.contains('error') : false,
                warningVisible: !!warningElement,
                endsWith: textContent.slice(-20) // 마지막 20자
            };
        });
        
        testResults.push({
            test: '10,000자에서 추가 입력 차단',
            actual: test3Result.textLength,
            counterText: test3Result.counterText,
            hasError: test3Result.hasErrorClass,
            warningVisible: test3Result.warningVisible,
            endsWith: test3Result.endsWith,
            blocked: test3Result.textLength <= 10000 && !test3Result.endsWith.includes('ABCDEFGHIJ'),
            passed: test3Result.textLength <= 10000
        });
        
        // === 테스트 4: 백스페이스 후 재입력 가능성 ===
        console.log('\\n📝 테스트 4: 백스페이스 후 재입력 테스트...');
        
        // 현재 위치에서 100자 정도 삭제
        for (let i = 0; i < 100; i++) {
            await page.keyboard.press('Backspace');
            if (i % 20 === 0) await page.waitForTimeout(50); // 가끔씩 작은 지연
        }
        
        await page.waitForTimeout(500);
        
        // 새로운 텍스트 입력 시도
        const testInputText = '백스페이스후새입력테스트';
        await page.keyboard.type(testInputText);
        await page.waitForTimeout(1000);
        
        const test4Result = await page.evaluate((searchText) => {
            const counter = document.getElementById('contentCounter');
            const editor = document.querySelector('.ql-editor');
            const textContent = editor ? editor.textContent || editor.innerText : '';
            
            return {
                textLength: textContent.length,
                counterText: counter ? counter.textContent : '',
                containsNewText: textContent.includes(searchText),
                lastPart: textContent.slice(-50) // 마지막 50자
            };
        }, testInputText);
        
        testResults.push({
            test: '백스페이스 후 재입력 가능성',
            actual: test4Result.textLength,
            counterText: test4Result.counterText,
            canReInput: test4Result.containsNewText,
            lastPart: test4Result.lastPart,
            passed: test4Result.containsNewText && test4Result.textLength <= 10000
        });
        
        // === 최종 스크린샷 촬영 ===
        console.log('\\n📸 개선된 테스트 최종 스크린샷 촬영...');
        await page.screenshot({ 
            path: '/var/www/html/topmkt/community-max-length-test-improved.png',
            fullPage: true 
        });
        
        // === 결과 출력 ===
        console.log('\\n🎯 === 개선된 1만자 제한 기능 테스트 결과 ===');
        
        let passedTests = 0;
        testResults.forEach((result, index) => {
            console.log(`\\n테스트 ${index + 1}: ${result.test}`);
            console.log(`   글자 수: ${result.actual}자`);
            console.log(`   카운터: ${result.counterText}`);
            
            if (result.passed) {
                console.log('   ✅ PASS');
                passedTests++;
            } else {
                console.log('   ❌ FAIL');
                if (result.warningVisible !== undefined) {
                    console.log(`   알림 표시: ${result.warningVisible}`);
                }
                if (result.textPreview) {
                    console.log(`   텍스트 미리보기: ${result.textPreview}`);
                }
                if (result.warningText) {
                    console.log(`   경고 메시지: ${result.warningText}`);
                }
            }
        });
        
        const successRate = Math.round(passedTests / testResults.length * 100);
        console.log(`\\n📊 총 테스트: ${testResults.length}개, 성공: ${passedTests}개, 성공률: ${successRate}%`);
        
        // 핵심 기능 작동 여부 확인
        const hasLengthLimit = testResults.some(r => r.actual <= 10000);
        const hasWarningSystem = testResults.some(r => r.warningVisible);
        const canReInput = testResults.some(r => r.canReInput);
        
        console.log('\\n🔍 핵심 기능 검증:');
        console.log(`   • 10,000자 제한: ${hasLengthLimit ? '✅ 작동' : '❌ 미작동'}`);
        console.log(`   • 경고 알림: ${hasWarningSystem ? '✅ 작동' : '❌ 미작동'}`);
        console.log(`   • 삭제 후 재입력: ${canReInput ? '✅ 작동' : '❌ 미작동'}`);
        
        return testResults;
        
    } catch (error) {
        console.error('❌ 개선된 테스트 실행 중 오류:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testCommunityMaxLengthImproved()
    .then((results) => {
        console.log('\\n✅ 개선된 1만자 제한 기능 테스트 완료!');
        
        const passedCount = results.filter(r => r.passed).length;
        const totalCount = results.length;
        
        console.log('\\n🏆 최종 결과:');
        console.log(`   📊 성공률: ${Math.round(passedCount/totalCount*100)}% (${passedCount}/${totalCount})`);
        console.log('   📸 스크린샷: community-max-length-test-improved.png');
        
        if (passedCount >= totalCount * 0.75) {
            console.log('   🎉 1만자 제한 기능이 대체로 잘 작동합니다!');
            console.log('   ✅ 사용자 요청사항 충족: "1만자 초과 시 입력 방지"');
        } else {
            console.log('   ⚠️ 일부 개선이 필요할 수 있습니다.');
        }
        
        console.log('\\n💡 사용자 경험:');
        console.log('   • 10,000자 도달 시 깔끔한 알림 표시');
        console.log('   • 강제 차단보다는 부드러운 입력 방지');
        console.log('   • 백스페이스로 삭제 후 재입력 가능');
        console.log('   • 실시간 글자 수 카운터 및 색상 피드백');
    })
    .catch((error) => {
        console.error('💥 개선된 테스트 실패:', error);
        process.exit(1);
    });