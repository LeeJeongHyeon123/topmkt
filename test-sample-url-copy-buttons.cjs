/**
 * Sample 페이지 URL 복사 버튼들 동작 테스트
 */

const { chromium } = require('playwright');

async function testURLCopyButtons() {
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'] 
    });
    
    try {
        const page = await browser.newPage();
        
        console.log('📄 Sample 페이지 접속 중...');
        await page.goto('https://www.topmktx.com/sample');
        await page.waitForTimeout(3000);
        
        // 현재 URL 확인
        const currentURL = await page.url();
        console.log('🌐 현재 페이지 URL:', currentURL);
        
        // 배너 버튼과 하단 버튼 존재 여부 확인
        const bannerBtn = await page.$('.banner-copy-btn');
        const bottomBtn = await page.$('.copy-btn');
        
        console.log('\n🔍 버튼 존재 여부:');
        console.log('   배너 URL 복사 버튼:', bannerBtn ? '✅ 존재' : '❌ 없음');
        console.log('   하단 URL 복사 버튼:', bottomBtn ? '✅ 존재' : '❌ 없음');
        
        if (bannerBtn) {
            console.log('\n🧪 배너 버튼 테스트 중...');
            
            // 버튼 텍스트 확인
            const bannerBtnText = await page.$eval('.banner-copy-btn', el => el.textContent);
            console.log('   초기 텍스트:', bannerBtnText);
            
            // 클릭 테스트 (JavaScript 실행하여 클립보드 내용 확인)
            await page.evaluate(() => {
                // 클립보드 API 모킹
                window.testClipboard = '';
                navigator.clipboard = {
                    writeText: function(text) {
                        window.testClipboard = text;
                        return Promise.resolve();
                    }
                };
            });
            
            await page.click('.banner-copy-btn');
            await page.waitForTimeout(500);
            
            // 클립보드 내용 확인
            const copiedURL = await page.evaluate(() => window.testClipboard);
            console.log('   복사된 URL:', copiedURL);
            console.log('   URL 올바름:', copiedURL === currentURL ? '✅ 정확' : '❌ 불일치');
            
            // 버튼 상태 변화 확인
            const btnTextAfterClick = await page.$eval('.banner-copy-btn', el => el.textContent);
            console.log('   클릭 후 텍스트:', btnTextAfterClick);
            console.log('   상태 변화:', btnTextAfterClick.includes('복사완료') ? '✅ 성공 메시지 표시' : '❌ 상태 변화 없음');
            
            // 2초 후 원래 텍스트로 복원되는지 확인
            await page.waitForTimeout(2500);
            const btnTextAfterDelay = await page.$eval('.banner-copy-btn', el => el.textContent);
            console.log('   2초 후 텍스트:', btnTextAfterDelay);
            console.log('   텍스트 복원:', btnTextAfterDelay === bannerBtnText ? '✅ 정상 복원' : '❌ 복원 실패');
        }
        
        if (bottomBtn) {
            console.log('\n🧪 하단 버튼 테스트 중...');
            
            // 하단 버튼까지 스크롤
            await page.evaluate(() => {
                document.querySelector('.copy-btn').scrollIntoView({ behavior: 'smooth' });
            });
            await page.waitForTimeout(1000);
            
            // 버튼 텍스트 확인
            const bottomBtnText = await page.$eval('.copy-btn', el => el.textContent);
            console.log('   초기 텍스트:', bottomBtnText);
            
            // 클립보드 리셋
            await page.evaluate(() => { window.testClipboard = ''; });
            
            await page.click('.copy-btn');
            await page.waitForTimeout(500);
            
            // 클립보드 내용 확인
            const copiedURL2 = await page.evaluate(() => window.testClipboard);
            console.log('   복사된 URL:', copiedURL2);
            console.log('   URL 올바름:', copiedURL2 === currentURL ? '✅ 정확' : '❌ 불일치');
            
            // 버튼 상태 변화 확인
            const btnTextAfterClick2 = await page.$eval('.copy-btn', el => el.textContent);
            console.log('   클릭 후 텍스트:', btnTextAfterClick2);
            console.log('   상태 변화:', btnTextAfterClick2.includes('복사완료') ? '✅ 성공 메시지 표시' : '❌ 상태 변화 없음');
            
            // 2초 후 원래 텍스트로 복원되는지 확인
            await page.waitForTimeout(2500);
            const btnTextAfterDelay2 = await page.$eval('.copy-btn', el => el.textContent);
            console.log('   2초 후 텍스트:', btnTextAfterDelay2);
            console.log('   텍스트 복원:', btnTextAfterDelay2 === bottomBtnText ? '✅ 정상 복원' : '❌ 복원 실패');
        }
        
        // 스크린샷 촬영
        await page.screenshot({ 
            path: '/var/www/html/topmkt/sample-url-buttons-test.png',
            fullPage: true
        });
        
        console.log('\n📷 테스트 스크린샷: sample-url-buttons-test.png');
        
        // URL 정확성 최종 검증
        console.log('\n📋 URL 정확성 최종 검증:');
        console.log('   현재 페이지:', currentURL);
        console.log('   예상 URL: https://www.topmktx.com/sample');
        console.log('   URL 매치:', currentURL === 'https://www.topmktx.com/sample' ? '✅ 완전 일치' : '❌ 불일치');
        
        return {
            currentURL,
            bannerBtnExists: !!bannerBtn,
            bottomBtnExists: !!bottomBtn
        };
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error);
        return { error: error.message };
    } finally {
        await browser.close();
    }
}

testURLCopyButtons().then(result => {
    console.log('\n🏁 테스트 완료');
});