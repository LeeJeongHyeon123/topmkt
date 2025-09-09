const { chromium } = require('playwright');

/**
 * user/edit.php 디버깅 테스트
 */

async function debugUserEdit() {
    console.log('🔍 user/edit.php 디버깅 시작');
    
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
        
        // user/edit (profile) 페이지로 이동
        console.log('🔄 profile/edit 페이지로 이동...');
        await page.goto('https://www.topmktx.com/profile/edit');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(3000);
        
        // DOM 요소 확인 (다양한 선택자로 확인)
        const debugInfo = await page.evaluate(() => {
            return {
                // 기본 확인
                bioEditor: !!document.querySelector('#bio-editor'),
                bioEditorQlEditor: !!document.querySelector('#bio-editor .ql-editor'),
                generalQlEditor: !!document.querySelector('.ql-editor'),
                imageCounter: !!document.querySelector('#imageCounter'),
                
                // Quill 관련
                quillGlobal: !!window.quill,
                quillInstance: typeof window.quill,
                
                // 모든 ql-editor 찾기
                allQlEditors: Array.from(document.querySelectorAll('.ql-editor')).map(el => ({
                    id: el.id,
                    className: el.className,
                    parentId: el.parentElement?.id,
                    parentClass: el.parentElement?.className
                })),
                
                // bio-editor 상세 확인
                bioEditorDetails: (() => {
                    const bioEditor = document.querySelector('#bio-editor');
                    if (!bioEditor) return null;
                    return {
                        className: bioEditor.className,
                        innerHTML: bioEditor.innerHTML.substring(0, 100) + '...',
                        children: Array.from(bioEditor.children).map(child => ({
                            tagName: child.tagName,
                            className: child.className,
                            id: child.id
                        }))
                    };
                })()
            };
        });
        
        console.log('🔍 DOM 요소 상세 확인:', JSON.stringify(debugInfo, null, 2));
        
        // 이미지 추가 테스트 (올바른 선택자 찾기)
        let editorSelector = null;
        if (debugInfo.bioEditorQlEditor) {
            editorSelector = '#bio-editor .ql-editor';
        } else if (debugInfo.generalQlEditor) {
            editorSelector = '.ql-editor';
        }
        
        if (editorSelector) {
            console.log(`📷 발견된 에디터로 이미지 테스트: ${editorSelector}`);
            
            const addResult = await page.evaluate((selector) => {
                const editor = document.querySelector(selector);
                if (!editor) return { success: false, message: '에디터 없음' };
                
                // 3개 이미지 추가
                for (let i = 0; i < 3; i++) {
                    const img = document.createElement('img');
                    img.src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAI/hQBdAAAAAElFTkSuQmCC';
                    img.style.width = '50px';
                    img.style.height = '30px';
                    editor.appendChild(img);
                }
                
                return { success: true, imagesAdded: 3, selector };
            }, editorSelector);
            
            console.log('결과:', addResult);
            
            // 카운터 확인
            await page.waitForTimeout(2000);
            
            const counterCheck = await page.evaluate(() => {
                const counter = document.querySelector('#imageCounter');
                const editor = document.querySelector('#bio-editor .ql-editor') || document.querySelector('.ql-editor');
                const images = editor ? editor.querySelectorAll('img') : [];
                
                return {
                    counterText: counter ? counter.textContent : '카운터 없음',
                    counterExists: !!counter,
                    imageCount: images.length,
                    editorExists: !!editor
                };
            });
            
            console.log('📊 카운터 확인:', counterCheck);
        } else {
            console.log('❌ 에디터를 찾을 수 없습니다.');
        }
        
        // 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/debug-user-edit.png',
            fullPage: true 
        });
        
        console.log('📸 스크린샷 저장: debug-user-edit.png');
        
    } catch (error) {
        console.error('💥 디버깅 오류:', error);
    } finally {
        await browser.close();
    }
}

debugUserEdit();