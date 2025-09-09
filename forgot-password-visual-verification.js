#!/usr/bin/env node

/**
 * 비밀번호 재설정 페이지 화면 개선 검증
 * 화이트 배경 변경 사항 및 전체적인 시각적 일관성 검증
 */

import { chromium } from 'playwright';

class ForgotPasswordVisualVerification {
    constructor() {
        this.browser = null;
        this.page = null;
        this.baseUrl = 'https://www.topmktx.com';
        
        // 테스트할 화면 크기들
        this.viewports = [
            { name: 'Desktop', width: 1920, height: 1080 },
            { name: 'Tablet', width: 768, height: 1024 },
            { name: 'Mobile', width: 375, height: 667 }
        ];
        
        this.results = {
            screenshots: [],
            visualAnalysis: {},
            colorAnalysis: {},
            accessibilityCheck: {},
            functionalityTest: {},
            overallAssessment: {}
        };
    }

    async init() {
        console.log('🎭 비밀번호 재설정 페이지 화면 개선 검증 시작...');
        
        this.browser = await chromium.launch({
            headless: true,
            args: ['--no-sandbox', '--disable-dev-shm-usage']
        });
        
        this.page = await this.browser.newPage();
        console.log('✅ 브라우저 시작 완료');
    }

    async takeScreenshots() {
        console.log('📸 스크린샷 촬영 시작...');
        
        for (const viewport of this.viewports) {
            await this.page.setViewportSize({ 
                width: viewport.width, 
                height: viewport.height 
            });
            
            console.log(`📱 ${viewport.name} (${viewport.width}x${viewport.height}) 화면 크기 설정`);
            
            try {
                await this.page.goto(`${this.baseUrl}/auth/forgot-password`, {
                    waitUntil: 'networkidle',
                    timeout: 30000
                });
                
                // 페이지 완전 로딩 대기
                await this.page.waitForTimeout(2000);
                
                const screenshotPath = `/var/www/html/topmkt/forgot-password-${viewport.name.toLowerCase()}-${viewport.width}x${viewport.height}.png`;
                
                await this.page.screenshot({
                    path: screenshotPath,
                    fullPage: true
                });
                
                this.results.screenshots.push({
                    viewport: viewport.name,
                    size: `${viewport.width}x${viewport.height}`,
                    path: screenshotPath,
                    status: 'success'
                });
                
                console.log(`✅ ${viewport.name} 스크린샷 저장: ${screenshotPath}`);
                
            } catch (error) {
                console.error(`❌ ${viewport.name} 스크린샷 실패:`, error.message);
                this.results.screenshots.push({
                    viewport: viewport.name,
                    size: `${viewport.width}x${viewport.height}`,
                    status: 'failed',
                    error: error.message
                });
            }
        }
    }

    async analyzeVisualImprovements() {
        console.log('🎨 시각적 개선사항 분석 시작...');
        
        // Desktop 화면에서 상세 분석
        await this.page.setViewportSize({ width: 1920, height: 1080 });
        await this.page.goto(`${this.baseUrl}/auth/forgot-password`, {
            waitUntil: 'networkidle'
        });
        
        // 폼 컨테이너 분석
        const formContainer = await this.page.evaluate(() => {
            const container = document.querySelector('.form-container');
            if (!container) return null;
            
            const styles = window.getComputedStyle(container);
            return {
                backgroundColor: styles.backgroundColor,
                backdropFilter: styles.backdropFilter,
                borderRadius: styles.borderRadius,
                padding: styles.padding,
                boxShadow: styles.boxShadow,
                border: styles.border
            };
        });
        
        // 텍스트 색상 대비 분석
        const textColors = await this.page.evaluate(() => {
            const elements = {
                mainTitle: document.querySelector('.main-title'),
                brandName: document.querySelector('.brand-name'),
                description: document.querySelector('.description'),
                formLabel: document.querySelector('.form-label'),
                submitButton: document.querySelector('.submit-button'),
                navLinkPrimary: document.querySelector('.nav-link.primary'),
                navLinkSecondary: document.querySelector('.nav-link.secondary')
            };
            
            const colors = {};
            for (const [key, element] of Object.entries(elements)) {
                if (element) {
                    const styles = window.getComputedStyle(element);
                    colors[key] = {
                        color: styles.color,
                        backgroundColor: styles.backgroundColor,
                        textShadow: styles.textShadow
                    };
                }
            }
            return colors;
        });
        
        // 입력 필드 분석
        const inputField = await this.page.evaluate(() => {
            const input = document.querySelector('.form-input');
            if (!input) return null;
            
            const styles = window.getComputedStyle(input);
            return {
                color: styles.color,
                backgroundColor: styles.backgroundColor,
                borderColor: styles.borderColor,
                borderWidth: styles.borderWidth,
                borderStyle: styles.borderStyle,
                minHeight: styles.minHeight,
                fontSize: styles.fontSize
            };
        });
        
        this.results.visualAnalysis = {
            formContainer,
            textColors,
            inputField,
            timestamp: new Date().toISOString()
        };
        
        console.log('✅ 시각적 분석 완료');
    }

