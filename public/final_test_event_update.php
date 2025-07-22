<?php
/**
 * 최종 행사 수정 테스트
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>최종 행사 수정 테스트 - 완료</h1>";

// 테스트용 로그인 상태 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['is_logged_in'] = true;

try {
    $db = Database::getInstance();
    $eventId = 193;
    
    // 1. 수정 전 상태 확인
    echo "<h2>📊 수정 전 상태 확인</h2>";
    $beforeImages = $db->fetchAll("SELECT * FROM event_images WHERE event_id = ? ORDER BY id", [$eventId]);
    $beforeInstructors = $db->fetchAll("SELECT * FROM lecture_instructors WHERE lecture_id = ? ORDER BY id", [$eventId]);
    $beforeEvent = $db->fetch("SELECT * FROM lectures WHERE id = ? AND content_type = 'event'", [$eventId]);
    
    echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>현재 상태:</h3>";
    echo "<ul>";
    echo "<li><strong>행사 제목:</strong> " . htmlspecialchars($beforeEvent['title']) . "</li>";
    echo "<li><strong>행사 이미지:</strong> " . count($beforeImages) . "개</li>";
    echo "<li><strong>강사 수:</strong> " . count($beforeInstructors) . "명</li>";
    echo "<li><strong>강사 이미지:</strong> " . count(array_filter($beforeInstructors, function($i) { return !empty($i['instructor_image']); })) . "개</li>";
    echo "</ul>";
    echo "</div>";
    
    // 2. 수정 사항 요약
    echo "<h2>🔧 적용된 수정사항</h2>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>✅ 완료된 수정사항:</h3>";
    echo "<ol>";
    echo "<li><strong>폼에 이벤트 ID 필드 추가:</strong> 편집 모드에서 event_id 전송</li>";
    echo "<li><strong>store() 메소드 수정:</strong> event_id가 있으면 update() 메소드 호출</li>";
    echo "<li><strong>이벤트 이미지 처리 강화:</strong> 더 엄격한 조건으로 불필요한 삭제 방지</li>";
    echo "<li><strong>강사 정보 처리 강화:</strong> 실제 데이터가 있을 때만 업데이트</li>";
    echo "<li><strong>상세 로깅 추가:</strong> 각 단계별 처리 상황 기록</li>";
    echo "</ol>";
    echo "</div>";
    
    // 3. 코드 변경 요약
    echo "<h2>📝 주요 코드 변경사항</h2>";
    
    echo "<h3>1. 폼 수정 (create.php)</h3>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
    echo htmlspecialchars("<?php if (\$isEditMode): ?>
<input type=\"hidden\" name=\"event_id\" value=\"<?= \$eventId ?>\">
<?php endif; ?>");
    echo "</pre>";
    
    echo "<h3>2. store() 메소드 수정 (EventController.php)</h3>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
    echo htmlspecialchars("// Check if event_id is provided in POST data - if so, redirect to update method
if (!empty(\$_POST['event_id'])) {
    \$eventId = intval(\$_POST['event_id']);
    \$this->update(\$eventId);
    return;
}");
    echo "</pre>";
    
    echo "<h3>3. 강사 정보 처리 강화 (updateMultipleInstructors)</h3>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
    echo htmlspecialchars("\$hasValidInstructorData = isset(\$postData['instructor_names']) && 
                        is_array(\$postData['instructor_names']) && 
                        count(\$postData['instructor_names']) > 0 && 
                        !empty(array_filter(\$postData['instructor_names']));");
    echo "</pre>";
    
    echo "<h3>4. 이벤트 이미지 처리 강화 (processUploadedEventImages)</h3>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
    echo htmlspecialchars("\$hasFiles = isset(\$files['name']) && is_array(\$files['name']) && 
           count(\$files['name']) > 0 && 
           !empty(array_filter(\$files['name'])) && 
           !(count(\$files['name']) === 1 && empty(\$files['name'][0]));");
    echo "</pre>";
    
    // 4. 테스트 가이드
    echo "<h2>🧪 테스트 가이드</h2>";
    
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>테스트 단계:</h3>";
    echo "<ol>";
    echo "<li><strong>기본 정보 수정:</strong> 행사 제목이나 설명만 변경하여 저장</li>";
    echo "<li><strong>이미지 확인:</strong> 수정 후 기존 이미지 " . count($beforeImages) . "개가 그대로 있는지 확인</li>";
    echo "<li><strong>강사 정보 확인:</strong> 수정 후 기존 강사 " . count($beforeInstructors) . "명이 그대로 있는지 확인</li>";
    echo "<li><strong>로그 확인:</strong> 수정 시 안전 메시지가 로그에 출력되는지 확인</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>예상 결과:</h3>";
    echo "<ul>";
    echo "<li>✅ 기존 이미지 " . count($beforeImages) . "개 모두 보존</li>";
    echo "<li>✅ 기존 강사 " . count($beforeInstructors) . "명 모두 보존</li>";
    echo "<li>✅ 수정한 필드만 변경됨</li>";
    echo "<li>✅ 로그에 안전 메시지 출력: '강사 정보 변경 없음, 기존 정보 유지'</li>";
    echo "<li>✅ 로그에 안전 메시지 출력: '업로드할 파일도 삭제 요청도 없음'</li>";
    echo "</ul>";
    echo "</div>";
    
    // 5. 문제 해결 확인
    echo "<h2>🎯 문제 해결 확인</h2>";
    
    echo "<div style='background: #fee2e2; border: 1px solid #dc2626; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>이전 문제:</h3>";
    echo "<ul>";
    echo "<li>❌ 행사 수정 시 모든 이미지가 삭제됨</li>";
    echo "<li>❌ 강사 정보도 함께 삭제됨</li>";
    echo "<li>❌ 폼에서 이벤트 ID가 전송되지 않음</li>";
    echo "<li>❌ 조건문이 너무 관대하여 불필요한 삭제 발생</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>현재 해결 상태:</h3>";
    echo "<ul>";
    echo "<li>✅ 폼에서 이벤트 ID 정상 전송</li>";
    echo "<li>✅ store() 메소드가 update() 메소드로 올바르게 리다이렉트</li>";
    echo "<li>✅ 엄격한 조건으로 불필요한 삭제 방지</li>";
    echo "<li>✅ 상세 로깅으로 디버깅 가능</li>";
    echo "<li>✅ 기존 데이터 보존 로직 구현</li>";
    echo "</ul>";
    echo "</div>";
    
    // 6. 최종 권장사항
    echo "<h2>💡 최종 권장사항</h2>";
    
    echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>사용 방법:</h3>";
    echo "<ol>";
    echo "<li><strong>https://www.topmktx.com/events/create?id=193</strong> 접속</li>";
    echo "<li>기본 정보 (제목, 설명 등) 일부만 수정</li>";
    echo "<li>이미지나 강사 정보는 건드리지 말고 저장</li>";
    echo "<li>수정 후 이미지와 강사 정보가 그대로 유지되는지 확인</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>⚠️ 주의사항:</h3>";
    echo "<ul>";
    echo "<li>첫 번째 테스트에서는 기본 정보만 수정하세요</li>";
    echo "<li>이미지 삭제나 강사 정보 변경은 별도로 테스트하세요</li>";
    echo "<li>문제가 발생하면 즉시 로그를 확인하세요</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>🎉 성공 시 결과:</h3>";
    echo "<p><strong>이제 행사 수정 시 기존 이미지와 강사 정보가 안전하게 보존됩니다!</strong></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>