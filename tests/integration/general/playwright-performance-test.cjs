const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1920, height: 1080 }
    });
    const page = await context.newPage();

    const metrics = {
        consoleRepeats: {}
    };

    page.on('console', msg => {
        const text = msg.text();
        if (metrics.consoleRepeats[text]) {
            metrics.consoleRepeats[text]++;
        } else {
            metrics.consoleRepeats[text] = 1;
        }
    });

    await page.goto('https://www.topmktx.com/lectures/210', {
        waitUntil: 'networkidle',
        timeout: 60000
    });

    const performanceMetrics = await page.evaluate(() => {
        const perfData = {
            navigation: {},
            resources: [],
            eventListeners: 0,
            domNodes: document.querySelectorAll('*').length,
            scripts: document.querySelectorAll('script').length
        };

        const navTiming = performance.getEntriesByType('navigation')[0];
        if (navTiming) {
            perfData.navigation = {
                domContentLoaded: navTiming.domContentLoadedEventEnd - navTiming.domContentLoadedEventStart,
                loadComplete: navTiming.loadEventEnd - navTiming.loadEventStart,
                domInteractive: navTiming.domInteractive - navTiming.fetchStart,
                totalTime: navTiming.loadEventEnd - navTiming.fetchStart
            };
        }

        const resources = performance.getEntriesByType('resource');
        resources.forEach(res => {
            if (res.initiatorType === 'script' || res.name.includes('.js')) {
                perfData.resources.push({
                    name: res.name.split('/').pop(),
                    duration: res.duration,
                    size: res.transferSize || 0
                });
            }
        });

        const scriptContent = Array.from(document.querySelectorAll('script'))
            .map(s => s.textContent)
            .join('\n');
        const addEventListenerCount = (scriptContent.match(/addEventListener\(/g) || []).length;
        perfData.eventListeners = addEventListenerCount;

        return perfData;
    });

    console.log('\n🖱️ 이미지 모달 클릭 테스트 시작...\n');

    const imageClickStart = Date.now();

    try {
        await page.click('.gallery-item', { timeout: 5000 });
        const imageClickEnd = Date.now();
        const imageClickDuration = imageClickEnd - imageClickStart;

        console.log('이미지 클릭 시간: ' + imageClickDuration + 'ms');

        const modalVisible = await page.isVisible('#imageModal');
        console.log('모달 표시 상태: ' + modalVisible);

        const closeClickStart = Date.now();
        await page.click('.modal-image-close', { timeout: 5000 });
        const closeClickEnd = Date.now();
        const closeClickDuration = closeClickEnd - closeClickStart;

        console.log('닫기 버튼 클릭 시간: ' + closeClickDuration + 'ms');

        if (imageClickDuration > 1000 || closeClickDuration > 1000) {
            console.log('\n심각한 버벅거림 감지!');
            console.log('이미지 클릭: ' + imageClickDuration + 'ms');
            console.log('닫기 클릭: ' + closeClickDuration + 'ms');
        }

    } catch (error) {
        console.log('이미지 모달 테스트 실패: ' + error.message);
    }

    console.log('\n=== 성능 측정 결과 ===\n');

    console.log('1. Navigation Timing:');
    console.log('   DOMContentLoaded: ' + performanceMetrics.navigation.domContentLoaded + 'ms');
    console.log('   DOM Interactive: ' + performanceMetrics.navigation.domInteractive + 'ms');
    console.log('   전체 로드: ' + performanceMetrics.navigation.totalTime + 'ms');

    console.log('\n2. DOM 복잡도:');
    console.log('   DOM 노드: ' + performanceMetrics.domNodes + '개');
    console.log('   스크립트: ' + performanceMetrics.scripts + '개');

    console.log('\n3. Event Listener:');
    console.log('   addEventListener: ' + performanceMetrics.eventListeners + '개');

    console.log('\n4. JavaScript 리소스 (상위 10개):');
    performanceMetrics.resources
        .sort((a, b) => b.duration - a.duration)
        .slice(0, 10)
        .forEach(res => {
            console.log('   ' + res.name + ': ' + res.duration.toFixed(0) + 'ms (' + (res.size/1024).toFixed(1) + 'KB)');
        });

    console.log('\n5. 콘솔 반복 (3회 이상):');
    Object.entries(metrics.consoleRepeats)
        .filter(([msg, count]) => count >= 3)
        .sort((a, b) => b[1] - a[1])
        .forEach(([msg, count]) => {
            console.log('   [' + count + '회] ' + msg.substring(0, 80));
        });

    await browser.close();

    console.log('\n=== 테스트 완료 ===\n');
})();
