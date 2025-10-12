import { chromium } from 'playwright';
import { writeFileSync } from 'fs';

const BASE_URL = 'http://localhost:8081';

async function testLecturesMobileUsability() {
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--disable-dev-shm-usage', '--no-sandbox']
    });
    
    const devices = [
        { name: 'iPhone SE', viewport: { width: 375, height: 667 } },
        { name: 'iPad', viewport: { width: 768, height: 1024 } },
        { name: 'Small Mobile', viewport: { width: 320, height: 568 } }
    ];
    
    const results = {};
    
    for (const device of devices) {
        console.log(`📱 Testing ${device.name} (${device.viewport.width}x${device.viewport.height})`);
        
        const context = await browser.newContext({
            viewport: device.viewport,
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1'
        });
        
        const page = await context.newPage();
        
        try {
            // 강의일정 페이지 접근 (정적 HTML 테스트)
            await page.goto(`file:///var/www/html/topmkt/lectures_mobile_test.html`, { 
                waitUntil: 'networkidle',
                timeout: 30000 
            });
            
            // 페이지 로딩 대기
            await page.waitForSelector('.lectures-header', { timeout: 10000 });
            
            // 현재 상태 스크린샷 (개선 전)
            const screenshotPath = `lectures_${device.name.toLowerCase().replace(' ', '_')}_before.png`;
            await page.screenshot({ 
                path: screenshotPath,
                fullPage: true
            });
            
            // 터치 타겟 및 폰트 크기 측정
            const measurements = await page.evaluate(() => {
                const measurements = {};
                
                // 터치 타겟 측정
                const touchElements = [
                    '.month-nav-btn',
                    '.view-btn', 
                    '.btn-create',
                    '.legend-item',
                    '.sidebar-lecture-item'
                ];
                
                measurements.touchTargets = {};
                touchElements.forEach(selector => {
                    const elements = document.querySelectorAll(selector);
                    if (elements.length > 0) {
                        const element = elements[0];
                        const rect = element.getBoundingClientRect();
                        const style = window.getComputedStyle(element);
                        measurements.touchTargets[selector] = {
                            width: rect.width,
                            height: rect.height,
                            fontSize: style.fontSize,
                            padding: style.padding
                        };
                    }
                });
                
                // 폰트 크기 측정
                const textElements = [
                    '.sidebar-lecture-title',
                    '.sidebar-lecture-meta',
                    '.lecture-list-title',
                    '.lecture-list-meta',
                    '.lecture-list-description',
                    '.legend-item'
                ];
                
                measurements.fontSizes = {};
                textElements.forEach(selector => {
                    const elements = document.querySelectorAll(selector);
                    if (elements.length > 0) {
                        const element = elements[0];
                        const style = window.getComputedStyle(element);
                        measurements.fontSizes[selector] = {
                            fontSize: style.fontSize,
                            lineHeight: style.lineHeight
                        };
                    }
                });
                
                return measurements;
            });
            
            results[device.name] = {
                viewport: device.viewport,
                screenshot: screenshotPath,
                measurements: measurements,
                status: 'completed'
            };
            
            console.log(`✅ ${device.name} 측정 완료`);
            
        } catch (error) {
            console.error(`❌ ${device.name} 테스트 실패:`, error.message);
            results[device.name] = {
                viewport: device.viewport,
                error: error.message,
                status: 'failed'
            };
        }
        
        await context.close();
    }
    
    await browser.close();
    
    // 결과 저장
    writeFileSync('lectures_mobile_before_analysis.json', JSON.stringify(results, null, 2));
    
    // 개선 필요 사항 분석
    console.log('\n📊 모바일 사용성 분석 결과:');
    for (const [deviceName, result] of Object.entries(results)) {
        if (result.status === 'completed') {
            console.log(`\n${deviceName}:`);
            
            // 터치 타겟 분석
            for (const [selector, data] of Object.entries(result.measurements.touchTargets)) {
                if (data.height < 44) {
                    console.log(`  ⚠️  ${selector}: 터치 타겟 높이 ${data.height}px (권장: 44px+)`);
                }
                if (data.width < 44) {
                    console.log(`  ⚠️  ${selector}: 터치 타겟 너비 ${data.width}px (권장: 44px+)`);
                }
            }
            
            // 폰트 크기 분석
            for (const [selector, data] of Object.entries(result.measurements.fontSizes)) {
                const fontSize = parseFloat(data.fontSize);
                if (fontSize < 16) {
                    console.log(`  ⚠️  ${selector}: 폰트 크기 ${data.fontSize} (권장: 16px+)`);
                }
            }
        }
    }
    
    console.log('\n📸 스크린샷 생성 완료:');
    for (const [deviceName, result] of Object.entries(results)) {
        if (result.screenshot) {
            console.log(`  - ${result.screenshot}`);
        }
    }
    
    return results;
}

