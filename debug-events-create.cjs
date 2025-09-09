const { chromium } = require('playwright');

/**
 * events/create.php 디버깅 테스트
 */

async function debugEventsCreate() {
    console.log('🔍 events/create.php 디버깅 시작');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        const page = await context.newPage();
        
        // 로그인
        console.log('🔐 로그인 중...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForLoadState('networkidle');
        
        // events/create 페이지로 이동
        console.log('🔄 events/create 페이지로 이동...');
        await page.goto('https://www.topmktx.com/events/create');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(3000);
        
        // 제목 입력
        await page.fill('#title', '[DEBUG] events/create 디버깅 테스트');
        
        // DOM 요소 확인
        const debugInfo = await page.evaluate(() => {
            return {
                quillContainer: !!document.querySelector('#quill-editor'),
                quillEditor: !!document.querySelector('#quill-editor .ql-editor'),
                imageCounter: !!document.querySelector('#imageCounter'),
                quillGlobal: !!window.quill,
                updateImageCounterFunc: typeof updateImageCounter === 'function',
                windowUpdateImageCounterFunc: typeof window.updateImageCounter === 'function'
            };
        });
        
        console.log('🔍 DOM 요소 확인:', debugInfo);
        
        // 이미지 3개 추가하고 카운터 확인
        console.log('📷 이미지 3개 추가 테스트...');
        const addResult = await page.evaluate(() => {
            const editor = document.querySelector('#quill-editor .ql-editor');
            if (!editor) return { success: false, message: '에디터 없음' };
            
            for (let i = 0; i < 3; i++) {
                const img = document.createElement('img');
                img.src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAI/hQBdAAAAAElFTkSuQmCC';
                img.style.width = '50px';
                img.style.height = '30px';
                editor.appendChild(img);
            }
            
            // text-change 이벤트 수동 트리거
            if (window.quill) {
                window.quill.update();
            }
            
            return { success: true, imagesAdded: 3 };
        });
        
        console.log('결과:', addResult);
        
        // 잠시 대기 후 카운터 확인
        await page.waitForTimeout(2000);
        
        const counterCheck = await page.evaluate(() => {
            const counter = document.querySelector('#imageCounter');
            const editor = document.querySelector('#quill-editor .ql-editor');
            const images = editor ? editor.querySelectorAll('img') : [];
            
            return {
                counterText: counter ? counter.textContent : '카운터 없음',
                imageCount: images.length,
                updateImageCounterExists: typeof updateImageCounter === 'function',
                windowUpdateImageCounterExists: typeof window.updateImageCounter === 'function'
            };
        });
        
        console.log('📊 카운터 확인:', counterCheck);
        
        // updateImageCounter 함수 수동 호출
        console.log('🔧 window.updateImageCounter 수동 호출...');
        const callResult = await page.evaluate(() => {
            if (typeof window.updateImageCounter === 'function') {
                window.updateImageCounter();
                return 'window.updateImageCounter 호출 완료';
            }
            return 'window.updateImageCounter 함수 없음';
        });
        
        console.log('호출 결과:', callResult);
        
        await page.waitForTimeout(1000);
        
        const finalCheck = await page.evaluate(() => {
            const counter = document.querySelector('#imageCounter');
            const editor = document.querySelector('#quill-editor .ql-editor');
            const images = editor ? editor.querySelectorAll('img') : [];
            
            return {
                counterText: counter ? counter.textContent : '카운터 없음',
                imageCount: images.length
            };
        });
        
        console.log('📊 최종 확인:', finalCheck);
        
        // 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/debug-events-create.png',
            fullPage: true 
        });
        
        console.log('📸 스크린샷 저장: debug-events-create.png');
        
    } catch (error) {
        console.error('💥 디버깅 오류:', error);
    } finally {
        await browser.close();
    }
}

debugEventsCreate();