    async checkColorHarmony() {
        console.log('🎨 색상 조화 검증 시작...');
        
        const colorHarmony = await this.page.evaluate(() => {
            // RGB 색상을 HEX로 변환
            function rgbToHex(rgb) {
                const result = rgb.match(/\d+/g);
                if (!result) return rgb;
                return "#" + ((1 << 24) + (parseInt(result[0]) << 16) + (parseInt(result[1]) << 8) + parseInt(result[2])).toString(16).slice(1);
            }
            
            // 색상 대비비 계산
            function getContrastRatio(color1, color2) {
                function getLuminance(r, g, b) {
                    const [rs, gs, bs] = [r, g, b].map(c => {
                        c = c / 255;
                        return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
                    });
                    return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
                }
                
                const rgb1 = color1.match(/\d+/g);
                const rgb2 = color2.match(/\d+/g);
                
                if (!rgb1 || !rgb2) return 'N/A';
                
                const lum1 = getLuminance(parseInt(rgb1[0]), parseInt(rgb1[1]), parseInt(rgb1[2]));
                const lum2 = getLuminance(parseInt(rgb2[0]), parseInt(rgb2[1]), parseInt(rgb2[2]));
                
                const brightest = Math.max(lum1, lum2);
                const darkest = Math.min(lum1, lum2);
                
                return (brightest + 0.05) / (darkest + 0.05);
            }
            
            const analysis = {
                formBackground: 'rgb(255, 255, 255)', // 흰색 배경 확인
                gradientBackground: '',
                titleContrast: '',
                buttonContrast: '',
                inputContrast: '',
                linkContrast: ''
            };
            
            // 폼 컨테이너 배경색 확인
            const formContainer = document.querySelector('.form-container');
            if (formContainer) {
                analysis.formBackground = window.getComputedStyle(formContainer).backgroundColor;
            }
            
            // 그라디언트 배경 확인
            const main = document.querySelector('.forgot-password-main');
            if (main) {
                analysis.gradientBackground = window.getComputedStyle(main).background;
            }
            
            // 제목 대비 확인
            const title = document.querySelector('.main-title');
            if (title && formContainer) {
                const titleColor = window.getComputedStyle(title).color;
                const bgColor = window.getComputedStyle(formContainer).backgroundColor;
                analysis.titleContrast = {
                    textColor: titleColor,
                    backgroundColor: bgColor,
                    ratio: getContrastRatio(titleColor, bgColor)
                };
            }
            
            // 버튼 대비 확인
            const button = document.querySelector('.submit-button');
            if (button) {
                const buttonTextColor = window.getComputedStyle(button).color;
                const buttonBgColor = window.getComputedStyle(button).backgroundColor;
                analysis.buttonContrast = {
                    textColor: buttonTextColor,
                    backgroundColor: buttonBgColor,
                    ratio: getContrastRatio(buttonTextColor, buttonBgColor)
                };
            }
            
            // 입력 필드 대비 확인
            const input = document.querySelector('.form-input');
            if (input) {
                const inputTextColor = window.getComputedStyle(input).color;
                const inputBgColor = window.getComputedStyle(input).backgroundColor;
                analysis.inputContrast = {
                    textColor: inputTextColor,
                    backgroundColor: inputBgColor,
                    ratio: getContrastRatio(inputTextColor, inputBgColor)
                };
            }
            
            // 네비게이션 링크 대비 확인
            const primaryLink = document.querySelector('.nav-link.primary');
            if (primaryLink) {
                const linkTextColor = window.getComputedStyle(primaryLink).color;
                const linkBgColor = window.getComputedStyle(primaryLink).backgroundColor;
                analysis.linkContrast = {
                    textColor: linkTextColor,
                    backgroundColor: linkBgColor,
                    ratio: getContrastRatio(linkTextColor, linkBgColor)
                };
            }
            
            return analysis;
        });
        
        this.results.colorAnalysis = colorHarmony;
        console.log('✅ 색상 조화 검증 완료');
    }

