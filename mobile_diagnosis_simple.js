import { chromium } from 'playwright';

async function diagnoseMobileUI() {
    console.log('🚨 강의 상세 페이지 모바일 UI 긴급 진단 시작...\n');

    const browser = await chromium.launch({ headless: true });
    
    try {
        // 1. 모바일 뷰포트 (iPhone SE)
        console.log('📱 모바일 뷰포트 (375x667) 테스트...');
        const mobilePage = await browser.newPage({
            viewport: { width: 375, height: 667 }
        });
        
        await mobilePage.goto('https://www.topmktx.com/lectures/3?view=list', { 
            waitUntil: 'networkidle',
            timeout: 30000 
        });
        
        await mobilePage.screenshot({ 
            path: '/var/www/html/topmkt/lectures_detail_mobile_broken.png',
            fullPage: true 
        });
        console.log('✅ 모바일 스크린샷 저장됨');

        // 2. 소형 모바일 뷰포트 (iPhone 5)
        console.log('📱 소형 모바일 뷰포트 (320x568) 테스트...');
        const smallPage = await browser.newPage({
            viewport: { width: 320, height: 568 }
        });
        
        await smallPage.goto('https://www.topmktx.com/lectures/3?view=list', { 
            waitUntil: 'networkidle',
            timeout: 30000 
        });
        
        await smallPage.screenshot({ 
            path: '/var/www/html/topmkt/lectures_detail_small_broken.png',
            fullPage: true 
        });
        console.log('✅ 소형 모바일 스크린샷 저장됨');

        // 3. 모바일 UI 문제점 분석
        console.log('🔍 모바일 UI 문제점 분석 중...');
        
        const issues = await mobilePage.evaluate(() => {
            const problems = [];
            
            // 수평 스크롤 확인
            if (document.body.scrollWidth > window.innerWidth) {
                problems.push(`🚨 수평 스크롤 발생: ${document.body.scrollWidth}px > ${window.innerWidth}px`);
            }
            
            // 고정된 너비 요소 확인
            const fixedElements = Array.from(document.querySelectorAll('*')).filter(el => {
                const style = window.getComputedStyle(el);
                const width = style.width;
                if (width && width.includes('px')) {
                    const widthValue = parseInt(width);
                    return widthValue > window.innerWidth;
                }
                return false;
            }).map(el => {
                const style = window.getComputedStyle(el);
                return `${el.tagName}.${el.className}: ${style.width}`;
            });
            
            if (fixedElements.length > 0) {
                problems.push(`🚨 화면 너비 초과 요소들:`);
                fixedElements.forEach(elem => problems.push(`  - ${elem}`));
            }
            
            // 작은 터치 타겟 확인
            const smallButtons = Array.from(document.querySelectorAll('button, a, .btn')).filter(el => {
                const rect = el.getBoundingClientRect();
                return rect.width > 0 && rect.height > 0 && (rect.width < 44 || rect.height < 44);
            });
            
            if (smallButtons.length > 0) {
                problems.push(`🚨 작은 터치 타겟: ${smallButtons.length}개 (권장 최소 44px)`);
            }
            
            // 텍스트 가독성 확인
            const smallTexts = Array.from(document.querySelectorAll('*')).filter(el => {
                const style = window.getComputedStyle(el);
                const fontSize = parseFloat(style.fontSize);
                return fontSize < 14 && el.textContent.trim().length > 10;
            });
            
            if (smallTexts.length > 0) {
                problems.push(`🚨 가독성 문제: ${smallTexts.length}개 요소의 폰트 크기가 14px 미만`);
            }
            
            // 레이아웃 구조 확인
            const mainContent = document.querySelector('.lecture-content');
            if (mainContent) {
                const style = window.getComputedStyle(mainContent);
                problems.push(`📊 메인 콘텐츠 레이아웃: ${style.display}, 그리드: ${style.gridTemplateColumns}`);
            }
            
            // 사이드바 확인
            const sidebar = document.querySelector('.lecture-sidebar');
            if (sidebar) {
                const style = window.getComputedStyle(sidebar);
                const rect = sidebar.getBoundingClientRect();
                problems.push(`📊 사이드바 크기: ${rect.width}px × ${rect.height}px`);
            }
            
            return problems;
        });

        // 4. JavaScript 에러 확인
        const errors = await mobilePage.evaluate(() => {
            return window.errors || [];
        });

        // 5. 결과 보고서 생성
        const report = {
            timestamp: new Date().toISOString(),
            testUrl: 'https://www.topmktx.com/lectures/3?view=list',
            viewports: [
                { name: 'Mobile (iPhone SE)', size: '375x667' },
                { name: 'Small Mobile (iPhone 5)', size: '320x568' }
            ],
            screenshots: [
                'lectures_detail_mobile_broken.png',
                'lectures_detail_small_broken.png'
            ],
            issues: issues,
            jsErrors: errors,
            criticalProblems: issues.filter(issue => issue.includes('🚨')),
            recommendations: [
                '1. 수평 스크롤 제거 - max-width: 100% 적용',
                '2. 터치 타겟 크기 확대 - 최소 44px × 44px',
                '3. 폰트 크기 조정 - 최소 14px 이상',
                '4. 그리드 레이아웃을 플렉스박스로 변경',
                '5. 사이드바를 모바일에서 하단으로 이동'
            ]
        };

        console.log('\n📋 진단 완료! 결과 요약:');
        console.log('=====================================');
        console.log(`🔍 발견된 문제점: ${issues.length}개`);
        console.log(`🚨 심각한 문제: ${report.criticalProblems.length}개`);
        
        issues.forEach(issue => console.log(`  ${issue}`));
        
        console.log('\n📸 생성된 스크린샷:');
        report.screenshots.forEach(path => console.log(`  - ${path}`));
        
        console.log('\n💡 권장 수정사항:');
        report.recommendations.forEach(rec => console.log(`  ${rec}`));
        
        // JSON 보고서 저장
        await browser.close();
        
        // fs 모듈을 사용하여 파일 저장
        const fs = await import('fs');
        fs.default.writeFileSync('/var/www/html/topmkt/mobile_ui_diagnosis_report.json', JSON.stringify(report, null, 2));
        console.log('\n💾 상세 보고서 저장됨: mobile_ui_diagnosis_report.json');
        
        return report;

    } catch (error) {
        console.error('❌ 진단 중 오류 발생:', error);
        await browser.close();
        throw error;
    }
}

diagnoseMobileUI().catch(console.error);