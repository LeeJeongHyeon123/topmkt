import { chromium } from 'playwright';

/**
 * 비밀번호 찾기 폼 유효성 검사 및 제출 테스트
 */

async function testFormValidationAndSubmission() {
    console.log('🔐 비밀번호 찾기 폼 유효성 검사 테스트 시작...\n');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const page = await browser.newPage();
    
    try {
        // 페이지 이동
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForLoadState('networkidle');
        
        console.log('📝 폼 요소 확인...');
        
        // 폼 요소 존재 확인
        const phoneInput = await page.locator('input[name="phone"]');
        const submitButton = await page.locator('button[type="submit"]');
        
        const hasPhoneInput = await phoneInput.count() > 0;
        const hasSubmitButton = await submitButton.count() > 0;
        
        console.log(`입력 필드 존재: ${hasPhoneInput}`);
        console.log(`제출 버튼 존재: ${hasSubmitButton}`);
        
        if (!hasPhoneInput || !hasSubmitButton) {
            console.log('❌ 필수 폼 요소가 없습니다.');
            return;
        }
        
        // 1. 빈 폼 제출 테스트
        console.log('\n🧪 테스트 1: 빈 폼 제출');
        await phoneInput.clear();
        await submitButton.click();
        await page.waitForTimeout(2000);
        
        const emptyFormValidation = await page.evaluate(() => {
            const phoneField = document.querySelector('input[name="phone"]');
            const form = document.querySelector('form');
            
            return {
                validationMessage: phoneField ? phoneField.validationMessage : '',
                isValid: phoneField ? phoneField.validity.valid : false,
                formCheckValidity: form ? form.checkValidity() : false,
                htmlValidation: phoneField ? phoneField.required : false
            };
        });
        
        console.log('빈 폼 유효성 검사 결과:', emptyFormValidation);
        
        // 2. 유효한 전화번호 입력 테스트  
        console.log('\n🧪 테스트 2: 유효한 전화번호 입력');
        await phoneInput.fill('010-1234-5678');
        await page.waitForTimeout(1000);
        
        const validPhoneValue = await phoneInput.inputValue();
        console.log(`입력된 값: ${validPhoneValue}`);
        
        // 3. 폼 제출 및 응답 확인
        console.log('\n🧪 테스트 3: 폼 제출');
        
        // 네트워크 요청 모니터링
        const requests = [];
        page.on('request', req => {
            if (req.method() === 'POST') {
                requests.push({
                    url: req.url(),
                    method: req.method(),
                    postData: req.postData()
                });
            }
        });
        
        const responses = [];
        page.on('response', res => {
            if (res.request().method() === 'POST') {
                responses.push({
                    url: res.url(),
                    status: res.status(),
                    statusText: res.statusText()
                });
            }
        });
        
        // 폼 제출
        await submitButton.click();
        await page.waitForTimeout(3000);
        
        // 제출 후 상태 확인
        const currentUrl = page.url();
        const pageContent = await page.content();
        
        // 에러 메시지 또는 성공 메시지 확인
        const messages = await page.evaluate(() => {
            const errorSelectors = ['.error', '.alert-danger', '.text-danger', '.invalid-feedback'];
            const successSelectors = ['.success', '.alert-success', '.text-success'];
            
            const errors = [];
            const successes = [];
            
            errorSelectors.forEach(selector => {
                const elements = document.querySelectorAll(selector);
                elements.forEach(el => {
                    if (el.textContent.trim()) {
                        errors.push(el.textContent.trim());
                    }
                });
            });
            
            successSelectors.forEach(selector => {
                const elements = document.querySelectorAll(selector);
                elements.forEach(el => {
                    if (el.textContent.trim()) {
                        successes.push(el.textContent.trim());
                    }
                });
            });
            
            return { errors, successes };
        });
        
        // 현재 폼 상태 확인
        const formState = await page.evaluate(() => {
            const form = document.querySelector('form');
            const phoneInput = document.querySelector('input[name="phone"]');
            const submitBtn = document.querySelector('button[type="submit"]');
            
            return {
                formExists: !!form,
                phoneInputExists: !!phoneInput,
                phoneInputValue: phoneInput ? phoneInput.value : '',
                submitButtonExists: !!submitBtn,
                submitButtonDisabled: submitBtn ? submitBtn.disabled : false,
                submitButtonText: submitBtn ? submitBtn.textContent.trim() : ''
            };
        });
        
        console.log('\n📊 제출 결과:');
        console.log(`현재 URL: ${currentUrl}`);
        console.log(`POST 요청 수: ${requests.length}`);
        console.log(`POST 응답 수: ${responses.length}`);
        console.log('에러 메시지:', messages.errors);
        console.log('성공 메시지:', messages.successes);
        console.log('폼 상태:', formState);
        
        if (requests.length > 0) {
            console.log('\n📤 POST 요청:');
            requests.forEach((req, i) => {
                console.log(`${i + 1}. ${req.method} ${req.url}`);
                if (req.postData) {
                    console.log(`   데이터: ${req.postData.substring(0, 200)}...`);
                }
            });
        }
        
        if (responses.length > 0) {
            console.log('\n📥 POST 응답:');
            responses.forEach((res, i) => {
                console.log(`${i + 1}. ${res.status} ${res.statusText} - ${res.url}`);
            });
        }
        
        // 4. JavaScript 검증 로직 확인
        console.log('\n🧪 테스트 4: JavaScript 검증 로직');
        const jsValidationCheck = await page.evaluate(() => {
            // 페이지에서 유효성 검사 관련 함수 확인
            const validationFunctions = [];
            
            // 전역 함수 체크
            if (typeof validatePhoneNumber === 'function') {
                validationFunctions.push('validatePhoneNumber');
            }
            if (typeof submitForm === 'function') {
                validationFunctions.push('submitForm');
            }
            if (typeof handleSubmit === 'function') {
                validationFunctions.push('handleSubmit');
            }
            
            // 이벤트 리스너 확인
            const form = document.querySelector('form');
            const submitBtn = document.querySelector('button[type="submit"]');
            
            return {
                validationFunctions,
                formHasEventListener: form ? !!form.onsubmit : false,
                submitButtonHasEventListener: submitBtn ? !!submitBtn.onclick : false,
                formAction: form ? form.action : '',
                formMethod: form ? form.method : ''
            };
        });
        
        console.log('JavaScript 검증:', jsValidationCheck);
        
    } catch (error) {
        console.error('❌ 테스트 실행 중 오류:', error);
    } finally {
        await browser.close();
    }
    
    console.log('\n🎉 폼 테스트 완료!');
}

// 스크립트 실행
testFormValidationAndSubmission().catch(console.error);