/**
 * 🚀 Ultra Think: 업로드 관련 요소 확인
 */

const { chromium } = require('playwright');

async function testUploadElements() {
    console.log('🚀 업로드 관련 요소 확인');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
    });
    
    try {
        const context = await browser.newContext({
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });
        
        const page = await context.newPage();
        
        console.log('1️⃣ 공지사항 작성 페이지 접근...');
        await page.goto('https://www.topmktx.com/notices/write', { 
            waitUntil: 'domcontentloaded',
            timeout: 30000
        });
        
        await page.waitForTimeout(3000);
        
        // 페이지 제목 확인
        const title = await page.title();
        console.log('📄 페이지 제목:', title);
        
        // input[type="file"] 요소들 확인
        const fileInputs = await page.$$eval('input[type="file"]', 
            inputs => inputs.map(input => ({
                id: input.id,
                name: input.name,
                className: input.className,
                accept: input.accept,
                multiple: input.multiple
            }))
        );
        
        console.log('📁 파일 입력 요소들:', fileInputs.length, '개');
        fileInputs.forEach((input, index) => {
            console.log(`   ${index + 1}. ID: ${input.id}, NAME: ${input.name}, CLASS: ${input.className}`);
            console.log(`      ACCEPT: ${input.accept}, MULTIPLE: ${input.multiple}`);
        });
        
        // 이미지 업로드 관련 요소들 확인
        const uploadElements = await page.evaluate(() => {
            const elements = [];
            
            // ID로 찾기
            const byId = ['imageInput', 'uploadArea', 'uploadProgress'];
            byId.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    elements.push({
                        type: 'ID',
                        selector: '#' + id,
                        tagName: el.tagName,
                        visible: el.offsetParent !== null
                    });
                }
            });
            
            // 클래스로 찾기
            const byClass = ['.upload-area', '.upload-input', '.upload-button'];
            byClass.forEach(className => {
                const els = document.querySelectorAll(className);
                els.forEach((el, index) => {
                    elements.push({
                        type: 'CLASS',
                        selector: className + (els.length > 1 ? `[${index}]` : ''),
                        tagName: el.tagName,
                        visible: el.offsetParent !== null
                    });
                });
            });
            
            return elements;
        });
        
        console.log('🎯 업로드 관련 요소들:', uploadElements.length, '개');
        uploadElements.forEach((el, index) => {
            console.log(`   ${index + 1}. ${el.type}: ${el.selector} (${el.tagName}) - 보임: ${el.visible ? '✅' : '❌'}`);
        });
        
        // Quill 에디터 확인
        const quillElements = await page.evaluate(() => {
            const elements = [];
            const quillToolbar = document.querySelector('.ql-toolbar');
            const quillContainer = document.querySelector('.ql-container');
            const quillEditor = document.querySelector('.ql-editor');
            
            if (quillToolbar) elements.push({ name: 'ql-toolbar', exists: true });
            if (quillContainer) elements.push({ name: 'ql-container', exists: true });
            if (quillEditor) elements.push({ name: 'ql-editor', exists: true });
            
            return elements;
        });
        
        console.log('📝 Quill 에디터 요소들:', quillElements.length, '개');
        quillElements.forEach(el => {
            console.log(`   - ${el.name}: ${el.exists ? '✅' : '❌'}`);
        });
        
        // 페이지 소스에서 imageInput 검색
        const pageContent = await page.content();
        const hasImageInput = pageContent.includes('imageInput');
        const hasUploadArea = pageContent.includes('uploadArea');
        const hasFileInput = pageContent.includes('type="file"');
        
        console.log('🔍 페이지 소스 검색:');
        console.log('   imageInput 포함:', hasImageInput ? '✅' : '❌');
        console.log('   uploadArea 포함:', hasUploadArea ? '✅' : '❌');
        console.log('   type="file" 포함:', hasFileInput ? '✅' : '❌');
        
        // 에러 메시지 확인
        const hasErrorText = pageContent.includes('오류') || pageContent.includes('error');
        console.log('   에러 메시지 포함:', hasErrorText ? '⚠️ 있음' : '✅ 없음');
        
        // JavaScript 에러 확인
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        
        await page.waitForTimeout(2000);
        
        if (errors.length > 0) {
            console.log('⚠️ JavaScript 에러:', errors.length, '개');
            errors.forEach((error, index) => {
                console.log(`   ${index + 1}. ${error}`);
            });
        } else {
            console.log('✅ JavaScript 에러 없음');
        }
        
        // 스크린샷 저장
        await page.screenshot({ path: '/tmp/upload_elements_screenshot.png' });
        console.log('📷 스크린샷 저장: /tmp/upload_elements_screenshot.png');
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error.message);
    } finally {
        await browser.close();
    }
}

// 테스트 실행
testUploadElements();