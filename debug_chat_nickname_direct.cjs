/**
 * 채팅방 닉네임 표시 문제 직접 분석 스크립트
 * 특정 채팅방에서 대화 상대 닉네임이 안 보이는 문제를 정확히 진단
 *
 * @author Claude (Anthropic)
 * @date 2025-09-15
 */

const { chromium } = require('playwright');
const fs = require('fs');

class DirectChatNicknameAnalyzer {
    constructor() {
        this.baseUrl = 'https://www.topmktx.com';
        this.targetRoomId = '-OSTjuT0YIJpiPVMKijP';
        this.analysisResults = {
            chatPageAccess: false,
            chatHeaderName: null,
            messageElements: [],
            nicknameVisibility: {
                headerNickname: null,
                messageNicknames: []
            },
            cssIssues: [],
            recommendations: []
        };
    }

    async analyze() {
        console.log('🔍 채팅방 닉네임 표시 문제 직접 분석 시작...');
        console.log(`📍 대상 채팅방: ${this.targetRoomId}`);

        const browser = await chromium.launch({
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });

        try {
            const context = await browser.newContext();
            const page = await context.newPage();

            // 1. 로그인 시뮬레이션 (DevLoginHelper 방식)
            console.log('\n1️⃣ 사용자 로그인 시뮬레이션...');
            await this.simulateLogin(page);

            // 2. 채팅 페이지 접속
            console.log('\n2️⃣ 채팅 페이지 접속...');
            await page.goto(`${this.baseUrl}/chat`, { waitUntil: 'networkidle' });
            await page.waitForTimeout(5000);
            this.analysisResults.chatPageAccess = true;

            // 3. JavaScript로 특정 채팅방 직접 활성화
            console.log('\n3️⃣ JavaScript로 채팅방 직접 활성화...');
            const roomActivated = await this.activateTargetRoom(page);

            if (!roomActivated) {
                console.log('❌ 채팅방 활성화 실패');
                return this.analysisResults;
            }

            // 4. 채팅 헤더 닉네임 분석
            console.log('\n4️⃣ 채팅 헤더 닉네임 분석...');
            await this.analyzeChatHeader(page);

            // 5. 메시지 영역 닉네임 분석
            console.log('\n5️⃣ 메시지 영역 닉네임 분석...');
            await this.analyzeMessageNicknames(page);

            // 6. CSS 문제 진단
            console.log('\n6️⃣ CSS 문제 진단...');
            await this.diagnoseCssIssues(page);

            // 7. 스크린샷 촬영
            console.log('\n7️⃣ 문제 상황 스크린샷 촬영...');
            await page.screenshot({
                path: '/var/www/html/topmkt/chat_nickname_direct_analysis.png',
                fullPage: true
            });

            // 8. 분석 결과 정리
            console.log('\n8️⃣ 분석 결과 정리...');
            this.generateRecommendations();

        } catch (error) {
            console.error('❌ 분석 중 오류:', error.message);
        } finally {
            await browser.close();
        }

        return this.analysisResults;
    }

    async simulateLogin(page) {
        try {
            // 메인 페이지로 이동
            await page.goto(this.baseUrl);

            // 로컬스토리지와 세션스토리지에 인증 정보 설정
            await page.evaluate(() => {
                // 로컬스토리지 설정
                localStorage.setItem('user_id', '4');
                localStorage.setItem('user_role', 'ROLE_ADMIN');
                localStorage.setItem('logged_in', 'true');
                localStorage.setItem('dev_login', 'true');

                // 세션스토리지 설정
                sessionStorage.setItem('user_id', '4');
                sessionStorage.setItem('user_role', 'ROLE_ADMIN');
                sessionStorage.setItem('logged_in', 'true');

                // 전역 변수 설정
                window.currentUser = {
                    id: 4,
                    role: 'ROLE_ADMIN',
                    logged_in: true,
                    nickname: '우리집탄이'
                };

                console.log('🔧 로그인 상태 설정 완료');
            });

            // 쿠키 설정
            await page.context().addCookies([
                {
                    name: 'PHPSESSID',
                    value: 'dev_session_' + Date.now(),
                    domain: 'www.topmktx.com',
                    path: '/'
                },
                {
                    name: 'user_session',
                    value: JSON.stringify({
                        user_id: 4,
                        role: 'ROLE_ADMIN',
                        nickname: '우리집탄이'
                    }),
                    domain: 'www.topmktx.com',
                    path: '/'
                }
            ]);

            console.log('✅ 로그인 시뮬레이션 완료');

        } catch (error) {
            console.error('❌ 로그인 시뮬레이션 실패:', error.message);
        }
    }

