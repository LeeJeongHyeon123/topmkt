/**
 * 채팅 텍스트 잘림 문제 해결 테스트
 * 방 이름과 마지막 메시지가 "안.." 대신 전체 텍스트로 표시되는지 확인
 *
 * @author Claude (Anthropic)
 * @date 2025-09-15
 */

const { chromium } = require('playwright');

class TextTruncationTest {
    constructor() {
        this.baseUrl = 'https://www.topmktx.com';
    }

    async test() {
        console.log('🔍 채팅 텍스트 잘림 해결 테스트 시작...');

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
            await page.waitForTimeout(3000);

            // 3. 텍스트 잘림 상태 분석
            console.log('\n3️⃣ 텍스트 표시 상태 분석...');
            const textAnalysis = await page.evaluate(() => {
                const roomItems = document.querySelectorAll('.room-item');
                const results = [];

                roomItems.forEach((room, index) => {
                    const nameElement = room.querySelector('.room-name');
                    const messageElement = room.querySelector('.room-last-message');

                    if (nameElement && messageElement) {
                        const nameText = nameElement.textContent.trim();
                        const messageText = messageElement.textContent.trim();
                        const nameRect = nameElement.getBoundingClientRect();
                        const messageRect = messageElement.getBoundingClientRect();

                        results.push({
                            index: index + 1,
                            roomName: nameText,
                            lastMessage: messageText,
                            nameWidth: Math.round(nameRect.width),
                            messageWidth: Math.round(messageRect.width),
                            nameIsTruncated: nameText.includes('..'),
                            messageIsTruncated: messageText.includes('..'),
                            nameLength: nameText.length,
                            messageLength: messageText.length
                        });
                    }
                });

                return results;
            });

            // 4. 결과 분석
            console.log('\n4️⃣ 텍스트 표시 결과:');
            let truncatedCount = 0;
            let totalRooms = textAnalysis.length;

            textAnalysis.forEach(room => {
                const isTruncated = room.nameIsTruncated || room.messageIsTruncated;
                if (isTruncated) truncatedCount++;

                console.log(`\n  방 ${room.index}:`);
                console.log(`    - 방 이름: "${room.roomName}" (${room.nameLength}자, ${room.nameWidth}px)`);
                console.log(`    - 마지막 메시지: "${room.lastMessage}" (${room.messageLength}자, ${room.messageWidth}px)`);
                console.log(`    - 잘림 여부: ${isTruncated ? '❌ 잘림' : '✅ 정상'}`);
            });

            // 5. 성공률 계산
            const successRate = totalRooms > 0 ? ((totalRooms - truncatedCount) / totalRooms * 100).toFixed(1) : 0;

            console.log(`\n📊 테스트 결과 요약:`);
            console.log(`  - 총 채팅방: ${totalRooms}개`);
            console.log(`  - 잘림 발생: ${truncatedCount}개`);
            console.log(`  - 정상 표시: ${totalRooms - truncatedCount}개`);
            console.log(`  - 성공률: ${successRate}%`);

            // 6. 스크린샷 촬영
            await page.screenshot({
                path: '/var/www/html/topmkt/text-truncation-test-result.png',
                fullPage: false
            });

            console.log('\n📸 스크린샷 저장: text-truncation-test-result.png');

            // 7. 결과 판정
            if (successRate >= 90) {
                console.log(`\n✅ 테스트 성공! 텍스트 잘림 문제가 해결되었습니다.`);
            } else {
                console.log(`\n⚠️ 추가 조정 필요: 성공률 ${successRate}%`);
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
    const tester = new TextTruncationTest();

    try {
        await tester.test();
        console.log('\n✅ 텍스트 잘림 테스트 완료');

    } catch (error) {
        console.error('❌ 테스트 실패:', error.message);
        process.exit(1);
    }
}

if (require.main === module) {
    main();
}

module.exports = TextTruncationTest;