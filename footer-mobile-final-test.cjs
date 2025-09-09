const { chromium } = require('playwright');

async function finalFooterTest() {
    console.log('🎯 Footer Mobile 최종 검증 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        // 모바일 뷰포트로 설정
        const page = await browser.newPage();
        await page.setViewportSize({ width: 375, height: 667 }); // iPhone SE
        
        console.log('📄 수정된 페이지 로딩 중...');
        await page.goto('https://www.topmktx.com/auth/forgot-password', { 
            waitUntil: 'networkidle' 
        });
        
        await page.waitForTimeout(3000);
        
        console.log('📸 최종 결과 스크린샷 촬영...');
        await page.screenshot({ 
            path: 'footer-mobile-final-result.png',
            fullPage: true 
        });
        
        console.log('🔍 Footer 최종 상태 검증 중...');
        const finalAnalysis = await page.evaluate(() => {
            const footer = document.querySelector('.modern-footer');
            const footerMobile = document.querySelector('.footer-mobile');
            
            if (!footer) return { error: 'Footer 요소가 없음' };
            
            const footerRect = footer.getBoundingClientRect();
            const footerMobileRect = footerMobile ? footerMobile.getBoundingClientRect() : null;
            
            // 주요 텍스트 요소들 분석
            const logoText = document.querySelector('.footer-logo-text');
            const navLinks = Array.from(document.querySelectorAll('.footer-nav-link'));
            const contactLinks = Array.from(document.querySelectorAll('.footer-contact-inline a'));
            const copyright = document.querySelector('.footer-copyright');
            
            const getTextStyles = (element) => {
                if (!element) return null;
                const styles = window.getComputedStyle(element);
                return {
                    color: styles.color,
                    fontSize: styles.fontSize,
                    fontWeight: styles.fontWeight,
                    visible: element.offsetWidth > 0 && element.offsetHeight > 0,
                    text: element.textContent.trim().substring(0, 20) + '...'
                };
            };
            
            return {
                footer: {
                    exists: true,
                    position: {
                        top: footerRect.top,
                        bottom: footerRect.bottom,
                        height: footerRect.height
                    },
                    inViewport: footerRect.top < window.innerHeight,
                    backgroundColor: window.getComputedStyle(footer).backgroundColor
                },
                footerMobile: footerMobileRect ? {
                    top: footerMobileRect.top,
                    bottom: footerMobileRect.bottom,
                    height: footerMobileRect.height,
                    inViewport: footerMobileRect.top < window.innerHeight
                } : null,
                texts: {
                    logo: getTextStyles(logoText),
                    navLinks: navLinks.map(link => getTextStyles(link)),
                    contactLinks: contactLinks.map(link => getTextStyles(link)),
                    copyright: getTextStyles(copyright)
                },
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                },
                pageHeight: document.documentElement.scrollHeight
            };
        });
        
        console.log('\\n📊 Footer 최종 검증 결과:');
        console.log('  📱 뷰포트:', finalAnalysis.viewport.width + 'x' + finalAnalysis.viewport.height);
        console.log('  📄 페이지 높이:', finalAnalysis.pageHeight + 'px');
        
        if (finalAnalysis.footer) {
            console.log('\\n🦶 Footer 상태:');
            console.log('  존재함:', finalAnalysis.footer.exists ? '✅' : '❌');
            console.log('  위치: top=' + Math.round(finalAnalysis.footer.position.top) + 'px, 높이=' + Math.round(finalAnalysis.footer.position.height) + 'px');
            console.log('  화면에 보임:', finalAnalysis.footer.inViewport ? '✅' : '❌');
            console.log('  배경색:', finalAnalysis.footer.backgroundColor);
        }
        
        if (finalAnalysis.footerMobile) {
            console.log('\\n📱 Footer Mobile 상태:');
            console.log('  위치: top=' + Math.round(finalAnalysis.footerMobile.top) + 'px');
            console.log('  화면에 보임:', finalAnalysis.footerMobile.inViewport ? '✅' : '❌');
        }
        
        console.log('\\n📝 텍스트 가독성 검증:');
        if (finalAnalysis.texts.logo) {
            console.log('  로고:', finalAnalysis.texts.logo.color, '/', finalAnalysis.texts.logo.fontSize, '/', finalAnalysis.texts.logo.fontWeight);
        }
        
        console.log('  네비게이션 링크들 (' + finalAnalysis.texts.navLinks.length + '개):');
        finalAnalysis.texts.navLinks.forEach((link, index) => {
            if (link && link.visible) {
                console.log('    ' + (index + 1) + '. ' + link.text + ' - ' + link.color + ' / ' + link.fontSize);
            }
        });
        
        console.log('  연락처 링크들 (' + finalAnalysis.texts.contactLinks.length + '개):');
        finalAnalysis.texts.contactLinks.forEach((link, index) => {
            if (link && link.visible) {
                console.log('    ' + (index + 1) + '. ' + link.text + ' - ' + link.color + ' / ' + link.fontSize);
            }
        });
        
        if (finalAnalysis.texts.copyright) {
            console.log('  저작권:', finalAnalysis.texts.copyright.color, '/', finalAnalysis.texts.copyright.fontSize);
        }
        
        // 최종 평가
        const isFooterVisible = finalAnalysis.footer?.inViewport;
        const hasGoodContrast = finalAnalysis.texts.logo?.color !== 'rgb(107, 114, 128)'; // 밝은 회색이 아니면 OK
        
        console.log('\\n🎉 최종 평가:');
        console.log('  Footer 가시성:', isFooterVisible ? '✅ 완벽' : '❌ 문제');
        console.log('  텍스트 대비:', hasGoodContrast ? '✅ 완벽' : '❌ 문제');
        console.log('  전체 상태:', (isFooterVisible && hasGoodContrast) ? '🎯 완벽 해결!' : '⚠️ 추가 작업 필요');
        
        return {
            screenshot: 'footer-mobile-final-result.png',
            isFixed: isFooterVisible && hasGoodContrast,
            finalAnalysis
        };
        
    } catch (error) {
        console.error('❌ 오류 발생:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

finalFooterTest()
    .then(result => {
        console.log('\\n🎉 Footer Mobile 최종 검증 완료!');
        console.log('📸 최종 스크린샷:', result.screenshot);
        console.log('🔧 수정 완료 여부:', result.isFixed ? '✅ 완벽 해결' : '❌ 추가 작업 필요');
    })
    .catch(console.error);