<?php
/**
 * 채팅방 클릭 문제 디버깅 도구
 */

require_once '../src/config/config.php';
require_once '../src/config/database.php';

echo "<h1>채팅방 클릭 문제 디버깅</h1>";

// 문제가 있는 채팅방 ID들
$problematic_rooms = ['-OSTjuT0YIJpiPVMKijP', '-room_4_5'];
$working_room = 'OSSjjMUdG_zrd_kW3V_';

echo "<h2>🔍 채팅방 상태 분석</h2>";

foreach ([$working_room, ...$problematic_rooms] as $roomId) {
    echo "<div style='border: 1px solid #ccc; margin: 10px 0; padding: 15px; border-radius: 8px;'>";
    echo "<h3>채팅방 ID: <code style='background: #f0f0f0; padding: 2px 6px;'>{$roomId}</code></h3>";
    
    // 1. Firebase에서 채팅방 정보 확인 (시뮬레이션)
    echo "<h4>📊 예상 Firebase 구조:</h4>";
    echo "<pre>";
    echo "chatRooms/{$roomId}/\n";
    echo "├── type: 'private'\n";
    echo "├── participants/\n";
    echo "│   ├── user1_id: true\n";
    echo "│   └── user2_id: true\n";
    echo "├── lastMessage: '마지막 메시지'\n";
    echo "├── lastMessageTime: timestamp\n";
    echo "└── createdAt: timestamp\n\n";
    
    echo "messages/{$roomId}/\n";
    echo "└── message_id/\n";
    echo "    ├── senderId: 'user_id'\n";
    echo "    ├── text: '메시지 내용'\n";
    echo "    └── timestamp: timestamp\n";
    echo "</pre>";
    
    // 2. 채팅방 ID 형식 분석
    echo "<h4>🔍 채팅방 ID 형식 분석:</h4>";
    echo "<ul>";
    echo "<li><strong>길이:</strong> " . strlen($roomId) . " 문자</li>";
    echo "<li><strong>형식:</strong> ";
    
    if (strpos($roomId, '-room_') === 0) {
        echo "MySQL 스타일 (숫자 기반)";
        preg_match('/^-room_(\d+)_(\d+)$/', $roomId, $matches);
        if ($matches) {
            echo " - 사용자 {$matches[1]}과 {$matches[2]} 간의 채팅방";
        }
    } elseif (strpos($roomId, '-') === 0) {
        echo "Firebase 푸시 키 스타일 (자동 생성)";
    } else {
        echo "기타 형식";
    }
    echo "</li>";
    
    echo "<li><strong>특수문자:</strong> ";
    $special_chars = preg_replace('/[a-zA-Z0-9_-]/', '', $roomId);
    echo $special_chars ? htmlspecialchars($special_chars) : "없음";
    echo "</li>";
    echo "</ul>";
    
    // 3. JavaScript에서 발생할 수 있는 문제점 분석
    echo "<h4>⚠️ 잠재적 문제점:</h4>";
    echo "<ul>";
    
    // DOM 선택자 문제
    if (strpos($roomId, '-') === 0 || preg_match('/[^a-zA-Z0-9_-]/', $roomId)) {
        echo "<li><span style='color: red;'>❌ DOM 선택자 문제:</span> ID가 숫자나 특수문자로 시작하거나 특수문자 포함</li>";
    } else {
        echo "<li><span style='color: green;'>✅ DOM 선택자 OK</span></li>";
    }
    
    // CSS 선택자 이스케이프 필요성
    if (strpos($roomId, '-') !== false) {
        echo "<li><span style='color: orange;'>⚠️ CSS 선택자:</span> 하이픈 포함으로 이스케이프 필요할 수 있음</li>";
    }
    
    // JavaScript 문자열 처리
    if (strpos($roomId, "'") !== false || strpos($roomId, '"') !== false) {
        echo "<li><span style='color: red;'>❌ JavaScript 문자열:</span> 따옴표 포함으로 문자열 처리 오류 가능</li>";
    } else {
        echo "<li><span style='color: green;'>✅ JavaScript 문자열 OK</span></li>";
    }
    
    echo "</ul>";
    
    // 4. 올바른 JavaScript 선택자 제안
    echo "<h4>🔧 올바른 선택자 사용법:</h4>";
    echo "<pre>";
    echo "// 문제가 될 수 있는 방법:\n";
    echo "document.querySelector('[data-room-id=\"{$roomId}\"]')\n\n";
    
    echo "// 안전한 방법:\n";
    echo "document.querySelector('[data-room-id=\"' + " . json_encode($roomId) . " + '\"]')\n";
    echo "// 또는\n";
    echo "document.querySelector(`[data-room-id=\"{$roomId}\"]`)\n";
    echo "</pre>";
    
    echo "</div>";
}

