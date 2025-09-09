const { chromium } = require('playwright');

async function debugCsrfToken() {
    console.log('🔍 CSRF 토큰 디버깅');
    
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
        
        console.log('📱 비밀번호 찾기 페이지 로딩...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('form', { timeout: 10000 });
        await page.waitForTimeout(2000);
        
        // 1. HTML에서 CSRF 토큰 확인
        const htmlCsrfToken = await page.evaluate(() => {
            const input = document.querySelector('input[name="csrf_token"]');
            return {
                exists: !!input,
                value: input ? input.value : null,
                type: input ? input.type : null,
                name: input ? input.name : null
            };
        });
        
        console.log('📄 HTML CSRF 토큰 정보:');
        console.log(`   존재: ${htmlCsrfToken.exists ? '✅' : '❌'}`);
        console.log(`   타입: ${htmlCsrfToken.type}`);
        console.log(`   이름: ${htmlCsrfToken.name}`);
        console.log(`   값: ${htmlCsrfToken.value ? htmlCsrfToken.value.substring(0, 20) + '...' : 'NULL'}`);
        
        // 2. 폼 데이터 생성 시뮬레이션
        const formDataTest = await page.evaluate(() => {
            const form = document.querySelector('form');
            if (!form) return null;
            
            const formData = new FormData(form);
            const entries = [];
            
            // FormData의 모든 항목 확인
            for (let [key, value] of formData.entries()) {
                entries.push({
                    key: key,
                    value: typeof value === 'string' ? 
                           (value.length > 50 ? value.substring(0, 20) + '...' : value) : 
                           '[FILE]'
                });
            }
            
            return {
                formExists: !!form,
                action: form.action,
                method: form.method,
                entries: entries
            };
        });
        
        console.log('\n📋 FormData 테스트:');
        console.log(`   폼 존재: ${formDataTest.formExists ? '✅' : '❌'}`);
        console.log(`   액션: ${formDataTest.action}`);
        console.log(`   메서드: ${formDataTest.method}`);
        console.log('   FormData 항목들:');
        formDataTest.entries.forEach((entry, idx) => {
            console.log(`      ${idx + 1}. ${entry.key}: ${entry.value}`);
        });
        
        // 3. 수동으로 전화번호 입력하고 FormData 재확인
        console.log('\n📝 전화번호 입력 후 FormData 재확인...');
        await page.fill('input[name="phone"]', '010-1234-5678');
        
        const formDataWithPhone = await page.evaluate(() => {
            const form = document.querySelector('form');
            const formData = new FormData(form);
            const entries = [];
            
            for (let [key, value] of formData.entries()) {
                entries.push({
                    key: key,
                    value: typeof value === 'string' ? 
                           (key === 'csrf_token' ? value.substring(0, 20) + '...' : value) : 
                           '[FILE]'
                });
            }
            
            return entries;
        });
        
        console.log('   전화번호 입력 후 FormData:');
        formDataWithPhone.forEach((entry, idx) => {
            console.log(`      ${idx + 1}. ${entry.key}: ${entry.value}`);
        });
        
        // 4. CSRF 토큰 값 비교
        const csrfFromHtml = htmlCsrfToken.value;
        const csrfFromFormData = formDataWithPhone.find(entry => entry.key === 'csrf_token')?.value;
        
        console.log('\n🔐 CSRF 토큰 비교:');
        console.log(`   HTML 토큰: ${csrfFromHtml ? csrfFromHtml.substring(0, 20) + '...' : 'NULL'}`);
        console.log(`   FormData 토큰: ${csrfFromFormData ? csrfFromFormData.replace('...', '') + '...' : 'NULL'}`);
        console.log(`   토큰 일치: ${csrfFromHtml && csrfFromFormData && csrfFromHtml.startsWith(csrfFromFormData.replace('...', '')) ? '✅' : '❌'}`);
        
        return {
            htmlToken: htmlCsrfToken,
            formData: formDataWithPhone
        };
        
    } catch (error) {
        console.error('❌ CSRF 디버깅 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
debugCsrfToken()
    .then(results => {
        console.log('\n📊 CSRF 토큰 디버깅 완료');
        
        if (results.htmlToken.exists) {
            const hasCSRFInFormData = results.formData.some(entry => entry.key === 'csrf_token');
            console.log(`HTML에 CSRF 토큰: ✅`);
            console.log(`FormData에 CSRF 토큰: ${hasCSRFInFormData ? '✅' : '❌'}`);
            
            if (!hasCSRFInFormData) {
                console.log('🚨 문제: CSRF 토큰이 FormData에 포함되지 않고 있습니다!');
                console.log('   - 폼 필드가 제대로 설정되지 않았을 수 있습니다.');
                console.log('   - JavaScript에서 FormData 생성 시 토큰이 누락될 수 있습니다.');
            }
        } else {
            console.log('❌ HTML에 CSRF 토큰이 없습니다!');
        }
    })
    .catch(error => {
        console.error('💥 CSRF 디버깅 실행 실패:', error);
        process.exit(1);
    });