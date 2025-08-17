/**
 * 강의 상세 페이지 모바일 UI 테스트
 * 사용자 피드백: "모바일 UI 완전 개판" → 완전 수정 후 검증
 */

const { chromium } = require('playwright');

async function testMobileLectureDetail() {
    console.log('🚀 강의 상세 페이지 모바일 UI 테스트 시작');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        // 모바일 뷰포트 시뮬레이션 (iPhone 13 Pro)
        const context = await browser.newContext({
            viewport: { width: 390, height: 844 },
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1'
        });
        
        const page = await context.newPage();
        
        console.log('📱 모바일 뷰포트 설정 완료 (390x844)');
        
        // 강의 상세 페이지 접속 (/lectures/3?view=list)
        await page.goto('https://www.topmktx.com/lectures/3?view=list');
        await page.waitForLoadState('networkidle');
        
        console.log('✅ 강의 상세 페이지 로드 완료');
        
        // 1. 전체 페이지 스크린샷 (수정 후)
        await page.screenshot({ 
            path: '/var/www/html/topmkt/mobile_lecture_detail_after_fix.png',
            fullPage: true 
        });
        console.log('📸 수정 후 전체 페이지 스크린샷 완료');
        
        // 2. 모바일 UI 검증 항목들
        const checks = {
            // 터치 타겟 크기 검증 (44px 이상)
            buttonSize: await page.evaluate(() => {
                const buttons = document.querySelectorAll('.btn, .btn-register, .btn-primary');
                const results = [];
                for (let btn of buttons) {
                    const rect = btn.getBoundingClientRect();
                    results.push({
                        text: btn.textContent.trim().substring(0, 20),
                        height: rect.height,
                        width: rect.width,
                        isTouch: rect.height >= 44 && rect.width >= 44
                    });
                }
                return results;
            }),
            
            // 폰트 크기 검증 (16px 이상)
            fontSize: await page.evaluate(() => {
                const elements = document.querySelectorAll('p, .meta-item, .lecture-description, .sidebar-card p');
                const results = [];
                for (let el of elements) {
                    const style = window.getComputedStyle(el);
                    const fontSize = parseFloat(style.fontSize);
                    if (el.textContent.trim() && fontSize > 0) {
                        results.push({
                            text: el.textContent.trim().substring(0, 30),
                            fontSize: fontSize,
                            isReadable: fontSize >= 16
                        });
                    }
                }
                return results.slice(0, 10); // 상위 10개만
            }),
            
            // 수평 스크롤 체크
            horizontalScroll: await page.evaluate(() => {
                return {
                    bodyScrollWidth: document.body.scrollWidth,
                    windowInnerWidth: window.innerWidth,
                    hasHorizontalScroll: document.body.scrollWidth > window.innerWidth
                };
            }),
            
            // 레이아웃 구조 검증
            layout: await page.evaluate(() => {
                const content = document.querySelector('.lecture-content');
                const sidebar = document.querySelector('.lecture-sidebar');
                const contentStyle = window.getComputedStyle(content);
                const sidebarStyle = window.getComputedStyle(sidebar);
                
                return {
                    contentGridColumns: contentStyle.gridTemplateColumns,
                    sidebarOrder: sidebarStyle.order,
                    isSingleColumn: contentStyle.gridTemplateColumns.includes('1fr') && !contentStyle.gridTemplateColumns.includes('2fr'),
                    actualColumns: contentStyle.gridTemplateColumns,
                    sidebarOrderNum: sidebarStyle.order
                };
            })
        };
        
        // 3. 스크롤 테스트 (전체 페이지 탐색)
        console.log('📜 페이지 스크롤 테스트 시작...');
        await page.evaluate(() => {
            window.scrollTo(0, document.body.scrollHeight / 3);
        });
        await page.waitForTimeout(1000);
        
        await page.evaluate(() => {
            window.scrollTo(0, document.body.scrollHeight * 2 / 3);
        });
        await page.waitForTimeout(1000);
        
        await page.evaluate(() => {
            window.scrollTo(0, document.body.scrollHeight);
        });
        await page.waitForTimeout(1000);
        
        // 4. 하단 영역 스크린샷 (사이드바가 하단으로 이동되었는지 확인)
        await page.screenshot({ 
            path: '/var/www/html/topmkt/mobile_lecture_detail_bottom.png',
            clip: { x: 0, y: 400, width: 390, height: 400 }
        });
        console.log('📸 하단 영역 스크린샷 완료');
        
        // 5. 버튼 클릭 테스트 (터치 대응 확인)
        try {
            const registerButton = await page.locator('.btn-register').first();
            if (await registerButton.isVisible()) {
                await registerButton.click();
                console.log('✅ 신청 버튼 클릭 테스트 성공');
                await page.waitForTimeout(2000);
                
                // 모달이 뜨면 닫기
                const closeBtn = await page.locator('.btn-secondary, .close, [data-dismiss="modal"]').first();
                if (await closeBtn.isVisible()) {
                    await closeBtn.click();
                    console.log('✅ 모달 닫기 완료');
                }
            }
        } catch (error) {
            console.log('ℹ️ 버튼 클릭 테스트 스킵 (로그인 필요 또는 버튼 없음)');
        }
        
        // 6. 결과 분석 및 출력
        console.log('\n📊 모바일 UI 검증 결과:');
        console.log('==========================================');
        
        // 터치 타겟 분석
        const touchTargets = checks.buttonSize.filter(btn => btn.isTouch).length;
        const totalButtons = checks.buttonSize.length;
        console.log(`🎯 터치 타겟 (44px+): ${touchTargets}/${totalButtons} (${Math.round(touchTargets/totalButtons*100)}%)`);
        
        if (touchTargets < totalButtons) {
            console.log('⚠️ 터치 타겟 미달 버튼들:');
            checks.buttonSize.filter(btn => !btn.isTouch).forEach(btn => {
                console.log(`   - "${btn.text}": ${btn.height}px × ${btn.width}px`);
            });
        }
        
        // 폰트 크기 분석
        const readableFonts = checks.fontSize.filter(el => el.isReadable).length;
        const totalTexts = checks.fontSize.length;
        console.log(`📝 가독성 폰트 (16px+): ${readableFonts}/${totalTexts} (${Math.round(readableFonts/totalTexts*100)}%)`);
        
        if (readableFonts < totalTexts) {
            console.log('⚠️ 가독성 미달 텍스트들:');
            checks.fontSize.filter(el => !el.isReadable).forEach(el => {
                console.log(`   - "${el.text}": ${el.fontSize}px`);
            });
        }
        
        // 수평 스크롤 분석
        console.log(`📱 수평 스크롤: ${checks.horizontalScroll.hasHorizontalScroll ? '❌ 있음' : '✅ 없음'}`);
        console.log(`   - Body 너비: ${checks.horizontalScroll.bodyScrollWidth}px`);
        console.log(`   - 화면 너비: ${checks.horizontalScroll.windowInnerWidth}px`);
        
        // 레이아웃 분석
        console.log(`🏗️ 레이아웃: ${checks.layout.isSingleColumn ? '✅ 단일 컬럼' : '❌ 멀티 컬럼'}`);
        console.log(`   - 실제 컬럼: ${checks.layout.actualColumns}`);
        console.log(`📋 사이드바 위치: ${(checks.layout.sidebarOrderNum === '10' || checks.layout.sidebarOrderNum === 10) ? '✅ 하단' : '❌ 기본'}`);
        console.log(`   - 사이드바 order: ${checks.layout.sidebarOrderNum}`);
        
        // 최종 평가
        const score = (
            (touchTargets / totalButtons) * 30 +
            (readableFonts / totalTexts) * 30 +
            (!checks.horizontalScroll.hasHorizontalScroll ? 20 : 0) +
            (checks.layout.isSingleColumn ? 20 : 0)
        );
        
        console.log('\n🏆 모바일 UI 최종 점수:', Math.round(score), '/ 100점');
        
        if (score >= 90) {
            console.log('✅ 모바일 UI 완전 최적화 성공! 🎉');
        } else if (score >= 70) {
            console.log('🟡 모바일 UI 개선됨, 일부 추가 작업 필요');
        } else {
            console.log('❌ 모바일 UI 추가 개선 필요');
        }
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error.message);
    } finally {
        await browser.close();
        console.log('\n🔚 모바일 UI 테스트 완료');
    }
}

// 테스트 실행
testMobileLectureDetail().catch(console.error);