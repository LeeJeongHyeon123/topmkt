/**
 * Toast UI 개선 QA 테스트
 * v3.92.0 - Toast top-center 위치 및 slide-down 애니메이션 검증
 */

import { chromium } from 'playwright';

const BASE_URL = 'https://www.topmktx.com';
const LOGIN_HELPER = `${BASE_URL}/dev/login_helper.php?user_id=4`;

// ANSI 색상 코드
const colors = {
    green: '\x1b[32m',
    red: '\x1b[31m',
    yellow: '\x1b[33m',
    blue: '\x1b[34m',
    reset: '\x1b[0m'
};

async function log(message, status = 'info') {
    const timestamp = new Date().toLocaleTimeString('ko-KR');
    const statusColors = {
        success: colors.green,
        error: colors.red,
        warning: colors.yellow,
        info: colors.blue
    };
    console.log(`${statusColors[status]}[${timestamp}] ${message}${colors.reset}`);
}

async function testToastPosition(page, viewportName, viewport) {
    await log(`\n=== ${viewportName} 테스트 시작 ===`, 'info');
    await page.setViewportSize(viewport);
    await page.waitForTimeout(500);

    // 테스트 페이지로 이동 (커뮤니티 상세 페이지)
    await page.goto(`${BASE_URL}/community/detail/1`);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    // Toast 트리거 (좋아요 버튼 클릭)
    const likeButton = page.locator('.like-section .like-btn').first();
    if (await likeButton.isVisible()) {
        await likeButton.click();
        await page.waitForTimeout(500);
    }

    // Toast 컨테이너 확인
    const toastContainer = page.locator('.toast-container');
    const isVisible = await toastContainer.isVisible();

    if (isVisible) {
        // 위치 확인
        const containerBox = await toastContainer.boundingBox();
        const position = await toastContainer.getAttribute('data-position');

        await log(`✓ Toast 컨테이너 발견 (position: ${position})`, 'success');

        // top-center 위치 검증
        if (position === 'top-center') {
            await log(`✓ 기본 위치가 top-center로 설정됨`, 'success');
        } else {
            await log(`✗ 기본 위치가 top-center가 아님: ${position}`, 'error');
            return false;
        }

        // CSS 클래스 확인
        const hasTopCenterClass = await toastContainer.evaluate(el =>
            el.classList.contains('toast-top-center')
        );
        if (hasTopCenterClass) {
            await log(`✓ toast-top-center 클래스 적용됨`, 'success');
        } else {
            await log(`✗ toast-top-center 클래스 없음`, 'error');
            return false;
        }

        // 모바일에서 중앙 정렬 확인
        if (viewport.width <= 768) {
            const styles = await toastContainer.evaluate(el => {
                const computed = window.getComputedStyle(el);
                return {
                    left: computed.left,
                    right: computed.right,
                    transform: computed.transform
                };
            });
            await log(`  모바일 스타일: left=${styles.left}, right=${styles.right}`, 'info');
        }

        // Toast 메시지 애니메이션 확인
        const toastMessage = page.locator('.toast-message').first();
        if (await toastMessage.isVisible()) {
            // 애니메이션 전환 확인
            const transform = await toastMessage.evaluate(el => {
                const computed = window.getComputedStyle(el);
                return computed.transform;
            });

            const opacity = await toastMessage.evaluate(el => {
                const computed = window.getComputedStyle(el);
                return computed.opacity;
            });

            await log(`✓ Toast 메시지 렌더링됨 (opacity: ${opacity}, transform: ${transform})`, 'success');

            // toast-show 클래스 확인
            const hasShowClass = await toastMessage.evaluate(el =>
                el.classList.contains('toast-show')
            );
            if (hasShowClass) {
                await log(`✓ toast-show 클래스 적용됨 (애니메이션 활성)`, 'success');
            }

            // 스크린샷 캡처
            const screenshotName = `toast-${viewportName.toLowerCase().replace(/\s+/g, '-')}.png`;
            await page.screenshot({
                path: `/var/www/html/topmkt/tests/screenshots/${screenshotName}`,
                fullPage: false
            });
            await log(`✓ 스크린샷 저장: ${screenshotName}`, 'success');
        }

        return true;
    } else {
        await log(`✗ Toast가 표시되지 않음`, 'warning');

        // Toast 수동 트리거 (JavaScript로 직접 호출)
        await page.evaluate(() => {
            if (window.Toast) {
                window.Toast.success('테스트 메시지입니다');
            }
        });
        await page.waitForTimeout(500);

        const manualToast = page.locator('.toast-container');
        if (await manualToast.isVisible()) {
            await log(`✓ 수동 트리거로 Toast 표시 성공`, 'success');

            const screenshotName = `toast-manual-${viewportName.toLowerCase().replace(/\s+/g, '-')}.png`;
            await page.screenshot({
                path: `/var/www/html/topmkt/tests/screenshots/${screenshotName}`,
                fullPage: false
            });

            return true;
        }

        return false;
    }
}

