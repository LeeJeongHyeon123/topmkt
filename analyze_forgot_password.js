import { chromium } from 'playwright';
import fs from 'fs';

async function analyzeForgotPasswordPage() {
    const browser = await chromium.launch();
    const page = await browser.newPage();

    const results = {
        desktop: {},
        tablet: {},
        mobile: {}
    };

    try {
        // Desktop 분석 (1920x1080)
        await page.setViewportSize({ width: 1920, height: 1080 });
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForLoadState('networkidle');
        
        await page.screenshot({ 
            path: 'forgot-password-desktop.png',
            fullPage: true 
        });

        // 모든 텍스트 요소 분석
        const desktopTextElements = await page.evaluate(() => {
            const elements = [];
            const textNodes = document.querySelectorAll('*');
            
            textNodes.forEach(element => {
                if (element.innerText && element.innerText.trim() !== '') {
                    const style = window.getComputedStyle(element);
                    const rect = element.getBoundingClientRect();
                    
                    if (rect.width > 0 && rect.height > 0) {
                        elements.push({
                            tag: element.tagName,
                            text: element.innerText.trim().substring(0, 100),
                            selector: element.className ? `.${element.className.split(' ')[0]}` : element.tagName.toLowerCase(),
                            color: style.color,
                            backgroundColor: style.backgroundColor,
                            fontSize: style.fontSize,
                            fontWeight: style.fontWeight,
                            position: {
                                top: rect.top,
                                left: rect.left,
                                width: rect.width,
                                height: rect.height
                            }
                        });
                    }
                }
            });
            
            return elements;
        });

        results.desktop = desktopTextElements;

        // Tablet 분석 (768x1024)
        await page.setViewportSize({ width: 768, height: 1024 });
        await page.reload();
        await page.waitForLoadState('networkidle');
        
        await page.screenshot({ 
            path: 'forgot-password-tablet.png',
            fullPage: true 
        });

        // Mobile 분석 (375x667)
        await page.setViewportSize({ width: 375, height: 667 });
        await page.reload();
        await page.waitForLoadState('networkidle');
        
        await page.screenshot({ 
            path: 'forgot-password-mobile.png',
            fullPage: true 
        });

        const mobileTextElements = await page.evaluate(() => {
            const elements = [];
            const textNodes = document.querySelectorAll('*');
            
            textNodes.forEach(element => {
                if (element.innerText && element.innerText.trim() !== '') {
                    const style = window.getComputedStyle(element);
                    const rect = element.getBoundingClientRect();
                    
                    if (rect.width > 0 && rect.height > 0) {
                        elements.push({
                            tag: element.tagName,
                            text: element.innerText.trim().substring(0, 100),
                            selector: element.className ? `.${element.className.split(' ')[0]}` : element.tagName.toLowerCase(),
                            color: style.color,
                            backgroundColor: style.backgroundColor,
                            fontSize: style.fontSize,
                            fontWeight: style.fontWeight,
                            position: {
                                top: rect.top,
                                left: rect.left,
                                width: rect.width,
                                height: rect.height
                            }
                        });
                    }
                }
            });
            
            return elements;
        });

        results.mobile = mobileTextElements;

    } catch (error) {
        console.error('분석 중 오류 발생:', error);
    } finally {
        await browser.close();
    }

    // 결과를 JSON 파일로 저장
    fs.writeFileSync('forgot-password-analysis.json', JSON.stringify(results, null, 2));
    
    console.log('=== 비밀번호 재설정 페이지 텍스트 가시성 분석 결과 ===');
    console.log('스크린샷 생성됨:');
    console.log('- forgot-password-desktop.png');
    console.log('- forgot-password-tablet.png');
    console.log('- forgot-password-mobile.png');
    console.log('- forgot-password-analysis.json');
    
    // 대비가 낮은 텍스트 찾기
    function analyzeContrast(elements, deviceType) {
        console.log(`\n${deviceType} 가시성 문제 발견:`);
        
        elements.forEach((element, index) => {
            const color = element.color;
            const bgColor = element.backgroundColor;
            
            // RGB 값 추출
            const colorMatch = color.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/);
            const bgMatch = bgColor.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/);
            
            if (colorMatch && bgMatch) {
                const [, r1, g1, b1] = colorMatch.map(Number);
                const [, r2, g2, b2] = bgMatch.map(Number);
                
                // 간단한 대비 계산 (실제로는 더 복잡한 공식 사용)
                const luminance1 = (0.299 * r1 + 0.587 * g1 + 0.114 * b1) / 255;
                const luminance2 = (0.299 * r2 + 0.587 * g2 + 0.114 * b2) / 255;
                
                const contrast = Math.abs(luminance1 - luminance2);
                
                if (contrast < 0.3) { // 낮은 대비
                    console.log(`❌ 낮은 대비 발견:`);
                    console.log(`   텍스트: "${element.text}"`);
                    console.log(`   셀렉터: ${element.selector}`);
                    console.log(`   텍스트 색상: ${color}`);
                    console.log(`   배경 색상: ${bgColor}`);
                    console.log(`   대비값: ${contrast.toFixed(2)}`);
                    console.log('');
                }
            }
        });
    }
    
    analyzeContrast(results.desktop, 'Desktop');
    analyzeContrast(results.mobile, 'Mobile');
}

analyzeForgotPasswordPage().catch(console.error);