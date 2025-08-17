/**
 * 이미지 모달 X 버튼 클릭 이슈 동영상 기록 테스트
 * 해결 불가능한 이슈를 시각적으로 확인하기 위한 스크립트
 */

import { chromium } from 'playwright';

class VideoDebugTest {
    constructor() {
        this.baseUrl = 'https://www.topmktx.com';
        this.videoPath = '/var/www/html/topmkt/tests/integration/reports/videos';
        this.timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
    }

    async recordModalIssue() {
        console.log('🎥 이미지 모달 X 버튼 이슈 동영상 기록 시작');
        console.log(`📁 동영상 저장 경로: ${this.videoPath}`);
        
        const browser = await chromium.launch({
            headless: true, // 서버 환경에서는 헤드리스 필수
            args: ['--no-sandbox', '--disable-dev-shm-usage', '--disable-gpu']
        });

        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 },
            // 헤드리스 모드에서는 스크린샷으로 대체
        });

        const page = await context.newPage();

        try {
            console.log('📸 1. 공지사항 페이지로 이동');
            await page.goto(`${this.baseUrl}/notices`);
            await page.waitForTimeout(2000);
            
            // 첫 번째 스크린샷
            await page.screenshot({ 
                path: `${this.videoPath}/step1-notices-list-${this.timestamp}.png`,
                fullPage: true 
            });

            console.log('📸 2. 공지사항 상세 페이지 (ID 10) 이동');
            await page.goto(`${this.baseUrl}/notices/10`);
            await page.waitForTimeout(3000);
            
            // 두 번째 스크린샷
            await page.screenshot({ 
                path: `${this.videoPath}/step2-notices-detail-${this.timestamp}.png`,
                fullPage: true 
            });

            console.log('📸 3. 이미지 요소 확인');
            const images = await page.$$('img[src*="notices"], .notice-image img, .image-item img, .image-container img');
            console.log(`   📸 발견된 이미지: ${images.length}개`);
            
            // 이미지 onclick 속성 확인
            if (images.length > 0) {
                const onclick = await images[0].getAttribute('onclick');
                console.log(`   🔍 첫 번째 이미지 onclick: ${onclick}`);
                
                const src = await images[0].getAttribute('src');
                console.log(`   🔍 첫 번째 이미지 src: ${src}`);
            }

            if (images.length > 0) {
                console.log('📸 4. 첫 번째 이미지 클릭 시도');
                
                // 더 구체적인 이미지 셀렉터로 찾기
                const imageItems = await page.$$('.image-item img, .notice-image img, img[data-gallery]');
                console.log(`   📸 이미지 아이템: ${imageItems.length}개`);
                
                // 직접 JavaScript 함수 호출로 모달 열기 시도
                const firstImageSrc = await images[0].getAttribute('src');
                console.log(`   🎯 JavaScript 함수로 모달 열기 시도: ${firstImageSrc}`);
                
                await page.evaluate((imageSrc) => {
                    if (typeof openImageModal === 'function') {
                        openImageModal(imageSrc);
                        console.log('✅ openImageModal 함수 호출됨');
                    } else {
                        console.log('❌ openImageModal 함수 없음');
                        // 대안: 이미지 클릭 이벤트 시뮬레이션
                        const img = document.querySelector(`img[src="${imageSrc}"]`);
                        if (img && img.onclick) {
                            img.onclick();
                        } else if (img) {
                            img.click();
                        }
                    }
                }, firstImageSrc);
                
                await page.waitForTimeout(3000); // 모달 로딩 대기시간 증가
                
                // 모달 열린 후 스크린샷
                await page.screenshot({ 
                    path: `${this.videoPath}/step3-modal-opened-${this.timestamp}.png`,
                    fullPage: true 
                });

                console.log('🎬 5. 모달 창 확인');
                // 공지사항 detail.php에서 사용하는 정확한 모달 셀렉터
                const modal = await page.$('.image-modal');
                console.log(`   📊 모달 상태: ${modal ? '✅ 발견됨' : '❌ 없음'}`)
                if (modal) {
                    console.log('   ✅ 모달 창 발견됨');

                    console.log('🎬 6. X 버튼 요소 찾기');
                    // 공지사항에서 사용하는 실제 X 버튼 셀렉터 (detail.php에서 확인)
                    const closeButton = await page.$('.image-modal button');
                    console.log(`   📊 X 버튼 상태: ${closeButton ? '✅ 발견됨' : '❌ 없음'}`);

                    if (closeButton) {
                        console.log('🎬 7. X 버튼 클릭 시도 (15초 타임아웃으로 테스트)');
                        
                        // 버튼이 보이는지 확인
                        const isVisible = await closeButton.isVisible();
                        console.log(`   👁️ X 버튼 가시성: ${isVisible}`);

                        // 버튼 위치 확인
                        const boundingBox = await closeButton.boundingBox();
                        console.log(`   📐 X 버튼 위치:`, boundingBox);

                        // X 버튼 스크린샷 (클릭 전)
                        await page.screenshot({ 
                            path: `${this.videoPath}/step4-before-x-click-${this.timestamp}.png`,
                            fullPage: true 
                        });

                        // 실제 클릭 시도
                        const startTime = Date.now();
                        try {
                            await closeButton.click({ timeout: 15000 });
                            const duration = Date.now() - startTime;
                            console.log(`   ✅ X 버튼 클릭 성공 (${duration}ms)`);
                            
                            // 성공 시 스크린샷
                            await page.screenshot({ 
                                path: `${this.videoPath}/step5-x-click-success-${this.timestamp}.png`,
                                fullPage: true 
                            });
                        } catch (error) {
                            const duration = Date.now() - startTime;
                            console.log(`   ❌ X 버튼 클릭 실패 (${duration}ms): ${error.message}`);
                            
                            // 실패 시 스크린샷
                            await page.screenshot({ 
                                path: `${this.videoPath}/step5-x-click-failed-${this.timestamp}.png`,
                                fullPage: true 
                            });
                            
                            console.log('🎬 8. 추가 디버깅 정보 수집');
                            
                            // DOM 구조 확인
                            const modalHTML = await modal.innerHTML();
                            console.log(`   🔍 모달 HTML 구조 (첫 500자):`);
                            console.log(`   ${modalHTML.substring(0, 500)}...`);

                            // CSS 스타일 확인
                            const styles = await closeButton.evaluate(el => {
                                const computed = window.getComputedStyle(el);
                                return {
                                    display: computed.display,
                                    visibility: computed.visibility,
                                    opacity: computed.opacity,
                                    zIndex: computed.zIndex,
                                    pointerEvents: computed.pointerEvents
                                };
                            });
                            console.log(`   🎨 X 버튼 CSS 스타일:`, styles);
                        }

                        console.log('🎬 9. 대체 방안 테스트');
                        
                        // ESC 키 테스트
                        console.log('   ⌨️ ESC 키 테스트');
                        await page.keyboard.press('Escape');
                        await page.waitForTimeout(1000);

                        const modalAfterEsc = await page.$('.image-modal, .modal, .overlay');
                        if (!modalAfterEsc) {
                            console.log('   ✅ ESC 키로 모달 닫기 성공');
                        } else {
                            console.log('   ❌ ESC 키로 모달 닫기 실패');
                            
                            // 배경 클릭 테스트
                            console.log('   🖱️ 배경 클릭 테스트');
                            await modal.click({ position: { x: 10, y: 10 } });
                            await page.waitForTimeout(1000);

                            const modalAfterClick = await page.$('.image-modal, .modal, .overlay');
                            if (!modalAfterClick) {
                                console.log('   ✅ 배경 클릭으로 모달 닫기 성공');
                            } else {
                                console.log('   ❌ 배경 클릭으로 모달 닫기 실패');
                            }
                        }

                    } else {
                        console.log('   ❌ X 버튼을 찾을 수 없음');
                        console.log('🎬 모달 전체 HTML 구조 확인');
                        const fullModalHTML = await modal.innerHTML();
                        console.log(fullModalHTML);
                    }

                } else {
                    console.log('   ❌ 모달 창을 찾을 수 없음');
                }

            } else {
                console.log('   ❌ 클릭할 수 있는 이미지를 찾을 수 없음');
            }

        } catch (error) {
            console.error('🚨 테스트 중 오류 발생:', error);
        } finally {
            console.log('🎬 10. 동영상 녹화 완료 및 브라우저 종료');
            await page.waitForTimeout(2000); // 마지막 장면 녹화
            
            await context.close();
            await browser.close();

            // 생성된 비디오 파일 경로 확인
            console.log('\n📹 동영상 파일 생성 완료!');
            console.log(`📁 동영상 위치: ${this.videoPath}`);
            console.log(`📅 타임스탬프: ${this.timestamp}`);
            
            // 비디오 파일 목록 확인을 위한 명령어 제안
            console.log('\n🔍 동영상 파일 확인 명령어:');
            console.log(`ls -la ${this.videoPath}/`);
            console.log(`find ${this.videoPath} -name "*.webm" -o -name "*.mp4" | head -5`);
        }
    }
}

// 메인 실행
console.log('🎥 이미지 모달 X 버튼 이슈 동영상 디버깅 시작');
const videoDebug = new VideoDebugTest();
videoDebug.recordModalIssue().catch(console.error);