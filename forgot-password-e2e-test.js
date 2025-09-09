import { chromium } from 'playwright';
import fs from 'fs';

/**
 * 탑마케팅 비밀번호 찾기 페이지 종합 E2E 테스트
 * Phase 1: UI 개선 검증 (3개 뷰포트 스크린샷)
 * Phase 2: E2E 테스트 시나리오 (4개 시나리오)
 */

async function runForgotPasswordE2ETest() {
    console.log('🔐 비밀번호 찾기 페이지 종합 E2E 테스트 시작...\n');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const testResults = {
        phase1_screenshots: [],
        phase2_scenarios: [],
        summary: {
            total_tests: 0,
            passed: 0,
            failed: 0
        }
    };

    try {
        // Phase 1: UI 개선 검증 - 3개 뷰포트 스크린샷
        console.log('📸 Phase 1: UI 개선 검증 (스크린샷)');
        
        const viewports = [
            { name: 'Desktop', width: 1920, height: 1080 },
            { name: 'Tablet', width: 768, height: 1024 },
            { name: 'Mobile', width: 375, height: 667 }
        ];

        for (const viewport of viewports) {
            console.log(`\n🖥️  ${viewport.name} (${viewport.width}x${viewport.height}) 테스트 중...`);
            
            const page = await browser.newPage({
                viewport: { width: viewport.width, height: viewport.height }
            });
            
            await page.goto('https://www.topmktx.com/auth/forgot-password');
            await page.waitForLoadState('networkidle');
            
            // 스크린샷 촬영
            const screenshotPath = `forgot-password-${viewport.name.toLowerCase()}-${viewport.width}x${viewport.height}.png`;
            await page.screenshot({ 
                path: screenshotPath,
                fullPage: true 
            });
            
            // UI 요소 검증
            const uiAnalysis = await page.evaluate(() => {
                const results = {
                    title: document.title,
                    hasForm: !!document.querySelector('form'),
                    hasPhoneInput: !!document.querySelector('input[name="phone"]'),
                    hasSubmitButton: !!document.querySelector('button[type="submit"]'),
                    fontSizes: [],
                    touchTargets: []
                };
                
                // 폰트 크기 분석
                const elements = document.querySelectorAll('*');
                elements.forEach(el => {
                    const style = window.getComputedStyle(el);
                    const fontSize = parseFloat(style.fontSize);
                    if (fontSize > 0 && el.textContent.trim()) {
                        results.fontSizes.push({
                            element: el.tagName,
                            fontSize: fontSize,
                            text: el.textContent.trim().substring(0, 50)
                        });
                    }
                });
                
                // 터치 타겟 크기 분석
                const interactiveElements = document.querySelectorAll('button, input, a, [onclick]');
                interactiveElements.forEach(el => {
                    const rect = el.getBoundingClientRect();
                    results.touchTargets.push({
                        element: el.tagName,
                        width: rect.width,
                        height: rect.height,
                        minSize: Math.min(rect.width, rect.height)
                    });
                });
                
                return results;
            });
            
            testResults.phase1_screenshots.push({
                viewport: viewport.name,
                screenshot: screenshotPath,
                analysis: uiAnalysis,
                readabilityScore: uiAnalysis.fontSizes.filter(f => f.fontSize >= 16).length / uiAnalysis.fontSizes.length * 100,
                accessibilityScore: uiAnalysis.touchTargets.filter(t => t.minSize >= 44).length / uiAnalysis.touchTargets.length * 100
            });
            
            console.log(`✅ ${viewport.name} 스크린샷 저장: ${screenshotPath}`);
            console.log(`📊 가독성 점수: ${Math.round(uiAnalysis.fontSizes.filter(f => f.fontSize >= 16).length / uiAnalysis.fontSizes.length * 100)}%`);
            console.log(`🎯 접근성 점수: ${Math.round(uiAnalysis.touchTargets.filter(t => t.minSize >= 44).length / uiAnalysis.touchTargets.length * 100)}%`);
            
            await page.close();
        }

        // Phase 2: E2E 테스트 시나리오
        console.log('\n🎯 Phase 2: E2E 테스트 시나리오');
        
        const scenarios = [
            {
                name: '시나리오 1: Happy Path (정상 경로)',
                test: async () => {
                    const page = await browser.newPage();
                    await page.goto('https://www.topmktx.com/auth/forgot-password');
                    
                    // 유효한 전화번호 입력
                    await page.fill('input[name="phone"]', '010-1234-5678');
                    await page.waitForTimeout(500);
                    
                    // 폼 제출
                    await page.click('button[type="submit"]');
                    await page.waitForTimeout(2000);
                    
                    // 결과 확인 (리다이렉트 또는 성공 메시지)
                    const currentUrl = page.url();
                    const hasSuccessMessage = await page.locator('.success, .alert-success').count() > 0;
                    const hasErrorMessage = await page.locator('.error, .alert-danger').count() > 0;
                    
                    await page.close();
                    
                    return {
                        success: currentUrl.includes('reset-password') || hasSuccessMessage,
                        details: {
                            currentUrl,
                            hasSuccessMessage,
                            hasErrorMessage
                        }
                    };
                }
            },
            {
                name: '시나리오 2: 입력 유효성 검사',
                test: async () => {
                    const page = await browser.newPage();
                    await page.goto('https://www.topmktx.com/auth/forgot-password');
                    
                    const invalidFormats = ['010123456', '01012345678', '010-123-567'];
                    const results = [];
                    
                    for (const format of invalidFormats) {
                        await page.fill('input[name="phone"]', '');
                        await page.fill('input[name="phone"]', format);
                        await page.click('button[type="submit"]');
                        await page.waitForTimeout(1000);
                        
                        const hasValidationError = await page.locator('.error, .alert-danger, .invalid-feedback').count() > 0;
                        results.push({
                            format,
                            validationTriggered: hasValidationError
                        });
                    }
                    
                    // 빈 폼 제출 테스트
                    await page.fill('input[name="phone"]', '');
                    await page.click('button[type="submit"]');
                    await page.waitForTimeout(1000);
                    
                    const emptyFormError = await page.locator('.error, .alert-danger, .invalid-feedback').count() > 0;
                    results.push({
                        format: 'empty',
                        validationTriggered: emptyFormError
                    });
                    
                    await page.close();
                    
                    return {
                        success: results.every(r => r.validationTriggered),
                        details: results
                    };
                }
            },
            {
                name: '시나리오 3: UI/UX 상호작용 테스트',
                test: async () => {
                    const page = await browser.newPage();
                    await page.goto('https://www.topmktx.com/auth/forgot-password');
                    
                    // 입력 시 자동 포매팅 테스트
                    await page.fill('input[name="phone"]', '01012345678');
                    await page.waitForTimeout(500);
                    
                    const formattedValue = await page.inputValue('input[name="phone"]');
                    const autoFormatted = formattedValue.includes('-');
                    
                    // 키보드 네비게이션 테스트
                    await page.press('input[name="phone"]', 'Tab');
                    const submitButtonFocused = await page.evaluate(() => {
                        return document.activeElement.type === 'submit';
                    });
                    
                    // Enter 키 제출 테스트
                    await page.focus('input[name="phone"]');
                    await page.press('input[name="phone"]', 'Enter');
                    await page.waitForTimeout(1000);
                    
                    const formSubmitted = page.url() !== 'https://www.topmktx.com/auth/forgot-password';
                    
                    await page.close();
                    
                    return {
                        success: autoFormatted && submitButtonFocused,
                        details: {
                            autoFormatted,
                            submitButtonFocused,
                            formSubmitted,
                            formattedValue
                        }
                    };
                }
            },
            {
                name: '시나리오 4: 반응형 디바이스 테스트',
                test: async () => {
                    const deviceResults = [];
                    
                    for (const viewport of viewports) {
                        const page = await browser.newPage({
                            viewport: { width: viewport.width, height: viewport.height }
                        });
                        
                        await page.goto('https://www.topmktx.com/auth/forgot-password');
                        
                        // 요소 크기 및 레이아웃 검증
                        const layoutAnalysis = await page.evaluate(() => {
                            const phoneInput = document.querySelector('input[name="phone"]');
                            const submitButton = document.querySelector('button[type="submit"]');
                            
                            if (!phoneInput || !submitButton) {
                                return { error: 'Required elements not found' };
                            }
                            
                            const phoneRect = phoneInput.getBoundingClientRect();
                            const buttonRect = submitButton.getBoundingClientRect();
                            
                            return {
                                phoneInput: {
                                    width: phoneRect.width,
                                    height: phoneRect.height,
                                    visible: phoneRect.width > 0 && phoneRect.height > 0
                                },
                                submitButton: {
                                    width: buttonRect.width,
                                    height: buttonRect.height,
                                    touchFriendly: buttonRect.height >= 44
                                },
                                noOverlap: !phoneInput.checkVisibility || phoneInput.checkVisibility()
                            };
                        });
                        
                        // 기능 테스트
                        await page.fill('input[name="phone"]', '010-1234-5678');
                        await page.click('button[type="submit"]');
                        await page.waitForTimeout(1000);
                        
                        const functionalityWorks = !page.url().includes('error');
                        
                        deviceResults.push({
                            device: viewport.name,
                            layout: layoutAnalysis,
                            functionality: functionalityWorks
                        });
                        
                        await page.close();
                    }
                    
                    const allDevicesPass = deviceResults.every(d => 
                        d.layout && !d.layout.error && 
                        d.layout.phoneInput?.visible && 
                        d.layout.submitButton?.touchFriendly && 
                        d.functionality
                    );
                    
                    return {
                        success: allDevicesPass,
                        details: deviceResults
                    };
                }
            }
        ];

        // 각 시나리오 실행
        for (const scenario of scenarios) {
            console.log(`\n🧪 ${scenario.name} 실행 중...`);
            testResults.summary.total_tests++;
            
            try {
                const result = await scenario.test();
                testResults.phase2_scenarios.push({
                    name: scenario.name,
                    ...result
                });
                
                if (result.success) {
                    testResults.summary.passed++;
                    console.log(`✅ ${scenario.name}: 성공`);
                } else {
                    testResults.summary.failed++;
                    console.log(`❌ ${scenario.name}: 실패`);
                    console.log(`📋 세부사항:`, JSON.stringify(result.details, null, 2));
                }
            } catch (error) {
                testResults.summary.failed++;
                testResults.phase2_scenarios.push({
                    name: scenario.name,
                    success: false,
                    error: error.message
                });
                console.log(`❌ ${scenario.name}: 오류 - ${error.message}`);
            }
        }

    } catch (error) {
        console.error('❌ 테스트 실행 중 오류:', error);
    } finally {
        await browser.close();
    }

    // 최종 결과 보고서 생성
    const reportPath = 'forgot-password-e2e-test-report.json';
    fs.writeFileSync(reportPath, JSON.stringify(testResults, null, 2));
    
    console.log('\n📊 최종 결과 요약:');
    console.log(`📸 Phase 1 스크린샷: ${testResults.phase1_screenshots.length}개 완료`);
    console.log(`🎯 Phase 2 시나리오: ${testResults.summary.passed}/${testResults.summary.total_tests} 성공`);
    console.log(`💾 상세 보고서 저장: ${reportPath}`);
    
    // Phase 1 스크린샷 분석 요약
    console.log('\n📸 UI 개선 검증 결과:');
    testResults.phase1_screenshots.forEach(result => {
        console.log(`${result.viewport}: 가독성 ${Math.round(result.readabilityScore)}%, 접근성 ${Math.round(result.accessibilityScore)}%`);
    });
    
    console.log('\n🎉 비밀번호 찾기 페이지 E2E 테스트 완료!');
    
    return testResults;
}

// 스크립트 직접 실행 시
runForgotPasswordE2ETest().catch(console.error);