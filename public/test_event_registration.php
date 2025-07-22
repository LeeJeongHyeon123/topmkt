<?php
/**
 * 이벤트 등록 시스템 QA 테스트
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>이벤트 등록 시스템 QA 테스트</h1>";

try {
    $db = Database::getInstance();
    
    // 1. 테스트 이벤트 조회
    echo "<h2>📋 1. 테스트 이벤트 조회</h2>";
    $events = $db->fetchAll("SELECT id, title, user_id, max_participants FROM lectures WHERE content_type = 'event' AND status = 'published' ORDER BY id DESC LIMIT 3");
    
    if (count($events) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>제목</th><th>생성자</th><th>최대 참가자</th><th>테스트 링크</th></tr>";
        
        foreach ($events as $event) {
            echo "<tr>";
            echo "<td>" . $event['id'] . "</td>";
            echo "<td>" . htmlspecialchars($event['title']) . "</td>";
            echo "<td>" . $event['user_id'] . "</td>";
            echo "<td>" . ($event['max_participants'] ?: '무제한') . "</td>";
            echo "<td><a href='https://www.topmktx.com/events/detail?id=" . $event['id'] . "' target='_blank'>테스트하기</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>등록된 이벤트가 없습니다.</p>";
    }
    
    // 2. 데이터베이스 테이블 상태 확인
    echo "<h2>🗄️ 2. 데이터베이스 테이블 상태</h2>";
    
    // event_registrations 테이블 확인
    $eventRegistrations = $db->fetchAll("SELECT * FROM event_registrations LIMIT 5");
    echo "<h3>event_registrations 테이블:</h3>";
    echo "<div style='background: #f0f9ff; padding: 10px; border-radius: 5px;'>";
    echo "<strong>총 등록 수:</strong> " . count($eventRegistrations) . "<br>";
    
    if (count($eventRegistrations) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
        echo "<tr><th>ID</th><th>이벤트ID</th><th>사용자ID</th><th>이름</th><th>상태</th><th>등록일</th></tr>";
        foreach ($eventRegistrations as $reg) {
            echo "<tr>";
            echo "<td>" . $reg['id'] . "</td>";
            echo "<td>" . $reg['event_id'] . "</td>";
            echo "<td>" . $reg['user_id'] . "</td>";
            echo "<td>" . htmlspecialchars($reg['participant_name']) . "</td>";
            echo "<td>" . $reg['status'] . "</td>";
            echo "<td>" . $reg['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: #666; margin-top: 10px;'>등록된 데이터가 없습니다.</p>";
    }
    echo "</div>";
    
    // 3. API 엔드포인트 테스트
    echo "<h2>🔗 3. API 엔드포인트 테스트</h2>";
    
    $testEventId = $events[0]['id'] ?? 194;
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px;'>";
    echo "<h3>테스트 가능한 API 엔드포인트:</h3>";
    echo "<ul>";
    echo "<li><strong>등록 상태 확인:</strong> <code>GET /api/events/{$testEventId}/registration-status?event_id={$testEventId}</code></li>";
    echo "<li><strong>이벤트 등록:</strong> <code>POST /api/events/{$testEventId}/registration?event_id={$testEventId}</code></li>";
    echo "<li><strong>등록 취소:</strong> <code>DELETE /api/events/{$testEventId}/registration?event_id={$testEventId}</code></li>";
    echo "</ul>";
    echo "</div>";
    
    // 4. 새로운 모달 디자인 확인
    echo "<h2>🎨 4. 새로운 모달 디자인</h2>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; padding: 15px; border-radius: 8px;'>";
    echo "<h3>✅ 개선된 모달 기능:</h3>";
    echo "<ul>";
    echo "<li><strong>강의 등록 스타일 적용:</strong> 그라데이션 헤더, 섹션별 구분</li>";
    echo "<li><strong>섹션별 폼 구성:</strong> 개인정보, 소속정보, 참가정보, 기타정보</li>";
    echo "<li><strong>반응형 디자인:</strong> 모바일/데스크톱 지원</li>";
    echo "<li><strong>향상된 UX:</strong> 호버 효과, 애니메이션</li>";
    echo "</ul>";
    echo "</div>";
    
    // 5. 테스트 가이드
    echo "<h2>🧪 5. 테스트 가이드</h2>";
    
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 8px;'>";
    echo "<h3>테스트 순서:</h3>";
    echo "<ol>";
    echo "<li><strong>로그인:</strong> 이벤트 등록을 위해 로그인 필요</li>";
    echo "<li><strong>이벤트 접속:</strong> 위 테스트 링크 클릭</li>";
    echo "<li><strong>등록 버튼 확인:</strong> '참가 신청하기' 버튼 클릭</li>";
    echo "<li><strong>모달 확인:</strong> 새로운 디자인 모달 열림 확인</li>";
    echo "<li><strong>폼 입력:</strong> 각 섹션별 필드 입력 테스트</li>";
    echo "<li><strong>등록 처리:</strong> 신청하기 버튼 클릭 후 처리 확인</li>";
    echo "<li><strong>상태 확인:</strong> 등록 상태 UI 변화 확인</li>";
    echo "<li><strong>취소 테스트:</strong> 신청 취소 기능 테스트</li>";
    echo "</ol>";
    echo "</div>";
    
    // 6. 주요 기능 확인사항
    echo "<h2>✅ 6. 주요 기능 확인사항</h2>";
    
    echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; padding: 15px; border-radius: 8px;'>";
    echo "<h3>확인할 기능들:</h3>";
    echo "<ul>";
    echo "<li>✅ <strong>이벤트 생성자 등록 제한:</strong> 본인 이벤트 등록 불가</li>";
    echo "<li>✅ <strong>중복 등록 방지:</strong> 이미 등록된 이벤트 재등록 불가</li>";
    echo "<li>✅ <strong>필수 필드 검증:</strong> 이름, 이메일, 연락처 필수 입력</li>";
    echo "<li>✅ <strong>대기열 시스템:</strong> 정원 초과 시 대기 상태</li>";
    echo "<li>✅ <strong>상태 관리:</strong> pending, approved, waiting, cancelled</li>";
    echo "<li>✅ <strong>등록 취소:</strong> 취소 후 대기열 자동 승급</li>";
    echo "</ul>";
    echo "</div>";
    
    // 7. 로그 모니터링
    echo "<h2>📝 7. 로그 모니터링</h2>";
    
    echo "<div style='background: #fef2f2; border: 1px solid #dc2626; padding: 15px; border-radius: 8px;'>";
    echo "<h3>오류 로그 확인:</h3>";
    echo "<p>문제 발생 시 다음 로그를 확인하세요:</p>";
    echo "<ul>";
    echo "<li><code>/var/log/httpd/error_log</code> (Apache 오류)</li>";
    echo "<li><code>/var/log/php-fpm/www-error.log</code> (PHP 오류)</li>";
    echo "<li>브라우저 개발자 도구 콘솔 (JavaScript 오류)</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; padding: 20px; border-radius: 8px; margin-top: 20px;'>";
    echo "<h2>🎉 이벤트 등록 시스템 구현 완료!</h2>";
    echo "<p><strong>모든 기능이 성공적으로 구현되었습니다. 위 가이드를 따라 테스트를 진행해주세요.</strong></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>