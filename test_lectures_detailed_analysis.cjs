const { chromium } = require('playwright');

async function detailedMobileAnalysis() {
    console.log('🔍 강의일정 페이지 상세 모바일 분석 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const page = await browser.newPage();
    
    // 모바일 뷰포트 설정 (iPhone SE)
    await page.setViewportSize({ width: 375, height: 667 });
    await page.setExtraHTTPHeaders({
        'User-Agent': 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1'
    });
    
    try {
        await page.goto('https://www.topmktx.com/lectures', { 
            waitUntil: 'networkidle',
            timeout: 30000 
        });
        
        await page.waitForTimeout(2000);
        
        // 상세 분석 실행
        const analysis = await page.evaluate(() => {
            const results = {
                accessibility: {},
                touchTargets: {},
                typography: {},
                layout: {},
                interaction: {}
            };
            
            // 1. 접근성 분석
            results.accessibility = {
                imagesWithoutAlt: Array.from(document.querySelectorAll('img:not([alt])')).length,
                linksWithoutText: Array.from(document.querySelectorAll('a')).filter(a => !a.textContent.trim() && !a.getAttribute('aria-label')).length,
                buttonsWithoutText: Array.from(document.querySelectorAll('button')).filter(btn => !btn.textContent.trim() && !btn.getAttribute('aria-label')).length,
                headingStructure: Array.from(document.querySelectorAll('h1,h2,h3,h4,h5,h6')).map(h => ({
                    tag: h.tagName.toLowerCase(),
                    text: h.textContent.trim()
                }))
            };
            
            // 2. 터치 타겟 상세 분석
            const interactiveElements = document.querySelectorAll('button, a, input, select, textarea, [onclick], [role="button"], .btn');
            results.touchTargets = {
                elements: [],
                critical: [],
                recommendations: []
            };
            
            interactiveElements.forEach((element, index) => {
                const rect = element.getBoundingClientRect();
                const computedStyle = window.getComputedStyle(element);
                
                if (rect.width > 0 && rect.height > 0 && computedStyle.display !== 'none') {
                    const elementInfo = {
                        index: index,
                        tag: element.tagName.toLowerCase(),
                        className: element.className,
                        text: element.textContent?.trim().substring(0, 30) || '',
                        width: Math.round(rect.width),
                        height: Math.round(rect.height),
                        area: Math.round(rect.width * rect.height),
                        x: Math.round(rect.x),
                        y: Math.round(rect.y),
                        padding: computedStyle.padding,
                        margin: computedStyle.margin,
                        isCritical: rect.width < 44 || rect.height < 44
                    };
                    
                    results.touchTargets.elements.push(elementInfo);
                    
                    if (elementInfo.isCritical) {
                        results.touchTargets.critical.push(elementInfo);
                    }
                }
            });
            
            // 3. 타이포그래피 분석
            const textElements = document.querySelectorAll('*');
            const fontSizeDistribution = new Map();
            const lineHeightIssues = [];
            
            textElements.forEach(element => {
                const style = window.getComputedStyle(element);
                const fontSize = parseInt(style.fontSize);
                const lineHeight = parseFloat(style.lineHeight);
                
                if (fontSize && style.display !== 'none' && element.textContent.trim()) {
                    const count = fontSizeDistribution.get(fontSize) || 0;
                    fontSizeDistribution.set(fontSize, count + 1);
                    
                    // 줄간격 문제 체크 (일반적으로 1.2-1.6 권장)
                    if (lineHeight && (lineHeight < fontSize * 1.2 || lineHeight > fontSize * 1.8)) {
                        lineHeightIssues.push({
                            tag: element.tagName.toLowerCase(),
                            fontSize: fontSize,
                            lineHeight: lineHeight,
                            ratio: (lineHeight / fontSize).toFixed(2),
                            text: element.textContent.trim().substring(0, 20)
                        });
                    }
                }
            });
            
            results.typography = {
                fontSizeDistribution: Array.from(fontSizeDistribution.entries()).map(([size, count]) => ({ size, count })),
                smallFonts: Array.from(fontSizeDistribution.entries()).filter(([size]) => size < 16).map(([size, count]) => ({ size, count })),
                lineHeightIssues: lineHeightIssues.slice(0, 5),
                readabilityScore: fontSizeDistribution.has(16) || fontSizeDistribution.has(18) ? 'good' : 'needs-improvement'
            };
            
            // 4. 레이아웃 분석
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const scrollWidth = document.documentElement.scrollWidth;
            const scrollHeight = document.documentElement.scrollHeight;
            
            results.layout = {
                viewport: { width: viewportWidth, height: viewportHeight },
                document: { width: scrollWidth, height: scrollHeight },
                hasHorizontalScroll: scrollWidth > viewportWidth,
                hasVerticalScroll: scrollHeight > viewportHeight,
                overflowElements: [],
                narrowElements: []
            };
            
            // 오버플로우 요소 찾기
            document.querySelectorAll('*').forEach(element => {
                const rect = element.getBoundingClientRect();
                if (rect.width > viewportWidth + 10) { // 10px 여유분
                    results.layout.overflowElements.push({
                        tag: element.tagName.toLowerCase(),
                        className: element.className,
                        width: Math.round(rect.width),
                        computedWidth: window.getComputedStyle(element).width
                    });
                }
                
                // 너무 좁은 클릭 가능 요소
                if ((element.tagName === 'A' || element.tagName === 'BUTTON') && rect.width < 48) {
                    results.layout.narrowElements.push({
                        tag: element.tagName.toLowerCase(),
                        text: element.textContent.trim().substring(0, 20),
                        width: Math.round(rect.width)
                    });
                }
            });
            
            // 5. 인터랙션 분석
            results.interaction = {
                formElements: document.querySelectorAll('form').length,
                inputElements: document.querySelectorAll('input, textarea, select').length,
                navigationElements: document.querySelectorAll('nav, .nav, .navbar').length,
                menuButtons: document.querySelectorAll('.menu-toggle, .hamburger, [aria-label*="menu"]').length,
                modalTriggers: document.querySelectorAll('[data-toggle="modal"], .modal-trigger').length
            };
            
            return results;
        });
        
        // 결과 리포트 출력
        console.log('\n📊 강의일정 페이지 상세 모바일 분석 결과');
        console.log('='.repeat(60));
        
        // 접근성 분석
        console.log('\n🎯 접근성 (Accessibility)');
        console.log(`   alt 없는 이미지: ${analysis.accessibility.imagesWithoutAlt}개`);
        console.log(`   텍스트 없는 링크: ${analysis.accessibility.linksWithoutText}개`);
        console.log(`   텍스트 없는 버튼: ${analysis.accessibility.buttonsWithoutText}개`);
        console.log(`   제목 구조: ${analysis.accessibility.headingStructure.length}개`);
        
        // 터치 타겟 분석
        console.log('\n👆 터치 타겟 분석');
        console.log(`   전체 인터랙티브 요소: ${analysis.touchTargets.elements.length}개`);
        console.log(`   44px 미만 요소: ${analysis.touchTargets.critical.length}개`);
        
        if (analysis.touchTargets.critical.length > 0) {
            console.log('   ⚠️ 개선 필요한 터치 타겟:');
            analysis.touchTargets.critical.slice(0, 5).forEach(target => {
                console.log(`      - ${target.tag}(${target.className}): ${target.width}x${target.height}px "${target.text}"`);
            });
        }
        
        // 타이포그래피 분석
        console.log('\n📝 타이포그래피');
        console.log(`   폰트 크기 종류: ${analysis.typography.fontSizeDistribution.length}개`);
        console.log(`   16px 미만 폰트: ${analysis.typography.smallFonts.length}개`);
        console.log(`   가독성 점수: ${analysis.typography.readabilityScore}`);
        
        if (analysis.typography.smallFonts.length > 0) {
            console.log('   ⚠️ 작은 폰트들:');
            analysis.typography.smallFonts.forEach(font => {
                console.log(`      - ${font.size}px: ${font.count}개 요소`);
            });
        }
        
        // 레이아웃 분석
        console.log('\n📐 레이아웃');
        console.log(`   뷰포트: ${analysis.layout.viewport.width}x${analysis.layout.viewport.height}px`);
        console.log(`   문서 크기: ${analysis.layout.document.width}x${analysis.layout.document.height}px`);
        console.log(`   수평 스크롤: ${analysis.layout.hasHorizontalScroll ? '❌ 있음' : '✅ 없음'}`);
        console.log(`   오버플로우 요소: ${analysis.layout.overflowElements.length}개`);
        console.log(`   좁은 클릭 영역: ${analysis.layout.narrowElements.length}개`);
        
        // 인터랙션 분석
        console.log('\n🖱️ 인터랙션');
        console.log(`   폼 요소: ${analysis.interaction.formElements}개`);
        console.log(`   입력 필드: ${analysis.interaction.inputElements}개`);
        console.log(`   네비게이션: ${analysis.interaction.navigationElements}개`);
        console.log(`   메뉴 버튼: ${analysis.interaction.menuButtons}개`);
        
        // 전체 평가
        console.log('\n🎯 전체 평가');
        const issues = [];
        
        if (analysis.layout.hasHorizontalScroll) issues.push('수평 스크롤 발생');
        if (analysis.touchTargets.critical.length > 5) issues.push('터치 타겟 크기 부족');
        if (analysis.typography.smallFonts.length > 2) issues.push('폰트 크기 개선 필요');
        if (analysis.accessibility.imagesWithoutAlt > 0) issues.push('이미지 접근성 개선 필요');
        
        if (issues.length === 0) {
            console.log('   ✅ 전반적으로 양호한 모바일 반응형 상태');
        } else {
            console.log(`   ⚠️ 개선 권장 사항: ${issues.length}개`);
            issues.forEach(issue => console.log(`      - ${issue}`));
        }
        
        console.log('\n✅ 상세 분석 완료!');
        
        return analysis;
        
    } catch (error) {
        console.error('❌ 상세 분석 중 오류 발생:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
detailedMobileAnalysis().catch(console.error);