const { chromium } = require('playwright');

async function verifyContrastRatios() {
    console.log('🎨 로그인 페이지 색상 대비율 검증 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        const page = await context.newPage();
        
        await page.goto('https://www.topmktx.com/auth/login');
        await page.waitForLoadState('networkidle');
        await page.waitForSelector('.auth-side-info', { timeout: 10000 });
        
        // 색상 대비율 계산 함수들을 페이지에 주입
        await page.addScriptTag({
            content: `
                function rgbToLuminance(r, g, b) {
                    const [rs, gs, bs] = [r, g, b].map(c => {
                        c = c / 255;
                        return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
                    });
                    return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
                }
                
                function parseRgb(rgbString) {
                    const match = rgbString.match(/rgb\\(([^)]+)\\)/);
                    if (!match) return null;
                    return match[1].split(',').map(n => parseInt(n.trim()));
                }
                
                function getContrastRatio(color1, color2) {
                    const rgb1 = parseRgb(color1);
                    const rgb2 = parseRgb(color2);
                    if (!rgb1 || !rgb2) return null;
                    
                    const l1 = rgbToLuminance(...rgb1);
                    const l2 = rgbToLuminance(...rgb2);
                    
                    const lighter = Math.max(l1, l2);
                    const darker = Math.min(l1, l2);
                    
                    return (lighter + 0.05) / (darker + 0.05);
                }
                
                window.getContrastRatio = getContrastRatio;
            `
        });
        
        const contrastAnalysis = await page.evaluate(() => {
            const sideInfo = document.querySelector('.auth-side-info');
            const sideInfoBg = window.getComputedStyle(sideInfo.closest('.auth-section')).backgroundColor;
            
            // 실제 배경색 추정 (보라색 그라디언트)
            const estimatedBg = 'rgb(139, 92, 246)'; // 대략적인 보라색 배경
            
            const elements = [
                {
                    name: '안전한 로그인 (제목)',
                    element: document.querySelector('.auth-side-info h2'),
                    expectedColor: '#ffffff'
                },
                {
                    name: '최신 보안 기술로... (설명)',
                    element: document.querySelector('.auth-side-info > .side-info-content > p'),
                    expectedColor: '#e2e8f0'
                },
                {
                    name: 'SSL 암호화 (보안 기능)',
                    element: document.querySelector('.security-feature span'),
                    expectedColor: '#e2e8f0'
                },
                {
                    name: '로그인 후 이용 가능한 서비스 (제목)',
                    element: document.querySelector('.login-benefits h3'),
                    expectedColor: '#f1f5f9'
                },
                {
                    name: '커뮤니티 참여 (혜택)',
                    element: document.querySelector('.login-benefits li'),
                    expectedColor: '#cbd5e1'
                }
            ];
            
            const results = elements.map(item => {
                if (!item.element) return { ...item, error: 'Element not found' };
                
                const actualColor = window.getComputedStyle(item.element).color;
                const contrastRatio = window.getContrastRatio(actualColor, estimatedBg);
                
                return {
                    name: item.name,
                    actualColor,
                    expectedColor: item.expectedColor,
                    contrastRatio,
                    passesAA: contrastRatio >= 4.5,
                    passesAAA: contrastRatio >= 7.0,
                    textContent: item.element.textContent?.trim().substring(0, 30) + '...'
                };
            });
            
            return results;
        });
        
        // 스크린샷 촬영
        await page.screenshot({ 
            path: '/var/www/html/topmkt/login-contrast-verification.png',
            fullPage: true 
        });
        
        console.log('\n📊 색상 대비율 검증 결과:');
        console.log('=' * 80);
        
        let passCount = 0;
        contrastAnalysis.forEach((result, index) => {
            console.log(`\n${index + 1}. ${result.name}`);
            if (result.error) {
                console.log(`   ❌ 오류: ${result.error}`);
                return;
            }
            
            console.log(`   실제 색상: ${result.actualColor}`);
            console.log(`   예상 색상: ${result.expectedColor}`);
            console.log(`   대비율: ${result.contrastRatio?.toFixed(2)}:1`);
            
            if (result.passesAAA) {
                console.log('   ✅ AAA 등급 통과 (7.0:1 이상)');
                passCount++;
            } else if (result.passesAA) {
                console.log('   ✅ AA 등급 통과 (4.5:1 이상)');
                passCount++;
            } else {
                console.log('   ⚠️ 접근성 기준 미달 (4.5:1 미만)');
            }
            
            console.log(`   텍스트: "${result.textContent}"`);
        });
        
        console.log('\n🎯 === 최종 검증 결과 ===');
        console.log(`총 ${contrastAnalysis.length}개 요소 중 ${passCount}개 통과`);
        console.log(`성공률: ${Math.round(passCount / contrastAnalysis.length * 100)}%`);
        console.log('스크린샷: login-contrast-verification.png');
        
        return contrastAnalysis;
        
    } catch (error) {
        console.error('❌ 대비율 검증 중 오류:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
verifyContrastRatios()
    .then((results) => {
        console.log('\n✅ 색상 대비율 검증 완료!');
        
        const aaaPassed = results.filter(r => r.passesAAA && !r.error).length;
        const aaPassed = results.filter(r => r.passesAA && !r.error).length;
        
        console.log('\n📈 접근성 등급 분석:');
        console.log(`🥇 AAA 등급: ${aaaPassed}개`);
        console.log(`🥈 AA 등급: ${aaPassed}개`);
        console.log(`📜 WCAG 2.1 가이드라인 준수 여부: ${aaPassed === results.length ? '✅ 완전 준수' : '⚠️ 일부 미준수'}`);
    })
    .catch((error) => {
        console.error('💥 검증 실패:', error);
        process.exit(1);
    });