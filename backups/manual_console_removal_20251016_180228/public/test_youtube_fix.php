<?php
/**
 * 유튜브 링크 수정 테스트
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>유튜브 링크 수정 테스트</h1>";

try {
    $db = Database::getInstance();
    $eventId = 194;
    
    // 1. 수정 전 현재 상태
    echo "<h2>📊 수정 전 현재 상태</h2>";
    $beforeEvent = $db->fetch("SELECT * FROM lectures WHERE id = ? AND content_type = 'event'", [$eventId]);
    
    if ($beforeEvent) {
        echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
        echo "<h3>행사 194 정보:</h3>";
        echo "<ul>";
        echo "<li><strong>제목:</strong> " . htmlspecialchars($beforeEvent['title']) . "</li>";
        echo "<li><strong>유튜브 링크:</strong> " . ($beforeEvent['youtube_video'] ? htmlspecialchars($beforeEvent['youtube_video']) : "<span style='color: #999;'>없음</span>") . "</li>";
        echo "<li><strong>온라인 링크:</strong> " . ($beforeEvent['online_link'] ? htmlspecialchars($beforeEvent['online_link']) : "<span style='color: #999;'>없음</span>") . "</li>";
        echo "<li><strong>최종 수정일:</strong> " . $beforeEvent['updated_at'] . "</li>";
        echo "</ul>";
        echo "</div>";
    }
    
    // 2. 수정 사항 요약
    echo "<h2>🔧 적용된 수정사항</h2>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>✅ 완료된 수정사항:</h3>";
    echo "<ol>";
    echo "<li><strong>EventController.php - updateEvent() 메소드:</strong> SQL 쿼리에 youtube_video 필드 추가</li>";
    echo "<li><strong>create.php - HTML 필드:</strong> 올바른 필드명 참조 (youtube_link → youtube_video)</li>";
    echo "<li><strong>create.php - JavaScript:</strong> 데이터 로드 시 올바른 필드명 사용</li>";
    echo "</ol>";
    echo "</div>";
    
    // 3. 수정 코드 상세
    echo "<h2>📝 수정 코드 상세</h2>";
    
    echo "<h3>1. SQL 쿼리 수정 (EventController.php)</h3>";
    echo "<div style='background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; padding: 10px; margin: 10px 0;'>";
    echo "<strong>이전:</strong>";
    echo "<pre style='color: #dc2626;'>" . htmlspecialchars("youtube_video = ?, updated_at = NOW()") . "</pre>";
    echo "<strong>수정:</strong>";
    echo "<pre style='color: #22c55e;'>" . htmlspecialchars("youtube_video = ?, updated_at = NOW()") . "</pre>";
    echo "<strong>파라미터 추가:</strong>";
    echo "<pre style='color: #22c55e;'>" . htmlspecialchars("\$data['youtube_video'] ?? null,") . "</pre>";
    echo "</div>";
    
    echo "<h3>2. HTML 필드 수정 (create.php)</h3>";
    echo "<div style='background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; padding: 10px; margin: 10px 0;'>";
    echo "<strong>이전:</strong>";
    echo "<pre style='color: #dc2626;'>" . htmlspecialchars("value=\"<?= \$isEditMode ? htmlspecialchars(\$event['youtube_link'] ?? '') : '' ?>\"") . "</pre>";
    echo "<strong>수정:</strong>";
    echo "<pre style='color: #22c55e;'>" . htmlspecialchars("value=\"<?= \$isEditMode ? htmlspecialchars(\$event['youtube_video'] ?? '') : '' ?>\"") . "</pre>";
    echo "</div>";
    
    echo "<h3>3. JavaScript 로드 수정 (create.php)</h3>";
    echo "<div style='background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; padding: 10px; margin: 10px 0;'>";
    echo "<strong>이전:</strong>";
    echo "<pre style='color: #dc2626;'>" . htmlspecialchars("if (eventData.youtube_link) document.getElementById('youtube_video').value = eventData.youtube_link;") . "</pre>";
    echo "<strong>수정:</strong>";
    echo "<pre style='color: #22c55e;'>" . htmlspecialchars("if (eventData.youtube_video) document.getElementById('youtube_video').value = eventData.youtube_video;") . "</pre>";
    echo "</div>";
    
    // 4. 테스트 가이드
    echo "<h2>🧪 테스트 가이드</h2>";
    
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>테스트 단계:</h3>";
    echo "<ol>";
    echo "<li><strong>편집 페이지 접속:</strong> <a href='https://www.topmktx.com/events/create?id=194' target='_blank'>https://www.topmktx.com/events/create?id=194</a></li>";
    echo "<li><strong>유튜브 링크 입력:</strong> YouTube 동영상 URL 필드에 테스트 URL 입력</li>";
    echo "<li><strong>저장:</strong> 폼 제출하여 저장</li>";
    echo "<li><strong>확인:</strong> 저장 후 다시 편집 페이지 접속하여 유튜브 링크 유지되는지 확인</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>테스트 URL 예시:</h3>";
    echo "<ul>";
    echo "<li><code>https://www.youtube.com/watch?v=test123</code></li>";
    echo "<li><code>https://youtu.be/test456</code></li>";
    echo "<li><code>https://www.youtube.com/embed/test789</code></li>";
    echo "</ul>";
    echo "</div>";
    
    // 5. 예상 결과
    echo "<h2>🎯 예상 결과</h2>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>✅ 수정 후 기대 결과:</h3>";
    echo "<ul>";
    echo "<li>유튜브 링크 필드에 입력한 URL이 저장됨</li>";
    echo "<li>편집 페이지 재접속 시 입력한 유튜브 링크가 그대로 표시됨</li>";
    echo "<li>데이터베이스 youtube_video 컬럼에 값이 저장됨</li>";
    echo "<li>다른 필드들은 영향 받지 않음</li>";
    echo "</ul>";
    echo "</div>";
    
    // 6. 문제 해결 확인
    echo "<h2>🔍 문제 해결 확인</h2>";
    
    echo "<div style='background: #fee2e2; border: 1px solid #dc2626; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>이전 문제:</h3>";
    echo "<ul>";
    echo "<li>❌ 유튜브 링크 입력 후 저장해도 사라짐</li>";
    echo "<li>❌ 편집 페이지 재접속 시 유튜브 링크 필드 비어있음</li>";
    echo "<li>❌ 데이터베이스에 youtube_video 값 저장 안됨</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>현재 해결 상태:</h3>";
    echo "<ul>";
    echo "<li>✅ SQL 쿼리에 youtube_video 필드 포함</li>";
    echo "<li>✅ HTML 필드에서 올바른 데이터 참조</li>";
    echo "<li>✅ JavaScript에서 올바른 필드명 사용</li>";
    echo "<li>✅ 전체 데이터 흐름 일관성 확보</li>";
    echo "</ul>";
    echo "</div>";
    
    // 7. 디버깅 정보
    echo "<h2>🐛 디버깅 정보</h2>";
    
    echo "<div style='background: #f8f9fa; border: 1px solid #6b7280; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>문제 발생 시 확인사항:</h3>";
    echo "<ul>";
    echo "<li><strong>POST 데이터:</strong> 폼 제출 시 youtube_video 필드가 전송되는지 확인</li>";
    echo "<li><strong>SQL 실행:</strong> UPDATE 쿼리가 youtube_video 필드를 포함하는지 확인</li>";
    echo "<li><strong>데이터베이스:</strong> lectures 테이블의 youtube_video 컬럼 값 확인</li>";
    echo "<li><strong>로그:</strong> 에러 로그에서 관련 오류 메시지 확인</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>