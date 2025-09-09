const { chromium } = require('playwright');

async function analyzeHeaderOverlap() {
    console.log('📐 헤더-컨텐츠 겹침 문제 분석 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const viewports = [
            { width: 1920, height: 1080, name: 'Desktop' },
            { width: 768, height: 1024, name: 'Tablet' },
            { width: 375, height: 667, name: 'Mobile' }
        ];
        
        for (const viewport of viewports) {
            console.log(`\n📱 ${viewport.name} (${viewport.width}x${viewport.height}) 분석 중...`);
            
            const page = await browser.newPage();
            await page.setViewportSize({ width: viewport.width, height: viewport.height });
            
            await page.goto('https://www.topmktx.com/auth/forgot-password', { 
                waitUntil: 'networkidle' 
            });
            
            await page.waitForTimeout(3000);
            
            // 스크린샷 촬영
            await page.screenshot({ 
                path: `header-content-overlap-${viewport.name.toLowerCase()}.png`,
                fullPage: true 
            });
            
            // 헤더와 컨텐츠 영역 분석
            const analysis = await page.evaluate(() => {
                const header = document.querySelector('header, .header, .navbar, .site-header');
                const main = document.querySelector('main, .main-content, .forgot-password-main');
                const formContainer = document.querySelector('.form-container, .content-wrapper');
                
                const getElementInfo = (element, name) => {
                    if (!element) return { name, exists: false };
                    
                    const rect = element.getBoundingClientRect();
                    const styles = window.getComputedStyle(element);
                    
                    return {
                        name,
                        exists: true,
                        position: styles.position,
                        zIndex: styles.zIndex,
                        top: rect.top,
                        left: rect.left,
                        width: rect.width,
                        height: rect.height,
                        bottom: rect.bottom,
                        right: rect.right,
                        marginTop: styles.marginTop,
                        paddingTop: styles.paddingTop
                    };
                };
                
                const headerInfo = getElementInfo(header, 'Header');
                const mainInfo = getElementInfo(main, 'Main');
                const formInfo = getElementInfo(formContainer, 'Form Container');
                
                // 겹침 여부 계산
                const headerBottom = headerInfo.exists ? headerInfo.bottom : 0;
                const mainTop = mainInfo.exists ? mainInfo.top : 0;
                const formTop = formInfo.exists ? formInfo.top : 0;
                
                const isOverlapping = headerInfo.exists && mainInfo.exists && headerBottom > mainTop;
                const overlapAmount = isOverlapping ? headerBottom - mainTop : 0;
                
                return {
                    viewport: {
                        width: window.innerWidth,
                        height: window.innerHeight
                    },
                    elements: {
                        header: headerInfo,
                        main: mainInfo,
                        form: formInfo
                    },
                    overlap: {
                        isOverlapping,
                        amount: overlapAmount,
                        headerBottom,
                        mainTop,
                        formTop
                    }
                };
            });
            
            console.log(`  📊 ${viewport.name} 분석 결과:`);
            console.log(`    헤더 존재: ${analysis.elements.header.exists ? '✅' : '❌'}`);
            console.log(`    메인 존재: ${analysis.elements.main.exists ? '✅' : '❌'}`);
            
            if (analysis.elements.header.exists) {
                console.log(`    헤더 높이: ${Math.round(analysis.elements.header.height)}px`);
                console.log(`    헤더 위치: ${analysis.elements.header.position} (z-index: ${analysis.elements.header.zIndex})`);
                console.log(`    헤더 하단: ${Math.round(analysis.overlap.headerBottom)}px`);
            }
            
            if (analysis.elements.main.exists) {
                console.log(`    메인 상단: ${Math.round(analysis.overlap.mainTop)}px`);
                console.log(`    메인 여백: margin-top=${analysis.elements.main.marginTop}, padding-top=${analysis.elements.main.paddingTop}`);
            }
            
            if (analysis.elements.form.exists) {
                console.log(`    폼 상단: ${Math.round(analysis.overlap.formTop)}px`);
            }
            
            console.log(`    겹침 여부: ${analysis.overlap.isOverlapping ? '❌ 겹침' : '✅ 정상'}`);
            if (analysis.overlap.isOverlapping) {
                console.log(`    겹침 정도: ${Math.round(analysis.overlap.amount)}px`);
            }
            
            await page.close();
        }
        
        return {
            screenshots: [
                'header-content-overlap-desktop.png',
                'header-content-overlap-tablet.png', 
                'header-content-overlap-mobile.png'
            ]
        };
        
    } catch (error) {
        console.error('❌ 오류 발생:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

analyzeHeaderOverlap()
    .then(result => {
        console.log('\n🎉 헤더-컨텐츠 겹침 분석 완료!');
        console.log('📸 생성된 스크린샷들:');
        result.screenshots.forEach(screenshot => {
            console.log(`  - ${screenshot}`);
        });
    })
    .catch(console.error);