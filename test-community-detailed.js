import { chromium } from 'playwright';

async function testCommunityDetailed() {
    console.log('🚀 커뮤니티 페이지 상세 UI 검증 시작...');

    const browser = await chromium.launch();
    const context = await browser.newContext({
        viewport: { width: 375, height: 667 }, // iPhone SE 크기
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1'
    });

    const page = await context.newPage();

    try {
        console.log('📱 커뮤니티 페이지 접속 중...');
        await page.goto('https://www.topmktx.com/community');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(3000);

        // 페이지의 모든 요소 구조 파악
        console.log('🔍 페이지 구조 분석 중...');
        const pageStructure = await page.evaluate(() => {
            const selectors = [
                // 검색 관련 요소들
                'select[name="category"]',
                'select',
                'input[type="text"]',
                'input[name="search"]',
                'input[placeholder*="검색"]',
                'button[type="submit"]',
                '.search-btn',
                '.btn-search',
                // 글쓰기 버튼
                'a[href="/community/write"]',
                '.write-btn',
                '.btn-write',
                'a[href*="write"]',
                // 기타 버튼들
                'button',
                '.btn',
                // 검색 컨테이너
                '.search-container',
                '.search-form',
                'form'
            ];

            const found = {};
            selectors.forEach(selector => {
                const element = document.querySelector(selector);
                found[selector] = {
                    exists: !!element,
                    count: document.querySelectorAll(selector).length,
                    text: element ? element.textContent?.trim().substring(0, 50) : null,
                    className: element ? element.className : null
                };
            });

            return found;
        });

        console.log('\n📋 요소 존재 확인:');
        Object.entries(pageStructure).forEach(([selector, info]) => {
            if (info.exists) {
                console.log(`✅ ${selector}: ${info.count}개 (텍스트: "${info.text}", 클래스: "${info.className}")`);
            }
        });

        // 실제 존재하는 요소들의 높이 측정
        console.log('\n📏 존재하는 요소들의 높이 측정...');
        const measurements = await page.evaluate(() => {
            const results = {};

            // 모든 input 요소 찾기
            document.querySelectorAll('input').forEach((input, index) => {
                const rect = input.getBoundingClientRect();
                const style = getComputedStyle(input);
                results[`input_${index}`] = {
                    type: input.type,
                    placeholder: input.placeholder,
                    name: input.name,
                    height: Math.round(rect.height),
                    computedHeight: style.height,
                    padding: `${style.paddingTop} / ${style.paddingBottom}`,
                    border: `${style.borderTopWidth} / ${style.borderBottomWidth}`,
                    boxSizing: style.boxSizing
                };
            });

            // 모든 button 요소 찾기
            document.querySelectorAll('button').forEach((button, index) => {
                const rect = button.getBoundingClientRect();
                const style = getComputedStyle(button);
                results[`button_${index}`] = {
                    text: button.textContent?.trim(),
                    className: button.className,
                    height: Math.round(rect.height),
                    computedHeight: style.height,
                    padding: `${style.paddingTop} / ${style.paddingBottom}`,
                    border: `${style.borderTopWidth} / ${style.borderBottomWidth}`,
                    boxSizing: style.boxSizing
                };
            });

            // 모든 select 요소 찾기
            document.querySelectorAll('select').forEach((select, index) => {
                const rect = select.getBoundingClientRect();
                const style = getComputedStyle(select);
                results[`select_${index}`] = {
                    name: select.name,
                    className: select.className,
                    height: Math.round(rect.height),
                    computedHeight: style.height,
                    padding: `${style.paddingTop} / ${style.paddingBottom}`,
                    border: `${style.borderTopWidth} / ${style.borderBottomWidth}`,
                    boxSizing: style.boxSizing
                };
            });

            // 링크 버튼들 찾기
            document.querySelectorAll('a').forEach((link, index) => {
                if (link.href.includes('write') || link.className.includes('btn')) {
                    const rect = link.getBoundingClientRect();
                    const style = getComputedStyle(link);
                    results[`link_${index}`] = {
                        href: link.href,
                        text: link.textContent?.trim(),
                        className: link.className,
                        height: Math.round(rect.height),
                        computedHeight: style.height,
                        padding: `${style.paddingTop} / ${style.paddingBottom}`,
                        border: `${style.borderTopWidth} / ${style.borderBottomWidth}`,
                        boxSizing: style.boxSizing
                    };
                }
            });

            return results;
        });

        const targetHeight = 44;
        let perfectMatches = 0;
        let totalElements = 0;

        console.log('\n📊 상세 측정 결과:');
        console.log('================================');

        Object.entries(measurements).forEach(([key, data]) => {
            totalElements++;
            const matches = data.height === targetHeight;
            const status = matches ? '✅' : '❌';

            if (matches) perfectMatches++;

            console.log(`${status} ${key}: ${data.height}px`);
            console.log(`   타입: ${data.type || data.text || data.href}`);
            console.log(`   클래스: ${data.className}`);
            console.log(`   패딩: ${data.padding}`);
            console.log(`   테두리: ${data.border}`);
            console.log(`   박스사이징: ${data.boxSizing}`);
            console.log('');
        });

        console.log('================================');
        console.log(`✅ 44px 완벽 매칭: ${perfectMatches}/${totalElements}개 (${Math.round(perfectMatches/totalElements*100)}%)`);

        // 범위별 통계
        const heightStats = {};
        Object.values(measurements).forEach(data => {
            const height = data.height;
            heightStats[height] = (heightStats[height] || 0) + 1;
        });

        console.log('\n📊 높이별 통계:');
        Object.entries(heightStats)
            .sort((a, b) => parseInt(b[1]) - parseInt(a[1]))
            .forEach(([height, count]) => {
                const percentage = Math.round(count/totalElements*100);
                const status = height === '44' ? '🎯' : '📏';
                console.log(`${status} ${height}px: ${count}개 (${percentage}%)`);
            });

        // 최종 스크린샷
        await page.screenshot({
            path: 'community-final-verification.png',
            fullPage: true
        });

    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error);
    } finally {
        await browser.close();
        console.log('\n✅ 상세 검증 완료 - 스크린샷: community-final-verification.png');
    }
}

testCommunityDetailed();