import { chromium } from 'playwright';
import fs from 'fs';

// WCAG 대비 계산 함수
function getLuminance(r, g, b) {
    const sRGB = [r, g, b].map(c => {
        c = c / 255;
        return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
    });
    return 0.2126 * sRGB[0] + 0.7152 * sRGB[1] + 0.0722 * sRGB[2];
}

function getContrastRatio(color1, color2) {
    const lum1 = getLuminance(color1.r, color1.g, color1.b);
    const lum2 = getLuminance(color2.r, color2.g, color2.b);
    
    const brightest = Math.max(lum1, lum2);
    const darkest = Math.min(lum1, lum2);
    
    return (brightest + 0.05) / (darkest + 0.05);
}

function parseColor(colorString) {
    if (!colorString) return null;
    
    const rgbMatch = colorString.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/);
    const rgbaMatch = colorString.match(/rgba\((\d+),\s*(\d+),\s*(\d+),\s*[\d.]+\)/);
    
    if (rgbMatch) {
        return { r: parseInt(rgbMatch[1]), g: parseInt(rgbMatch[2]), b: parseInt(rgbMatch[3]) };
    } else if (rgbaMatch) {
        return { r: parseInt(rgbaMatch[1]), g: parseInt(rgbaMatch[2]), b: parseInt(rgbaMatch[3]) };
    }
    
    return null;
}

