/**
 * 채팅방 닉네임 표시 문제 분석 스크립트
 * room-id: -OSTjuT0YIJpiPVMKijP에서 대화 상대 닉네임이 안 보이는 문제 진단
 *
 * @author Claude (Anthropic)
 * @date 2025-09-15
 */

const DevLoginHelper = require('./test_helpers/DevLoginHelper.cjs');
const fs = require('fs');

class ChatNicknameAnalyzer {
    constructor() {
        this.targetRoomId = '-OSTjuT0YIJpiPVMKijP';
        this.helper = new DevLoginHelper();
        this.analysisResults = {
            roomAccess: false,
            htmlStructure: null,
            nicknameElements: [],
            cssAnalysis: {},
            hiddenElements: [],
            zIndexIssues: [],
            recommendations: []
        };
    }

    async analyze() {
        console.log('🔍 채팅방 닉네임 표시 문제 분석 시작...');
        console.log(`📍 대상 채팅방: ${this.targetRoomId}`);

        let session = null;

        try {
            // 1. DevLoginHelper로 user_id=4 로그인
            console.log('\n1️⃣ DevLoginHelper로 user_id=4 (우리집탄이) 로그인...');
            session = await this.helper.getDevSession('우리집탄이', { headless: true });
            console.log('✅ 로그인 성공');

            // 2. 채팅 페이지 접속
            console.log('\n2️⃣ 채팅 페이지 접속...');
            await session.page.goto('https://www.topmktx.com/chat', { waitUntil: 'networkidle' });
            await session.page.waitForTimeout(3000);

            // 3. 해당 채팅방 찾기 및 접속
            console.log(`\n3️⃣ 채팅방 ${this.targetRoomId} 찾기 및 접속...`);
            const roomFound = await this.accessTargetRoom(session.page);

            if (!roomFound) {
                console.log('❌ 대상 채팅방을 찾을 수 없습니다');
                return this.analysisResults;
            }

            this.analysisResults.roomAccess = true;
            console.log('✅ 채팅방 접속 성공');

            // 4. HTML 구조 분석
            console.log('\n4️⃣ 채팅방 HTML 구조 분석...');
            await this.analyzeHtmlStructure(session.page);

            // 5. 닉네임 요소 분석
            console.log('\n5️⃣ 닉네임 요소 분석...');
            await this.analyzeNicknameElements(session.page);

            // 6. CSS 스타일 분석
            console.log('\n6️⃣ CSS 스타일 및 가림 요소 분석...');
            await this.analyzeCssStyles(session.page);

            // 7. Z-index 및 위치 문제 분석
            console.log('\n7️⃣ Z-index 및 위치 문제 분석...');
            await this.analyzePositioning(session.page);

            // 8. 스크린샷 촬영
            console.log('\n8️⃣ 문제 상황 스크린샷 촬영...');
            await session.page.screenshot({
                path: '/var/www/html/topmkt/chat_nickname_issue_analysis.png',
                fullPage: true
            });

            // 9. 분석 결과 정리
            console.log('\n9️⃣ 분석 결과 정리...');
            this.generateRecommendations();

        } catch (error) {
            console.error('❌ 분석 중 오류:', error.message);
            console.error('스택 추적:', error.stack);
        } finally {
            if (session) {
                await this.helper.cleanup(session);
            }
        }

        return this.analysisResults;
    }

