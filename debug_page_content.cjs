/**
 * 채팅 페이지 실제 내용 분석 스크립트
 * 페이지가 어떻게 렌더링되고 있는지 확인
 *
 * @author Claude (Anthropic)
 * @date 2025-09-15
 */

const { chromium } = require('playwright');
const fs = require('fs');

class PageContentAnalyzer {
    constructor() {
        this.baseUrl = 'https://www.topmktx.com';
    }

    async analyze() {
        console.log('🔍 채팅 페이지 실제 내용 분석 시작...');

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
            const response = await page.goto(`${this.baseUrl}/chat`, { waitUntil: 'networkidle' });
            console.log(`응답 상태: ${response.status()}`);

            await page.waitForTimeout(3000);

            // 3. 페이지 타이틀 확인
            const title = await page.title();
            console.log(`페이지 타이틀: "${title}"`);

            // 4. 현재 URL 확인
            const currentUrl = page.url();
            console.log(`현재 URL: ${currentUrl}`);

            // 5. 페이지 전체 HTML 확인 (일부만)
            const htmlContent = await page.content();
            console.log(`\nHTML 길이: ${htmlContent.length} 문자`);

            // 6. body 내용 분석
            const bodyAnalysis = await page.evaluate(() => {
                const body = document.body;
                return {
                    className: body.className,
                    id: body.id,
                    childrenCount: body.children.length,
                    innerText: body.innerText.substring(0, 500),
                    innerHTML: body.innerHTML.substring(0, 1000)
                };
            });

            console.log('\nbody 요소 분석:');
            console.log(`  - 클래스: ${bodyAnalysis.className}`);
            console.log(`  - ID: ${bodyAnalysis.id}`);
            console.log(`  - 자식 요소 수: ${bodyAnalysis.childrenCount}`);
            console.log(`  - 텍스트 내용 (처음 500자):\n"${bodyAnalysis.innerText}"`);

            // 7. 에러 메시지 확인
            const errorMessages = await page.evaluate(() => {
                const errorElements = document.querySelectorAll('.error, .alert-danger, [class*="error"]');
                return Array.from(errorElements).map(el => ({
                    tagName: el.tagName,
                    className: el.className,
                    textContent: el.textContent.trim()
                }));
            });

            if (errorMessages.length > 0) {
                console.log('\n에러 메시지 발견:');
                errorMessages.forEach((error, index) => {
                    console.log(`  ${index + 1}. ${error.tagName}.${error.className}: "${error.textContent}"`);
                });
            }

            // 8. 리다이렉트 확인
            if (currentUrl !== `${this.baseUrl}/chat`) {
                console.log(`\n⚠️ 리다이렉트 발생: ${this.baseUrl}/chat → ${currentUrl}`);
            }

            // 9. 페이지 내 모든 클래스명 확인
            const allClasses = await page.evaluate(() => {
                const elements = document.querySelectorAll('*');
                const classes = new Set();
                elements.forEach(el => {
                    if (el.className && typeof el.className === 'string') {
                        el.className.split(' ').forEach(cls => {
                            if (cls.trim()) classes.add(cls.trim());
                        });
                    }
                });
                return Array.from(classes).sort();
            });

            console.log(`\n페이지 내 클래스명 (총 ${allClasses.length}개):`);
            const chatRelatedClasses = allClasses.filter(cls =>
                cls.includes('chat') || cls.includes('message') || cls.includes('room')
            );

            if (chatRelatedClasses.length > 0) {
                console.log('채팅 관련 클래스:');
                chatRelatedClasses.forEach(cls => console.log(`  - ${cls}`));
            } else {
                console.log('채팅 관련 클래스를 찾을 수 없음');
            }

            // 10. 스크린샷 촬영
            await page.screenshot({
                path: '/var/www/html/topmkt/chat_page_content_debug.png',
                fullPage: true
            });

            console.log('\n📸 스크린샷 저장: chat_page_content_debug.png');

            // 11. HTML 저장
            fs.writeFileSync('/var/www/html/topmkt/chat_page_debug.html', htmlContent);
            console.log('📄 HTML 저장: chat_page_debug.html');

        } catch (error) {
            console.error('❌ 분석 중 오류:', error.message);
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
                    value: 'dev_session_' + Date.now(),
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
    const analyzer = new PageContentAnalyzer();

    try {
        await analyzer.analyze();
        console.log('\n✅ 페이지 내용 분석 완료');

    } catch (error) {
        console.error('❌ 분석 실패:', error.message);
        process.exit(1);
    }
}

if (require.main === module) {
    main();
}

module.exports = PageContentAnalyzer;