// 5. 실제 채팅방 클릭 이벤트 체크를 위한 JavaScript 생성
echo "<h2>🔨 실시간 디버깅 도구</h2>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px;'>";
echo "<p>브라우저 개발자 도구 콘솔에서 다음 코드를 실행하여 채팅방 상태를 확인하세요:</p>";
echo "<textarea style='width: 100%; height: 200px; font-family: monospace;' readonly>";
echo "// 채팅방 디버깅 코드
const problematicRooms = ['-OSTjuT0YIJpiPVMKijP', '-room_4_5'];
const workingRoom = 'OSSjjMUdG_zrd_kW3V_';

console.log('=== 채팅방 요소 존재 여부 확인 ===');
[...problematicRooms, workingRoom].forEach(roomId => {
    const element = document.querySelector(`[data-room-id=\"{$roomId}\"]`);
    console.log(`Room ${roomId}:`, {
        exists: !!element,
        visible: element ? element.offsetParent !== null : false,
        clickable: element ? !element.disabled : false,
        classes: element ? element.className : 'N/A',
        style: element ? element.style.cssText : 'N/A'
    });
});

console.log('=== 채팅방 데이터 확인 ===');
if (window.chatRooms) {
    Object.keys(window.chatRooms).forEach(roomId => {
        console.log(`Room ${roomId}:`, window.chatRooms[roomId]);
    });
} else {
    console.log('chatRooms 객체가 없습니다.');
}

console.log('=== 이벤트 리스너 확인 ===');
problematicRooms.forEach(roomId => {
    const element = document.querySelector(`[data-room-id=\"{$roomId}\"]`);
    if (element) {
        console.log(`Room ${roomId} 클릭 테스트:`, element);
        element.addEventListener('click', (e) => {
            console.log('클릭 이벤트 발생:', roomId, e);
        });
    }
});";
echo "</textarea>";
echo "</div>";

// 6. 해결 방법 제안
echo "<h2>💡 해결 방법 제안</h2>";
echo "<ol>";
echo "<li><strong>DOM 선택자 수정:</strong> 특수문자가 포함된 ID에 대해 적절한 이스케이프 처리</li>";
echo "<li><strong>Firebase 데이터 확인:</strong> 해당 채팅방이 실제로 Firebase에 존재하는지 확인</li>";
echo "<li><strong>메시지 데이터 확인:</strong> 채팅방에 메시지가 없어서 렌더링되지 않는지 확인</li>";
echo "<li><strong>권한 확인:</strong> 현재 사용자가 해당 채팅방에 접근 권한이 있는지 확인</li>";
echo "<li><strong>이벤트 중복 확인:</strong> 여러 이벤트 리스너가 충돌하는지 확인</li>";
echo "</ol>";

echo "<h2>🔍 다음 단계</h2>";
echo "<p>1. 브라우저에서 https://www.topmktx.com/chat 접속</p>";
echo "<p>2. 개발자 도구 (F12) 열기</p>";
echo "<p>3. 콘솔 탭에서 위의 디버깅 코드 실행</p>";
echo "<p>4. 결과를 확인하여 문제점 파악</p>";

?>

<style>
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 20px; }
h1, h2, h3, h4 { color: #333; }
pre { background: #f8f9fa; padding: 15px; border-radius: 6px; overflow-x: auto; }
code { background: #f1f3f4; padding: 2px 6px; border-radius: 3px; }
ul, ol { line-height: 1.6; }
</style>