    async accessTargetRoom(page) {
        try {
            // 채팅방 목록에서 대상 room-id 찾기
            console.log('📋 채팅방 목록에서 대상 채팅방 검색...');

            // Firebase 기반 채팅방 로딩 대기
            await page.waitForTimeout(5000);

            // 모든 채팅방 항목 확인
            const roomElements = await page.$$('.chat-room-item, [data-room-id], .room-item, .chat-list-item');
            console.log(`발견된 채팅방 요소: ${roomElements.length}개`);

            // 각 채팅방 요소의 room-id 확인
            for (let i = 0; i < roomElements.length; i++) {
                const element = roomElements[i];

                // data-room-id 속성 확인
                const roomId = await element.getAttribute('data-room-id');
                console.log(`채팅방 ${i + 1}: room-id = ${roomId || 'null'}`);

                if (roomId === this.targetRoomId) {
                    console.log(`✅ 대상 채팅방 발견! 클릭하여 접속...`);
                    await element.click();
                    await page.waitForTimeout(3000);
                    return true;
                }
            }

            // room-id가 없는 경우 onclick 이벤트나 다른 방식으로 확인
            console.log('🔍 대체 방법으로 채팅방 검색...');

            // JavaScript로 직접 검색
            const foundRoom = await page.evaluate((targetRoomId) => {
                const allElements = document.querySelectorAll('*');
                for (let element of allElements) {
                    // onclick 속성에서 room-id 확인
                    const onclick = element.getAttribute('onclick');
                    if (onclick && onclick.includes(targetRoomId)) {
                        element.click();
                        return true;
                    }

                    // 텍스트 내용에서 room-id 확인
                    if (element.textContent && element.textContent.includes(targetRoomId)) {
                        element.click();
                        return true;
                    }
                }
                return false;
            }, this.targetRoomId);

            if (foundRoom) {
                await page.waitForTimeout(3000);
                return true;
            }

            return false;

        } catch (error) {
            console.error('채팅방 접속 오류:', error.message);
            return false;
        }
    }

    async analyzeHtmlStructure(page) {
        try {
            // 현재 페이지의 HTML 구조 분석
            const htmlStructure = await page.evaluate(() => {
                const chatContainer = document.querySelector('.chat-container, #chat-container, .chat-messages, .message-container');
                if (!chatContainer) return null;

                return {
                    outerHTML: chatContainer.outerHTML.substring(0, 2000), // 처음 2000자만
                    classList: Array.from(chatContainer.classList),
                    id: chatContainer.id,
                    childrenCount: chatContainer.children.length,
                    innerHTML: chatContainer.innerHTML.substring(0, 1000)
                };
            });

            this.analysisResults.htmlStructure = htmlStructure;
            console.log('HTML 구조 분석 완료:', htmlStructure ? '성공' : '실패');

            if (htmlStructure) {
                console.log(`- 컨테이너 클래스: ${htmlStructure.classList.join(', ')}`);
                console.log(`- 컨테이너 ID: ${htmlStructure.id}`);
                console.log(`- 자식 요소 수: ${htmlStructure.childrenCount}`);
            }

        } catch (error) {
            console.error('HTML 구조 분석 오류:', error.message);
        }
    }

    async analyzeNicknameElements(page) {
        try {
            // 닉네임 관련 요소들 분석
            const nicknameElements = await page.evaluate(() => {
                const elements = [];

                // 다양한 닉네임 셀렉터로 검색
                const selectors = [
                    '.nickname',
                    '.user-name',
                    '.sender-name',
                    '.chat-nickname',
                    '.message-sender',
                    '[class*="nickname"]',
                    '[class*="sender"]',
                    '[class*="user"]'
                ];

                selectors.forEach(selector => {
                    document.querySelectorAll(selector).forEach(element => {
                        const rect = element.getBoundingClientRect();
                        const computedStyle = window.getComputedStyle(element);

                        elements.push({
                            selector,
                            text: element.textContent.trim(),
                            innerHTML: element.innerHTML,
                            className: element.className,
                            id: element.id,
                            rect: {
                                x: rect.x,
                                y: rect.y,
                                width: rect.width,
                                height: rect.height
                            },
                            computedStyle: {
                                display: computedStyle.display,
                                visibility: computedStyle.visibility,
                                opacity: computedStyle.opacity,
                                zIndex: computedStyle.zIndex,
                                position: computedStyle.position,
                                overflow: computedStyle.overflow,
                                color: computedStyle.color,
                                backgroundColor: computedStyle.backgroundColor,
                                fontSize: computedStyle.fontSize,
                                fontWeight: computedStyle.fontWeight
                            },
                            isVisible: rect.width > 0 && rect.height > 0,
                            isInViewport: rect.top >= 0 && rect.left >= 0 &&
                                         rect.bottom <= window.innerHeight &&
                                         rect.right <= window.innerWidth
                        });
                    });
                });

                return elements;
            });

            this.analysisResults.nicknameElements = nicknameElements;
            console.log(`닉네임 요소 분석 완료: ${nicknameElements.length}개 발견`);

            nicknameElements.forEach((element, index) => {
                console.log(`\n닉네임 요소 ${index + 1}:`);
                console.log(`  - 셀렉터: ${element.selector}`);
                console.log(`  - 텍스트: "${element.text}"`);
                console.log(`  - 클래스: ${element.className}`);
                console.log(`  - 크기: ${element.rect.width}x${element.rect.height}`);
                console.log(`  - 위치: (${element.rect.x}, ${element.rect.y})`);
                console.log(`  - 표시 여부: ${element.isVisible ? '표시됨' : '숨겨짐'}`);
                console.log(`  - 뷰포트 내: ${element.isInViewport ? '예' : '아니오'}`);
                console.log(`  - Display: ${element.computedStyle.display}`);
                console.log(`  - Visibility: ${element.computedStyle.visibility}`);
                console.log(`  - Opacity: ${element.computedStyle.opacity}`);
                console.log(`  - Z-Index: ${element.computedStyle.zIndex}`);
                console.log(`  - Color: ${element.computedStyle.color}`);
            });

        } catch (error) {
            console.error('닉네임 요소 분석 오류:', error.message);
        }
    }

