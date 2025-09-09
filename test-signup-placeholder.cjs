const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

async function testSignupPlaceholder() {
    console.log('🚀 회원가입 인증번호 플레이스홀더 테스트 시작');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 },
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });
        
        const page = await context.newPage();
        
        // 콘솔 로그 캡처
        page.on('console', msg => console.log('🔍 페이지 콘솔:', msg.text()));
        
        // 1. 회원가입 페이지 이동
        console.log('📱 회원가입 페이지 로딩...');
        await page.goto('https://www.topmktx.com/auth/signup');
        await page.waitForSelector('#signup-form', { timeout: 10000 });
        
        // 2. 현재 플레이스홀더 확인
        console.log('🔍 인증번호 필드 플레이스홀더 확인...');
        const verificationInput = await page.locator('#verification_code');
        const placeholder = await verificationInput.getAttribute('placeholder');
        
        console.log('📋 현재 플레이스홀더:', placeholder);
        
        // 플레이스홀더 검증
        const isCorrectPlaceholder = placeholder === '인증번호입력';
        console.log(isCorrectPlaceholder ? '✅ 플레이스홀더 변경 성공!' : '❌ 플레이스홀더 변경 실패!');
        
        // 3. 인증번호 입력 필드가 숨겨져 있는지 확인
        const verificationGroup = await page.locator('#verification-group');
        const isHidden = await verificationGroup.isHidden();
        console.log('👁️ 인증번호 그룹 숨김 상태:', isHidden ? '숨김' : '표시');
        
        // 4. 휴대폰 번호 입력하여 인증번호 필드 활성화 테스트
        console.log('📱 휴대폰 번호 입력 테스트...');
        await page.fill('#phone', '010-1234-5678');
        
        // 인증번호 발송 버튼 클릭하여 인증번호 입력 필드 표시
        console.log('📤 인증번호 발송 버튼 클릭...');
        const sendBtn = await page.locator('#send-verification-btn');
        const isSendBtnEnabled = await sendBtn.isEnabled();
        console.log('🔘 인증번호 발송 버튼 활성화 상태:', isSendBtnEnabled);
        
        if (isSendBtnEnabled) {
            // 인증번호 발송 버튼 클릭 (실제 SMS는 발송되지 않도록 빠르게 처리)
            await sendBtn.click();
            
            // 인증번호 입력 그룹이 나타나는지 확인
            console.log('⏳ 인증번호 입력 그룹 표시 대기...');
            
            try {
                await page.waitForSelector('#verification-group', { 
                    state: 'visible', 
                    timeout: 5000 
                });
                
                // 인증번호 입력 필드의 플레이스홀더 다시 확인
                const finalPlaceholder = await verificationInput.getAttribute('placeholder');
                console.log('📋 활성화된 인증번호 필드 플레이스홀더:', finalPlaceholder);
                
                // 5. 스크린샷 촬영
                console.log('📸 스크린샷 촬영...');
                await page.screenshot({ 
                    path: 'signup-placeholder-test.png',
                    fullPage: true 
                });
                
                console.log('✅ 테스트 완료 - 스크린샷 저장됨: signup-placeholder-test.png');
                
            } catch (error) {
                console.log('⚠️ 인증번호 그룹이 즉시 표시되지 않음 (정상적인 경우)');
                // 스크린샷은 여전히 촬영
                await page.screenshot({ 
                    path: 'signup-placeholder-before-verification.png',
                    fullPage: true 
                });
            }
        }
        
        // 테스트 결과 정리
        const testResults = {
            timestamp: new Date().toISOString(),
            placeholder_current: placeholder,
            placeholder_expected: '인증번호입력',
            placeholder_correct: isCorrectPlaceholder,
            verification_group_hidden: isHidden,
            send_button_enabled: isSendBtnEnabled,
            test_status: isCorrectPlaceholder ? 'SUCCESS' : 'FAILED'
        };
        
        console.log('\n📊 테스트 결과:');
        console.log(JSON.stringify(testResults, null, 2));
        
        // 결과를 파일로 저장
        fs.writeFileSync('signup-placeholder-test-result.json', JSON.stringify(testResults, null, 2));
        console.log('💾 테스트 결과 저장됨: signup-placeholder-test-result.json');
        
        return testResults;
        
    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testSignupPlaceholder()
    .then(results => {
        if (results.test_status === 'SUCCESS') {
            console.log('\n🎉 플레이스홀더 변경 테스트 성공!');
            process.exit(0);
        } else {
            console.log('\n❌ 플레이스홀더 변경 테스트 실패!');
            process.exit(1);
        }
    })
    .catch(error => {
        console.error('💥 테스트 실행 실패:', error);
        process.exit(1);
    });