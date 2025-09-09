const { chromium } = require('playwright');

/**
 * 🚨 긴급 디버깅: 취소 버튼이 실제로 confirm을 표시하지 않는 문제 분석
 */

async function debugCancelButton() {
    console.log('🚨 긴급 디버깅: 취소 버튼 confirm 문제 분석 시작');
    
    const browser = await chromium.launch({ 
        headless: true, // 서버 환경에서는 헤드리스 모드 필수
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        const page = await context.newPage();
        
        // 모든 콘솔 로그 캡처
        page.on('console', msg => {
            console.log(`[페이지 콘솔] ${msg.type()}: ${msg.text()}`);
        });
        
        // JavaScript 에러 캡처
        page.on('pageerror', error => {
            console.log(`🔴 [JavaScript 에러] ${error.message}`);
        });
        
        // DevLoginHelper로 자동 로그인
        console.log('🔐 로그인...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForLoadState('networkidle');
        
        // 커뮤니티 글쓰기 페이지로 이동
        console.log('📝 글쓰기 페이지 이동...');
        await page.goto('https://www.topmktx.com/community/write');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(3000);
        
        // 페이지 소스에서 취소 버튼 확인
        console.log('\\n🔍 HTML 구조 분석:');
        const cancelBtnHtml = await page.locator('#cancelBtn').innerHTML();
        console.log(`취소 버튼 HTML: ${cancelBtnHtml}`);
        
        // JavaScript 변수들 확인
        console.log('\\n🔍 JavaScript 상태 분석:');
        const jsDebug = await page.evaluate(() => {
            const cancelBtn = document.getElementById('cancelBtn');
            const titleInput = document.getElementById('title');
            const quillEditor = document.querySelector('#quill-editor');
            
            return {
                cancelBtnExists: !!cancelBtn,
                titleInputExists: !!titleInput,
                quillExists: !!quillEditor,
                quillWindow: !!window.quill,
                cancelBtnTagName: cancelBtn?.tagName,
                cancelBtnType: cancelBtn?.type,
                hasClickListener: cancelBtn?._hasClickListener || 'unknown'
            };
        });
        
        console.log('JavaScript 상태:', JSON.stringify(jsDebug, null, 2));
        
        // 제목 입력 후 취소 테스트
        console.log('\\n🧪 실제 동작 테스트:');
        await page.fill('#title', '테스트 제목 - confirm 확인');
        console.log('제목 입력 완료');
        
        // Dialog 이벤트 모니터링
        let dialogCaught = false;
        page.on('dialog', async dialog => {
            console.log(`🚨 DIALOG 감지: "${dialog.message()}"`);
            dialogCaught = true;
            await dialog.dismiss();
        });
        
        // 취소 버튼 클릭 전 상태 확인
        const preClickState = await page.evaluate(() => {
            const cancelBtn = document.getElementById('cancelBtn');
            const titleValue = document.getElementById('title').value;
            
            return {
                titleValue,
                cancelBtnVisible: cancelBtn ? getComputedStyle(cancelBtn).display : 'not found'
            };
        });
        
        console.log('클릭 전 상태:', preClickState);
        
        // 실제 취소 버튼 클릭
        console.log('\\n🖱️ 취소 버튼 클릭...');
        await page.click('#cancelBtn');
        await page.waitForTimeout(2000);
        
        const finalUrl = page.url();
        
        console.log('\\n📊 결과 분석:');
        console.log(`Dialog 감지됨: ${dialogCaught}`);
        console.log(`최종 URL: ${finalUrl}`);
        console.log(`예상된 동작: Dialog 표시 후 사용자 선택`);
        console.log(`실제 동작: ${dialogCaught ? 'Dialog 표시됨' : 'Dialog 없이 바로 이동'}`);
        
        if (!dialogCaught) {
            console.log('\\n🔴 문제 확인: Confirm Dialog가 표시되지 않음!');
            console.log('가능한 원인:');
            console.log('1. JavaScript 이벤트 핸들러가 등록되지 않음');
            console.log('2. 변수 참조 오류');
            console.log('3. 스크립트 로드 순서 문제');
            console.log('4. 브라우저 캐시 문제');
        }
        
    } catch (error) {
        console.error('💥 디버깅 오류:', error);
    } finally {
        await browser.close();
    }
}

debugCancelButton();