const { chromium } = require('playwright');

async function analyzeFooterMobile() {
    console.log('📱 footer-mobile 영역 시각적 분석 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        // 모바일 뷰포트로 설정
        const page = await browser.newPage();
        await page.setViewportSize({ width: 375, height: 667 }); // iPhone SE
        
        console.log('📄 모바일로 페이지 로딩 중...');
        await page.goto('https://www.topmktx.com/auth/forgot-password', { 
            waitUntil: 'networkidle' 
        });
        
        await page.waitForTimeout(3000);
        
        console.log('📸 모바일 footer 분석용 스크린샷 촬영...');
        await page.screenshot({ 
            path: 'footer-mobile-analysis.png',
            fullPage: true 
        });
        
        console.log('🔍 footer-mobile 영역 상세 분석 중...');
        const footerAnalysis = await page.evaluate(() => {
            // footer 관련 요소들 찾기
            const footerElements = {
                footer: document.querySelector('footer'),
                footerMobile: document.querySelector('.footer-mobile'),
                footerContent: document.querySelector('.footer-content'),
                footerLinks: document.querySelectorAll('footer a'),
                footerText: document.querySelectorAll('footer p, footer span, footer div')
            };
            
            const getStyles = (element) => {
                if (!element) return null;
                const styles = window.getComputedStyle(element);
                const rect = element.getBoundingClientRect();
                return {
                    display: styles.display,
                    visibility: styles.visibility,
                    opacity: styles.opacity,
                    color: styles.color,
                    backgroundColor: styles.backgroundColor,
                    fontSize: styles.fontSize,
                    fontWeight: styles.fontWeight,
                    padding: styles.padding,
                    margin: styles.margin,
                    height: styles.height,
                    position: styles.position,
                    zIndex: styles.zIndex,
                    width: rect.width,
                    height: rect.height,
                    top: rect.top,
                    left: rect.left,
                    visible: rect.width > 0 && rect.height > 0,
                    inViewport: rect.top < window.innerHeight && rect.bottom > 0
                };
            };
            
            const getTextContent = (element) => {
                return element ? element.textContent.trim().substring(0, 50) + '...' : null;
            };
            
            // 모든 footer 관련 요소들 분석
            let footerLinksData = [];
            footerElements.footerLinks.forEach((link, index) => {
                footerLinksData.push({
                    index: index,
                    text: getTextContent(link),
                    href: link.href,
                    ...getStyles(link)
                });
            });
            
            let footerTextData = [];
            footerElements.footerText.forEach((text, index) => {
                if (text.textContent.trim().length > 0) {
                    footerTextData.push({
                        index: index,
                        text: getTextContent(text),
                        tagName: text.tagName,
                        ...getStyles(text)
                    });
                }
            });
            
            return {
                footer: getStyles(footerElements.footer),
                footerMobile: getStyles(footerElements.footerMobile),
                footerContent: getStyles(footerElements.footerContent),
                footerLinks: footerLinksData,
                footerText: footerTextData,
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight,
                    scrollY: window.scrollY
                },
                pageHeight: document.documentElement.scrollHeight
            };
        });
        
        console.log('\\n📊 Footer Mobile 분석 결과:');
        console.log('  📱 뷰포트:', footerAnalysis.viewport.width + 'x' + footerAnalysis.viewport.height);
        console.log('  📄 페이지 높이:', footerAnalysis.pageHeight + 'px');
        console.log('  🦶 Footer 요소:', footerAnalysis.footer ? '존재' : '없음');
        console.log('  📱 Footer Mobile:', footerAnalysis.footerMobile ? '존재' : '없음');
        console.log('  📝 Footer Content:', footerAnalysis.footerContent ? '존재' : '없음');
        
        if (footerAnalysis.footer) {
            console.log('\\n🦶 Footer 상세 정보:');
            console.log('  표시상태:', footerAnalysis.footer.display);
            console.log('  가시성:', footerAnalysis.footer.visibility);
            console.log('  투명도:', footerAnalysis.footer.opacity);
            console.log('  색상:', footerAnalysis.footer.color);
            console.log('  배경색:', footerAnalysis.footer.backgroundColor);
            console.log('  크기:', footerAnalysis.footer.width + 'x' + footerAnalysis.footer.height);
            console.log('  위치:', 'top: ' + footerAnalysis.footer.top + ', left: ' + footerAnalysis.footer.left);
            console.log('  화면에 보임:', footerAnalysis.footer.inViewport ? '예' : '아니오');
        }
        
        console.log('\\n🔗 Footer 링크들 (' + footerAnalysis.footerLinks.length + '개):');
        footerAnalysis.footerLinks.forEach((link, index) => {
            console.log('  ' + (index + 1) + '. ' + link.text);
            console.log('     색상:', link.color, '/ 크기:', link.fontSize, '/ 보임:', link.visible);
        });
        
        console.log('\\n📝 Footer 텍스트들 (' + footerAnalysis.footerText.length + '개):');
        footerAnalysis.footerText.forEach((text, index) => {
            console.log('  ' + (index + 1) + '. [' + text.tagName + '] ' + text.text);
            console.log('     색상:', text.color, '/ 크기:', text.fontSize, '/ 보임:', text.visible);
        });
        
        // 문제점 진단
        const issues = [];
        if (!footerAnalysis.footer) {
            issues.push('Footer 요소가 존재하지 않음');
        } else {
            if (footerAnalysis.footer.display === 'none') issues.push('Footer가 display: none으로 숨겨짐');
            if (footerAnalysis.footer.visibility === 'hidden') issues.push('Footer가 visibility: hidden으로 숨겨짐');
            if (parseFloat(footerAnalysis.footer.opacity) < 0.1) issues.push('Footer가 투명함 (opacity 낮음)');
            if (!footerAnalysis.footer.visible) issues.push('Footer 크기가 0 (width/height = 0)');
            if (!footerAnalysis.footer.inViewport) issues.push('Footer가 화면 밖에 위치');
        }
        
        console.log('\\n⚠️ 발견된 문제점들:');
        if (issues.length === 0) {
            console.log('  ✅ 특별한 문제점이 발견되지 않음');
        } else {
            issues.forEach((issue, index) => {
                console.log('  ' + (index + 1) + '. ' + issue);
            });
        }
        
        return {
            screenshot: 'footer-mobile-analysis.png',
            footerAnalysis,
            issues
        };
        
    } catch (error) {
        console.error('❌ 오류 발생:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

analyzeFooterMobile()
    .then(result => {
        console.log('\\n🎉 Footer Mobile 분석 완료!');
        console.log('📸 분석 스크린샷:', result.screenshot);
        console.log('🔍 문제점 개수:', result.issues.length + '개');
    })
    .catch(console.error);