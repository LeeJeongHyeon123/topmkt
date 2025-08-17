/**
 * 개발용 테스트 템플릿
 * 
 * 새로운 기능 테스트할 때 이 파일을 복사해서 사용하세요!
 * 
 * 사용법:
 * 1. 이 파일을 복사: cp test_template_dev.cjs my_new_test.cjs  
 * 2. testMyFeature() 함수 내용 수정
 * 3. 실행: node my_new_test.cjs
 */

const DevLoginHelper = require('./test_helpers/DevLoginHelper.cjs');

async function testMyFeature() {
    console.log('🚀 새로운 기능 테스트 시작');
    
    const helper = new DevLoginHelper();
    let session = null;
    
    try {
        // 1. 개발용 세션 생성 (비밀번호 없음!)
        console.log('🔧 1. 개발용 세션 생성...');
        session = await helper.getDevSession('우리집탄이', {
            headless: true,  // false로 하면 브라우저가 보임
            timeout: 30000
        });
        
        const { page, account } = session;
        console.log('✅ 세션 생성 성공');

        // 2. 원하는 페이지로 이동 (여기를 수정하세요!)
        console.log('📄 2. 테스트 페이지 접근...');
        const success = await helper.gotoWithDevAuth(
            page, 
            'https://www.topmktx.com/admin/users',  // <- 이 URL을 원하는 페이지로 변경
            account
        );
        
        if (!success) {
            throw new Error('페이지 접근 실패');
        }
        
        await page.waitForTimeout(3000);
        console.log('✅ 페이지 접근 성공');

        // 3. 여기에 테스트 로직 작성하세요!
        console.log('🧪 3. 기능 테스트 시작...');
        
        // 예시: 특정 요소 클릭
        // await page.click('.some-button');
        
        // 예시: 텍스트 입력
        // await page.fill('#some-input', '테스트 값');
        
        // 예시: 스크린샷 저장
        await page.screenshot({ 
            path: '/var/www/html/topmkt/my_test_result.png',
            fullPage: true 
        });
        console.log('📸 테스트 스크린샷 저장');

        // 예시: 특정 요소 존재 확인
        const elementExists = await page.$('.some-element') !== null;
        console.log('🔍 요소 존재:', elementExists);

        console.log('🎉 테스트 완료!');
        return true;

    } catch (error) {
        console.error('❌ 테스트 실패:', error.message);
        
        if (session && session.page) {
            await session.page.screenshot({ 
                path: '/var/www/html/topmkt/my_test_error.png',
                fullPage: true 
            });
            console.log('📸 오류 스크린샷 저장');
        }
        
        return false;
    } finally {
        if (session) {
            await helper.cleanup(session);
        }
    }
}

// 테스트 실행
testMyFeature().then(success => {
    if (success) {
        console.log('✅ 테스트 성공!');
        process.exit(0);
    } else {
        console.log('❌ 테스트 실패!');
        process.exit(1);
    }
}).catch(error => {
    console.error('🔥 예상치 못한 오류:', error);
    process.exit(1);
});