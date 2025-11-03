const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const context = await browser.newContext({
        viewport: { width: 1920, height: 1080 }
    });

    const page = await context.newPage();

    // 콘솔 로그 수집
    const consoleLogs = [];
    page.on('console', msg => {
        const type = msg.type();
        const text = msg.text();
        consoleLogs.push({ type, text });
        if (type === 'error' || type === 'warning') {
            console.log(`[CONSOLE ${type.toUpperCase()}] ${text}`);
        }
    });

    // 에러 수집
    const errors = [];
    page.on('pageerror', error => {
        errors.push(error.message);
        console.log(`[PAGE ERROR] ${error.message}`);
    });

    // 네트워크 요청 실패 수집
    const failedRequests = [];
    page.on('requestfailed', request => {
        failedRequests.push({
            url: request.url(),
            failure: request.failure().errorText
        });
        console.log(`[REQUEST FAILED] ${request.url()} - ${request.failure().errorText}`);
    });

    try {
        console.log('='.repeat(60));
        console.log('1. DevLoginHelper 로그인 시작 (user_id=14 - 010-6703-3056)');
        console.log('='.repeat(60));

        // DevLoginHelper로 로그인 (리다이렉트 대기)
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=14', {
            waitUntil: 'networkidle',
            timeout: 30000
        });

        console.log(`DevLoginHelper 페이지 로드: ${page.url()}`);

        // JavaScript 리다이렉트 대기 (2초)
        console.log('리다이렉트 대기 중...');
        await page.waitForURL('https://www.topmktx.com/admin/users', {
            timeout: 5000
        }).catch(() => console.log('⚠️ 리다이렉트 타임아웃 (정상일 수 있음)'));

        console.log(`리다이렉트 후 URL: ${page.url()}`);

        // 쿠키 확인
        const cookies = await context.cookies();
        const authToken = cookies.find(c => c.name === 'auth_token' || c.name === 'jwt_token');

        if (authToken) {
            console.log(`✅ 인증 쿠키 확인: ${authToken.name} = ${authToken.value.substring(0, 20)}...`);
        } else {
            console.log('❌ 인증 쿠키가 없습니다!');
            console.log(`쿠키 목록: ${cookies.map(c => c.name).join(', ')}`);
        }

        await page.waitForTimeout(1000);

        console.log('\n' + '='.repeat(60));
        console.log('2. 채팅 페이지 접속 시작...');
        console.log('='.repeat(60));

        // 타임아웃 감지용
        let isTimeout = false;
        const timeoutTimer = setTimeout(() => {
            isTimeout = true;
            console.log('\n⚠️  30초 경과 - 페이지가 응답하지 않습니다!');
        }, 30000);

        await page.goto('https://www.topmktx.com/chat', {
            waitUntil: 'domcontentloaded',
            timeout: 35000
        });

        clearTimeout(timeoutTimer);

        if (isTimeout) {
            console.log('❌ 페이지 로딩이 30초를 초과했습니다!');
        } else {
            console.log('\n✅ 페이지 DOMContentLoaded 완료');
        }

        // 10초 대기 (JavaScript 실행 확인)
        console.log('\n⏳ JavaScript 실행 10초 대기...');
        for (let i = 1; i <= 10; i++) {
            await page.waitForTimeout(1000);
            if (i % 2 === 0) {
                const state = await page.evaluate(() => ({
                    loading: document.getElementById('roomsLoading') !== null,
                    chatRooms: typeof chatRooms !== 'undefined' ? Object.keys(chatRooms).length : 0,
                    listeners: window.chatRoomsListener ? 'active' : 'null'
                }));
                console.log(`  ${i}초: 로딩=${state.loading}, 채팅방=${state.chatRooms}개, 리스너=${state.listeners}`);
            }
        }

        console.log('\n' + '='.repeat(60));
        console.log('3. 페이지 상태 최종 확인');
        console.log('='.repeat(60));

        // 채팅방 목록 로딩 확인
        const loadingElement = await page.$('#roomsLoading');
        console.log(`로딩 요소 존재: ${loadingElement !== null}`);

        // 채팅방 목록 존재 확인
        const chatRoomsList = await page.$('#chatRoomsList');
        console.log(`채팅방 목록 컨테이너 존재: ${chatRoomsList !== null}`);

        // 채팅방 아이템 개수
        const roomItems = await page.$$('.chat-room-item');
        console.log(`채팅방 아이템 개수: ${roomItems.length}`);

        // window 객체의 리스너 개수 확인
        const listenersInfo = await page.evaluate(() => {
            return {
                lastMessageListeners: window.lastMessageListeners ? Object.keys(window.lastMessageListeners).length : 0,
                roomListeners: window.roomListeners ? Object.keys(window.roomListeners).length : 0,
                chatRoomsListener: window.chatRoomsListener ? 'exists' : 'null',
                currentMessageListener: window.currentMessageListener ? 'exists' : 'null',
                chatRooms: typeof chatRooms !== 'undefined' ? Object.keys(chatRooms).length : 0,
                currentUserId: typeof currentUserId !== 'undefined' ? currentUserId : 'undefined',
                firebaseInitialized: typeof firebase !== 'undefined' && firebase.apps.length > 0
            };
        });

        console.log('\nFirebase 리스너 상태:');
        console.log(`  - Firebase 초기화: ${listenersInfo.firebaseInitialized}`);
        console.log(`  - currentUserId: ${listenersInfo.currentUserId}`);
        console.log(`  - lastMessageListeners: ${listenersInfo.lastMessageListeners}개`);
        console.log(`  - roomListeners: ${listenersInfo.roomListeners}개`);
        console.log(`  - chatRoomsListener: ${listenersInfo.chatRoomsListener}`);
        console.log(`  - currentMessageListener: ${listenersInfo.currentMessageListener}`);
        console.log(`  - chatRooms: ${listenersInfo.chatRooms}개`);

        // 스크린샷
        const screenshotPath = '/var/www/html/topmkt/temp/screenshots/chat_test.png';
        await page.screenshot({ path: screenshotPath, fullPage: true });
        console.log(`\n📸 스크린샷 저장: ${screenshotPath}`);

        console.log('\n' + '='.repeat(60));
        console.log('4. 수집된 에러 및 로그 요약');
        console.log('='.repeat(60));

        console.log(`\n❌ JavaScript 에러: ${errors.length}개`);
        errors.forEach((err, i) => {
            console.log(`  ${i + 1}. ${err}`);
        });

        console.log(`\n🔥 실패한 네트워크 요청: ${failedRequests.length}개`);
        failedRequests.forEach((req, i) => {
            console.log(`  ${i + 1}. ${req.url}`);
            console.log(`     └─ ${req.failure}`);
        });

        console.log(`\n📝 콘솔 에러/경고 메시지: ${consoleLogs.filter(l => l.type === 'error' || l.type === 'warning').length}개`);
        consoleLogs.filter(l => l.type === 'error' || l.type === 'warning').forEach((log, i) => {
            console.log(`  ${i + 1}. [${log.type}] ${log.text}`);
        });

        console.log('\n' + '='.repeat(60));
        console.log('5. 결론');
        console.log('='.repeat(60));

        if (errors.length === 0 && failedRequests.length === 0) {
            console.log('✅ JavaScript 에러 없음');
            console.log('✅ 네트워크 요청 실패 없음');

            if (chatRoomsList) {
                console.log('✅ 채팅방 목록 컨테이너 정상 로드');
            } else {
                console.log('❌ 채팅방 목록 컨테이너가 없습니다!');
            }

            if (roomItems.length > 0) {
                console.log(`✅ 채팅방 ${roomItems.length}개 로드 완료`);
            } else {
                console.log('⚠️  채팅방이 없거나 로드되지 않았습니다');
            }

            if (!listenersInfo.firebaseInitialized) {
                console.log('❌ Firebase가 초기화되지 않았습니다!');
            }

            if (listenersInfo.chatRoomsListener === 'null') {
                console.log('❌ chatRoomsListener가 등록되지 않았습니다!');
            }
        } else {
            console.log('❌ 문제 발견!');
            if (errors.length > 0) {
                console.log(`   - JavaScript 에러 ${errors.length}개`);
            }
            if (failedRequests.length > 0) {
                console.log(`   - 네트워크 요청 실패 ${failedRequests.length}개`);
            }
        }

    } catch (error) {
        console.error('\n❌ 테스트 실패:', error.message);
        console.error(error.stack);

        // 최종 스크린샷
        try {
            await page.screenshot({ path: '/var/www/html/topmkt/temp/screenshots/chat_test_error.png', fullPage: true });
            console.log('\n📸 에러 스크린샷 저장: /var/www/html/topmkt/temp/screenshots/chat_test_error.png');
        } catch (e) {}
    } finally {
        await browser.close();
    }
})();
