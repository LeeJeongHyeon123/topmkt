/**
 * 커뮤니티 기능 완전 테스트
 * 버튼 배치 변경 후 실제 기능 동작 확인
 */

const { chromium } = require('playwright');

async function testCommunityFunctionality() {
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'] 
    });
    
    try {
        const page = await browser.newPage();
        
        console.log('🔐 DevLoginHelper로 로그인 중...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForTimeout(2000);
        
        console.log('📋 커뮤니티 목록 페이지 접근...');
        await page.goto('https://www.topmktx.com/community');
        await page.waitForTimeout(2000);
        
        // 글쓰기 버튼 클릭
        console.log('✍️ 글쓰기 버튼 클릭...');
        await page.click('a[href="/community/write"]');
        await page.waitForTimeout(3000);
        
        // 페이지 로드 확인
        const pageTitle = await page.textContent('h1');
        console.log(`📝 페이지 제목: ${pageTitle}`);
        
        // 버튼 존재 및 위치 확인
        const buttons = await page.$$eval('.form-buttons .btn', buttons => {
            return buttons.map(btn => ({
                text: btn.textContent.trim(),
                id: btn.id,
                className: btn.className,
                visible: btn.offsetWidth > 0 && btn.offsetHeight > 0
            }));
        });
        
        console.log('🎯 버튼 상태 확인:');
        buttons.forEach((btn, idx) => {
            console.log(`  ${idx + 1}. "${btn.text}" (ID: ${btn.id}) - 표시: ${btn.visible ? '✅' : '❌'}`);
        });
        
        // 취소 버튼 기능 테스트 (내용 없을 때)
        console.log('\n❌ 취소 버튼 테스트 (내용 없음)...');
        
        // 취소 버튼 클릭 전에 현재 URL 확인
        const beforeUrl = page.url();
        console.log(`   현재 URL: ${beforeUrl}`);
        
        await page.click('#cancelBtn');
        await page.waitForTimeout(2000);
        
        const afterUrl = page.url();
        console.log(`   이동 후 URL: ${afterUrl}`);
        
        const cancelWorksCorrectly = afterUrl.includes('/community') && !afterUrl.includes('/write');
        console.log(`   취소 기능 동작: ${cancelWorksCorrectly ? '✅ 정상' : '❌ 오류'}`);
        
        // 다시 글쓰기 페이지로 이동
        console.log('\n📝 글쓰기 페이지 재진입...');
        await page.goto('https://www.topmktx.com/community/write');
        await page.waitForTimeout(2000);
        
        // 제목과 내용 입력
        console.log('📝 테스트 내용 입력...');
        await page.fill('#title', '버튼 배치 개선 테스트 게시글');
        await page.waitForTimeout(1000);
        
        // Quill 에디터에 내용 입력
        await page.click('.ql-editor');
        await page.type('.ql-editor', '이 게시글은 버튼 배치 개선을 테스트하기 위해 작성되었습니다.\n\n새로운 배치:\n- 취소 (좌측)\n- 작성하기 (우측)\n\n사용자 경험이 더욱 향상되었습니다!');
        await page.waitForTimeout(1000);
        
        // 내용이 있을 때 취소 버튼 테스트
        console.log('\n❌ 취소 버튼 테스트 (내용 있음)...');
        
        // confirm 대화상자 처리
        page.on('dialog', async dialog => {
            console.log(`   확인 대화상자: "${dialog.message()}"`);
            if (dialog.message().includes('작성 중인 내용이 사라집니다')) {
                console.log('   사용자가 "확인" 선택 시뮬레이션');
                await dialog.accept();
            } else {
                await dialog.dismiss();
            }
        });
        
        const beforeCancelUrl = page.url();
        await page.click('#cancelBtn');
        await page.waitForTimeout(3000);
        
        const afterCancelUrl = page.url();
        const confirmWorksCorrectly = afterCancelUrl.includes('/community') && !afterCancelUrl.includes('/write');
        console.log(`   확인 후 이동: ${confirmWorksCorrectly ? '✅ 정상' : '❌ 오류'}`);
        
        // 작성하기 기능 테스트
        console.log('\n✅ 작성하기 버튼 테스트...');
        await page.goto('https://www.topmktx.com/community/write');
        await page.waitForTimeout(2000);
        
        // 간단한 테스트 게시글 작성
        const testTitle = `테스트 게시글 ${new Date().toISOString()}`;
        await page.fill('#title', testTitle);
        await page.click('.ql-editor');
        await page.type('.ql-editor', '버튼 배치 개선 테스트를 위한 게시글입니다.');
        
        // 작성하기 버튼 클릭
        console.log('   작성하기 버튼 클릭...');
        
        // 네트워크 응답 대기
        page.on('response', response => {
            if (response.url().includes('/community/posts') && response.request().method() === 'POST') {
                console.log(`   API 응답: ${response.status()} ${response.statusText()}`);
            }
        });
        
        await page.click('#submitBtn');
        await page.waitForTimeout(5000);
        
        // 작성 후 페이지 확인
        const finalUrl = page.url();
        console.log(`   최종 URL: ${finalUrl}`);
        
        const submitWorksCorrectly = finalUrl.includes('/community') && 
                                   (finalUrl.includes('/posts/') || !finalUrl.includes('/write'));
        console.log(`   작성하기 기능 동작: ${submitWorksCorrectly ? '✅ 정상' : '❌ 오류'}`);
        
        // 종합 결과 스크린샷
        await page.screenshot({ 
            path: `/var/www/html/topmkt/community-functionality-test-result.png`,
            fullPage: true
        });
        
        console.log('\n🎉 기능 테스트 완료!');
        console.log('   결과 스크린샷: community-functionality-test-result.png');
        
        return {
            success: true,
            buttonLayout: buttons,
            cancelEmpty: cancelWorksCorrectly,
            cancelWithContent: confirmWorksCorrectly,
            submitFunction: submitWorksCorrectly
        };
        
    } catch (error) {
        console.error('❌ 테스트 실행 중 오류:', error);
        return { success: false, error: error.message };
    } finally {
        await browser.close();
    }
}

// 실행
if (require.main === module) {
    testCommunityFunctionality().then(result => {
        console.log('\n📊 최종 테스트 결과:');
        console.log(`   전체 성공: ${result.success ? '✅' : '❌'}`);
        if (result.success) {
            console.log(`   취소 (내용없음): ${result.cancelEmpty ? '✅' : '❌'}`);
            console.log(`   취소 (내용있음): ${result.cancelWithContent ? '✅' : '❌'}`);
            console.log(`   작성하기 기능: ${result.submitFunction ? '✅' : '❌'}`);
        }
        process.exit(result.success ? 0 : 1);
    });
}