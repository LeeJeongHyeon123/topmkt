/**
 * 현재 채팅 페이지 상태 직접 검사 스크립트
 * 채팅방 목록, DOM 구조, JavaScript 환경을 종합적으로 분석
 *
 * @author Claude (Anthropic)
 * @date 2025-09-15
 */

const { chromium } = require('playwright');
const fs = require('fs');

class ChatStateInspector {
    constructor() {
        this.baseUrl = 'https://www.topmktx.com';
        this.inspectionResults = {
            pageAccess: false,
            domStructure: {},
            javascriptErrors: [],
            firebaseStatus: null,
            chatRooms: [],
            chatElements: {},
            recommendations: []
        };
    }

    async inspect() {
        console.log('🔍 현재 채팅 페이지 상태 종합 검사 시작...');

        const browser = await chromium.launch({
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });

        try {
            const context = await browser.newContext();
            const page = await context.newPage();

            // JavaScript 오류 감지
            page.on('console', msg => {
                if (msg.type() === 'error') {
                    this.inspectionResults.javascriptErrors.push(msg.text());
                }
            });

            // 1. 로그인 시뮬레이션
            console.log('\n1️⃣ 로그인 시뮬레이션...');
            await this.simulateLogin(page);

            // 2. 채팅 페이지 접속
            console.log('\n2️⃣ 채팅 페이지 접속...');
            await page.goto(`${this.baseUrl}/chat`, { waitUntil: 'networkidle' });
            await page.waitForTimeout(8000); // 더 긴 대기 시간
            this.inspectionResults.pageAccess = true;

            // 3. DOM 구조 분석
            console.log('\n3️⃣ DOM 구조 분석...');
            await this.analyzeDomStructure(page);

            // 4. JavaScript 환경 검사
            console.log('\n4️⃣ JavaScript 환경 검사...');
            await this.checkJavaScriptEnvironment(page);

            // 5. Firebase 상태 확인
            console.log('\n5️⃣ Firebase 상태 확인...');
            await this.checkFirebaseStatus(page);

            // 6. 채팅방 목록 검사
            console.log('\n6️⃣ 채팅방 목록 검사...');
            await this.inspectChatRooms(page);

            // 7. 채팅 요소 상세 검사
            console.log('\n7️⃣ 채팅 요소 상세 검사...');
            await this.inspectChatElements(page);

            // 8. 스크린샷 촬영
            console.log('\n8️⃣ 현재 상태 스크린샷 촬영...');
            await page.screenshot({
                path: '/var/www/html/topmkt/chat_current_state_inspection.png',
                fullPage: true
            });

            // 9. 권장사항 생성
            console.log('\n9️⃣ 권장사항 생성...');
            this.generateRecommendations();

        } catch (error) {
            console.error('❌ 검사 중 오류:', error.message);
        } finally {
            await browser.close();
        }

        return this.inspectionResults;
    }

    async simulateLogin(page) {
        try {
            await page.goto(this.baseUrl);

            await page.evaluate(() => {
                localStorage.setItem('user_id', '4');
                localStorage.setItem('user_role', 'ROLE_ADMIN');
                localStorage.setItem('logged_in', 'true');
                sessionStorage.setItem('user_id', '4');
                sessionStorage.setItem('logged_in', 'true');

                window.currentUser = {
                    id: 4,
                    role: 'ROLE_ADMIN',
                    logged_in: true,
                    nickname: '우리집탄이'
                };
            });

            await page.context().addCookies([
                {
                    name: 'PHPSESSID',
                    value: 'dev_session_' + Date.now(),
                    domain: 'www.topmktx.com',
                    path: '/'
                }
            ]);

            console.log('✅ 로그인 시뮬레이션 완료');

        } catch (error) {
            console.error('❌ 로그인 시뮬레이션 실패:', error.message);
        }
    }