async function analyzeTextContrast() {
    const browser = await chromium.launch();
    const page = await browser.newPage();

    const results = [];

    try {
        // Desktop 분석
        await page.setViewportSize({ width: 1920, height: 1080 });
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForLoadState('networkidle');

        const textElements = await page.evaluate(() => {
            const elements = [];
            
            // 모든 텍스트 노드를 포함한 요소들 검사
            const allElements = document.querySelectorAll('*');
            
            allElements.forEach(element => {
                const text = element.innerText?.trim();
                if (text && text.length > 0 && text.length < 200) {
                    const style = window.getComputedStyle(element);
                    const rect = element.getBoundingClientRect();
                    
                    // 실제로 화면에 표시되는 요소만 검사
                    if (rect.width > 0 && rect.height > 0 && rect.top < window.innerHeight) {
                        // 부모 요소들의 배경색도 고려
                        let bgElement = element;
                        let backgroundColor = style.backgroundColor;
                        
                        // 투명한 배경색인 경우 부모 요소의 배경색 찾기
                        while (bgElement && (backgroundColor === 'rgba(0, 0, 0, 0)' || backgroundColor === 'transparent')) {
                            bgElement = bgElement.parentElement;
                            if (bgElement) {
                                backgroundColor = window.getComputedStyle(bgElement).backgroundColor;
                            }
                        }
                        
                        elements.push({
                            text: text.substring(0, 100),
                            tagName: element.tagName,
                            className: element.className,
                            id: element.id,
                            color: style.color,
                            backgroundColor: backgroundColor || 'rgb(255, 255, 255)', // 기본 배경색
                            fontSize: style.fontSize,
                            fontWeight: style.fontWeight,
                            position: {
                                top: Math.round(rect.top),
                                left: Math.round(rect.left),
                                width: Math.round(rect.width),
                                height: Math.round(rect.height)
                            }
                        });
                    }
                }
            });
            
            return elements;
        });

        results.push({ device: 'Desktop', elements: textElements });

        // Mobile 분석
        await page.setViewportSize({ width: 375, height: 667 });
        await page.reload();
        await page.waitForLoadState('networkidle');
        
        const mobileTextElements = await page.evaluate(() => {
            const elements = [];
            const allElements = document.querySelectorAll('*');
            
            allElements.forEach(element => {
                const text = element.innerText?.trim();
                if (text && text.length > 0 && text.length < 200) {
                    const style = window.getComputedStyle(element);
                    const rect = element.getBoundingClientRect();
                    
                    if (rect.width > 0 && rect.height > 0 && rect.top < window.innerHeight) {
                        let bgElement = element;
                        let backgroundColor = style.backgroundColor;
                        
                        while (bgElement && (backgroundColor === 'rgba(0, 0, 0, 0)' || backgroundColor === 'transparent')) {
                            bgElement = bgElement.parentElement;
                            if (bgElement) {
                                backgroundColor = window.getComputedStyle(bgElement).backgroundColor;
                            }
                        }
                        
                        elements.push({
                            text: text.substring(0, 100),
                            tagName: element.tagName,
                            className: element.className,
                            id: element.id,
                            color: style.color,
                            backgroundColor: backgroundColor || 'rgb(255, 255, 255)',
                            fontSize: style.fontSize,
                            fontWeight: style.fontWeight,
                            position: {
                                top: Math.round(rect.top),
                                left: Math.round(rect.left),
                                width: Math.round(rect.width),
                                height: Math.round(rect.height)
                            }
                        });
                    }
                }
            });
            
            return elements;
        });

        results.push({ device: 'Mobile', elements: mobileTextElements });

    } catch (error) {
        console.error('분석 중 오류 발생:', error);
    } finally {
        await browser.close();
    }

    // 대비 분석 결과
    console.log('=== 비밀번호 재설정 페이지 텍스트 가시성 상세 분석 ===\n');
    
    const contrastIssues = [];
    
    results.forEach(result => {
        console.log(`\n📱 ${result.device} 디바이스 분석:`);
        console.log(`총 ${result.elements.length}개 텍스트 요소 검사`);
        
        let lowContrastCount = 0;
        let criticalContrastCount = 0;
        
        result.elements.forEach((element, index) => {
            const textColor = parseColor(element.color);
            const bgColor = parseColor(element.backgroundColor);
            
            if (textColor && bgColor) {
                const contrastRatio = getContrastRatio(textColor, bgColor);
                
                // WCAG AA 기준: 일반 텍스트 4.5:1, 큰 텍스트 3:1
                const fontSize = parseFloat(element.fontSize);
                const isLargeText = fontSize >= 18 || (fontSize >= 14 && element.fontWeight >= 700);
                const minContrast = isLargeText ? 3.0 : 4.5;
                
                if (contrastRatio < minContrast) {
                    const severity = contrastRatio < 2.0 ? '🔴 심각' : contrastRatio < 3.0 ? '🟠 주의' : '🟡 개선 필요';
                    
                    if (contrastRatio < 2.0) criticalContrastCount++;
                    else lowContrastCount++;
                    
                    const issue = {
                        device: result.device,
                        severity,
                        text: element.text,
                        tagName: element.tagName,
                        className: element.className,
                        id: element.id,
                        textColor: element.color,
                        backgroundColor: element.backgroundColor,
                        contrastRatio: contrastRatio.toFixed(2),
                        minRequired: minContrast.toFixed(1),
                        fontSize: element.fontSize,
                        fontWeight: element.fontWeight,
                        position: element.position
                    };
                    
                    contrastIssues.push(issue);
                    
                    console.log(`\n${severity} 대비 문제 발견:`);
                    console.log(`  텍스트: "${element.text}"`);
                    console.log(`  요소: ${element.tagName}${element.className ? '.' + element.className.split(' ')[0] : ''}${element.id ? '#' + element.id : ''}`);
                    console.log(`  텍스트 색상: ${element.color}`);
                    console.log(`  배경 색상: ${element.backgroundColor}`);
                    console.log(`  대비율: ${contrastRatio.toFixed(2)}:1 (필요: ${minContrast}:1)`);
                    console.log(`  폰트 크기: ${element.fontSize} (가중치: ${element.fontWeight})`);
                    console.log(`  위치: ${element.position.top}px 상단, ${element.position.left}px 좌측`);
                }
            }
        });
        
        console.log(`\n📊 ${result.device} 요약:`);
        console.log(`  🔴 심각한 문제: ${criticalContrastCount}개`);
        console.log(`  🟠 개선 필요: ${lowContrastCount}개`);
        console.log(`  ✅ 정상: ${result.elements.length - lowContrastCount - criticalContrastCount}개`);
    });
    
    // JSON 파일로 상세 결과 저장
    fs.writeFileSync('contrast_issues_detailed.json', JSON.stringify(contrastIssues, null, 2));
    
    console.log(`\n\n📁 상세 분석 결과가 저장되었습니다:`);
    console.log(`  - contrast_issues_detailed.json: ${contrastIssues.length}개 문제 요소 상세 정보`);
    
    // 수정 권장사항 제공
    if (contrastIssues.length > 0) {
        console.log(`\n\n🔧 CSS 수정 권장사항:`);
        
        const groupedIssues = {};
        contrastIssues.forEach(issue => {
            const selector = issue.className ? `.${issue.className.split(' ')[0]}` : 
                           issue.id ? `#${issue.id}` : 
                           issue.tagName.toLowerCase();
            
            if (!groupedIssues[selector]) {
                groupedIssues[selector] = [];
            }
            groupedIssues[selector].push(issue);
        });
        
        Object.keys(groupedIssues).forEach(selector => {
            const issues = groupedIssues[selector];
            const firstIssue = issues[0];
            
            console.log(`\n/* ${selector} - ${issues.length}개 문제 */`);
            console.log(`${selector} {`);
            
            // 권장 색상 제안
            if (firstIssue.contrastRatio < 3.0) {
                console.log(`    color: #1f2937; /* 현재: ${firstIssue.textColor} */`);
                console.log(`    /* 또는 배경색을 밝게: background-color: #ffffff; */`);
            } else {
                console.log(`    color: #374151; /* 현재: ${firstIssue.textColor} */`);
            }
            console.log(`}`);
        });
    }
    
    return contrastIssues;
}

analyzeTextContrast().catch(console.error);