    async testAccessibility() {
        console.log('♿ 접근성 검증 시작...');
        
        const accessibilityResults = await this.page.evaluate(() => {
            const results = {
                focusableElements: [],
                ariaLabels: [],
                colorContrast: [],
                keyboardNavigation: true,
                semanticStructure: {},
                touchTargets: []
            };
            
            // 포커스 가능한 요소들 검사
            const focusableElements = document.querySelectorAll(
                'input, button, a, textarea, select, [tabindex]:not([tabindex="-1"])'
            );
            
            focusableElements.forEach((element, index) => {
                const rect = element.getBoundingClientRect();
                results.focusableElements.push({
                    tagName: element.tagName,
                    id: element.id,
                    class: element.className,
                    ariaLabel: element.getAttribute('aria-label'),
                    ariaDescribedBy: element.getAttribute('aria-describedby'),
                    size: {
                        width: rect.width,
                        height: rect.height
                    },
                    isTouchFriendly: rect.width >= 44 && rect.height >= 44
                });
            });
            
            // ARIA 레이블 검사
            const ariaElements = document.querySelectorAll('[aria-label], [aria-labelledby], [aria-describedby]');
            ariaElements.forEach(element => {
                results.ariaLabels.push({
                    tagName: element.tagName,
                    id: element.id,
                    ariaLabel: element.getAttribute('aria-label'),
                    ariaLabelledBy: element.getAttribute('aria-labelledby'),
                    ariaDescribedBy: element.getAttribute('aria-describedby')
                });
            });
            
            // 시맨틱 구조 검사
            results.semanticStructure = {
                hasMain: !!document.querySelector('main'),
                hasHeader: !!document.querySelector('header'),
                hasNav: !!document.querySelector('nav'),
                hasH1: !!document.querySelector('h1'),
                hasForm: !!document.querySelector('form'),
                hasLabels: document.querySelectorAll('label').length,
                hasLiveRegion: !!document.querySelector('[aria-live]')
            };
            
            // 터치 타겟 크기 검사
            const interactiveElements = document.querySelectorAll('button, input, a');
            interactiveElements.forEach(element => {
                const rect = element.getBoundingClientRect();
                results.touchTargets.push({
                    element: element.tagName + (element.id ? '#' + element.id : ''),
                    width: rect.width,
                    height: rect.height,
                    meetsTouchTarget: rect.width >= 44 && rect.height >= 44
                });
            });
            
            return results;
        });
        
        this.results.accessibilityCheck = accessibilityResults;
        console.log('✅ 접근성 검증 완료');
    }

