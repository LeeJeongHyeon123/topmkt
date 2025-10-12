/**
 * 모바일 헤더 프로필 이미지 위치 정밀 검사 도구
 * v3.15.1 - 2025-08-16
 */

const { chromium } = require('playwright');

async function testMobileHeaderProfile() {
    console.log('🔍 모바일 헤더 프로필 이미지 위치 정밀 검사 시작...\n');
    
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
        
        // DevLogin Helper로 사용자 ID 4 로그인
        console.log('1️⃣ DevLogin Helper로 사용자 ID 4 로그인...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForTimeout(2000);
        
        // 메인 페이지로 이동
        console.log('2️⃣ 메인 페이지로 이동...');
        await page.goto('https://www.topmktx.com/');
        await page.waitForTimeout(3000);
        
        // 헤더 요소들 위치 분석
        console.log('3️⃣ 헤더 요소들 위치 분석...');
        
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
                    left: headerRect.left,
                    top: headerRect.top,
                    right: headerRect.right,
                    bottom: headerRect.bottom,
                    width: headerRect.width,
                    height: headerRect.height
                },
                headerContent: {
                    left: headerContentRect.left,
                    top: headerContentRect.top,
                    right: headerContentRect.right,
                    bottom: headerContentRect.bottom,
                    width: headerContentRect.width,
                    height: headerContentRect.height,
                    padding: window.getComputedStyle(headerContent).padding
                },
                navAuth: {
                    left: navAuthRect.left,
                    top: navAuthRect.top,
                    right: navAuthRect.right,
                    bottom: navAuthRect.bottom,
                    width: navAuthRect.width,
                    height: navAuthRect.height,
                    marginLeft: window.getComputedStyle(navAuth).marginLeft
                },
                userMenu: {
                    left: userMenuRect.left,
                    top: userMenuRect.top,
                    right: userMenuRect.right,
                    bottom: userMenuRect.bottom,
                    width: userMenuRect.width,
                    height: userMenuRect.height,
                    padding: window.getComputedStyle(userMenu).padding
                },
                userAvatar: {
                    left: userAvatarRect.left,
                    top: userAvatarRect.top,
                    right: userAvatarRect.right,
                    bottom: userAvatarRect.bottom,
                    width: userAvatarRect.width,
                    height: userAvatarRect.height,
                    borderRadius: window.getComputedStyle(userAvatar).borderRadius
                },
                userAvatarImg: userAvatarImgRect ? {
                    left: userAvatarImgRect.left,
                    top: userAvatarImgRect.top,
                    right: userAvatarImgRect.right,
                    bottom: userAvatarImgRect.bottom,
                    width: userAvatarImgRect.width,
                    height: userAvatarImgRect.height,
                    objectFit: window.getComputedStyle(userAvatarImg).objectFit
                } : null,
                rightMargin: window.innerWidth - userMenuRect.right,
                isProfileCropped: userMenuRect.right > window.innerWidth,
                overflowAmount: Math.max(0, userMenuRect.right - window.innerWidth)
            };
        });
        
        console.log('📱 모바일 헤더 분석 결과:');
        console.log('==========================================');
        console.log(`화면 크기: ${headerAnalysis.viewport.width}x${headerAnalysis.viewport.height}`);
        console.log(`\n🏷️ 헤더 (.main-header):`);
        console.log(`  위치: left=${headerAnalysis.header.left}, top=${headerAnalysis.header.top}`);
        console.log(`  크기: ${headerAnalysis.header.width}x${headerAnalysis.header.height}`);
        
        console.log(`\n📦 헤더 콘텐츠 (.header-content):`);
        console.log(`  위치: left=${headerAnalysis.headerContent.left}, right=${headerAnalysis.headerContent.right}`);
        console.log(`  크기: ${headerAnalysis.headerContent.width}x${headerAnalysis.headerContent.height}`);
        console.log(`  패딩: ${headerAnalysis.headerContent.padding}`);
        
        console.log(`\n🔐 네비게이션 인증 (.nav-auth):`);
        console.log(`  위치: left=${headerAnalysis.navAuth.left}, right=${headerAnalysis.navAuth.right}`);
        console.log(`  크기: ${headerAnalysis.navAuth.width}x${headerAnalysis.navAuth.height}`);
        console.log(`  margin-left: ${headerAnalysis.navAuth.marginLeft}`);
        
        console.log(`\n👤 사용자 메뉴 (.user-menu):`);
        console.log(`  위치: left=${headerAnalysis.userMenu.left}, right=${headerAnalysis.userMenu.right}`);
        console.log(`  크기: ${headerAnalysis.userMenu.width}x${headerAnalysis.userMenu.height}`);
        console.log(`  패딩: ${headerAnalysis.userMenu.padding}`);
        
        console.log(`\n🖼️ 사용자 아바타 (.user-avatar):`);
        console.log(`  위치: left=${headerAnalysis.userAvatar.left}, right=${headerAnalysis.userAvatar.right}`);
        console.log(`  크기: ${headerAnalysis.userAvatar.width}x${headerAnalysis.userAvatar.height}`);
        console.log(`  border-radius: ${headerAnalysis.userAvatar.borderRadius}`);
        
        if (headerAnalysis.userAvatarImg) {
            console.log(`\n🖼️ 아바타 이미지 (.user-avatar img):`);
            console.log(`  위치: left=${headerAnalysis.userAvatarImg.left}, right=${headerAnalysis.userAvatarImg.right}`);
            console.log(`  크기: ${headerAnalysis.userAvatarImg.width}x${headerAnalysis.userAvatarImg.height}`);
            console.log(`  object-fit: ${headerAnalysis.userAvatarImg.objectFit}`);
        }
        
        console.log(`\n📏 오른쪽 여백 분석:`);
        console.log(`  오른쪽 여백: ${headerAnalysis.rightMargin}px`);
        console.log(`  프로필 잘림 여부: ${headerAnalysis.isProfileCropped ? '❌ 잘림' : '✅ 정상'}`);
        if (headerAnalysis.overflowAmount > 0) {
            console.log(`  넘치는 크기: ${headerAnalysis.overflowAmount}px`);
        }
        
        // 스크린샷 촬영
        console.log('\n📸 스크린샷 촬영...');
        await page.screenshot({ 
            path: '/var/www/html/topmkt/mobile_header_profile_check.png',
            fullPage: false 
        });
        
        // 문제가 있는 경우 해결책 제시
        if (headerAnalysis.isProfileCropped || headerAnalysis.rightMargin < 10) {
            console.log('\n⚠️ 문제 발견:');
            if (headerAnalysis.isProfileCropped) {
                console.log('  - 프로필 이미지가 화면 밖으로 넘어가서 잘립니다.');
            }
            if (headerAnalysis.rightMargin < 10) {
                console.log(`  - 오른쪽 여백이 너무 적습니다. (${headerAnalysis.rightMargin}px)`);
            }
            
            console.log('\n💡 해결 방법:');
            console.log('  1. .header-content에 padding-right 추가');
            console.log('  2. .nav-auth에 margin-right 추가');
            console.log('  3. .user-menu 크기 조정');
            console.log('  4. container padding 조정');
        } else {
            console.log('\n✅ 프로필 이미지가 정상적으로 표시되고 있습니다!');
        }
        
        console.log('\n📸 스크린샷 저장 완료: mobile_header_profile_check.png');
        
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
    testMobileHeaderProfile().then(result => {
        if (result) {
            console.log('\n🎉 모바일 헤더 프로필 이미지 검사 완료!');
        }
        process.exit(0);
    }).catch(console.error);
}

module.exports = { testMobileHeaderProfile };