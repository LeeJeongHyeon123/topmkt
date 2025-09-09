const { chromium } = require('playwright');

async function debugUIFeedbackDetailed() {
    console.log('🎨 실시간 중복검사 UI 피드백 상세 진단 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        const page = await context.newPage();
        
        // 네트워크 요청 모니터링
        const networkRequests = [];
        page.on('request', request => {
            if (request.url().includes('/auth/check-')) {
                networkRequests.push({
                    url: request.url(),
                    method: request.method(),
                    postData: request.postData()
                });
                console.log(`🌐 Network Request: ${request.method()} ${request.url()}`);
            }
        });
        
        page.on('response', response => {
            if (response.url().includes('/auth/check-')) {
                console.log(`🌐 Network Response: ${response.status()} ${response.url()}`);
            }
        });
        
        console.log('📄 회원가입 페이지 접속...');
        await page.goto('https://www.topmktx.com/auth/signup');
        await page.waitForLoadState('networkidle');
        await page.waitForSelector('#nickname', { timeout: 10000 });
        
        console.log('\n=== 1. 닉네임 실시간 중복검사 UI 상세 테스트 ===');
        
        const nicknameInput = page.locator('#nickname');
        const uniqueNickname = `테스트${Date.now()}`;
        
        console.log(`📝 고유 닉네임 입력: ${uniqueNickname}`);
        await nicknameInput.fill(uniqueNickname);
        
        // 디바운싱 대기
        console.log('⏳ 디바운싱 대기 중...');
        await page.waitForTimeout(1200);
        
        // UI 요소들의 상태를 단계별로 확인
        const nicknameUIState = await page.evaluate(() => {
            const statusMessage = document.getElementById('nickname-status-message');
            const statusIndicator = document.getElementById('nickname-indicator');
            const statusIcon = document.getElementById('nickname-status-icon');
            const messageText = document.getElementById('nickname-message-text');
            const input = document.getElementById('nickname');
            
            return {
                statusMessage: {
                    exists: !!statusMessage,
                    visible: statusMessage ? statusMessage.style.display !== 'none' : false,
                    computedDisplay: statusMessage ? getComputedStyle(statusMessage).display : 'none'
                },
                statusIndicator: {
                    exists: !!statusIndicator,
                    className: statusIndicator ? statusIndicator.className : '',
                    visible: statusIndicator ? statusIndicator.offsetParent !== null : false
                },
                statusIcon: {
                    exists: !!statusIcon,
                    className: statusIcon ? statusIcon.className : '',
                    visible: statusIcon ? statusIcon.offsetParent !== null : false
                },
                messageText: {
                    exists: !!messageText,
                    content: messageText ? messageText.textContent : '',
                    visible: messageText ? messageText.offsetParent !== null : false
                },
                input: {
                    value: input ? input.value : '',
                    className: input ? input.className : '',
                    hasValidClass: input ? input.classList.contains('valid') : false,
                    hasInvalidClass: input ? input.classList.contains('invalid') : false
                },
                globalVars: {
                    isNicknameAvailable: window.isNicknameAvailable,
                    checkFunction: typeof window.checkNicknameDuplication
                }
            };
        });
        
        console.log('📊 닉네임 UI 상태:');
        console.log(JSON.stringify(nicknameUIState, null, 2));
        
        console.log('\n=== 2. 휴대폰 번호 실시간 중복검사 UI 상세 테스트 ===');
        
        const phoneInput = page.locator('#phone');
        const uniquePhone = `010${Date.now().toString().slice(-8)}`;
        
        console.log(`📱 고유 휴대폰 번호 입력: ${uniquePhone}`);
        await phoneInput.fill(uniquePhone);
        
        // 디바운싱 대기
        console.log('⏳ 디바운싱 대기 중...');
        await page.waitForTimeout(1200);
        
        const phoneUIState = await page.evaluate(() => {
            const statusMessage = document.getElementById('phone-status-message');
            const statusIndicator = document.getElementById('phone-indicator');
            const statusIcon = document.getElementById('phone-status-icon');
            const messageText = document.getElementById('phone-message-text');
            const input = document.getElementById('phone');
            
            return {
                statusMessage: {
                    exists: !!statusMessage,
                    visible: statusMessage ? statusMessage.style.display !== 'none' : false,
                    computedDisplay: statusMessage ? getComputedStyle(statusMessage).display : 'none'
                },
                statusIndicator: {
                    exists: !!statusIndicator,
                    className: statusIndicator ? statusIndicator.className : '',
                    visible: statusIndicator ? statusIndicator.offsetParent !== null : false
                },
                statusIcon: {
                    exists: !!statusIcon,
                    className: statusIcon ? statusIcon.className : '',
                    visible: statusIcon ? statusIcon.offsetParent !== null : false
                },
                messageText: {
                    exists: !!messageText,
                    content: messageText ? messageText.textContent : '',
                    visible: messageText ? messageText.offsetParent !== null : false
                },
                input: {
                    value: input ? input.value : '',
                    className: input ? input.className : '',
                    hasValidClass: input ? input.classList.contains('valid') : false,
                    hasInvalidClass: input ? input.classList.contains('invalid') : false
                },
                globalVars: {
                    isPhoneAvailable: window.isPhoneAvailable,
                    checkFunction: typeof window.checkPhoneDuplication
                }
            };
        });
        
        console.log('📊 휴대폰 UI 상태:');
        console.log(JSON.stringify(phoneUIState, null, 2));
        
        console.log('\n=== 3. 수동 함수 호출 및 UI 반응 테스트 ===');
        
        console.log('🧪 수동으로 닉네임 중복검사 함수 호출...');
        await page.evaluate((nickname) => {
            if (window.checkNicknameDuplication) {
                window.checkNicknameDuplication(nickname);
            }
        }, uniqueNickname);
        
        await page.waitForTimeout(2000);
        
        const afterManualCheck = await page.evaluate(() => {
            const statusMessage = document.getElementById('nickname-status-message');
            const statusIcon = document.getElementById('nickname-status-icon');
            const input = document.getElementById('nickname');
            
            return {
                statusVisible: statusMessage ? statusMessage.style.display !== 'none' : false,
                iconClass: statusIcon ? statusIcon.className : '',
                inputClass: input ? input.className : '',
                inputHasValid: input ? input.classList.contains('valid') : false,
                isAvailable: window.isNicknameAvailable
            };
        });
        
        console.log('📊 수동 호출 후 상태:');
        console.log(JSON.stringify(afterManualCheck, null, 2));
        
        // 최종 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/debug-ui-feedback-detailed.png',
            fullPage: true 
        });
        
        console.log('\n📊 === 네트워크 요청 요약 ===');
        console.log(`🌐 총 API 요청: ${networkRequests.length}개`);
        networkRequests.forEach((req, index) => {
            console.log(`  ${index + 1}. ${req.method} ${req.url}`);
            if (req.postData) {
                console.log(`     데이터: ${req.postData}`);
            }
        });
        
        console.log('\n📋 === UI 피드백 진단 결과 ===');
        
        const issues = [];
        
        if (!nicknameUIState.statusMessage.visible) {
            issues.push('닉네임 상태 메시지가 표시되지 않음');
        }
        
        if (!nicknameUIState.input.hasValidClass && nicknameUIState.globalVars.isNicknameAvailable) {
            issues.push('닉네임 입력 필드에 valid 클래스가 적용되지 않음');
        }
        
        if (!phoneUIState.statusMessage.visible) {
            issues.push('휴대폰 상태 메시지가 표시되지 않음');
        }
        
        if (!phoneUIState.input.hasValidClass && phoneUIState.globalVars.isPhoneAvailable) {
            issues.push('휴대폰 입력 필드에 valid 클래스가 적용되지 않음');
        }
        
        if (issues.length > 0) {
            console.log('❌ 발견된 문제들:');
            issues.forEach((issue, index) => {
                console.log(`  ${index + 1}. ${issue}`);
            });
        } else {
            console.log('✅ UI 피드백 시스템이 정상 작동합니다!');
        }
        
        return { nicknameUIState, phoneUIState, networkRequests, issues };
        
    } catch (error) {
        console.error('❌ UI 피드백 진단 중 오류:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
debugUIFeedbackDetailed()
    .then((result) => {
        console.log('✅ UI 피드백 진단 완료!');
        process.exit(0);
    })
    .catch((error) => {
        console.error('💥 진단 실패:', error);
        process.exit(1);
    });