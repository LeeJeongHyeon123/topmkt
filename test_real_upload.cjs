/**
 * 🚀 Ultra Think: 실제 브라우저 환경에서 이미지 업로드 테스트
 */

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

async function testRealImageUpload() {
    console.log('🚀 실제 브라우저 환경에서 이미지 업로드 테스트');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
    });
    
    try {
        const context = await browser.newContext({
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });
        
        const page = await context.newPage();
        
        // 네트워크 요청 모니터링
        const uploadRequests = [];
        page.on('request', request => {
            if (request.url().includes('/api/media/upload-image')) {
                uploadRequests.push({
                    url: request.url(),
                    method: request.method(),
                    headers: request.headers()
                });
                console.log('📤 업로드 API 호출 감지:', request.url());
            }
        });
        
        page.on('response', response => {
            if (response.url().includes('/api/media/upload-image')) {
                console.log('📥 업로드 API 응답:', response.status(), response.statusText());
            }
        });
        
        // 콘솔 로그 캡처
        page.on('console', msg => {
            if (msg.text().includes('업로드') || msg.text().includes('upload') || msg.text().includes('error')) {
                console.log('🖥️ 브라우저 콘솔:', msg.text());
            }
        });
        
        // 에러 캡처
        page.on('pageerror', error => {
            console.log('⚠️ 페이지 에러:', error.message);
        });
        
        console.log('1️⃣ 공지사항 작성 페이지 접근...');
        await page.goto('https://www.topmktx.com/notices/write', { 
            waitUntil: 'domcontentloaded',
            timeout: 30000
        });
        
        await page.waitForTimeout(3000);
        
        // 로그인 상태 확인
        const isLoggedIn = await page.evaluate(() => {
            return document.body.textContent.includes('로그아웃') || 
                   document.body.textContent.includes('마이페이지') ||
                   !document.body.textContent.includes('로그인');
        });
        
        console.log('🔐 로그인 상태:', isLoggedIn ? '✅ 로그인됨' : '❌ 로그인 필요');
        
        if (!isLoggedIn) {
            console.log('❌ 로그인이 필요합니다. 테스트를 종료합니다.');
            return;
        }
        
        // 임시 테스트 이미지 생성
        const testImagePath = '/tmp/playwright_test_image.png';
        const { createCanvas } = require('canvas');
        const canvas = createCanvas(100, 100);
        const ctx = canvas.getContext('2d');
        
        // 간단한 테스트 이미지 그리기
        ctx.fillStyle = '#FF6B35';
        ctx.fillRect(0, 0, 100, 100);
        ctx.fillStyle = '#FFFFFF';
        ctx.font = '16px Arial';
        ctx.fillText('TEST', 35, 55);
        
        // PNG 파일로 저장
        const buffer = canvas.toBuffer('image/png');
        fs.writeFileSync(testImagePath, buffer);
        
        console.log('🖼️ 테스트 이미지 생성:', testImagePath);
        console.log('   파일 크기:', buffer.length, 'bytes');
        
        console.log('2️⃣ 이미지 업로드 시도...');
        
        // 파일 입력 요소 찾기
        const fileInput = await page.$('#imageInput');
        if (!fileInput) {
            console.log('❌ 파일 입력 요소를 찾을 수 없습니다.');
            return;
        }
        
        // 파일 선택
        await fileInput.setInputFiles(testImagePath);
        console.log('✅ 파일 선택 완료');
        
        // 업로드 처리 대기 (최대 10초)
        await page.waitForTimeout(2000);
        
        // 업로드 진행률 요소 확인
        const progressVisible = await page.isVisible('#uploadProgress');
        console.log('📊 업로드 진행률 표시:', progressVisible ? '✅ 보임' : '❌ 안보임');
        
        // 업로드 완료까지 대기
        let waitCount = 0;
        while (waitCount < 10) {
            const isProgressHidden = await page.evaluate(() => {
                const progress = document.getElementById('uploadProgress');
                return !progress || progress.style.display === 'none';
            });
            
            if (isProgressHidden) {
                console.log('✅ 업로드 처리 완료');
                break;
            }
            
            await page.waitForTimeout(1000);
            waitCount++;
            console.log(`⏳ 업로드 대기 중... (${waitCount}/10초)`);
        }
        
        // 업로드된 이미지 미리보기 확인
        const uploadedImages = await page.$$eval('.uploaded-image', 
            imgs => imgs.map(img => ({
                src: img.querySelector('img')?.src || 'NO_SRC',
                dataId: img.getAttribute('data-image-id')
            }))
        );
        
        console.log('📸 업로드된 이미지 미리보기:', uploadedImages.length, '개');
        uploadedImages.forEach((img, index) => {
            console.log(`   ${index + 1}. ID: ${img.dataId}, SRC: ${img.src}`);
        });
        
        // JavaScript 오류 메시지 확인
        const errorMessages = await page.evaluate(() => {
            const alerts = [];
            // alert이 호출되었는지 감지하기 위해 console.log로 대체
            window.originalAlert = window.alert;
            window.alert = function(message) {
                alerts.push(message);
                console.log('ALERT:', message);
                return true;
            };
            return alerts;
        });
        
        console.log('⚠️ 에러 메시지:', errorMessages.length > 0 ? errorMessages : '없음');
        
        // 네트워크 요청 결과 분석
        console.log('🌐 업로드 API 호출 내역:', uploadRequests.length, '회');
        uploadRequests.forEach((req, index) => {
            console.log(`   ${index + 1}. ${req.method} ${req.url}`);
        });
        
        // 페이지 스크린샷 (디버깅용)
        await page.screenshot({ path: '/tmp/upload_test_screenshot.png' });
        console.log('📷 스크린샷 저장: /tmp/upload_test_screenshot.png');
        
        // 결론
        if (uploadedImages.length > 0) {
            console.log('🎉 결론: 업로드가 성공적으로 완료됨');
        } else if (uploadRequests.length > 0) {
            console.log('⚠️ 결론: 업로드 API 호출은 됐지만 미리보기가 생성되지 않음');
        } else {
            console.log('❌ 결론: 업로드 API 호출조차 되지 않음');
        }
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error.message);
    } finally {
        await browser.close();
        
        // 임시 파일 정리
        try {
            if (fs.existsSync('/tmp/playwright_test_image.png')) {
                fs.unlinkSync('/tmp/playwright_test_image.png');
            }
        } catch (e) {
            console.log('🧹 임시 파일 정리 실패:', e.message);
        }
    }
}

// 테스트 실행
testRealImageUpload();