    async analyzeCssStyles(page) {
        try {
            // CSS 스타일 및 가림 요소 분석
            const cssAnalysis = await page.evaluate(() => {
                const analysis = {
                    hiddenElements: [],
                    overlappingElements: [],
                    transparentElements: [],
                    offscreenElements: []
                };

                // 모든 요소 검사
                document.querySelectorAll('*').forEach(element => {
                    const rect = element.getBoundingClientRect();
                    const computedStyle = window.getComputedStyle(element);

                    // 숨겨진 요소 찾기
                    if (computedStyle.display === 'none' ||
                        computedStyle.visibility === 'hidden' ||
                        parseFloat(computedStyle.opacity) === 0) {

                        if (element.textContent.includes('nickname') ||
                            element.className.includes('nickname') ||
                            element.className.includes('sender') ||
                            element.className.includes('user')) {

                            analysis.hiddenElements.push({
                                tagName: element.tagName,
                                className: element.className,
                                id: element.id,
                                text: element.textContent.trim().substring(0, 50),
                                reason: computedStyle.display === 'none' ? 'display:none' :
                                       computedStyle.visibility === 'hidden' ? 'visibility:hidden' :
                                       'opacity:0'
                            });
                        }
                    }

                    // 높은 z-index를 가진 요소들 (다른 요소를 가릴 수 있는)
                    const zIndex = parseInt(computedStyle.zIndex);
                    if (zIndex > 100) {
                        analysis.overlappingElements.push({
                            tagName: element.tagName,
                            className: element.className,
                            id: element.id,
                            zIndex: zIndex,
                            position: computedStyle.position,
                            rect: {
                                x: rect.x,
                                y: rect.y,
                                width: rect.width,
                                height: rect.height
                            }
                        });
                    }

                    // 투명하거나 반투명한 요소
                    const opacity = parseFloat(computedStyle.opacity);
                    if (opacity > 0 && opacity < 0.5) {
                        analysis.transparentElements.push({
                            tagName: element.tagName,
                            className: element.className,
                            opacity: opacity,
                            text: element.textContent.trim().substring(0, 30)
                        });
                    }
                });

                return analysis;
            });

            this.analysisResults.cssAnalysis = cssAnalysis;
            console.log('CSS 스타일 분석 완료');

            console.log(`\n숨겨진 요소: ${cssAnalysis.hiddenElements.length}개`);
            cssAnalysis.hiddenElements.forEach((element, index) => {
                console.log(`  ${index + 1}. ${element.tagName}.${element.className} - ${element.reason}`);
                console.log(`     텍스트: "${element.text}"`);
            });

            console.log(`\n높은 Z-Index 요소: ${cssAnalysis.overlappingElements.length}개`);
            cssAnalysis.overlappingElements.forEach((element, index) => {
                console.log(`  ${index + 1}. ${element.tagName}.${element.className} - z-index: ${element.zIndex}`);
                console.log(`     위치: (${element.rect.x}, ${element.rect.y}) 크기: ${element.rect.width}x${element.rect.height}`);
            });

            console.log(`\n반투명 요소: ${cssAnalysis.transparentElements.length}개`);
            cssAnalysis.transparentElements.forEach((element, index) => {
                console.log(`  ${index + 1}. ${element.tagName}.${element.className} - opacity: ${element.opacity}`);
            });

        } catch (error) {
            console.error('CSS 스타일 분석 오류:', error.message);
        }
    }

