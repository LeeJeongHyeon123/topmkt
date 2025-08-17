/**
 * 탑마케팅 모바일 반응형 최종 검증 테스트
 * 
 * 테스트 범위:
 * 1. 7개 핵심 페이지 (홈, 공지사항, 커뮤니티, 강의, 이벤트, 채팅, 프로필)
 * 2. 3개 뷰포트 (iPhone SE: 375x667, iPad: 768x1024, 소형모바일: 320x568)
 * 3. 터치 타겟, 폰트 크기, 수평 스크롤, UI 일관성 검증
 */

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

// 테스트 설정
const BASE_URL = 'https://www.topmktx.com';
const VIEWPORTS = [
    { name: 'iPhone_SE', width: 375, height: 667 },
    { name: 'iPad', width: 768, height: 1024 },
    { name: 'Small_Mobile', width: 320, height: 568 }
];

const PAGES = [
    { name: 'home', url: '/', title: '홈페이지' },
    { name: 'notices', url: '/notices', title: '공지사항' },
    { name: 'community', url: '/community', title: '커뮤니티' },
    { name: 'lectures', url: '/lectures', title: '강의일정' },
    { name: 'events', url: '/events', title: '행사일정' },
    { name: 'chat', url: '/chat', title: '채팅' },
    { name: 'profile', url: '/user/profile', title: '내프로필' }
];

// 테스트 결과 저장
const testResults = {
    timestamp: new Date().toISOString(),
    summary: {
        totalTests: 0,
        passedTests: 0,
        failedTests: 0,
        warningTests: 0
    },
    pages: {},
    overallScore: 0
};

/**
 * 터치 타겟 크기 검증 (최소 44px)
 */
async function checkTouchTargets(page, pageName) {
    console.log(`🔍 ${pageName} - 터치 타겟 크기 검증 중...`);
    
    const touchTargets = await page.evaluate(() => {
        const selectors = [
            'button', 'a', '.btn', '.feature-link', 
            'input[type="submit"]', 'input[type="button"]',
            '.notice-item', '.community-item', '.lecture-item',
            '.page-link', '.search-input', '.company-filter'
        ];
        
        const results = [];
        
        selectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach((el, index) => {
                if (el.offsetParent !== null) { // visible elements only
                    const rect = el.getBoundingClientRect();
                    const computedStyle = getComputedStyle(el);
                    const isInteractive = el.tagName === 'BUTTON' || 
                                        el.tagName === 'A' || 
                                        el.classList.contains('btn') ||
                                        el.type === 'submit' ||
                                        el.type === 'button' ||
                                        el.onclick !== null ||
                                        computedStyle.cursor === 'pointer';
                    
                    if (isInteractive) {
                        results.push({
                            selector: `${selector}:nth-child(${index + 1})`,
                            width: rect.width,
                            height: rect.height,
                            minDimension: Math.min(rect.width, rect.height),
                            text: el.textContent?.trim().substring(0, 30) || ''
                        });
                    }
                }
            });
        });
        
        return results;
    });
    
    const failedTargets = touchTargets.filter(target => target.minDimension < 44);
    const warningTargets = touchTargets.filter(target => target.minDimension >= 44 && target.minDimension < 48);
    
    return {
        total: touchTargets.length,
        failed: failedTargets.length,
        warning: warningTargets.length,
        passed: touchTargets.length - failedTargets.length,
        details: {
            failed: failedTargets,
            warning: warningTargets
        }
    };
}

/**
 * 폰트 크기 검증 (최소 16px for body text)
 */