    async analyzeDomStructure(page) {
        try {
            const domStructure = await page.evaluate(() => {
                const structure = {
                    chatContainer: null,
                    chatSidebar: null,
                    chatMain: null,
                    chatRoomsList: null,
                    activeChatArea: null,
                    chatPartnerName: null,
                    messagesContainer: null
                };

                // 각 주요 요소 검사
                const elements = [
                    'chatContainer',
                    'chatSidebar',
                    'chatMain',
                    'chatRoomsList',
                    'activeChatArea',
                    'chatPartnerName',
                    'chatMessages'
                ];

                elements.forEach(elementId => {
                    const element = document.getElementById(elementId) ||
                                  document.querySelector(`.${elementId.replace(/([A-Z])/g, '-$1').toLowerCase()}`);

                    if (element) {
                        const rect = element.getBoundingClientRect();
                        const computedStyle = window.getComputedStyle(element);

                        structure[elementId] = {
                            exists: true,
                            tagName: element.tagName,
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
                                opacity: computedStyle.opacity
                            },
                            children: element.children.length,
                            innerHTML: element.innerHTML.substring(0, 200) + '...'
                        };
                    } else {
                        structure[elementId] = { exists: false };
                    }
                });

                return structure;
            });

            this.inspectionResults.domStructure = domStructure;

            console.log('DOM 구조 분석 결과:');
            Object.entries(domStructure).forEach(([key, value]) => {
                if (value.exists) {
                    console.log(`  ✅ ${key}: ${value.tagName} (${value.rect.width}x${value.rect.height})`);
                    console.log(`     Display: ${value.computedStyle.display}, Visibility: ${value.computedStyle.visibility}`);
                } else {
                    console.log(`  ❌ ${key}: 존재하지 않음`);
                }
            });

        } catch (error) {
            console.error('DOM 구조 분석 오류:', error.message);
        }
    }

    async checkJavaScriptEnvironment(page) {
        try {
            const jsEnvironment = await page.evaluate(() => {
                return {
                    firebase: typeof firebase !== 'undefined',
                    database: typeof database !== 'undefined',
                    currentUser: typeof currentUser !== 'undefined' ? currentUser : null,
                    currentUserId: typeof currentUserId !== 'undefined' ? currentUserId : null,
                    activeRoomId: typeof activeRoomId !== 'undefined' ? activeRoomId : null,
                    chatRooms: typeof chatRooms !== 'undefined' ? Object.keys(chatRooms || {}).length : 0,
                    users: typeof users !== 'undefined' ? Object.keys(users || {}).length : 0,
                    globalFunctions: [
                        'openChat',
                        'loadUserChatRooms',
                        'updateChatHeader',
                        'createMessageElement'
                    ].map(funcName => ({
                        name: funcName,
                        exists: typeof window[funcName] === 'function'
                    }))
                };
            });

            console.log('JavaScript 환경 검사 결과:');
            console.log(`  - Firebase 로드: ${jsEnvironment.firebase ? '✅' : '❌'}`);
            console.log(`  - Database 로드: ${jsEnvironment.database ? '✅' : '❌'}`);
            console.log(`  - currentUser: ${jsEnvironment.currentUser ? '✅ ' + JSON.stringify(jsEnvironment.currentUser) : '❌'}`);
            console.log(`  - currentUserId: ${jsEnvironment.currentUserId ? '✅ ' + jsEnvironment.currentUserId : '❌'}`);
            console.log(`  - activeRoomId: ${jsEnvironment.activeRoomId ? '✅ ' + jsEnvironment.activeRoomId : '❌'}`);
            console.log(`  - 채팅방 수: ${jsEnvironment.chatRooms}`);
            console.log(`  - 사용자 수: ${jsEnvironment.users}`);

            console.log('  - 전역 함수:');
            jsEnvironment.globalFunctions.forEach(func => {
                console.log(`    ${func.name}: ${func.exists ? '✅' : '❌'}`);
            });

        } catch (error) {
            console.error('JavaScript 환경 검사 오류:', error.message);
        }
    }

    async checkFirebaseStatus(page) {
        try {
            const firebaseStatus = await page.evaluate(() => {
                if (typeof firebase === 'undefined') {
                    return { loaded: false, error: 'Firebase not loaded' };
                }

                try {
                    const database = firebase.database();
                    const connectedRef = database.ref('.info/connected');

                    return new Promise((resolve) => {
                        connectedRef.once('value', (snapshot) => {
                            resolve({
                                loaded: true,
                                connected: snapshot.val(),
                                databaseUrl: firebase.app().options.databaseURL || 'unknown'
                            });
                        }, (error) => {
                            resolve({
                                loaded: true,
                                connected: false,
                                error: error.message
                            });
                        });
                    });
                } catch (error) {
                    return {
                        loaded: true,
                        connected: false,
                        error: error.message
                    };
                }
            });

            // Promise가 반환되면 대기
            const status = await firebaseStatus;
            this.inspectionResults.firebaseStatus = status;

            console.log('Firebase 상태:');
            console.log(`  - 로드됨: ${status.loaded ? '✅' : '❌'}`);
            if (status.loaded) {
                console.log(`  - 연결됨: ${status.connected ? '✅' : '❌'}`);
                if (status.databaseUrl) {
                    console.log(`  - Database URL: ${status.databaseUrl}`);
                }
                if (status.error) {
                    console.log(`  - 오류: ${status.error}`);
                }
            }

        } catch (error) {
            console.error('Firebase 상태 확인 오류:', error.message);
        }
    }

    async inspectChatRooms(page) {
        try {
            const chatRooms = await page.evaluate(() => {
                // DOM에서 채팅방 아이템 찾기
                const roomItems = document.querySelectorAll('.chat-room-item');
                const rooms = [];

                roomItems.forEach((item, index) => {
                    const rect = item.getBoundingClientRect();
                    const roomId = item.getAttribute('data-room-id');
                    const roomName = item.querySelector('.room-name')?.textContent?.trim();
                    const roomLastMessage = item.querySelector('.room-last-message')?.textContent?.trim();

                    rooms.push({
                        index: index,
                        roomId: roomId,
                        name: roomName,
                        lastMessage: roomLastMessage,
                        className: item.className,
                        rect: {
                            width: rect.width,
                            height: rect.height
                        },
                        visible: rect.width > 0 && rect.height > 0,
                        innerHTML: item.innerHTML.substring(0, 300) + '...'
                    });
                });

                // JavaScript 변수에서 채팅방 데이터 확인
                let jsRooms = [];
                if (typeof chatRooms !== 'undefined') {
                    jsRooms = Object.entries(chatRooms).map(([roomId, roomData]) => ({
                        roomId: roomId,
                        type: roomData.type,
                        name: roomData.name,
                        participants: Object.keys(roomData.participants || {}),
                        lastMessage: roomData.lastMessage || null
                    }));
                }

                return {
                    domRooms: rooms,
                    jsRooms: jsRooms,
                    loadingElement: document.getElementById('roomsLoading') ? {
                        exists: true,
                        display: window.getComputedStyle(document.getElementById('roomsLoading')).display
                    } : { exists: false }
                };
            });

            this.inspectionResults.chatRooms = chatRooms;

            console.log('채팅방 목록 검사 결과:');
            console.log(`  - DOM 채팅방: ${chatRooms.domRooms.length}개`);
            console.log(`  - JavaScript 채팅방: ${chatRooms.jsRooms.length}개`);
            console.log(`  - 로딩 요소: ${chatRooms.loadingElement.exists ? '있음 (display: ' + chatRooms.loadingElement.display + ')' : '없음'}`);

            if (chatRooms.domRooms.length > 0) {
                console.log('\n  DOM 채팅방 상세:');
                chatRooms.domRooms.forEach((room, index) => {
                    console.log(`    ${index + 1}. ID: ${room.roomId || 'null'}`);
                    console.log(`       이름: ${room.name || 'null'}`);
                    console.log(`       표시: ${room.visible ? '✅' : '❌'}`);
                    console.log(`       크기: ${room.rect.width}x${room.rect.height}`);
                });
            }

            if (chatRooms.jsRooms.length > 0) {
                console.log('\n  JavaScript 채팅방 상세:');
                chatRooms.jsRooms.forEach((room, index) => {
                    console.log(`    ${index + 1}. ID: ${room.roomId}`);
                    console.log(`       이름: ${room.name}`);
                    console.log(`       타입: ${room.type}`);
                    console.log(`       참가자: ${room.participants.join(', ')}`);
                });
            }

        } catch (error) {
            console.error('채팅방 목록 검사 오류:', error.message);
        }
    }

    async inspectChatElements(page) {
        try {
            const chatElements = await page.evaluate(() => {
                const elements = {};

                // 채팅 관련 주요 요소들 검사
                const selectors = [
                    '#chatPartnerName',
                    '#chatPartnerAvatar',
                    '#activeChatArea',
                    '#chatMessages',
                    '.message-item',
                    '.message-avatar',
                    '.chat-partner-info'
                ];

                selectors.forEach(selector => {
                    const element = document.querySelector(selector);
                    const allElements = document.querySelectorAll(selector);

                    elements[selector] = {
                        found: !!element,
                        count: allElements.length,
                        details: element ? {
                            tagName: element.tagName,
                            className: element.className,
                            id: element.id,
                            textContent: element.textContent?.trim()?.substring(0, 100),
                            innerHTML: element.innerHTML?.substring(0, 200) + '...',
                            rect: element.getBoundingClientRect(),
                            computedStyle: {
                                display: window.getComputedStyle(element).display,
                                visibility: window.getComputedStyle(element).visibility,
                                opacity: window.getComputedStyle(element).opacity,
                                color: window.getComputedStyle(element).color,
                                fontSize: window.getComputedStyle(element).fontSize
                            }
                        } : null
                    };
                });

                return elements;
            });

            this.inspectionResults.chatElements = chatElements;

            console.log('채팅 요소 상세 검사 결과:');
            Object.entries(chatElements).forEach(([selector, data]) => {
                console.log(`\n  ${selector}:`);
                console.log(`    - 발견됨: ${data.found ? '✅' : '❌'}`);
                console.log(`    - 개수: ${data.count}`);

                if (data.details) {
                    const details = data.details;
                    console.log(`    - 태그: ${details.tagName}`);
                    console.log(`    - 텍스트: "${details.textContent}"`);
                    console.log(`    - 크기: ${details.rect.width}x${details.rect.height}`);
                    console.log(`    - 위치: (${details.rect.x}, ${details.rect.y})`);
                    console.log(`    - Display: ${details.computedStyle.display}`);
                    console.log(`    - Visibility: ${details.computedStyle.visibility}`);
                    console.log(`    - Opacity: ${details.computedStyle.opacity}`);
                    console.log(`    - Color: ${details.computedStyle.color}`);
                }
            });

        } catch (error) {
            console.error('채팅 요소 검사 오류:', error.message);
        }
    }

    generateRecommendations() {
        const recommendations = [];

        // 1. Firebase 연결 문제
        if (this.inspectionResults.firebaseStatus && !this.inspectionResults.firebaseStatus.connected) {
            recommendations.push({
                priority: 'critical',
                issue: 'Firebase 연결 실패',
                solution: 'Firebase 설정 및 네트워크 연결 확인 필요'
            });
        }

        // 2. 채팅방 목록 문제
        if (this.inspectionResults.chatRooms.domRooms.length === 0 &&
            this.inspectionResults.chatRooms.jsRooms.length === 0) {
            recommendations.push({
                priority: 'high',
                issue: '채팅방이 전혀 로드되지 않음',
                solution: 'loadUserChatRooms 함수 실행 상태 및 Firebase 데이터 확인'
            });
        }

        // 3. DOM 요소 누락
        const missingElements = Object.entries(this.inspectionResults.domStructure)
            .filter(([key, value]) => !value.exists)
            .map(([key]) => key);

        if (missingElements.length > 0) {
            recommendations.push({
                priority: 'medium',
                issue: `DOM 요소 누락: ${missingElements.join(', ')}`,
                solution: 'HTML 템플릿에서 해당 요소들이 정상적으로 렌더링되는지 확인'
            });
        }

        // 4. JavaScript 오류
        if (this.inspectionResults.javascriptErrors.length > 0) {
            recommendations.push({
                priority: 'high',
                issue: `JavaScript 오류 ${this.inspectionResults.javascriptErrors.length}개 발견`,
                solution: '콘솔 오류를 확인하고 스크립트 로딩 순서 점검'
            });
        }

        // 5. 채팅 헤더 닉네임 문제
        const chatPartnerName = this.inspectionResults.chatElements['#chatPartnerName'];
        if (chatPartnerName && chatPartnerName.found) {
            const details = chatPartnerName.details;
            if (!details.textContent || details.textContent === '사용자') {
                recommendations.push({
                    priority: 'medium',
                    issue: '채팅 헤더 닉네임이 기본값이거나 비어있음',
                    solution: 'updateChatHeader 함수에서 사용자 정보를 올바르게 설정하는지 확인'
                });
            }

            if (details.computedStyle.display === 'none' ||
                details.computedStyle.visibility === 'hidden' ||
                parseFloat(details.computedStyle.opacity) < 0.1) {
                recommendations.push({
                    priority: 'high',
                    issue: '채팅 헤더 닉네임이 CSS로 숨겨져 있음',
                    solution: 'CSS 스타일에서 display, visibility, opacity 속성 확인'
                });
            }
        }

        this.inspectionResults.recommendations = recommendations;

        console.log('\n🎯 종합 권장사항:');
        if (recommendations.length === 0) {
            console.log('  특별한 문제가 발견되지 않았습니다.');
        } else {
            recommendations.forEach((rec, index) => {
                console.log(`\n  ${index + 1}. [${rec.priority.toUpperCase()}] ${rec.issue}`);
                console.log(`     해결방안: ${rec.solution}`);
            });
        }
    }

    async saveInspectionReport() {
        const report = {
            timestamp: new Date().toISOString(),
            inspection: this.inspectionResults
        };

        const reportPath = '/var/www/html/topmkt/chat_state_inspection_report.json';
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        console.log(`\n📊 검사 리포트 저장: ${reportPath}`);

        return reportPath;
    }
}

// 실행
async function main() {
    const inspector = new ChatStateInspector();

    try {
        const results = await inspector.inspect();
        await inspector.saveInspectionReport();

        console.log('\n✅ 채팅 페이지 상태 종합 검사 완료');

        // 요약 출력
        console.log('\n📋 검사 요약:');
        console.log(`- 페이지 접속: ${results.pageAccess ? '성공' : '실패'}`);
        console.log(`- DOM 채팅방: ${results.chatRooms.domRooms?.length || 0}개`);
        console.log(`- JS 채팅방: ${results.chatRooms.jsRooms?.length || 0}개`);
        console.log(`- JavaScript 오류: ${results.javascriptErrors.length}개`);
        console.log(`- 권장사항: ${results.recommendations.length}개`);

        if (results.firebaseStatus) {
            console.log(`- Firebase: ${results.firebaseStatus.loaded ? '로드됨' : '로드안됨'}, ${results.firebaseStatus.connected ? '연결됨' : '연결안됨'}`);
        }

    } catch (error) {
        console.error('❌ 검사 실패:', error.message);
        process.exit(1);
    }
}

if (require.main === module) {
    main();
}

module.exports = ChatStateInspector;