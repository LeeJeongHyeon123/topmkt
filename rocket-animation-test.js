#!/usr/bin/env node

/**
 * 탑마케팅 웹사이트 로고 애니메이션 테스트 스크립트
 * 메인 페이지 로켓 착륙 애니메이션과 서브 페이지 정적 애니메이션 비교
 */

import { chromium } from 'playwright';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

async function testRocketAnimation() {
    console.log('🚀 탑마케팅 로고 애니메이션 테스트 시작...');
    
    // 브라우저 실행
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-dev-shm-usage']
    });
    
    const context = await browser.newContext({
        viewport: { width: 1920, height: 1080 }
    });
    
    const page = await context.newPage();
    
    try {
        // 1. 메인 페이지 방문 및 로켓 착륙 애니메이션 캡처
        console.log('📍 메인 페이지 방문 중...');
        await page.goto('https://www.topmktx.com/', { waitUntil: 'networkidle' });
        
        console.log('⏱️  로켓 착륙 애니메이션 완료 대기 (3초)...');
        await page.waitForTimeout(3000);
        
        const screenshotPath1 = join(__dirname, 'main-page-rocket-animation.png');
        await page.screenshot({ 
            path: screenshotPath1,
            fullPage: false,
            type: 'png'
        });
        console.log(`✅ 메인 페이지 스크린샷 저장: ${screenshotPath1}`);
        
        // 2. 커뮤니티 페이지 방문 및 정적 애니메이션 캡처
        console.log('📍 커뮤니티 페이지 방문 중...');
        await page.goto('https://www.topmktx.com/community', { waitUntil: 'networkidle' });
        
        console.log('⏱️  페이지 로드 대기 (2초)...');
        await page.waitForTimeout(2000);
        
        const screenshotPath2 = join(__dirname, 'community-page-rocket-static.png');
        await page.screenshot({ 
            path: screenshotPath2,
            fullPage: false,
            type: 'png'
        });
        console.log(`✅ 커뮤니티 페이지 스크린샷 저장: ${screenshotPath2}`);
        
        // 3. 강의 페이지 방문 및 정적 애니메이션 캡처  
        console.log('📍 강의 페이지 방문 중...');
        await page.goto('https://www.topmktx.com/lectures', { waitUntil: 'networkidle' });
        
        console.log('⏱️  페이지 로드 대기 (2초)...');
        await page.waitForTimeout(2000);
        
        const screenshotPath3 = join(__dirname, 'lectures-page-rocket-static.png');
        await page.screenshot({ 
            path: screenshotPath3,
            fullPage: false,
            type: 'png'
        });
        console.log(`✅ 강의 페이지 스크린샷 저장: ${screenshotPath3}`);
        
        console.log('\n🎯 테스트 완료! 생성된 스크린샷:');
        console.log(`   - 메인 페이지 (로켓 착륙): main-page-rocket-animation.png`);
        console.log(`   - 커뮤니티 페이지 (정적): community-page-rocket-static.png`);
        console.log(`   - 강의 페이지 (정적): lectures-page-rocket-static.png`);
        
        // 로켓 애니메이션 상태 확인을 위한 추가 정보 수집
        await page.goto('https://www.topmktx.com/', { waitUntil: 'networkidle' });
        const rocketInfo = await page.evaluate(() => {
            const rocket = document.querySelector('.rocket');
            const rocketContainer = document.querySelector('.logo-animation-container');
            
            return {
                rocketExists: !!rocket,
                rocketContainer: !!rocketContainer,
                rocketClasses: rocket ? rocket.className : null,
                containerClasses: rocketContainer ? rocketContainer.className : null,
                animationState: window.getComputedStyle ? 
                    (rocket ? window.getComputedStyle(rocket).animationName : null) : null
            };
        });
        
        console.log('\n🔍 로켓 애니메이션 상태 정보:');
        console.log(`   - 로켓 요소 존재: ${rocketInfo.rocketExists}`);
        console.log(`   - 컨테이너 존재: ${rocketInfo.rocketContainer}`);
        console.log(`   - 로켓 클래스: ${rocketInfo.rocketClasses}`);
        console.log(`   - 컨테이너 클래스: ${rocketInfo.containerClasses}`);
        console.log(`   - 애니메이션 상태: ${rocketInfo.animationState}`);
        
    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error.message);
    } finally {
        await browser.close();
        console.log('🔚 브라우저 종료 완료');
    }
}

// 스크립트 실행
testRocketAnimation().catch(console.error);