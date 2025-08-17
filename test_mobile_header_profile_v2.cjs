/**
 * 모바일 헤더 프로필 이미지 위치 정밀 검사 도구 v2
 * v3.15.2 - 2025-08-16 (캐시 우회 버전)
 */

const { chromium } = require('playwright');

async function testMobileHeaderProfileV2() {
    console.log('🔍 모바일 헤더 프로필 이미지 위치 정밀 검사 v2 시작...\n');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--disable-blink-features=AutomationControlled', '--no-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 390, height: 844 }, // iPhone 12 Pro 크기
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15'
        });
        
        const page = await context.newPage();
        
        // 브라우저 캐시 완전 무력화
        await page.setExtraHTTPHeaders({
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
        });
        
        // DevLogin Helper로 사용자 ID 4 로그인
        console.log('1️⃣ DevLogin Helper로 사용자 ID 4 로그인...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForTimeout(2000);
        
        // 메인 페이지로 이동 (timestamp로 캐시 우회)
        const timestamp = Date.now();
        console.log('2️⃣ 메인 페이지로 이동 (캐시 우회)...');
        await page.goto(`https://www.topmktx.com/?cache_bust=${timestamp}`);
        await page.waitForTimeout(3000);
        
        // CSS 강제 새로고침
        console.log('3️⃣ CSS 강제 새로고침...');
        await page.addStyleTag({
            content: `
                /* 긴급 모바일 헤더 수정 - 인라인 강제 적용 */
                @media (max-width: 768px) {
                    .main-header .container {
                        padding-left: 16px !important;
                        padding-right: 20px !important;
                        box-sizing: border-box !important;
                    }
                    
                    .header-content {
                        position: relative !important;
                        width: 100% !important;
                        max-width: calc(100vw - 32px) !important;
                        overflow: hidden !important;
                    }
                    
                    .nav-auth {
                        position: absolute !important;
                        right: 0 !important;
                        top: 50% !important;
                        transform: translateY(-50%) !important;
                        margin-right: 0 !important;
                    }
                    
                    .user-menu {
                        width: 50px !important;
                        height: 50px !important;
                        padding: 8px !important;
                        border-radius: 25px !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                    }
                    
                    .user-avatar {
                        width: 32px !important;
                        height: 32px !important;
                        margin: 0 !important;
                    }
                    
                    .user-name {
                        display: none !important;
                    }
                    
                    .user-menu i {
                        display: none !important;
                    }
                }
            `
        });
        
        await page.waitForTimeout(1000);
        
        // 헤더 요소들 위치 분석
        console.log('4️⃣ 헤더 요소들 위치 분석...');
        
        const headerAnalysis = await page.evaluate(() => {
            const header = document.querySelector('.main-header');
            const headerContent = document.querySelector('.header-content');
            const navAuth = document.querySelector('.nav-auth');
            const userMenu = document.querySelector('.user-menu');
            const userAvatar = document.querySelector('.user-avatar');
            const userAvatarImg = document.querySelector('.user-avatar img');
            
            if (!header || !headerContent || !navAuth || !userMenu || !userAvatar) {
                return { error: '필수 헤더 요소를 찾을 수 없습니다.' };
            }
            
            const headerRect = header.getBoundingClientRect();
            const headerContentRect = headerContent.getBoundingClientRect();
            const navAuthRect = navAuth.getBoundingClientRect();
            const userMenuRect = userMenu.getBoundingClientRect();
            const userAvatarRect = userAvatar.getBoundingClientRect();
            const userAvatarImgRect = userAvatarImg ? userAvatarImg.getBoundingClientRect() : null;
            
            return {
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                },
                header: {
                    left: Math.round(headerRect.left),
                    top: Math.round(headerRect.top),
                    right: Math.round(headerRect.right),
                    bottom: Math.round(headerRect.bottom),
                    width: Math.round(headerRect.width),
                    height: Math.round(headerRect.height)
                },
                headerContent: {
                    left: Math.round(headerContentRect.left),
                    top: Math.round(headerContentRect.top),
                    right: Math.round(headerContentRect.right),
                    bottom: Math.round(headerContentRect.bottom),
                    width: Math.round(headerContentRect.width),
                    height: Math.round(headerContentRect.height)
                },
                navAuth: {
                    left: Math.round(navAuthRect.left),
                    top: Math.round(navAuthRect.top),
                    right: Math.round(navAuthRect.right),
                    bottom: Math.round(navAuthRect.bottom),
                    width: Math.round(navAuthRect.width),
                    height: Math.round(navAuthRect.height)
                },
                userMenu: {
                    left: Math.round(userMenuRect.left),
                    top: Math.round(userMenuRect.top),
                    right: Math.round(userMenuRect.right),
                    bottom: Math.round(userMenuRect.bottom),
                    width: Math.round(userMenuRect.width),
                    height: Math.round(userMenuRect.height)
                },
                userAvatar: {
                    left: Math.round(userAvatarRect.left),
                    top: Math.round(userAvatarRect.top),
                    right: Math.round(userAvatarRect.right),
                    bottom: Math.round(userAvatarRect.bottom),
                    width: Math.round(userAvatarRect.width),
                    height: Math.round(userAvatarRect.height)
                },
                userAvatarImg: userAvatarImgRect ? {
                    left: Math.round(userAvatarImgRect.left),
                    top: Math.round(userAvatarImgRect.top),
                    right: Math.round(userAvatarImgRect.right),
                    bottom: Math.round(userAvatarImgRect.bottom),
                    width: Math.round(userAvatarImgRect.width),
                    height: Math.round(userAvatarImgRect.height)
                } : null,
                rightMargin: window.innerWidth - Math.round(userMenuRect.right),
                isProfileCropped: userMenuRect.right > window.innerWidth,
                overflowAmount: Math.max(0, Math.round(userMenuRect.right - window.innerWidth))
            };
        });
        
        console.log('📱 모바일 헤더 분석 결과 (수정 후):');
        console.log('==========================================');
        console.log(`화면 크기: ${headerAnalysis.viewport.width}x${headerAnalysis.viewport.height}`);
        
        console.log(`\n👤 사용자 메뉴 (.user-menu):`);
        console.log(`  위치: left=${headerAnalysis.userMenu.left}, right=${headerAnalysis.userMenu.right}`);
        console.log(`  크기: ${headerAnalysis.userMenu.width}x${headerAnalysis.userMenu.height}`);
        
        console.log(`\n🖼️ 사용자 아바타 (.user-avatar):`);
        console.log(`  위치: left=${headerAnalysis.userAvatar.left}, right=${headerAnalysis.userAvatar.right}`);
        console.log(`  크기: ${headerAnalysis.userAvatar.width}x${headerAnalysis.userAvatar.height}`);
        
        if (headerAnalysis.userAvatarImg) {
            console.log(`\n🖼️ 아바타 이미지 (.user-avatar img):`);
            console.log(`  위치: left=${headerAnalysis.userAvatarImg.left}, right=${headerAnalysis.userAvatarImg.right}`);
            console.log(`  크기: ${headerAnalysis.userAvatarImg.width}x${headerAnalysis.userAvatarImg.height}`);
        }
        
        console.log(`\n📏 오른쪽 여백 분석:`);
        console.log(`  오른쪽 여백: ${headerAnalysis.rightMargin}px`);
        console.log(`  프로필 잘림 여부: ${headerAnalysis.isProfileCropped ? '❌ 잘림' : '✅ 정상'}`);
        if (headerAnalysis.overflowAmount > 0) {
            console.log(`  넘치는 크기: ${headerAnalysis.overflowAmount}px`);
        }
        
        // 수정 후 스크린샷 촬영
        console.log('\n📸 수정 후 스크린샷 촬영...');
        await page.screenshot({ 
            path: '/var/www/html/topmkt/mobile_header_profile_fixed.png',
            fullPage: false 
        });
        
        // 결과 판정
        if (!headerAnalysis.isProfileCropped && headerAnalysis.rightMargin >= 5) {
            console.log('\n🎉 ✅ 문제 해결 완료!');
            console.log('  - 프로필 이미지가 정상적으로 표시됩니다.');
            console.log(`  - 충분한 오른쪽 여백이 확보되었습니다. (${headerAnalysis.rightMargin}px)`);
        } else {
            console.log('\n⚠️ 추가 조치 필요:');
            if (headerAnalysis.isProfileCropped) {
                console.log('  - 프로필 이미지가 여전히 잘립니다.');
            }
            if (headerAnalysis.rightMargin < 5) {
                console.log(`  - 오른쪽 여백이 부족합니다. (${headerAnalysis.rightMargin}px)`);
            }
        }
        
        console.log('\n📸 수정 후 스크린샷 저장 완료: mobile_header_profile_fixed.png');
        
        return headerAnalysis;
        
    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error);
        return null;
    } finally {
        await browser.close();
    }
}

// 스크립트 실행
if (require.main === module) {
    testMobileHeaderProfileV2().then(result => {
        if (result) {
            console.log('\n🎉 모바일 헤더 프로필 이미지 검사 v2 완료!');
        }
        process.exit(0);
    }).catch(console.error);
}

module.exports = { testMobileHeaderProfileV2 };