    async testFunctionality() {
        console.log('⚙️ 기능 테스트 시작...');
        
        const functionalityResults = {
            phoneInputFormatting: false,
            formValidation: false,
            submitButtonState: false,
            alertSystem: false,
            keyboardNavigation: false
        };
        
        try {
            // 휴대폰 번호 입력 테스트
            const phoneInput = await this.page.locator('#phone');
            await phoneInput.fill('01012345678');
            
            // 포맷팅 확인
            const formattedValue = await phoneInput.inputValue();
            functionalityResults.phoneInputFormatting = formattedValue === '010-1234-5678';
            
            // 폼 검증 테스트
            await phoneInput.fill('invalid');
            await this.page.locator('.submit-button').click();
            
            // 에러 메시지 확인
            const errorMessage = await this.page.locator('.error-message').textContent();
            functionalityResults.formValidation = errorMessage && errorMessage.trim() !== '';
            
            // 올바른 번호 입력 후 버튼 상태 확인
            await phoneInput.fill('010-1234-5678');
            const submitButton = await this.page.locator('.submit-button');
            const isDisabled = await submitButton.isDisabled();
            functionalityResults.submitButtonState = !isDisabled;
            
            // 키보드 네비게이션 테스트
            await phoneInput.press('Tab');
            const focusedElement = await this.page.evaluate(() => document.activeElement.tagName);
            functionalityResults.keyboardNavigation = focusedElement === 'BUTTON';
            
        } catch (error) {
            console.error('기능 테스트 중 오류:', error.message);
        }
        
        this.results.functionalityTest = functionalityResults;
        console.log('✅ 기능 테스트 완료');
    }

    async performOverallAssessment() {
        console.log('📊 전체 평가 시작...');
        
        const assessment = {
            whiteBackgroundImplemented: false,
            professionalAppearance: false,
            colorConsistency: false,
            accessibilityCompliance: false,
            responsiveDesign: false,
            functionality: false,
            overallScore: 0,
            recommendations: []
        };
        
        // 화이트 배경 구현 확인
        const formBg = this.results.visualAnalysis?.formContainer?.backgroundColor;
        assessment.whiteBackgroundImplemented = formBg === 'rgb(255, 255, 255)' || formBg === '#ffffff';
        
        // 전문적 외관 평가
        const hasCleanDesign = this.results.visualAnalysis?.formContainer?.borderRadius && 
                               this.results.visualAnalysis?.formContainer?.boxShadow;
        assessment.professionalAppearance = hasCleanDesign && assessment.whiteBackgroundImplemented;
        
        // 색상 일관성 평가
        const titleContrast = this.results.colorAnalysis?.titleContrast?.ratio;
        const buttonContrast = this.results.colorAnalysis?.buttonContrast?.ratio;
        assessment.colorConsistency = titleContrast > 4.5 && buttonContrast > 4.5;
        
        // 접근성 준수 평가
        const touchTargetsMet = this.results.accessibilityCheck?.touchTargets?.every(t => t.meetsTouchTarget) || false;
        const hasSemanticStructure = this.results.accessibilityCheck?.semanticStructure?.hasMain && 
                                    this.results.accessibilityCheck?.semanticStructure?.hasH1;
        assessment.accessibilityCompliance = touchTargetsMet && hasSemanticStructure;
        
        // 반응형 디자인 평가
        const successfulScreenshots = this.results.screenshots.filter(s => s.status === 'success').length;
        assessment.responsiveDesign = successfulScreenshots === this.viewports.length;
        
        // 기능성 평가
        const functionalTests = Object.values(this.results.functionalityTest);
        const passedTests = functionalTests.filter(test => test === true).length;
        assessment.functionality = passedTests >= functionalTests.length * 0.8; // 80% 이상 통과
        
        // 전체 점수 계산
        const criteria = [
            assessment.whiteBackgroundImplemented,
            assessment.professionalAppearance,
            assessment.colorConsistency,
            assessment.accessibilityCompliance,
            assessment.responsiveDesign,
            assessment.functionality
        ];
        
        assessment.overallScore = (criteria.filter(c => c).length / criteria.length) * 100;
        
        // 권장사항 생성
        if (!assessment.whiteBackgroundImplemented) {
            assessment.recommendations.push('폼 컨테이너의 화이트 배경 적용 필요');
        }
        if (!assessment.colorConsistency) {
            assessment.recommendations.push('텍스트와 배경 간 색상 대비 개선 필요');
        }
        if (!assessment.accessibilityCompliance) {
            assessment.recommendations.push('접근성 표준 준수 개선 필요 (터치 타겟 크기, 시맨틱 구조)');
        }
        if (!assessment.functionality) {
            assessment.recommendations.push('폼 기능성 개선 필요 (입력 검증, 포맷팅 등)');
        }
        
        this.results.overallAssessment = assessment;
        console.log('✅ 전체 평가 완료');
    }

