/**
 * 채팅 room-name 텍스트 가시성 수정 검증 테스트
 * 0.89px width 문제가 해결되었는지 확인
 *
 * @author Claude (Anthropic)
 * @date 2025-09-15
 */

const { chromium } = require('playwright');

class RoomNameWidthTest {
    constructor() {
        this.baseUrl = 'https://www.topmktx.com';
    }

    async test() {
        console.log('🔍 room-name 너비 수정 검증 테스트 시작...');

        const browser = await chromium.launch({
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });

        try {
            const context = await browser.newContext();
            const page = await context.newPage();

            // 1. 로그인 시뮬레이션
            console.log('\n1️⃣ 로그인 시뮬레이션...');
            await this.simulateLogin(page);

            // 2. 채팅 페이지 접속
            console.log('\n2️⃣ 채팅 페이지 접속...');
            await page.goto(`${this.baseUrl}/chat`, { waitUntil: 'networkidle' });
            await page.waitForTimeout(5000); // 채팅 로딩 대기

            // 3. room-name 요소들 너비 분석
            console.log('\n3️⃣ room-name 요소 너비 분석...');
            const widthAnalysis = await page.evaluate(() => {
                const roomNameElements = document.querySelectorAll('.room-name');
                const results = [];

                roomNameElements.forEach((element, index) => {
                    const rect = element.getBoundingClientRect();
                    const computedStyle = window.getComputedStyle(element);
                    const textContent = element.textContent.trim();

                    results.push({
                        index: index + 1,
                        textContent: textContent,
                        width: rect.width,
                        height: rect.height,
                        computedMinWidth: computedStyle.minWidth,
                        computedMaxWidth: computedStyle.maxWidth,
                        isVisible: rect.width > 0 && rect.height > 0 &&
                                  computedStyle.display !== 'none' &&
                                  computedStyle.visibility !== 'hidden' &&
                                  parseFloat(computedStyle.opacity) > 0,
                        hasText: textContent.length > 0
                    });
                });

                return results;
            });

            // 4. 결과 분석
            console.log('\n4️⃣ room-name 너비 분석 결과:');
            let totalElements = widthAnalysis.length;
            let visibleElements = 0;
            let problematicElements = 0;

            widthAnalysis.forEach(room => {
                const isProblematic = room.width < 10; // 10px 미만은 문제 있음
                if (room.isVisible) visibleElements++;
                if (isProblematic) problematicElements++;

                console.log(`\n  room-name ${room.index}:`);
                console.log(`    - 텍스트: "${room.textContent}"`);
                console.log(`    - 너비: ${room.width.toFixed(2)}px`);
                console.log(`    - 높이: ${room.height.toFixed(2)}px`);
                console.log(`    - 계산된 min-width: ${room.computedMinWidth}`);
                console.log(`    - 가시성: ${room.isVisible ? '✅ 보임' : '❌ 안보임'}`);
                console.log(`    - 상태: ${isProblematic ? '❌ 문제있음 (너비 부족)' : '✅ 정상'}`);
            });

            // 5. 성공률 계산
            const visibilityRate = totalElements > 0 ? (visibleElements / totalElements * 100).toFixed(1) : 0;
            const successRate = totalElements > 0 ? ((totalElements - problematicElements) / totalElements * 100).toFixed(1) : 0;

            console.log(`\n📊 테스트 결과 요약:`);
            console.log(`  - 총 room-name 요소: ${totalElements}개`);
            console.log(`  - 가시적 요소: ${visibleElements}개`);
            console.log(`  - 문제 요소 (10px 미만): ${problematicElements}개`);
            console.log(`  - 가시성 비율: ${visibilityRate}%`);
            console.log(`  - 너비 성공률: ${successRate}%`);

            // 6. 스크린샷 촬영
            await page.screenshot({
                path: '/var/www/html/topmkt/room-name-width-test-result.png',
                fullPage: false
            });

            console.log('\n📸 스크린샷 저장: room-name-width-test-result.png');

            // 7. 결과 판정
            if (successRate >= 95 && problematicElements === 0) {
                console.log(`\n✅ 테스트 성공! room-name 너비 문제가 완전히 해결되었습니다.`);
                console.log(`✅ 모든 room-name 요소가 적절한 너비(10px 이상)를 가지고 있습니다.`);
            } else if (problematicElements > 0) {
                console.log(`\n⚠️ 일부 문제 발견: ${problematicElements}개 요소가 여전히 너비 부족`);
                console.log(`⚠️ 0.89px와 같은 극소 너비 문제가 여전히 존재할 수 있습니다.`);
            } else {
                console.log(`\n🔧 추가 조정 필요: 성공률 ${successRate}%`);
            }

        } catch (error) {
            console.error('❌ 테스트 중 오류:', error.message);
        } finally {
            await browser.close();
        }
    }

    async simulateLogin(page) {
        try {
            await page.goto(this.baseUrl);

            await page.evaluate(() => {
                localStorage.setItem('user_id', '4');
                localStorage.setItem('user_role', 'ROLE_ADMIN');
                localStorage.setItem('logged_in', 'true');
                sessionStorage.setItem('user_id', '4');
                sessionStorage.setItem('logged_in', 'true');
            });

            await page.context().addCookies([
                {
                    name: 'PHPSESSID',
                    value: 'test_session_' + Date.now(),
                    domain: 'www.topmktx.com',
                    path: '/'
                }
            ]);

        } catch (error) {
            console.error('❌ 로그인 시뮬레이션 실패:', error.message);
        }
    }
}

// 실행
async function main() {
    const tester = new RoomNameWidthTest();

    try {
        await tester.test();
        console.log('\n✅ room-name 너비 수정 검증 테스트 완료');

    } catch (error) {
        console.error('❌ 테스트 실패:', error.message);
        process.exit(1);
    }
}

if (require.main === module) {
    main();
}

module.exports = RoomNameWidthTest;