// 개선 사항 적용 함수
async function testAfterImprovement() {
    console.log('\n🔧 개선 사항 적용 후 재테스트...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--disable-dev-shm-usage', '--no-sandbox']
    });
    
    const devices = [
        { name: 'iPhone SE', viewport: { width: 375, height: 667 } },
        { name: 'iPad', viewport: { width: 768, height: 1024 } },
        { name: 'Small Mobile', viewport: { width: 320, height: 568 } }
    ];
    
    const results = {};
    
    for (const device of devices) {
        console.log(`📱 Re-testing ${device.name} after improvements`);
        
        const context = await browser.newContext({
            viewport: device.viewport,
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1'
        });
        
        const page = await context.newPage();
        
        try {
            await page.goto(`file:///var/www/html/topmkt/lectures_mobile_test.html`, { 
                waitUntil: 'networkidle',
                timeout: 30000 
            });
            
            await page.waitForSelector('.lectures-header', { timeout: 10000 });
            
            // 개선 후 스크린샷
            const screenshotPath = `lectures_${device.name.toLowerCase().replace(' ', '_')}_fixed.png`;
            await page.screenshot({ 
                path: screenshotPath,
                fullPage: true
            });
            
            // 개선 후 측정
            const measurements = await page.evaluate(() => {
                const measurements = {};
                
                // 터치 타겟 재측정
                const touchElements = [
                    '.month-nav-btn',
                    '.view-btn', 
                    '.btn-create',
                    '.legend-item',
                    '.sidebar-lecture-item'
                ];
                
                measurements.touchTargets = {};
                touchElements.forEach(selector => {
                    const elements = document.querySelectorAll(selector);
                    if (elements.length > 0) {
                        const element = elements[0];
                        const rect = element.getBoundingClientRect();
                        const style = window.getComputedStyle(element);
                        measurements.touchTargets[selector] = {
                            width: rect.width,
                            height: rect.height,
                            fontSize: style.fontSize,
                            padding: style.padding
                        };
                    }
                });
                
                // 폰트 크기 재측정
                const textElements = [
                    '.sidebar-lecture-title',
                    '.sidebar-lecture-meta',
                    '.lecture-list-title',
                    '.lecture-list-meta',
                    '.lecture-list-description',
                    '.legend-item'
                ];
                
                measurements.fontSizes = {};
                textElements.forEach(selector => {
                    const elements = document.querySelectorAll(selector);
                    if (elements.length > 0) {
                        const element = elements[0];
                        const style = window.getComputedStyle(element);
                        measurements.fontSizes[selector] = {
                            fontSize: style.fontSize,
                            lineHeight: style.lineHeight
                        };
                    }
                });
                
                return measurements;
            });
            
            results[device.name] = {
                viewport: device.viewport,
                screenshot: screenshotPath,
                measurements: measurements,
                status: 'completed'
            };
            
            console.log(`✅ ${device.name} 재측정 완료`);
            
        } catch (error) {
            console.error(`❌ ${device.name} 재테스트 실패:`, error.message);
            results[device.name] = {
                viewport: device.viewport,
                error: error.message,
                status: 'failed'
            };
        }
        
        await context.close();
    }
    
    await browser.close();
    
    // 개선 후 결과 저장
    writeFileSync('lectures_mobile_after_analysis.json', JSON.stringify(results, null, 2));
    
    console.log('\n📸 개선 후 스크린샷 생성 완료:');
    for (const [deviceName, result] of Object.entries(results)) {
        if (result.screenshot) {
            console.log(`  - ${result.screenshot}`);
        }
    }
    
    return results;
}

// 메인 실행
async function main() {
    try {
        console.log('🚀 강의일정 페이지 모바일 사용성 개선 테스트 시작');
        
        // 1. 개선 전 테스트
        console.log('\n📊 1단계: 개선 전 현재 상태 측정');
        const beforeResults = await testLecturesMobileUsability();
        
        // 2. 개선 사항이 적용되기를 기다림 (수동으로 적용 후 재실행)
        console.log('\n⏳ 개선 사항 적용을 위해 CSS 수정이 필요합니다...');
        console.log('   CSS 수정 후 다시 이 스크립트를 실행하세요.');
        
        return beforeResults;
        
    } catch (error) {
        console.error('❌ 테스트 실행 중 오류:', error);
        throw error;
    }
}

// 개선 후 테스트만 실행하는 함수
async function runAfterTest() {
    try {
        console.log('🔧 개선 후 재테스트 실행');
        const afterResults = await testAfterImprovement();
        return afterResults;
    } catch (error) {
        console.error('❌ 개선 후 테스트 실행 중 오류:', error);
        throw error;
    }
}

// 명령줄 인수에 따라 실행
const args = process.argv.slice(2);
if (args.includes('--after')) {
    runAfterTest();
} else {
    main();
}

export { main, runAfterTest };