async function checkFontSizes(page, pageName) {
    console.log(`🔍 ${pageName} - 폰트 크기 검증 중...`);
    
    const fontSizes = await page.evaluate(() => {
        const textElements = document.querySelectorAll('p, span, div, li, td, button, a, input');
        const results = [];
        
        textElements.forEach((el, index) => {
            if (el.offsetParent !== null && el.textContent?.trim()) {
                const computedStyle = getComputedStyle(el);
                const fontSize = parseFloat(computedStyle.fontSize);
                const tagName = el.tagName.toLowerCase();
                
                // 헤딩 태그는 제외 (h1, h2, h3 등은 더 클 수 있음)
                if (!['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].includes(tagName)) {
                    results.push({
                        element: `${tagName}:nth-child(${index + 1})`,
                        fontSize: fontSize,
                        text: el.textContent.trim().substring(0, 30),
                        classes: el.className
                    });
                }
            }
        });
        
        return results;
    });
    
    const smallFonts = fontSizes.filter(font => font.fontSize < 16);
    const tinyFonts = fontSizes.filter(font => font.fontSize < 14);
    
    return {
        total: fontSizes.length,
        smallFonts: smallFonts.length,
        tinyFonts: tinyFonts.length,
        averageSize: fontSizes.reduce((sum, font) => sum + font.fontSize, 0) / fontSizes.length,
        details: {
            small: smallFonts.slice(0, 10), // 처음 10개만
            tiny: tinyFonts
        }
    };
}

/**
 * 수평 스크롤 검증
 */
async function checkHorizontalScroll(page, pageName) {
    console.log(`🔍 ${pageName} - 수평 스크롤 검증 중...`);
    
    const scrollInfo = await page.evaluate(() => {
        const body = document.body;
        const html = document.documentElement;
        
        const bodyWidth = body.scrollWidth;
        const viewportWidth = window.innerWidth;
        const hasHorizontalScroll = bodyWidth > viewportWidth;
        
        // 넘치는 요소들 찾기
        const overflowingElements = [];
        const allElements = document.querySelectorAll('*');
        
        allElements.forEach((el, index) => {
            if (el.offsetParent !== null) {
                const rect = el.getBoundingClientRect();
                if (rect.right > viewportWidth + 10) { // 10px 여유
                    overflowingElements.push({
                        element: el.tagName.toLowerCase() + (el.className ? `.${el.className.split(' ')[0]}` : ''),
                        right: rect.right,
                        width: rect.width,
                        viewportWidth: viewportWidth,
                        overflow: rect.right - viewportWidth
                    });
                }
            }
        });
        
        return {
            bodyWidth,
            viewportWidth,
            hasHorizontalScroll,
            overflowAmount: Math.max(0, bodyWidth - viewportWidth),
            overflowingElements: overflowingElements.slice(0, 5) // 처음 5개만
        };
    });
    
    return scrollInfo;
}

/**
 * 버튼 텍스트 줄바꿈 검증
 */
async function checkButtonTextWrapping(page, pageName) {
    console.log(`🔍 ${pageName} - 버튼 텍스트 줄바꿈 검증 중...`);
    
    const buttonInfo = await page.evaluate(() => {
        const buttons = document.querySelectorAll('button, .btn, a[class*="btn"]');
        const results = [];
        
        buttons.forEach((btn, index) => {
            if (btn.offsetParent !== null) {
                const rect = btn.getBoundingClientRect();
                const computedStyle = getComputedStyle(btn);
                const lineHeight = parseFloat(computedStyle.lineHeight) || parseFloat(computedStyle.fontSize) * 1.2;
                const hasWrapping = rect.height > lineHeight * 1.5; // 1.5줄 이상이면 줄바꿈
                
                if (hasWrapping) {
                    results.push({
                        text: btn.textContent?.trim() || '',
                        height: rect.height,
                        lineHeight: lineHeight,
                        classes: btn.className,
                        estimatedLines: Math.round(rect.height / lineHeight)
                    });
                }
            }
        });
        
        return results;
    });
    
    return {
        total: buttonInfo.length,
        wrapped: buttonInfo.filter(btn => btn.estimatedLines > 1).length,
        details: buttonInfo
    };
}

/**
 * 페이지별 종합 테스트 실행
 */
async function testPage(browser, pageInfo, viewport) {
    const page = await browser.newPage();
    await page.setViewportSize({ width: viewport.width, height: viewport.height });
    
    try {
        console.log(`\n📱 ${pageInfo.title} (${viewport.name}: ${viewport.width}x${viewport.height}) 테스트 시작`);
        
        // 페이지 로드
        await page.goto(BASE_URL + pageInfo.url, { 
            waitUntil: 'networkidle',
            timeout: 30000 
        });
        
        // 로딩 대기
        await page.waitForTimeout(2000);
        
        // 스크린샷 촬영
        const screenshotPath = `/var/www/html/topmkt/screenshots/${pageInfo.name}_${viewport.name}.png`;
        await page.screenshot({ 
            path: screenshotPath, 
            fullPage: true
        });
        console.log(`📸 스크린샷 저장: ${screenshotPath}`);
        
        // 각종 검증 실행
        const touchTargetResults = await checkTouchTargets(page, pageInfo.title);
        const fontSizeResults = await checkFontSizes(page, pageInfo.title);
        const horizontalScrollResults = await checkHorizontalScroll(page, pageInfo.title);
        const buttonWrappingResults = await checkButtonTextWrapping(page, pageInfo.title);
        
        // 결과 계산
        const pageScore = calculatePageScore({
            touchTargets: touchTargetResults,
            fontSizes: fontSizeResults,
            horizontalScroll: horizontalScrollResults,
            buttonWrapping: buttonWrappingResults
        });
        
        const result = {
            viewport: viewport.name,
            url: pageInfo.url,
            screenshot: screenshotPath,
            score: pageScore,
            tests: {
                touchTargets: touchTargetResults,
                fontSizes: fontSizeResults,
                horizontalScroll: horizontalScrollResults,
                buttonWrapping: buttonWrappingResults
            },
            timestamp: new Date().toISOString()
        };
        
        console.log(`✅ ${pageInfo.title} (${viewport.name}) 완료 - 점수: ${pageScore}/100`);
        return result;
        
    } catch (error) {
        console.error(`❌ ${pageInfo.title} (${viewport.name}) 테스트 실패:`, error.message);
        return {
            viewport: viewport.name,
            url: pageInfo.url,
            error: error.message,
            score: 0,
            timestamp: new Date().toISOString()
        };
    } finally {
        await page.close();
    }
}

/**
 * 페이지 점수 계산
 */
function calculatePageScore(testResults) {
    let score = 100;
    
    // 터치 타겟 점수 (40점)
    const touchTargetScore = testResults.touchTargets.total > 0 ? 
        Math.max(0, 40 - (testResults.touchTargets.failed * 10) - (testResults.touchTargets.warning * 2)) : 40;
    
    // 폰트 크기 점수 (25점)
    const fontSizeScore = testResults.fontSizes.total > 0 ?
        Math.max(0, 25 - (testResults.fontSizes.tinyFonts * 5) - (testResults.fontSizes.smallFonts * 2)) : 25;
    
    // 수평 스크롤 점수 (25점)
    const horizontalScrollScore = testResults.horizontalScroll.hasHorizontalScroll ? 
        Math.max(0, 25 - Math.min(25, testResults.horizontalScroll.overflowAmount / 10)) : 25;
    
    // 버튼 텍스트 점수 (10점)
    const buttonScore = testResults.buttonWrapping.total > 0 ?
        Math.max(0, 10 - (testResults.buttonWrapping.wrapped * 3)) : 10;
    
    return Math.round(touchTargetScore + fontSizeScore + horizontalScrollScore + buttonScore);
}

/**
 * 메인 테스트 실행
 */
async function runMobileResponsiveTest() {
    console.log('🚀 탑마케팅 모바일 반응형 최종 검증 시작\n');
    
    // 스크린샷 디렉토리 생성
    const screenshotDir = '/var/www/html/topmkt/screenshots';
    if (!fs.existsSync(screenshotDir)) {
        fs.mkdirSync(screenshotDir, { recursive: true });
    }
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        let totalScore = 0;
        let testCount = 0;
        
        // 각 페이지를 각 뷰포트에서 테스트
        for (const pageInfo of PAGES) {
            testResults.pages[pageInfo.name] = {};
            
            for (const viewport of VIEWPORTS) {
                const result = await testPage(browser, pageInfo, viewport);
                testResults.pages[pageInfo.name][viewport.name] = result;
                
                if (result.score !== undefined) {
                    totalScore += result.score;
                    testCount++;
                    
                    if (result.score >= 90) testResults.summary.passedTests++;
                    else if (result.score >= 70) testResults.summary.warningTests++;
                    else testResults.summary.failedTests++;
                }
            }
        }
        
        testResults.summary.totalTests = testCount;
        testResults.overallScore = testCount > 0 ? Math.round(totalScore / testCount) : 0;
        
        // 결과 저장
        const reportPath = '/var/www/html/topmkt/mobile_responsive_test_results.json';
        fs.writeFileSync(reportPath, JSON.stringify(testResults, null, 2));
        
        console.log('\n🎉 모바일 반응형 테스트 완료!');
        console.log('📊 종합 결과:');
        console.log(`   전체 점수: ${testResults.overallScore}/100`);
        console.log(`   통과: ${testResults.summary.passedTests}개`);
        console.log(`   경고: ${testResults.summary.warningTests}개`);
        console.log(`   실패: ${testResults.summary.failedTests}개`);
        console.log(`📄 상세 결과: ${reportPath}`);
        
        return testResults;
        
    } catch (error) {
        console.error('❌ 테스트 실행 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 테스트 실행
if (require.main === module) {
    runMobileResponsiveTest()
        .then(results => {
            console.log('\n✅ 모든 테스트가 성공적으로 완료되었습니다.');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ 테스트 실행 실패:', error);
            process.exit(1);
        });
}

module.exports = { runMobileResponsiveTest };