    async activateTargetRoom(page) {
        try {
            // JavaScript로 직접 채팅방 활성화
            const result = await page.evaluate((roomId) => {
                // Firebase 관련 변수 확인
                if (typeof firebase === 'undefined' || typeof database === 'undefined') {
                    console.log('❌ Firebase가 로드되지 않음');
                    return false;
                }

                // 현재 사용자 ID 설정
                if (typeof currentUserId === 'undefined') {
                    window.currentUserId = 4;
                }

                // 채팅방 데이터 직접 설정
                window.activeRoomId = roomId;

                // 채팅 영역 표시
                const chatWelcome = document.getElementById('chatWelcome');
                const activeChatArea = document.getElementById('activeChatArea');

                if (chatWelcome) chatWelcome.style.display = 'none';
                if (activeChatArea) activeChatArea.style.display = 'flex';

                // 채팅 헤더 정보 설정
                const chatPartnerName = document.getElementById('chatPartnerName');
                const chatPartnerAvatar = document.getElementById('chatPartnerAvatar');

                if (chatPartnerName) {
                    chatPartnerName.textContent = '대화상대';  // 기본값 설정
                }

                if (chatPartnerAvatar) {
                    chatPartnerAvatar.textContent = 'U';
                }

                // Firebase에서 채팅방 데이터 로드 시도
                try {
                    const roomRef = database.ref(`rooms/${roomId}`);
                    roomRef.once('value', (snapshot) => {
                        const roomData = snapshot.val();
                        if (roomData) {
                            console.log('📂 채팅방 데이터 로드됨:', roomData);

                            // 상대방 사용자 ID 찾기
                            const participants = roomData.participants || {};
                            let otherUserId = null;

                            for (const userId in participants) {
                                if (userId != currentUserId) {
                                    otherUserId = userId;
                                    break;
                                }
                            }

                            if (otherUserId) {
                                console.log('👤 상대방 사용자 ID:', otherUserId);
                                window.currentPartnerUserId = otherUserId;

                                // 메시지 로드
                                const messagesRef = database.ref(`messages/${roomId}`);
                                messagesRef.once('value', (messagesSnapshot) => {
                                    const messages = messagesSnapshot.val() || {};
                                    const messagesList = Object.entries(messages)
                                        .map(([id, data]) => ({ id, ...data }))
                                        .sort((a, b) => a.timestamp - b.timestamp);

                                    console.log(`💬 메시지 ${messagesList.length}개 로드됨`);

                                    // 메시지 표시
                                    const messagesContainer = document.getElementById('chatMessages');
                                    if (messagesContainer) {
                                        messagesContainer.innerHTML = '';

                                        messagesList.forEach(message => {
                                            const messageElement = document.createElement('div');
                                            messageElement.className = `message-item ${message.senderId == currentUserId ? 'own' : ''}`;

                                            const isOwn = message.senderId == currentUserId;
                                            const senderName = message.senderName || '사용자';

                                            messageElement.innerHTML = `
                                                ${!isOwn ? `
                                                    <div class="message-avatar">
                                                        ${senderName.substring(0, 1).toUpperCase()}
                                                    </div>
                                                ` : ''}
                                                <div class="message-bubble">
                                                    <p class="message-text">${message.text || message.message}</p>
                                                    <div class="message-time">${new Date(message.timestamp).toLocaleTimeString()}</div>
                                                </div>
                                            `;

                                            messagesContainer.appendChild(messageElement);
                                        });
                                    }
                                });
                            }
                        }
                    });
                } catch (fbError) {
                    console.error('Firebase 로드 오류:', fbError);
                }

                return true;

            }, this.targetRoomId);

            if (result) {
                console.log('✅ 채팅방 활성화 성공');
                await page.waitForTimeout(3000); // 데이터 로드 대기
                return true;
            } else {
                console.log('❌ 채팅방 활성화 실패');
                return false;
            }

        } catch (error) {
            console.error('❌ 채팅방 활성화 오류:', error.message);
            return false;
        }
    }

