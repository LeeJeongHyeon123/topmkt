import { chromium } from 'playwright';

async function testCommunityMobile() {
    console.log('🚀 커뮤니티 페이지 모바일 UI 검증 시작...');

    const browser = await chromium.launch();
    const context = await browser.newContext({
        viewport: { width: 375, height: 667 }, // iPhone SE 크기
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1'
    });

    const page = await context.newPage();

    try {
        // 커뮤니티 페이지로 이동
        console.log('📱 iPhone SE 크기(375x667)로 커뮤니티 페이지 접속 중...');
        await page.goto('https://www.topmktx.com/community');

        // 페이지 로딩 대기
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);

        // 스크린샷 촬영
        console.log('📸 스크린샷 촬영 중...');
        await page.screenshot({
            path: 'community-mobile-verification.png',
            fullPage: true
        });

        // UI 요소들의 높이 측정
        console.log('📏 UI 요소들의 높이 측정 중...');

        const measurements = await page.evaluate(() => {
            const elements = {
                searchFilter: document.querySelector('select[name="category"]'),
                searchInput: document.querySelector('input[name="search"]'),
                searchButton: document.querySelector('button[type="submit"]'),
                writeButton: document.querySelector('a[href="/community/write"]')
            };

            const results = {};

            for (const [key, element] of Object.entries(elements)) {
                if (element) {
                    const rect = element.getBoundingClientRect();
                    const computedStyle = getComputedStyle(element);
                    results[key] = {
                        height: rect.height,
                        computedHeight: computedStyle.height,
                        paddingTop: computedStyle.paddingTop,
                        paddingBottom: computedStyle.paddingBottom,
                        borderTop: computedStyle.borderTopWidth,
                        borderBottom: computedStyle.borderBottomWidth,
                        found: true
                    };
                } else {
                    results[key] = { found: false };
                }
            }

            return results;
        });

        // 결과 출력
        console.log('\n📊 UI 요소 높이 측정 결과:');
        console.log('================================');

        const targetHeight = 44;
        let allMatch = true;

        for (const [elementName, data] of Object.entries(measurements)) {
            if (data.found) {
                const actualHeight = Math.round(data.height);
                const matches = actualHeight === targetHeight;
                const status = matches ? '✅' : '❌';

                console.log(`${status} ${elementName}: ${actualHeight}px (목표: ${targetHeight}px)`);
                console.log(`   - Computed Height: ${data.computedHeight}`);
                console.log(`   - Padding: ${data.paddingTop} / ${data.paddingBottom}`);
                console.log(`   - Border: ${data.borderTop} / ${data.borderBottom}`);
                console.log('');

                if (!matches) allMatch = false;
            } else {
                console.log(`❌ ${elementName}: 요소를 찾을 수 없음`);
                allMatch = false;
            }
        }

        console.log('================================');
        if (allMatch) {
            console.log('🎉 모든 요소가 44px 높이로 통일되었습니다!');
        } else {
            console.log('⚠️  일부 요소의 높이가 목표치와 다릅니다.');
        }

        // 페이지의 전체 구조 확인
        const pageInfo = await page.evaluate(() => {
            return {
                title: document.title,
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                },
                hasSearchForm: !!document.querySelector('.search-container'),
                hasWriteButton: !!document.querySelector('a[href="/community/write"]'),
                totalPosts: document.querySelectorAll('.community-post').length
            };
        });

        console.log('\n📄 페이지 정보:');
        console.log(`제목: ${pageInfo.title}`);
        console.log(`뷰포트: ${pageInfo.viewport.width}x${pageInfo.viewport.height}`);
        console.log(`검색 폼 존재: ${pageInfo.hasSearchForm ? '✅' : '❌'}`);
        console.log(`글쓰기 버튼 존재: ${pageInfo.hasWriteButton ? '✅' : '❌'}`);
        console.log(`게시글 수: ${pageInfo.totalPosts}개`);

    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error);
    } finally {
        await browser.close();
        console.log('\n✅ 테스트 완료 - 스크린샷: community-mobile-verification.png');
    }
}

testCommunityMobile();