import { chromium } from 'playwright';

async function testBrowseButton() {
    console.log('🚀 탑마케팅 "둘러보기" 버튼 테스트 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 },
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });
        
        const page = await context.newPage();
        
        // 1. 메인 페이지 방문
        console.log('📍 1. 메인 페이지 방문 중...');
        await page.goto('https://www.topmktx.com/', { 
            waitUntil: 'networkidle',
            timeout: 30000 
        });
        
        // 2. 페이지 완전 로드 대기 (3초)
        console.log('⏱️  2. 페이지 완전 로드 대기 (3초)...');
        await page.waitForTimeout(3000);
        
        // 3. "둘러보기" 버튼 요소 찾기
        console.log('🔍 3. "둘러보기" 버튼 요소 찾기...');
        
        // 여러 가능한 셀렉터로 버튼 찾기
        let browseButton = null;
        const selectors = [
            '.btn-ghost',
            'a[href="/community"]',
            'button:has-text("둘러보기")',
            'a:has-text("둘러보기")',
            '.btn:has-text("둘러보기")'
        ];
        
        for (const selector of selectors) {
            try {
                browseButton = await page.locator(selector).first();
                if (await browseButton.count() > 0) {
                    console.log(`✅ 버튼 발견: ${selector}`);
                    break;
                }
            } catch (e) {
                // 다음 셀렉터 시도
            }
        }
        
        if (!browseButton || await browseButton.count() === 0) {
            console.log('❌ "둘러보기" 버튼을 찾을 수 없습니다.');
            
            // 페이지의 모든 버튼과 링크 출력
            const allButtons = await page.locator('button, a').all();
            console.log('📋 페이지의 모든 버튼/링크:');
            for (let i = 0; i < Math.min(allButtons.length, 10); i++) {
                const text = await allButtons[i].textContent();
                const href = await allButtons[i].getAttribute('href');
                console.log(`   - 텍스트: "${text?.trim()}", href: ${href}`);
            }
            return;
        }
        
        // 4. 버튼의 href 속성 확인
        console.log('🔗 4. 버튼의 href 속성 확인...');
        const href = await browseButton.getAttribute('href');
        const buttonText = await browseButton.textContent();
        console.log(`   - 버튼 텍스트: "${buttonText?.trim()}"`);
        console.log(`   - href 속성: ${href}`);
        
        if (href === '/community') {
            console.log('✅ href가 올바르게 /community로 설정됨');
        } else {
            console.log(`⚠️  href가 예상과 다름: ${href} (예상: /community)`);
        }
        
        // 5. "둘러보기" 버튼 클릭
        console.log('👆 5. "둘러보기" 버튼 클릭...');
        
        // 네트워크 대기를 위한 Promise 설정
        const navigationPromise = page.waitForURL('**/community*', { timeout: 30000 });
        
        await browseButton.click();
        
        // 페이지 이동 대기
        try {
            await navigationPromise;
            console.log('✅ 페이지 이동 성공');
        } catch (e) {
            console.log('⚠️  페이지 이동 대기 중 타임아웃 - 현재 URL 확인');
        }
        
        // 6. 페이지 이동 후 3초 대기
        console.log('⏱️  6. 페이지 이동 후 3초 대기...');
        await page.waitForTimeout(3000);
        
        // 7. 현재 URL 확인
        console.log('🌐 7. 현재 URL 확인...');
        const currentUrl = page.url();
        console.log(`   - 현재 URL: ${currentUrl}`);
        
        const isCommunityPage = currentUrl.includes('/community');
        if (isCommunityPage) {
            console.log('✅ 커뮤니티 페이지로 정상 이동 완료');
        } else {
            console.log('❌ 커뮤니티 페이지로 이동하지 않음');
        }
        
        // 8. 커뮤니티 페이지 로딩 완료 스크린샷 캡처
        console.log('📸 8. 커뮤니티 페이지 스크린샷 캡처...');
        await page.screenshot({ 
            path: '/var/www/html/topmkt/browse-button-fixed-community.png',
            fullPage: true 
        });
        console.log('✅ 스크린샷 저장 완료: browse-button-fixed-community.png');
        
        // 결과 요약
        console.log('\n📊 테스트 결과 요약:');
        console.log(`   - 버튼 발견: ✅`);
        console.log(`   - href 속성: ${href === '/community' ? '✅' : '❌'} (${href})`);
        console.log(`   - 페이지 이동: ${isCommunityPage ? '✅' : '❌'}`);
        console.log(`   - 최종 URL: ${currentUrl}`);
        
        if (isCommunityPage && href === '/community') {
            console.log('🎉 모든 테스트 통과! "둘러보기" 버튼이 정상 작동합니다.');
        } else {
            console.log('⚠️  일부 테스트 실패 - 추가 검토 필요');
        }
        
    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error.message);
        if (error.stack) {
            console.error('스택 추적:', error.stack);
        }
    } finally {
        await browser.close();
        console.log('🏁 브라우저 종료 및 테스트 완료');
    }
}

// 테스트 실행
testBrowseButton().catch(console.error);