    async analyzePositioning(page) {
        try {
            // z-index 및 위치 관련 문제 분석
            const positionAnalysis = await page.evaluate(() => {
                const analysis = {
                    zIndexConflicts: [],
                    positionIssues: [],
                    overflowIssues: []
                };

                // 채팅 관련 요소들의 위치 분석
                const chatElements = document.querySelectorAll('[class*="chat"], [class*="message"], [class*="nickname"], [class*="sender"]');

                chatElements.forEach(element => {
                    const rect = element.getBoundingClientRect();
                    const computedStyle = window.getComputedStyle(element);
                    const parent = element.parentElement;
                    const parentStyle = parent ? window.getComputedStyle(parent) : null;

                    // z-index 충돌 검사
                    const zIndex = parseInt(computedStyle.zIndex) || 0;
                    if (zIndex > 0) {
                        analysis.zIndexConflicts.push({
                            element: {
                                tagName: element.tagName,
                                className: element.className,
                                id: element.id,
                                text: element.textContent.trim().substring(0, 30)
                            },
                            zIndex: zIndex,
                            position: computedStyle.position
                        });
                    }

                    // 위치 문제 검사
                    if (computedStyle.position === 'absolute' || computedStyle.position === 'fixed') {
                        analysis.positionIssues.push({
                            element: {
                                tagName: element.tagName,
                                className: element.className,
                                text: element.textContent.trim().substring(0, 30)
                            },
                            position: computedStyle.position,
                            top: computedStyle.top,
                            left: computedStyle.left,
                            right: computedStyle.right,
                            bottom: computedStyle.bottom
                        });
                    }

                    // overflow 문제 검사
                    if (parentStyle && parentStyle.overflow === 'hidden') {
                        const parentRect = parent.getBoundingClientRect();

                        // 요소가 부모를 벗어났는지 확인
                        const isOverflowing = rect.left < parentRect.left ||
                                            rect.right > parentRect.right ||
                                            rect.top < parentRect.top ||
                                            rect.bottom > parentRect.bottom;

                        if (isOverflowing) {
                            analysis.overflowIssues.push({
                                element: {
                                    tagName: element.tagName,
                                    className: element.className,
                                    text: element.textContent.trim().substring(0, 30)
                                },
                                parent: {
                                    tagName: parent.tagName,
                                    className: parent.className
                                },
                                elementRect: rect,
                                parentRect: parentRect
                            });
                        }
                    }
                });

                return analysis;
            });

            this.analysisResults.zIndexIssues = positionAnalysis;
            console.log('위치 분석 완료');

            console.log(`\nZ-Index 설정된 요소: ${positionAnalysis.zIndexConflicts.length}개`);
            positionAnalysis.zIndexConflicts.forEach((item, index) => {
                console.log(`  ${index + 1}. ${item.element.tagName}.${item.element.className}`);
                console.log(`     Z-Index: ${item.zIndex}, Position: ${item.position}`);
                console.log(`     텍스트: "${item.element.text}"`);
            });

            console.log(`\n절대 위치 요소: ${positionAnalysis.positionIssues.length}개`);
            positionAnalysis.positionIssues.forEach((item, index) => {
                console.log(`  ${index + 1}. ${item.element.tagName}.${item.element.className}`);
                console.log(`     Position: ${item.position}`);
                console.log(`     좌표: top:${item.top}, left:${item.left}, right:${item.right}, bottom:${item.bottom}`);
            });

            console.log(`\nOverflow 문제: ${positionAnalysis.overflowIssues.length}개`);
            positionAnalysis.overflowIssues.forEach((item, index) => {
                console.log(`  ${index + 1}. ${item.element.tagName}.${item.element.className}`);
                console.log(`     부모: ${item.parent.tagName}.${item.parent.className}`);
                console.log(`     요소가 부모 영역을 벗어남`);
            });

        } catch (error) {
            console.error('위치 분석 오류:', error.message);
        }
    }