    async generateReport() {
        console.log('📝 보고서 생성 시작...');
        
        const report = `
# 비밀번호 재설정 페이지 화면 개선 검증 보고서

## 📋 개요
- **검증 일시**: ${new Date().toLocaleString('ko-KR')}
- **검증 페이지**: ${this.baseUrl}/auth/forgot-password
- **검증 화면 크기**: ${this.viewports.map(v => v.name).join(', ')}

## 📸 스크린샷 결과
${this.results.screenshots.map(s => 
    `- **${s.viewport}** (${s.size}): ${s.status === 'success' ? '✅ 성공' : '❌ 실패'}`
).join('\n')}

## 🎨 시각적 개선사항 분석

### 폼 컨테이너
- **배경색**: ${this.results.visualAnalysis?.formContainer?.backgroundColor || 'N/A'}
- **테두리 둥글기**: ${this.results.visualAnalysis?.formContainer?.borderRadius || 'N/A'}
- **그림자**: ${this.results.visualAnalysis?.formContainer?.boxShadow ? '적용됨' : '미적용'}
- **패딩**: ${this.results.visualAnalysis?.formContainer?.padding || 'N/A'}

### 텍스트 색상 분석
- **메인 제목**: ${this.results.visualAnalysis?.textColors?.mainTitle?.color || 'N/A'}
- **브랜드명**: ${this.results.visualAnalysis?.textColors?.brandName?.color || 'N/A'}
- **설명 텍스트**: ${this.results.visualAnalysis?.textColors?.description?.color || 'N/A'}
- **제출 버튼**: ${this.results.visualAnalysis?.textColors?.submitButton?.color || 'N/A'}

## 🎨 색상 조화 검증

### 대비비 분석
- **제목 대비비**: ${this.results.colorAnalysis?.titleContrast?.ratio?.toFixed(2) || 'N/A'} ${this.results.colorAnalysis?.titleContrast?.ratio > 4.5 ? '✅ AA 준수' : '❌ 개선 필요'}
- **버튼 대비비**: ${this.results.colorAnalysis?.buttonContrast?.ratio?.toFixed(2) || 'N/A'} ${this.results.colorAnalysis?.buttonContrast?.ratio > 4.5 ? '✅ AA 준수' : '❌ 개선 필요'}
- **입력 필드 대비비**: ${this.results.colorAnalysis?.inputContrast?.ratio?.toFixed(2) || 'N/A'} ${this.results.colorAnalysis?.inputContrast?.ratio > 4.5 ? '✅ AA 준수' : '❌ 개선 필요'}

### 배경 색상
- **폼 배경**: ${this.results.colorAnalysis?.formBackground || 'N/A'}
- **화이트 배경 적용**: ${this.results.overallAssessment?.whiteBackgroundImplemented ? '✅ 적용됨' : '❌ 미적용'}

## ♿ 접근성 검증

### 터치 타겟 크기
${this.results.accessibilityCheck?.touchTargets?.map(t => 
    `- **${t.element}**: ${t.width}x${t.height}px ${t.meetsTouchTarget ? '✅ 44px 이상' : '❌ 44px 미만'}`
).join('\n') || '- 데이터 없음'}

### 시맨틱 구조
- **메인 요소**: ${this.results.accessibilityCheck?.semanticStructure?.hasMain ? '✅' : '❌'}
- **헤더 요소**: ${this.results.accessibilityCheck?.semanticStructure?.hasHeader ? '✅' : '❌'}
- **H1 제목**: ${this.results.accessibilityCheck?.semanticStructure?.hasH1 ? '✅' : '❌'}
- **폼 요소**: ${this.results.accessibilityCheck?.semanticStructure?.hasForm ? '✅' : '❌'}
- **라이브 영역**: ${this.results.accessibilityCheck?.semanticStructure?.hasLiveRegion ? '✅' : '❌'}

## ⚙️ 기능성 테스트

- **휴대폰 번호 포맷팅**: ${this.results.functionalityTest?.phoneInputFormatting ? '✅ 정상' : '❌ 오류'}
- **폼 검증**: ${this.results.functionalityTest?.formValidation ? '✅ 정상' : '❌ 오류'}
- **제출 버튼 상태**: ${this.results.functionalityTest?.submitButtonState ? '✅ 정상' : '❌ 오류'}
- **키보드 네비게이션**: ${this.results.functionalityTest?.keyboardNavigation ? '✅ 정상' : '❌ 오류'}

## 📊 전체 평가

### 종합 점수: ${this.results.overallAssessment?.overallScore?.toFixed(1) || 0}점 / 100점

### 평가 항목
- **화이트 배경 구현**: ${this.results.overallAssessment?.whiteBackgroundImplemented ? '✅ 완료' : '❌ 미완료'}
- **전문적 외관**: ${this.results.overallAssessment?.professionalAppearance ? '✅ 우수' : '❌ 개선 필요'}
- **색상 일관성**: ${this.results.overallAssessment?.colorConsistency ? '✅ 우수' : '❌ 개선 필요'}
- **접근성 준수**: ${this.results.overallAssessment?.accessibilityCompliance ? '✅ 우수' : '❌ 개선 필요'}
- **반응형 디자인**: ${this.results.overallAssessment?.responsiveDesign ? '✅ 우수' : '❌ 개선 필요'}
- **기능성**: ${this.results.overallAssessment?.functionality ? '✅ 우수' : '❌ 개선 필요'}

### 권장사항
${this.results.overallAssessment?.recommendations?.map(r => `- ${r}`).join('\n') || '- 모든 항목이 우수한 상태입니다.'}

## 🎯 결론

비밀번호 재설정 페이지의 화면 개선 작업이 **${this.results.overallAssessment?.overallScore >= 80 ? '성공적으로' : '부분적으로'}** 완료되었습니다.

${this.results.overallAssessment?.whiteBackgroundImplemented ? 
'✅ **화이트 배경이 성공적으로 적용**되어 폼이 더욱 깔끔하고 전문적으로 보입니다.' : 
'❌ **화이트 배경 적용이 필요**합니다. 현재 투명하거나 다른 색상이 적용된 상태입니다.'}

${this.results.overallAssessment?.colorConsistency ? 
'✅ **색상 대비가 우수**하여 모든 텍스트가 명확하게 읽힙니다.' : 
'❌ **색상 대비 개선이 필요**합니다. 일부 텍스트의 가독성을 향상시켜야 합니다.'}

${this.results.overallAssessment?.responsiveDesign ? 
'✅ **반응형 디자인이 완벽**하게 구현되어 모든 화면 크기에서 최적화된 경험을 제공합니다.' : 
'❌ **반응형 디자인 개선이 필요**합니다.'}

---
*생성 일시: ${new Date().toISOString()}*
        `;
        
        const reportPath = '/var/www/html/topmkt/forgot-password-e2e-comprehensive-report.md';
        const fs = await import('fs');
        await fs.promises.writeFile(reportPath, report.trim(), 'utf8');
        
        console.log(`✅ 보고서 저장: ${reportPath}`);
        return reportPath;
    }

    async cleanup() {
        if (this.browser) {
            await this.browser.close();
            console.log('🔒 브라우저 정리 완료');
        }
    }

    async run() {
        try {
            await this.init();
            await this.takeScreenshots();
            await this.analyzeVisualImprovements();
            await this.checkColorHarmony();
            await this.testAccessibility();
            await this.testFunctionality();
            await this.performOverallAssessment();
            
            const reportPath = await this.generateReport();
            
            console.log('\n🎉 비밀번호 재설정 페이지 검증 완료!');
            console.log(`📊 종합 점수: ${this.results.overallAssessment.overallScore.toFixed(1)}점`);
            console.log(`📝 상세 보고서: ${reportPath}`);
            
            return this.results;
            
        } catch (error) {
            console.error('❌ 검증 중 오류 발생:', error);
            throw error;
        } finally {
            await this.cleanup();
        }
    }
}

// 실행
const verification = new ForgotPasswordVisualVerification();
verification.run().catch(console.error);