<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>채팅방 클릭 디버깅</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
        .test-button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .test-button:hover { background: #0056b3; }
        .code-block { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; margin: 10px 0; }
        .status { padding: 10px; border-radius: 5px; margin: 10px 0; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>
    <h1>🔍 채팅방 클릭 문제 디버깅 도구</h1>
    
    <div class="debug-section">
        <h2>📊 현재 상태 확인</h2>
        <button class="test-button" onclick="checkChatRoomElements()">채팅방 요소 확인</button>
        <button class="test-button" onclick="testClickEvents()">클릭 이벤트 테스트</button>
        <button class="test-button" onclick="checkZIndex()">Z-Index 충돌 확인</button>
        <button class="test-button" onclick="checkOverlapping()">요소 겹침 확인</button>
        <div id="statusOutput"></div>
    </div>
    
    <div class="debug-section">
        <h2>🛠️ 수동 테스트</h2>
        <p>문제가 있는 채팅방 ID들을 직접 클릭해보세요:</p>
        <button class="test-button" onclick="manualClickTest('-OSTjuT0YIJpiPVMKijP')">-OSTjuT0YIJpiPVMKijP 클릭</button>
        <button class="test-button" onclick="manualClickTest('room_4_5')">room_4_5 클릭</button>
        <button class="test-button" onclick="manualClickTest('-OSSjjMUdG_zrd_kW3V_')">-OSSjjMUdG_zrd_kW3V_ 클릭 (정상)</button>
    </div>
    
    <div class="debug-section">
        <h2>🔧 고급 디버깅 코드</h2>
        <p>브라우저 개발자 도구 콘솔에서 실행하세요:</p>
        <div class="code-block">// 1. 모든 채팅방 요소 찾기 및 상태 확인
const roomElements = document.querySelectorAll('.chat-room-item');
console.log('=== 채팅방 요소 분석 ===');
roomElements.forEach((element, index) => {
    const roomId = element.getAttribute('data-room-id');
    const rect = element.getBoundingClientRect();
    const style = window.getComputedStyle(element);
    
    console.log(`채팅방 ${index + 1}: ${roomId}`, {
        visible: rect.width > 0 && rect.height > 0,
        position: { x: rect.x, y: rect.y, width: rect.width, height: rect.height },
        zIndex: style.zIndex,
        pointerEvents: style.pointerEvents,
        display: style.display,
        visibility: style.visibility,
        opacity: style.opacity,
        transform: style.transform,
        overflow: style.overflow
    });
});

// 2. 클릭 이벤트 강제 추가 및 테스트
console.log('=== 클릭 이벤트 강제 테스트 ===');
roomElements.forEach(element => {
    const roomId = element.getAttribute('data-room-id');
    
    // 기존 이벤트 제거 (혹시 모를 중복 방지)
    element.removeEventListener('click', arguments.callee);
    
    // 새로운 테스트 이벤트 추가
    element.addEventListener('click', function(e) {
        console.log(`🎯 강제 클릭 이벤트 발생: ${roomId}`);
        console.log('Event details:', e);
        console.log('Target:', e.target);
        console.log('CurrentTarget:', e.currentTarget);
    }, { capture: true, passive: false });
    
    console.log(`클릭 이벤트 추가됨: ${roomId}`);
});

// 3. 마우스 이벤트 리스너 추가 (클릭이 안 되는 이유 파악)
console.log('=== 마우스 이벤트 리스너 추가 ===');
roomElements.forEach(element => {
    const roomId = element.getAttribute('data-room-id');
    
    ['mousedown', 'mouseup', 'click', 'touchstart', 'touchend'].forEach(eventType => {
        element.addEventListener(eventType, function(e) {
            console.log(`🖱️ ${eventType} 이벤트: ${roomId}`, e);
        }, { capture: true });
    });
});

// 4. 문제가 있는 채팅방에 시각적 하이라이트 추가
console.log('=== 시각적 하이라이트 추가 ===');
const problematicRooms = ['-OSTjuT0YIJpiPVMKijP', 'room_4_5'];
problematicRooms.forEach(roomId => {
    roomElements.forEach(element => {
        if (element.getAttribute('data-room-id') === roomId) {
            element.style.border = '3px solid red';
            element.style.backgroundColor = 'rgba(255, 0, 0, 0.1)';
            console.log(`하이라이트 적용: ${roomId}`);
        }
    });
});

// 5. 겹치는 요소 찾기
console.log('=== 겹치는 요소 찾기 ===');
roomElements.forEach(element => {
    const rect = element.getBoundingClientRect();
    const centerX = rect.x + rect.width / 2;
    const centerY = rect.y + rect.height / 2;
    const topElement = document.elementFromPoint(centerX, centerY);
    
    if (topElement !== element) {
        console.log(`⚠️ 요소 겹침 감지:`, {
            roomId: element.getAttribute('data-room-id'),
            expected: element,
            actual: topElement,
            topElementInfo: {
                tagName: topElement.tagName,
                className: topElement.className,
                id: topElement.id
            }
        });
    }
});</div>
    </div>
    
    <div class="debug-section">
        <h2>🔧 임시 해결책</h2>
        <p>클릭이 안 되는 채팅방을 강제로 열어보세요:</p>
        <button class="test-button" onclick="forceOpenRoom('-OSTjuT0YIJpiPVMKijP')">강제로 -OSTjuT0YIJpiPVMKijP 열기</button>
        <button class="test-button" onclick="forceOpenRoom('room_4_5')">강제로 room_4_5 열기</button>
    </div>

    <script>
        function checkChatRoomElements() {
            const output = document.getElementById('statusOutput');
            const roomElements = document.querySelectorAll('.chat-room-item');
            
            let html = '<div class="status success">채팅방 요소 확인 결과:</div>';
            html += `<p>총 ${roomElements.length}개의 채팅방 요소 발견</p>`;
            
            roomElements.forEach((element, index) => {
                const roomId = element.getAttribute('data-room-id');
                const rect = element.getBoundingClientRect();
                const isVisible = rect.width > 0 && rect.height > 0;
                
                html += `<div class="status ${isVisible ? 'success' : 'error'}">
                    <strong>${index + 1}. ${roomId}</strong><br>
                    크기: ${rect.width} x ${rect.height}<br>
                    위치: (${Math.round(rect.x)}, ${Math.round(rect.y)})<br>
                    표시: ${isVisible ? '정상' : '숨김'}
                </div>`;
            });
            
            output.innerHTML = html;
        }
        
        function testClickEvents() {
            const output = document.getElementById('statusOutput');
            const roomElements = document.querySelectorAll('.chat-room-item');
            
            let html = '<div class="status warning">클릭 이벤트 테스트 중...</div>';
            
            roomElements.forEach(element => {
                const roomId = element.getAttribute('data-room-id');
                
                // 테스트 클릭 이벤트 추가
                element.addEventListener('click', function(e) {
                    console.log(`🔍 테스트 클릭: ${roomId}`);
                    alert(`테스트 클릭 성공: ${roomId}`);
                }, { once: true });
                
                html += `<p>✅ ${roomId}에 테스트 이벤트 추가됨</p>`;
            });
            
            html += '<div class="status warning">이제 채팅방을 클릭해보세요. 알림이 뜨면 이벤트가 정상 작동하는 것입니다.</div>';
            output.innerHTML = html;
        }
        
        function checkZIndex() {
            const output = document.getElementById('statusOutput');
            const roomElements = document.querySelectorAll('.chat-room-item');
            
            let html = '<div class="status warning">Z-Index 확인 결과:</div>';
            
            roomElements.forEach(element => {
                const roomId = element.getAttribute('data-room-id');
                const style = window.getComputedStyle(element);
                const zIndex = style.zIndex;
                
                html += `<p><strong>${roomId}</strong>: z-index = ${zIndex}</p>`;
            });
            
            output.innerHTML = html;
        }
        
        function checkOverlapping() {
            const output = document.getElementById('statusOutput');
            const roomElements = document.querySelectorAll('.chat-room-item');
            
            let html = '<div class="status warning">요소 겹침 확인 결과:</div>';
            
            roomElements.forEach(element => {
                const roomId = element.getAttribute('data-room-id');
                const rect = element.getBoundingClientRect();
                const centerX = rect.x + rect.width / 2;
                const centerY = rect.y + rect.height / 2;
                const topElement = document.elementFromPoint(centerX, centerY);
                
                if (topElement === element) {
                    html += `<div class="status success">✅ ${roomId}: 겹침 없음</div>`;
                } else {
                    html += `<div class="status error">❌ ${roomId}: 다른 요소에 가려짐<br>
                        가리는 요소: ${topElement.tagName}.${topElement.className}</div>`;
                }
            });
            
            output.innerHTML = html;
        }
        
        function manualClickTest(roomId) {
            console.log(`🧪 수동 클릭 테스트: ${roomId}`);
            
            // 요소 찾기
            const roomElements = document.querySelectorAll('.chat-room-item');
            let targetElement = null;
            
            for (const element of roomElements) {
                if (element.getAttribute('data-room-id') === roomId) {
                    targetElement = element;
                    break;
                }
            }
            
            if (targetElement) {
                console.log(`요소 발견: ${roomId}`, targetElement);
                
                // 강제 클릭 이벤트 발생
                const clickEvent = new MouseEvent('click', {
                    view: window,
                    bubbles: true,
                    cancelable: true
                });
                
                targetElement.dispatchEvent(clickEvent);
                console.log(`클릭 이벤트 발생시킴: ${roomId}`);
            } else {
                console.error(`요소를 찾을 수 없음: ${roomId}`);
                alert(`요소를 찾을 수 없습니다: ${roomId}`);
            }
        }
        
        function forceOpenRoom(roomId) {
            // 채팅 페이지의 openChatRoom 함수 직접 호출
            if (typeof openChatRoom === 'function') {
                console.log(`🔓 강제로 채팅방 열기: ${roomId}`);
                openChatRoom(roomId);
            } else {
                alert('openChatRoom 함수를 찾을 수 없습니다. 채팅 페이지에서 실행해주세요.');
            }
        }
        
        // 페이지 로드 시 안내
        window.addEventListener('load', function() {
            const output = document.getElementById('statusOutput');
            output.innerHTML = '<div class="status warning">채팅 페이지(https://www.topmktx.com/chat)에서 이 도구를 사용하거나, 개발자 도구에서 제공된 코드를 실행하세요.</div>';
        });
    </script>
</body>
</html>