const { chromium } = require('playwright');

async function verifyReadability() {
    console.log('👀 가독성 개선 결과 검증 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const page = await browser.newPage();
        await page.setViewportSize({ width: 1920, height: 1080 });
        
        console.log('📄 페이지 로딩 중...');
        await page.goto('https://www.topmktx.com/auth/forgot-password', { 
            waitUntil: 'networkidle' 
        });
        
        await page.waitForTimeout(3000);
        
        console.log('📸 가독성 개선 결과 스크린샷 촬영...');
        await page.screenshot({ 
            path: 'forgot-password-readability-fixed.png',
            fullPage: true 
        });
        
        console.log('🔍 텍스트 가독성 분석 중...');
        const readabilityAnalysis = await page.evaluate(() => {
            const elements = {
                brandName: document.querySelector('.brand-name'),
                title: document.querySelector('h1'),
                description: document.querySelector('.description'),
                label: document.querySelector('.form-label'),
                input: document.querySelector('input[type="tel"]'),
                formHelp: document.querySelector('.form-help')
            };
            
            const getStyles = (element) => {
                if (!element) return null;
                const styles = window.getComputedStyle(element);
                return {
                    color: styles.color,
                    backgroundColor: styles.backgroundColor,
                    fontWeight: styles.fontWeight,
                    fontSize: styles.fontSize,
                    visible: element.offsetHeight > 0 && element.offsetWidth > 0
                };
            };
            
            const getTextContent = (element) => {
                return element ? element.textContent.trim().substring(0, 20) + '...' : null;
            };
            
            return {
                brandName: { 
                    ...(getStyles(elements.brandName) || {}), 
                    text: getTextContent(elements.brandName) 
                },
                title: { 
                    ...(getStyles(elements.title) || {}), 
                    text: getTextContent(elements.title) 
                },
                description: { 
                    ...(getStyles(elements.description) || {}), 
                    text: getTextContent(elements.description) 
                },
                label: { 
                    ...(getStyles(elements.label) || {}), 
                    text: getTextContent(elements.label) 
                },
                input: { 
                    ...(getStyles(elements.input) || {}) 
                },
                formHelp: { 
                    ...(getStyles(elements.formHelp) || {}), 
                    text: getTextContent(elements.formHelp) 
                }
            };
        });
        
        console.log('\n📊 가독성 분석 결과:');
        console.log('  🏷️ Brand Name:', readabilityAnalysis.brandName?.color, '/ 표시됨:', readabilityAnalysis.brandName?.visible, '/ 텍스트:', readabilityAnalysis.brandName?.text);
        console.log('  📝 Title:', readabilityAnalysis.title?.color, '/ 표시됨:', readabilityAnalysis.title?.visible);
        console.log('  💬 Description:', readabilityAnalysis.description?.color, '/ 표시됨:', readabilityAnalysis.description?.visible);
        console.log('  🏷️ Label:', readabilityAnalysis.label?.color, '/ 표시됨:', readabilityAnalysis.label?.visible);
        console.log('  📝 Input:', readabilityAnalysis.input?.color, '/ 표시됨:', readabilityAnalysis.input?.visible);
        console.log('  💡 Form Help:', readabilityAnalysis.formHelp?.color, '/ 표시됨:', readabilityAnalysis.formHelp?.visible);
        
        // 가독성 평가
        const isDarkText = (color) => {
            if (!color) return false;
            const match = color.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/);
            if (match) {
                const [r, g, b] = [parseInt(match[1]), parseInt(match[2]), parseInt(match[3])];
                return (r + g + b) / 3 < 128;
            }
            return false;
        };
        
        const allVisible = Object.values(readabilityAnalysis).every(item => item?.visible !== false);
        const allDarkText = Object.values(readabilityAnalysis).every(item => 
            !item?.color || isDarkText(item.color)
        );
        
        console.log('\n✨ 가독성 평가:');
        console.log('  모든 요소 표시:', allVisible ? '✅ 완벽' : '❌ 문제');
        console.log('  진한 텍스트:', allDarkText ? '✅ 완벽' : '❌ 문제');
        console.log('  전체 평가:', (allVisible && allDarkText) ? '🎉 완벽한 가독성' : '⚠️ 추가 조정 필요');
        
        return {
            screenshot: 'forgot-password-readability-fixed.png',
            readabilityAnalysis,
            isReadable: allVisible && allDarkText
        };
        
    } catch (error) {
        console.error('❌ 오류 발생:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

verifyReadability()
    .then(result => {
        console.log('\n🎉 가독성 검증 완료!');
        console.log('📸 검증 스크린샷:', result.screenshot);
        console.log('👀 가독성 상태:', result.isReadable ? '✅ 완벽' : '⚠️ 추가 조정 필요');
    })
    .catch(console.error);