    async analyzeChatHeader(page) {
        try {
            const headerAnalysis = await page.evaluate(() => {
                const chatPartnerName = document.getElementById('chatPartnerName');

                if (!chatPartnerName) {
                    return { exists: false };
                }

                const rect = chatPartnerName.getBoundingClientRect();
                const computedStyle = window.getComputedStyle(chatPartnerName);

                return {
                    exists: true,
                    text: chatPartnerName.textContent.trim(),
                    innerHTML: chatPartnerName.innerHTML,
                    className: chatPartnerName.className,
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
                        color: computedStyle.color,
                        fontSize: computedStyle.fontSize,
                        fontWeight: computedStyle.fontWeight,
                        zIndex: computedStyle.zIndex,
                        position: computedStyle.position
                    },
                    isVisible: rect.width > 0 && rect.height > 0,
                    parent: {
                        tagName: chatPartnerName.parentElement?.tagName,
                        className: chatPartnerName.parentElement?.className
                    }
                };
            });

            this.analysisResults.chatHeaderName = headerAnalysis;

            console.log('채팅 헤더 닉네임 분석 결과:');
            if (headerAnalysis.exists) {
                console.log(`  - 텍스트: "${headerAnalysis.text}"`);
                console.log(`  - 클래스: ${headerAnalysis.className}`);
                console.log(`  - 크기: ${headerAnalysis.rect.width}x${headerAnalysis.rect.height}`);
                console.log(`  - 위치: (${headerAnalysis.rect.x}, ${headerAnalysis.rect.y})`);
                console.log(`  - 표시 여부: ${headerAnalysis.isVisible ? '표시됨' : '숨겨짐'}`);
                console.log(`  - Display: ${headerAnalysis.computedStyle.display}`);
                console.log(`  - Visibility: ${headerAnalysis.computedStyle.visibility}`);
                console.log(`  - Opacity: ${headerAnalysis.computedStyle.opacity}`);
                console.log(`  - Color: ${headerAnalysis.computedStyle.color}`);
            } else {
                console.log('  - 채팅 헤더 닉네임 요소를 찾을 수 없음');
            }

        } catch (error) {
            console.error('채팅 헤더 분석 오류:', error.message);
        }
    }

    async analyzeMessageNicknames(page) {
        try {
            const messageAnalysis = await page.evaluate(() => {
                const messageItems = document.querySelectorAll('.message-item:not(.own)');
                const results = [];

                messageItems.forEach((messageItem, index) => {
                    const messageAvatar = messageItem.querySelector('.message-avatar');
                    const messageBubble = messageItem.querySelector('.message-bubble');

                    if (messageAvatar) {
                        const rect = messageAvatar.getBoundingClientRect();
                        const computedStyle = window.getComputedStyle(messageAvatar);

                        results.push({
                            index: index,
                            type: 'avatar',
                            text: messageAvatar.textContent.trim(),
                            innerHTML: messageAvatar.innerHTML,
                            className: messageAvatar.className,
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
                                color: computedStyle.color,
                                backgroundColor: computedStyle.backgroundColor,
                                fontSize: computedStyle.fontSize,
                                fontWeight: computedStyle.fontWeight,
                                zIndex: computedStyle.zIndex,
                                position: computedStyle.position
                            },
                            isVisible: rect.width > 0 && rect.height > 0,
                            messageText: messageBubble ? messageBubble.textContent.trim().substring(0, 50) : ''
                        });
                    }
                });

                return results;
            });

            this.analysisResults.messageElements = messageAnalysis;

            console.log(`메시지 닉네임 분석 결과: ${messageAnalysis.length}개 발견`);

            messageAnalysis.forEach((message, index) => {
                console.log(`\n메시지 ${index + 1}:`);
                console.log(`  - 아바타 텍스트: "${message.text}"`);
                console.log(`  - 클래스: ${message.className}`);
                console.log(`  - 크기: ${message.rect.width}x${message.rect.height}`);
                console.log(`  - 위치: (${message.rect.x}, ${message.rect.y})`);
                console.log(`  - 표시 여부: ${message.isVisible ? '표시됨' : '숨겨짐'}`);
                console.log(`  - Display: ${message.computedStyle.display}`);
                console.log(`  - Visibility: ${message.computedStyle.visibility}`);
                console.log(`  - Opacity: ${message.computedStyle.opacity}`);
                console.log(`  - Color: ${message.computedStyle.color}`);
                console.log(`  - Background: ${message.computedStyle.backgroundColor}`);
                console.log(`  - 메시지 내용: "${message.messageText}"`);
            });

        } catch (error) {
            console.error('메시지 닉네임 분석 오류:', error.message);
        }
    }

    async diagnoseCssIssues(page) {
        try {
            const cssIssues = await page.evaluate(() => {
                const issues = [];

                // 1. 채팅 헤더 닉네임 검사
                const chatPartnerName = document.getElementById('chatPartnerName');
                if (chatPartnerName) {
                    const rect = chatPartnerName.getBoundingClientRect();
                    const computedStyle = window.getComputedStyle(chatPartnerName);

                    if (computedStyle.display === 'none') {
                        issues.push({
                            element: 'chatPartnerName',
                            issue: 'display: none',
                            severity: 'high'
                        });
                    }

                    if (computedStyle.visibility === 'hidden') {
                        issues.push({
                            element: 'chatPartnerName',
                            issue: 'visibility: hidden',
                            severity: 'high'
                        });
                    }

                    if (parseFloat(computedStyle.opacity) === 0) {
                        issues.push({
                            element: 'chatPartnerName',
                            issue: 'opacity: 0',
                            severity: 'high'
                        });
                    }

                    if (rect.width === 0 || rect.height === 0) {
                        issues.push({
                            element: 'chatPartnerName',
                            issue: '크기가 0px',
                            severity: 'high'
                        });
                    }

                    // 색상 검사 (배경과 같은 색상인지)
                    const parent = chatPartnerName.parentElement;
                    if (parent) {
                        const parentStyle = window.getComputedStyle(parent);
                        if (computedStyle.color === parentStyle.backgroundColor) {
                            issues.push({
                                element: 'chatPartnerName',
                                issue: '텍스트 색상이 배경과 동일',
                                severity: 'medium'
                            });
                        }
                    }
                }

                // 2. 메시지 아바타 검사
                const messageAvatars = document.querySelectorAll('.message-avatar');
                messageAvatars.forEach((avatar, index) => {
                    const rect = avatar.getBoundingClientRect();
                    const computedStyle = window.getComputedStyle(avatar);

                    if (computedStyle.display === 'none') {
                        issues.push({
                            element: `message-avatar-${index}`,
                            issue: 'display: none',
                            severity: 'medium'
                        });
                    }

                    if (rect.width === 0 || rect.height === 0) {
                        issues.push({
                            element: `message-avatar-${index}`,
                            issue: '크기가 0px',
                            severity: 'medium'
                        });
                    }
                });

                // 3. 오버레이 요소 검사 (높은 z-index로 가리는 요소)
                const allElements = document.querySelectorAll('*');
                allElements.forEach(element => {
                    const computedStyle = window.getComputedStyle(element);
                    const zIndex = parseInt(computedStyle.zIndex);

                    if (zIndex > 100 && (computedStyle.position === 'absolute' || computedStyle.position === 'fixed')) {
                        const rect = element.getBoundingClientRect();

                        // 채팅 헤더 영역과 겹치는지 확인
                        const headerName = document.getElementById('chatPartnerName');
                        if (headerName) {
                            const headerRect = headerName.getBoundingClientRect();

                            const isOverlapping = !(rect.right < headerRect.left ||
                                                   rect.left > headerRect.right ||
                                                   rect.bottom < headerRect.top ||
                                                   rect.top > headerRect.bottom);

                            if (isOverlapping) {
                                issues.push({
                                    element: `overlay-${element.tagName}.${element.className}`,
                                    issue: `높은 z-index(${zIndex})로 닉네임을 가림`,
                                    severity: 'critical'
                                });
                            }
                        }
                    }
                });

                return issues;
            });

            this.analysisResults.cssIssues = cssIssues;

            console.log(`CSS 문제 진단 결과: ${cssIssues.length}개 발견`);

            cssIssues.forEach((issue, index) => {
                console.log(`\n문제 ${index + 1}:`);
                console.log(`  - 요소: ${issue.element}`);
                console.log(`  - 문제: ${issue.issue}`);
                console.log(`  - 심각도: ${issue.severity}`);
            });

        } catch (error) {
            console.error('CSS 문제 진단 오류:', error.message);
        }
    }

    generateRecommendations() {
        const recommendations = [];

        // 1. 채팅 헤더 닉네임 문제
        if (this.analysisResults.chatHeaderName) {
            const header = this.analysisResults.chatHeaderName;

            if (!header.exists) {
                recommendations.push({
                    priority: 'high',
                    issue: '채팅 헤더 닉네임 요소가 존재하지 않음',
                    solution: 'chatPartnerName ID를 가진 요소가 DOM에 있는지 확인'
                });
            } else if (!header.isVisible) {
                if (header.computedStyle.display === 'none') {
                    recommendations.push({
                        priority: 'high',
                        issue: '채팅 헤더 닉네임이 display:none으로 숨겨짐',
                        solution: 'CSS에서 #chatPartnerName { display: block; } 추가'
                    });
                }

                if (header.computedStyle.visibility === 'hidden') {
                    recommendations.push({
                        priority: 'high',
                        issue: '채팅 헤더 닉네임이 visibility:hidden으로 숨겨짐',
                        solution: 'CSS에서 #chatPartnerName { visibility: visible; } 추가'
                    });
                }

                if (parseFloat(header.computedStyle.opacity) === 0) {
                    recommendations.push({
                        priority: 'high',
                        issue: '채팅 헤더 닉네임이 opacity:0으로 투명함',
                        solution: 'CSS에서 #chatPartnerName { opacity: 1; } 추가'
                    });
                }
            } else if (header.text.trim() === '' || header.text === '사용자') {
                recommendations.push({
                    priority: 'medium',
                    issue: '채팅 헤더 닉네임이 기본값이거나 비어있음',
                    solution: 'JavaScript에서 상대방 닉네임을 올바르게 설정하는지 확인'
                });
            }
        }

        // 2. 메시지 아바타 문제
        const invisibleMessages = this.analysisResults.messageElements.filter(msg => !msg.isVisible);
        if (invisibleMessages.length > 0) {
            recommendations.push({
                priority: 'medium',
                issue: `${invisibleMessages.length}개의 메시지 아바타가 보이지 않음`,
                solution: '.message-avatar CSS 스타일을 확인하고 display, visibility, opacity 속성 점검'
            });
        }

        // 3. CSS 문제별 권장사항
        const criticalIssues = this.analysisResults.cssIssues.filter(issue => issue.severity === 'critical');
        if (criticalIssues.length > 0) {
            recommendations.push({
                priority: 'critical',
                issue: '높은 z-index 요소가 닉네임을 가리고 있음',
                solution: '가리는 요소의 z-index를 낮추거나 닉네임 요소의 z-index를 높이기'
            });
        }

        const hiddenIssues = this.analysisResults.cssIssues.filter(issue =>
            issue.issue.includes('display: none') ||
            issue.issue.includes('visibility: hidden') ||
            issue.issue.includes('opacity: 0')
        );

        if (hiddenIssues.length > 0) {
            recommendations.push({
                priority: 'high',
                issue: 'CSS 속성으로 인해 닉네임 요소가 숨겨짐',
                solution: 'display, visibility, opacity 속성을 확인하고 수정'
            });
        }

        this.analysisResults.recommendations = recommendations;

        console.log('\n🎯 문제 해결 권장사항:');
        if (recommendations.length === 0) {
            console.log('  특별한 문제가 발견되지 않았습니다.');
        } else {
            recommendations.forEach((rec, index) => {
                console.log(`\n  ${index + 1}. [${rec.priority.toUpperCase()}] ${rec.issue}`);
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

        const reportPath = '/var/www/html/topmkt/chat_nickname_direct_analysis_report.json';
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        console.log(`\n📊 분석 리포트 저장: ${reportPath}`);

        return reportPath;
    }
}

// 실행
async function main() {
    const analyzer = new DirectChatNicknameAnalyzer();

    try {
        const results = await analyzer.analyze();
        await analyzer.saveAnalysisReport();

        console.log('\n✅ 채팅방 닉네임 표시 문제 직접 분석 완료');

        // 요약 출력
        console.log('\n📋 분석 요약:');
        console.log(`- 채팅 페이지 접속: ${results.chatPageAccess ? '성공' : '실패'}`);
        console.log(`- 채팅 헤더 닉네임: ${results.chatHeaderName?.exists ? '발견' : '미발견'}`);
        console.log(`- 메시지 요소: ${results.messageElements.length}개`);
        console.log(`- CSS 문제: ${results.cssIssues.length}개`);
        console.log(`- 권장사항: ${results.recommendations.length}개`);

        if (results.chatHeaderName?.exists) {
            console.log(`- 헤더 닉네임 텍스트: "${results.chatHeaderName.text}"`);
            console.log(`- 헤더 닉네임 표시: ${results.chatHeaderName.isVisible ? '표시됨' : '숨겨짐'}`);
        }

    } catch (error) {
        console.error('❌ 분석 실패:', error.message);
        process.exit(1);
    }
}

if (require.main === module) {
    main();
}

module.exports = DirectChatNicknameAnalyzer;