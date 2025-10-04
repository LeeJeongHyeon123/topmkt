// 헤더 섹션 상세 검증 테스트
const { chromium } = require('playwright');

async function validateHeaderSection() {
    console.log('🔍 헤더 섹션 상세 검증 시작');

    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();

    try {
        await page.goto('https://www.topmktx.com/events?view=calendar', {
            waitUntil: 'networkidle',
            timeout: 15000
        });

        // 헤더 요소 분석
        const headerAnalysis = await page.evaluate(() => {
            const header = document.querySelector('.events-header');
            if (!header) return { error: '헤더 요소를 찾을 수 없음' };

            const styles = window.getComputedStyle(header);
            const rect = header.getBoundingClientRect();

            return {
                존재여부: '✅ 있음',
                배경: styles.background,
                색상: styles.color,
                패딩: styles.padding,
                마진: styles.margin,
                위치: `x:${Math.round(rect.x)}, y:${Math.round(rect.y)}`,
                크기: `${Math.round(rect.width)}x${Math.round(rect.height)}`,
                텍스트정렬: styles.textAlign,
                테두리반경: styles.borderRadius,
                표시여부: styles.display,
                가시성: styles.visibility,
                투명도: styles.opacity
            };
        });

        console.log('\n📋 헤더 섹션 분석 결과:');
        Object.entries(headerAnalysis).forEach(([key, value]) => {
            console.log(`  ${key}: ${value}`);
        });

        // 헤더 내용 확인
        const headerContent = await page.evaluate(() => {
            const header = document.querySelector('.events-header');
            if (!header) return '헤더 없음';

            const h1 = header.querySelector('h1');
            const p = header.querySelector('p');

            return {
                제목: h1 ? h1.textContent.trim() : '제목 없음',
                설명: p ? p.textContent.trim() : '설명 없음',
                전체HTML: header.innerHTML.replace(/\s+/g, ' ').trim().substring(0, 200) + '...'
            };
        });

        console.log('\n📝 헤더 내용:');
        Object.entries(headerContent).forEach(([key, value]) => {
            console.log(`  ${key}: ${value}`);
        });

        // 레이아웃 문제 검사
        const layoutIssues = await page.evaluate(() => {
            const issues = [];
            const header = document.querySelector('.events-header');
            const container = document.querySelector('.events-container');

            if (header && container) {
                const headerRect = header.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();

                // 헤더가 컨테이너를 벗어나는지 확인
                if (headerRect.left < containerRect.left || headerRect.right > containerRect.right) {
                    issues.push('헤더가 컨테이너를 벗어남');
                }

                // 헤더가 화면을 벗어나는지 확인
                if (headerRect.left < 0 || headerRect.right > window.innerWidth) {
                    issues.push('헤더가 화면을 벗어남');
                }

                // 헤더 높이가 비정상적인지 확인
                if (headerRect.height < 50 || headerRect.height > 300) {
                    issues.push(`헤더 높이 비정상: ${Math.round(headerRect.height)}px`);
                }
            }

            return issues;
        });

        console.log('\n⚠️ 레이아웃 문제:');
        if (layoutIssues.length === 0) {
            console.log('  ✅ 문제 없음');
        } else {
            layoutIssues.forEach(issue => console.log(`  ❌ ${issue}`));
        }

        // 스크린샷 저장
        await page.screenshot({
            path: 'header-validation-result.png',
            fullPage: false
        });

        console.log('\n📸 헤더 검증 스크린샷 저장됨: header-validation-result.png');

    } catch (error) {
        console.error('❌ 헤더 검증 실패:', error.message);
    }

    await browser.close();
}

validateHeaderSection().catch(console.error);