    generateRecommendations() {
        const recommendations = [];

        // 1. 숨겨진 닉네임 요소가 있는 경우
        const hiddenNicknames = this.analysisResults.cssAnalysis.hiddenElements || [];
        if (hiddenNicknames.length > 0) {
            recommendations.push({
                issue: '숨겨진 닉네임 요소 발견',
                elements: hiddenNicknames.length,
                solution: 'display:none, visibility:hidden, opacity:0 속성을 제거하거나 조정'
            });
        }

        // 2. 닉네임 요소가 뷰포트 밖에 있는 경우
        const invisibleNicknames = this.analysisResults.nicknameElements.filter(el => !el.isVisible);
        if (invisibleNicknames.length > 0) {
            recommendations.push({
                issue: '크기가 0인 닉네임 요소',
                elements: invisibleNicknames.length,
                solution: 'width, height CSS 속성 확인 및 조정'
            });
        }

        // 3. Z-index 문제
        const highZIndexElements = this.analysisResults.cssAnalysis.overlappingElements || [];
        if (highZIndexElements.length > 0) {
            recommendations.push({
                issue: '높은 Z-index 요소가 닉네임을 가릴 가능성',
                elements: highZIndexElements.length,
                solution: '닉네임 요소의 z-index를 높이거나 가리는 요소의 z-index를 조정'
            });
        }

        // 4. 투명도 문제
        const transparentElements = this.analysisResults.cssAnalysis.transparentElements || [];
        const transparentNicknames = transparentElements.filter(el =>
            el.className.includes('nickname') || el.text.includes('nickname')
        );
        if (transparentNicknames.length > 0) {
            recommendations.push({
                issue: '반투명 닉네임 요소',
                elements: transparentNicknames.length,
                solution: 'opacity 값을 1.0으로 조정'
            });
        }

        // 5. Overflow 문제
        const overflowIssues = this.analysisResults.zIndexIssues.overflowIssues || [];
        if (overflowIssues.length > 0) {
            recommendations.push({
                issue: '부모 요소의 overflow:hidden으로 잘린 닉네임',
                elements: overflowIssues.length,
                solution: '부모 요소의 overflow 속성을 visible로 변경하거나 레이아웃 조정'
            });
        }

        this.analysisResults.recommendations = recommendations;

        console.log('\n🎯 문제 해결 권장사항:');
        if (recommendations.length === 0) {
            console.log('  특별한 문제가 발견되지 않았습니다.');
        } else {
            recommendations.forEach((rec, index) => {
                console.log(`\n  ${index + 1}. ${rec.issue}`);
                console.log(`     영향받는 요소: ${rec.elements}개`);
                console.log(`     해결방안: ${rec.solution}`);
            });
        }
    }

    async saveAnalysisReport() {
        const report = {
            timestamp: new Date().toISOString(),
            targetRoomId: this.targetRoomId,
            analysis: this.analysisResults
        };

        const reportPath = '/var/www/html/topmkt/chat_nickname_analysis_report.json';
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        console.log(`\n📊 분석 리포트 저장: ${reportPath}`);

        return reportPath;
    }
}

// 실행
async function main() {
    const analyzer = new ChatNicknameAnalyzer();

    try {
        const results = await analyzer.analyze();
        await analyzer.saveAnalysisReport();

        console.log('\n✅ 채팅방 닉네임 표시 문제 분석 완료');

        // 요약 출력
        console.log('\n📋 분석 요약:');
        console.log(`- 채팅방 접속: ${results.roomAccess ? '성공' : '실패'}`);
        console.log(`- 닉네임 요소 발견: ${results.nicknameElements.length}개`);
        console.log(`- 숨겨진 요소: ${results.cssAnalysis.hiddenElements?.length || 0}개`);
        console.log(`- 높은 Z-index 요소: ${results.cssAnalysis.overlappingElements?.length || 0}개`);
        console.log(`- 권장사항: ${results.recommendations.length}개`);

    } catch (error) {
        console.error('❌ 분석 실패:', error.message);
        process.exit(1);
    }
}

if (require.main === module) {
    main();
}

module.exports = ChatNicknameAnalyzer;