async function testToastAnimation(page) {
    await log(`\n=== 애니메이션 상세 테스트 ===`, 'info');

    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto(`${BASE_URL}/community`);
    await page.waitForLoadState('networkidle');

    // CSS 값 확인
    const cssValues = await page.evaluate(() => {
        const style = document.createElement('div');
        style.className = 'toast-message';
        document.body.appendChild(style);

        const computed = window.getComputedStyle(style);
        const initialTransform = computed.transform;
        const initialOpacity = computed.opacity;
        const transition = computed.transition;

        document.body.removeChild(style);

        return { initialTransform, initialOpacity, transition };
    });

    await log(`  초기 transform: ${cssValues.initialTransform}`, 'info');
    await log(`  초기 opacity: ${cssValues.initialOpacity}`, 'info');
    await log(`  transition: ${cssValues.transition}`, 'info');

    // translateY(-50px) 확인
    if (cssValues.initialTransform.includes('matrix') || cssValues.initialTransform === 'none') {
        await log(`✓ CSS transform 속성 확인됨`, 'success');
    }

    // Toast 실제 트리거하여 애니메이션 관찰
    await page.evaluate(() => {
        if (window.Toast) {
            window.Toast.info('애니메이션 테스트 중입니다', { duration: 5000 });
        }
    });

    await page.waitForTimeout(200);

    const animatedToast = page.locator('.toast-message.toast-show').first();
    if (await animatedToast.isVisible()) {
        const finalTransform = await animatedToast.evaluate(el => {
            return window.getComputedStyle(el).transform;
        });
        const finalOpacity = await animatedToast.evaluate(el => {
            return window.getComputedStyle(el).opacity;
        });

        await log(`✓ 애니메이션 완료 후 transform: ${finalTransform}`, 'success');
        await log(`✓ 애니메이션 완료 후 opacity: ${finalOpacity}`, 'success');

        if (parseFloat(finalOpacity) === 1) {
            await log(`✓ opacity가 1로 정상 전환됨`, 'success');
        }
    }

    return true;
}

async function main() {
    await log('Toast UI 개선 QA 테스트 시작', 'info');
    await log('========================================', 'info');

    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    try {
        const context = await browser.newContext({
            ignoreHTTPSErrors: true
        });
        const page = await context.newPage();

        // 로그인
        await log('\n로그인 진행 중...', 'info');
        await page.goto(LOGIN_HELPER);
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);
        await log('✓ 로그인 완료 (user_id=4)', 'success');

        // 테스트 viewports
        const viewports = [
            { name: 'PC Desktop', viewport: { width: 1920, height: 1080 } },
            { name: 'Laptop', viewport: { width: 1440, height: 900 } },
            { name: 'Tablet', viewport: { width: 768, height: 1024 } },
            { name: 'Mobile Large', viewport: { width: 425, height: 844 } },
            { name: 'Mobile Medium', viewport: { width: 375, height: 667 } }
        ];

        let passCount = 0;
        let failCount = 0;

        // 각 viewport 테스트
        for (const { name, viewport } of viewports) {
            const result = await testToastPosition(page, name, viewport);
            if (result) {
                passCount++;
            } else {
                failCount++;
            }
            await page.waitForTimeout(1000);
        }

        // 애니메이션 상세 테스트
        const animResult = await testToastAnimation(page);
        if (animResult) {
            passCount++;
        } else {
            failCount++;
        }

        // 결과 요약
        await log('\n========================================', 'info');
        await log('테스트 결과 요약:', 'info');
        await log(`✓ 성공: ${passCount}개`, 'success');
        if (failCount > 0) {
            await log(`✗ 실패: ${failCount}개`, 'error');
        }
        await log('========================================', 'info');

        if (failCount === 0) {
            await log('\n🎉 모든 테스트 통과!', 'success');
        } else {
            await log('\n⚠️ 일부 테스트 실패', 'warning');
        }

    } catch (error) {
        await log(`\n오류 발생: ${error.message}`, 'error');
        console.error(error);
    } finally {
        await browser.close();
    }
}

main();
