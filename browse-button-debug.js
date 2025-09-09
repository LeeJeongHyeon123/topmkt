import { chromium } from 'playwright';

async function debugBrowseButtonIssue() {
    console.log('🔍 "둘러보기" 버튼 무한 로딩 디버깅 시작...\n');
    
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    // 네트워크 요청 모니터링
    const requests = [];
    page.on('request', request => {
        requests.push({
            url: request.url(),
            method: request.method(),
            timestamp: Date.now()
        });
        console.log(`📡 요청: ${request.method()} ${request.url()}`);
    });
    
    page.on('response', response => {
        console.log(`📥 응답: ${response.status()} ${response.url()}`);
    });
    
    // 콘솔 로그 캡처
    page.on('console', msg => {
        console.log(`[BROWSER] ${msg.text()}`);
    });
    
    try {
        // 1. 메인 페이지 방문
        console.log('1️⃣ 메인 페이지 방문...');
        await page.goto('https://www.topmktx.com/', { 
            waitUntil: 'networkidle',
            timeout: 10000
        });
        
        // 2. 로딩 상태 확인 (클릭 전)
        console.log('2️⃣ 클릭 전 로딩 상태 확인...');
        const loadingBeforeClick = await page.evaluate(() => {
            return {
                loadingVisible: document.querySelector('.loading-overlay').style.display !== 'none',
                isLoading: window.topMarketingLoader ? window.topMarketingLoader.isLoading : false,
                activeRequests: window.topMarketingLoader ? window.topMarketingLoader.activeRequests : 0
            };
        });
        console.log('클릭 전 상태:', loadingBeforeClick);
        
        // 3. "둘러보기" 버튼 요소 정보 상세 분석
        console.log('3️⃣ "둘러보기" 버튼 상세 분석...');
        const buttonInfo = await page.evaluate(() => {
            const button = document.querySelector('a[href="#features"]');
            return {
                exists: !!button,
                href: button?.href,
                textContent: button?.textContent?.trim(),
                className: button?.className,
                boundingBox: button ? {
                    x: button.getBoundingClientRect().x,
                    y: button.getBoundingClientRect().y,
                    width: button.getBoundingClientRect().width,
                    height: button.getBoundingClientRect().height
                } : null
            };
        });
        console.log('둘러보기 버튼 정보:', buttonInfo);
        
        // 4. #features 섹션 정보
        const featuresInfo = await page.evaluate(() => {
            const features = document.querySelector('#features');
            return {
                exists: !!features,
                offsetTop: features?.offsetTop,
                scrollHeight: features?.scrollHeight,
                boundingBox: features ? {
                    x: features.getBoundingClientRect().x,
                    y: features.getBoundingClientRect().y,
                    width: features.getBoundingClientRect().width,
                    height: features.getBoundingClientRect().height
                } : null
            };
        });
        console.log('#features 섹션 정보:', featuresInfo);
        
        // 5. 버튼 클릭 및 즉시 상태 확인
        console.log('5️⃣ 둘러보기 버튼 클릭...');
        const clickStartTime = Date.now();
        
        // 클릭 전 이벤트 리스너 추가
        await page.evaluate(() => {
            window.clickDebugLog = [];
            
            // 모든 클릭 이벤트를 캐치
            document.addEventListener('click', (e) => {
                window.clickDebugLog.push({
                    timestamp: Date.now(),
                    target: e.target.tagName,
                    href: e.target.href || 'N/A',
                    defaultPrevented: e.defaultPrevented
                });
            }, true);
        });
        
        await page.click('a[href="#features"]');
        
        // 6. 클릭 직후 상태 확인 (단계별로 매우 세밀하게)
        const statusChecks = [];
        for (let i = 0; i < 10; i++) {
            const delay = i * 200; // 0ms, 200ms, 400ms, ... 2000ms
            await page.waitForTimeout(200);
            
            const status = await page.evaluate(() => {
                const overlay = document.querySelector('.loading-overlay');
                return {
                    timestamp: Date.now(),
                    loadingDisplay: overlay?.style.display,
                    loadingVisible: overlay?.offsetParent !== null,
                    scrollY: window.scrollY,
                    isLoading: window.topMarketingLoader ? window.topMarketingLoader.isLoading : false,
                    activeRequests: window.topMarketingLoader ? window.topMarketingLoader.activeRequests : 0,
                    url: window.location.href,
                    clickLog: window.clickDebugLog || []
                };
            });
            
            statusChecks.push({
                delay: delay + 200,
                status: status
            });
            
            console.log(`⏱️  ${delay + 200}ms 후 상태:`, {
                로딩표시: status.loadingVisible,
                스크롤위치: status.scrollY,
                활성요청: status.activeRequests,
                로딩중: status.isLoading
            });
            
            // 로딩이 끝났다면 루프 종료
            if (!status.isLoading && !status.loadingVisible) {
                console.log('✅ 로딩이 완료되었습니다.');
                break;
            }
        }
        
        // 7. 최종 상태 확인
        console.log('7️⃣ 최종 상태 확인...');
        const finalStatus = await page.evaluate(() => {
            const overlay = document.querySelector('.loading-overlay');
            const features = document.querySelector('#features');
            
            return {
                currentUrl: window.location.href,
                scrollY: window.scrollY,
                featuresPosition: features ? features.getBoundingClientRect().y : null,
                loadingVisible: overlay?.offsetParent !== null,
                isLoading: window.topMarketingLoader ? window.topMarketingLoader.isLoading : false,
                activeRequests: window.topMarketingLoader ? window.topMarketingLoader.activeRequests : 0,
                loadingClasses: overlay?.className,
                loadingStyle: overlay?.style.cssText,
                clickDebugLog: window.clickDebugLog || []
            };
        });
        
        console.log('최종 상태:', finalStatus);
        
        // 8. 스크린샷 촬영
        await page.screenshot({ 
            path: '/var/www/html/topmkt/browse-button-debug-result.png',
            fullPage: true
        });
        
        // 9. 분석 결과
        console.log('\n📊 분석 결과:');
        console.log('='.repeat(50));
        
        console.log('1. 버튼 정보:', buttonInfo);
        console.log('2. #features 섹션:', featuresInfo);
        console.log('3. 클릭 후 상태 변화:');
        statusChecks.forEach((check, index) => {
            console.log(`   ${check.delay}ms: 로딩=${check.status.isLoading}, 표시=${check.status.loadingVisible}, 스크롤=${check.status.scrollY}`);
        });
        
        // 10. 문제점 진단
        console.log('\n🔍 문제점 진단:');
        
        if (finalStatus.loadingVisible || finalStatus.isLoading) {
            console.log('❌ 무한 로딩 확인됨!');
            console.log('   - 로딩 표시 중:', finalStatus.loadingVisible);
            console.log('   - 로딩 상태:', finalStatus.isLoading);
            console.log('   - 활성 요청:', finalStatus.activeRequests);
            console.log('   - 로딩 클래스:', finalStatus.loadingClasses);
        } else {
            console.log('✅ 로딩이 정상적으로 완료됨');
        }
        
        // #features로의 스크롤 확인
        if (featuresInfo.exists && featuresInfo.boundingBox) {
            const scrolledCorrectly = Math.abs(finalStatus.scrollY - featuresInfo.boundingBox.y) < 100;
            if (scrolledCorrectly) {
                console.log('✅ #features 섹션으로 올바르게 스크롤됨');
            } else {
                console.log('❌ #features 섹션으로 스크롤되지 않음');
                console.log(`   현재 스크롤: ${finalStatus.scrollY}px`);
                console.log(`   #features 위치: ${featuresInfo.boundingBox.y}px`);
            }
        }
        
        // 네트워크 요청 분석
        console.log('\n📡 네트워크 요청 분석:');
        const recentRequests = requests.filter(req => 
            req.timestamp > clickStartTime - 1000 && req.timestamp < clickStartTime + 5000
        );
        if (recentRequests.length > 0) {
            console.log('클릭 시점 전후 요청들:');
            recentRequests.forEach(req => {
                console.log(`   ${req.method} ${req.url}`);
            });
        } else {
            console.log('클릭과 관련된 네트워크 요청 없음');
        }
        
    } catch (error) {
        console.error('❌ 오류 발생:', error.message);
    }
    
    await browser.close();
    console.log('\n✅ 디버깅 완료!');
}

// 실행
debugBrowseButtonIssue().catch(console.error);