<?php
/**
 * 채팅방 디버깅 도구
 */

// Firebase 설정 정보 확인
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/config.php';

echo "<h1>채팅방 디버깅 도구</h1>";

?>

<script>
// Firebase 설정 확인
console.log('🔥 Firebase 설정 확인:');
console.log('FIREBASE_CONFIG:', <?php echo json_encode(FIREBASE_CONFIG); ?>);

// Firebase 초기화 시뮬레이션
const firebaseConfig = <?php echo json_encode(FIREBASE_CONFIG); ?>;

// 실제 Firebase 연결 테스트
if (typeof firebase !== 'undefined') {
    console.log('✅ Firebase SDK 로드됨');
    
    try {
        firebase.initializeApp(firebaseConfig);
        const database = firebase.database();
        console.log('✅ Firebase 초기화 성공');
        
        // room_4_5 채팅방 데이터 확인
        console.log('🔍 채팅방 room_4_5 데이터 확인 중...');
        
        database.ref('chatRooms/room_4_5').once('value', function(snapshot) {
            const roomData = snapshot.val();
            console.log('📂 채팅방 데이터:', roomData);
            
            if (!roomData) {
                console.error('❌ 채팅방 room_4_5가 존재하지 않음');
                document.getElementById('result').innerHTML = '<p style="color: red;">❌ 채팅방 room_4_5가 Firebase에 존재하지 않습니다.</p>';
                return;
            }
            
            // 메시지 확인
            database.ref('messages/room_4_5').limitToLast(10).once('value', function(msgSnapshot) {
                const messages = msgSnapshot.val() || {};
                console.log('💬 메시지 데이터:', messages);
                
                let resultHtml = `
                    <h2>✅ 채팅방 room_4_5 분석 결과</h2>
                    <h3>📂 채팅방 정보</h3>
                    <pre>${JSON.stringify(roomData, null, 2)}</pre>
                    
                    <h3>💬 최근 메시지 (최대 10개)</h3>
                    <pre>${JSON.stringify(messages, null, 2)}</pre>
                    
                    <h3>📊 요약</h3>
                    <ul>
                        <li>채팅방 존재: ✅</li>
                        <li>참여자 수: ${roomData.participants ? Object.keys(roomData.participants).length : 0}명</li>
                        <li>메시지 수: ${Object.keys(messages).length}개</li>
                        <li>채팅방 타입: ${roomData.type || '미정의'}</li>
                        <li>마지막 활동: ${roomData.lastActivity ? new Date(roomData.lastActivity).toLocaleString() : '없음'}</li>
                    </ul>
                `;
                
                if (Object.keys(messages).length === 0) {
                    resultHtml += '<p style="color: orange;">⚠️ 메시지가 없습니다. 이것이 채팅방이 표시되지 않는 원인일 수 있습니다.</p>';
                }
                
                document.getElementById('result').innerHTML = resultHtml;
            });
        });
        
        // 사용자 채팅방 목록 확인 (사용자 ID 4 기준)
        database.ref('userRooms/4').once('value', function(snapshot) {
            const userRooms = snapshot.val() || {};
            console.log('👤 사용자 4의 채팅방 목록:', userRooms);
            
            if (!userRooms['room_4_5']) {
                console.warn('⚠️ 사용자 4가 room_4_5에 등록되지 않음');
            }
        });
        
    } catch (error) {
        console.error('❌ Firebase 초기화 실패:', error);
        document.getElementById('result').innerHTML = '<p style="color: red;">❌ Firebase 초기화 실패: ' + error.message + '</p>';
    }
} else {
    console.error('❌ Firebase SDK가 로드되지 않음');
    document.getElementById('result').innerHTML = '<p style="color: red;">❌ Firebase SDK가 로드되지 않았습니다.</p>';
}
</script>

<!-- Firebase SDK 로드 -->
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>

<div id="result">
    <p>🔄 Firebase 연결 및 채팅방 데이터 확인 중...</p>
</div>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2, h3 { color: #333; }
pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>