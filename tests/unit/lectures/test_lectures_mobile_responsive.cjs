const { chromium } = require('playwright');

async function testLecturesResponsive() {
    console.log('🚀 강의일정 페이지 모바일 반응형 테스트 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const page = await browser.newPage();
    
    // 기본 설정
    await page.setExtraHTTPHeaders({
        'User-Agent': 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1'
    });
    
    const testResults = {
        mobile: {},
        tablet: {},
        smallMobile: {}
    };
    
    const viewports = [
        { name: 'mobile', width: 375, height: 667, label: 'iPhone SE' },
        { name: 'tablet', width: 768, height: 1024, label: 'iPad' },
        { name: 'smallMobile', width: 320, height: 568, label: 'iPhone 5' }
    ];
    
    try {
        for (const viewport of viewports) {
            console.log(`\n📱 ${viewport.label} (${viewport.width}x${viewport.height}) 테스트 중...`);
            
            // 뷰포트 설정
            await page.setViewportSize({ 
                width: viewport.width, 
                height: viewport.height 
            });
            
            // 강의일정 페이지 접속
            await page.goto('https://www.topmktx.com/lectures', { 
                waitUntil: 'networkidle',
                timeout: 30000 
            });
            
            // 페이지 로드 대기
            await page.waitForTimeout(2000);
            
            const result = testResults[viewport.name];
            
            // 1. 페이지 기본 정보 수집
            const pageInfo = await page.evaluate(() => {
                return {
                    title: document.title,
                    scrollWidth: document.documentElement.scrollWidth,
                    clientWidth: document.documentElement.clientWidth,
                    hasHorizontalScroll: document.documentElement.scrollWidth > document.documentElement.clientWidth
                };
            });
            
            result.pageInfo = pageInfo;
            result.hasHorizontalScroll = pageInfo.hasHorizontalScroll;
            
            console.log(`   수평 스크롤: ${result.hasHorizontalScroll ? '❌ 있음' : '✅ 없음'}`);
            console.log(`   페이지 너비: ${pageInfo.scrollWidth}px (뷰포트: ${pageInfo.clientWidth}px)`);
            
            // 2. 터치 타겟 크기 검사 (버튼, 링크 등)
            const touchTargets = await page.evaluate(() => {
                const interactiveElements = document.querySelectorAll('button, a, input[type="button"], input[type="submit"], .btn, [onclick]');
                const results = [];
                
                interactiveElements.forEach((element, index) => {
                    const rect = element.getBoundingClientRect();
                    const computedStyle = window.getComputedStyle(element);
                    
                    if (rect.width > 0 && rect.height > 0) {
                        results.push({
                            index: index,
                            tagName: element.tagName.toLowerCase(),
                            className: element.className,
                            width: Math.round(rect.width),
                            height: Math.round(rect.height),
                            text: element.textContent?.trim().substring(0, 20) || '',
                            isVisible: computedStyle.display !== 'none' && computedStyle.visibility !== 'hidden'
                        });
                    }
                });
                
                return results;
            });
            
            const smallTouchTargets = touchTargets.filter(target => 
                target.isVisible && (target.width < 44 || target.height < 44)
            );
            
            result.touchTargets = {
                total: touchTargets.length,
                smallTargets: smallTouchTargets.length,
                smallTargetsList: smallTouchTargets
            };
            
            console.log(`   터치 타겟: 총 ${touchTargets.length}개, 44px 미만: ${smallTouchTargets.length}개`);
            
            // 3. 폰트 크기 검사
            const fontSizes = await page.evaluate(() => {
                const textElements = document.querySelectorAll('p, span, div, h1, h2, h3, h4, h5, h6, a, button, input, label');
                const fontSizeMap = new Map();
                
                textElements.forEach(element => {
                    const computedStyle = window.getComputedStyle(element);
                    const fontSize = parseInt(computedStyle.fontSize);
                    
                    if (fontSize && computedStyle.display !== 'none') {
                        const count = fontSizeMap.get(fontSize) || 0;
                        fontSizeMap.set(fontSize, count + 1);
                    }
                });
                
                return Array.from(fontSizeMap.entries()).map(([size, count]) => ({ size, count }));
            });
            
            const smallFonts = fontSizes.filter(font => font.size < 16);
            result.fontSizes = {
                allSizes: fontSizes,
                smallFonts: smallFonts,
                hasSmallFonts: smallFonts.length > 0
            };
            
            console.log(`   폰트 크기: 16px 미만 발견 ${smallFonts.length}개`);
            
            // 4. 주요 UI 요소 확인
            const uiElements = await page.evaluate(() => {
                const elements = {
                    header: document.querySelector('header, .header, .navbar'),
                    navigation: document.querySelector('nav, .nav, .navigation'),
                    mainContent: document.querySelector('main, .main-content, .content'),
                    sidebar: document.querySelector('.sidebar, .side-nav'),
                    footer: document.querySelector('footer, .footer'),
                    buttons: document.querySelectorAll('button, .btn'),
                    forms: document.querySelectorAll('form'),
                    tables: document.querySelectorAll('table')
                };
                
                const results = {};
                
                Object.keys(elements).forEach(key => {
                    if (elements[key]) {
                        if (elements[key].length !== undefined) {
                            // NodeList인 경우
                            results[key] = {
                                count: elements[key].length,
                                visible: Array.from(elements[key]).filter(el => {
                                    const style = window.getComputedStyle(el);
                                    return style.display !== 'none' && style.visibility !== 'hidden';
                                }).length
                            };
                        } else {
                            // 단일 요소인 경우
                            const rect = elements[key].getBoundingClientRect();
                            const style = window.getComputedStyle(elements[key]);
                            results[key] = {
                                exists: true,
                                visible: style.display !== 'none' && style.visibility !== 'hidden',
                                width: Math.round(rect.width),
                                height: Math.round(rect.height)
                            };
                        }
                    } else {
                        results[key] = { exists: false };
                    }
                });
                
                return results;
            });
            
            result.uiElements = uiElements;
            
            // 5. 테이블 반응형 확인
            if (uiElements.tables && uiElements.tables.count > 0) {
                const tableInfo = await page.evaluate(() => {
                    const tables = document.querySelectorAll('table');
                    return Array.from(tables).map((table, index) => {
                        const rect = table.getBoundingClientRect();
                        const parent = table.parentElement;
                        const parentRect = parent.getBoundingClientRect();
                        
                        return {
                            index: index,
                            width: Math.round(rect.width),
                            parentWidth: Math.round(parentRect.width),
                            overflows: rect.width > parentRect.width,
                            hasHorizontalScroll: table.scrollWidth > table.clientWidth
                        };
                    });
                });
                
                result.tableInfo = tableInfo;
                console.log(`   테이블: ${tableInfo.length}개, 오버플로우: ${tableInfo.filter(t => t.overflows).length}개`);
            }
            
            // 6. 스크린샷 저장
            const screenshotPath = `/var/www/html/topmkt/lectures_${viewport.name}_current.png`;
            await page.screenshot({ 
                path: screenshotPath,
                fullPage: true,
                type: 'png'
            });
            
            console.log(`   스크린샷 저장: lectures_${viewport.name}_current.png`);
            
            // 추가 대기 (다음 뷰포트 테스트 전)
            await page.waitForTimeout(1000);
        }
        
    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
    
    // 결과 리포트 생성
    console.log('\n📊 테스트 결과 요약:');
    console.log('='.repeat(50));
    
    for (const [viewportName, result] of Object.entries(testResults)) {
        const viewport = viewports.find(v => v.name === viewportName);
        console.log(`\n📱 ${viewport.label} (${viewport.width}x${viewport.height})`);
        console.log(`   수평 스크롤: ${result.hasHorizontalScroll ? '❌' : '✅'}`);
        console.log(`   작은 터치 타겟: ${result.touchTargets?.smallTargets || 0}개`);
        console.log(`   16px 미만 폰트: ${result.fontSizes?.smallFonts?.length || 0}개`);
        
        if (result.touchTargets?.smallTargets > 0) {
            console.log('   ⚠️ 작은 터치 타겟들:');
            result.touchTargets.smallTargetsList.slice(0, 3).forEach(target => {
                console.log(`      - ${target.tagName} (${target.width}x${target.height}px): "${target.text}"`);
            });
        }
        
        if (result.fontSizes?.smallFonts?.length > 0) {
            console.log('   ⚠️ 작은 폰트들:');
            result.fontSizes.smallFonts.slice(0, 3).forEach(font => {
                console.log(`      - ${font.size}px (${font.count}개 요소)`);
            });
        }
    }
    
    console.log('\n✅ 강의일정 페이지 모바일 반응형 테스트 완료!');
    
    return testResults;
}

// 실행
testLecturesResponsive().catch(console.error);