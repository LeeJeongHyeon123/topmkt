<?php
/**
 * 이미지 보존 테스트
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>이미지 보존 테스트</h1>";

// 테스트용 로그인 상태 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['is_logged_in'] = true;

try {
    $db = Database::getInstance();
    $eventId = 189;
    
    // 1. 현재 상태 확인
    echo "<h2>📊 현재 상태 확인</h2>";
    
    // 행사 기본 정보
    $event = $db->fetch("SELECT * FROM lectures WHERE id = ? AND content_type = 'event'", [$eventId]);
    echo "<h3>행사 정보</h3>";
    echo "<p><strong>제목:</strong> " . htmlspecialchars($event['title']) . "</p>";
    echo "<p><strong>설명 길이:</strong> " . strlen($event['description']) . " 문자</p>";
    
    // 행사 이미지 확인
    $eventImages = $db->fetchAll("SELECT * FROM event_images WHERE event_id = ? ORDER BY id", [$eventId]);
    echo "<h3>행사 이미지</h3>";
    echo "<p>총 " . count($eventImages) . "개</p>";
    
    if (count($eventImages) > 0) {
        echo "<ul>";
        foreach ($eventImages as $image) {
            $exists = file_exists(ROOT_PATH . '/public' . $image['image_path']);
            echo "<li>ID: " . $image['id'] . " - " . htmlspecialchars($image['image_path']) . " (" . ($exists ? "✅ 존재" : "❌ 없음") . ")</li>";
        }
        echo "</ul>";
    }
    
    // 강사 정보 확인
    $instructors = $db->fetchAll("SELECT * FROM lecture_instructors WHERE lecture_id = ? ORDER BY id", [$eventId]);
    echo "<h3>강사 정보</h3>";
    echo "<p>총 " . count($instructors) . "명</p>";
    
    if (count($instructors) > 0) {
        echo "<ul>";
        foreach ($instructors as $instructor) {
            $imagePath = $instructor['instructor_image'];
            $exists = $imagePath && file_exists(ROOT_PATH . '/public' . $imagePath);
            echo "<li>";
            echo "<strong>" . htmlspecialchars($instructor['instructor_name']) . "</strong>";
            if ($imagePath) {
                echo " - 이미지: " . htmlspecialchars($imagePath) . " (" . ($exists ? "✅ 존재" : "❌ 없음") . ")";
            } else {
                echo " - 이미지: 없음";
            }
            echo "</li>";
        }
        echo "</ul>";
    }
    
    // 2. 수정된 코드 분석
    echo "<h2>🔧 수정된 코드 분석</h2>";
    
    echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>주요 수정사항:</h3>";
    echo "<ul>";
    echo "<li><strong>이벤트 이미지:</strong> 무조건 삭제 → 삭제 요청된 이미지만 삭제</li>";
    echo "<li><strong>강사 정보:</strong> 무조건 삭제 → 새 강사 정보가 있을 때만 삭제</li>";
    echo "<li><strong>기존 이미지:</strong> 기존 이미지 유지 로직 추가</li>";
    echo "<li><strong>조건부 처리:</strong> 파일 업로드 없이도 삭제 요청 처리</li>";
    echo "</ul>";
    echo "</div>";
    
    // 3. 테스트 시나리오
    echo "<h2>🧪 테스트 시나리오</h2>";
    
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>⚠️ 주의사항</h3>";
    echo "<p>실제 수정 테스트를 진행하기 전에 다음 사항을 확인하세요:</p>";
    echo "<ul>";
    echo "<li>데이터베이스 백업 완료 여부</li>";
    echo "<li>이미지 파일 백업 완료 여부</li>";
    echo "<li>테스트 환경에서 우선 검증</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>✅ 예상 개선사항</h3>";
    echo "<ul>";
    echo "<li>기존 이미지 보존: 수정 시 기존 이미지들이 유지됨</li>";
    echo "<li>선택적 삭제: 사용자가 삭제 버튼을 클릭한 이미지만 삭제</li>";
    echo "<li>강사 정보 보존: 강사 정보 변경 없이 다른 필드만 수정 시 강사 정보 유지</li>";
    echo "<li>안전한 업데이트: 불필요한 데이터 삭제 방지</li>";
    echo "</ul>";
    echo "</div>";
    
    // 4. 코드 변경 요약
    echo "<h2>📝 코드 변경 요약</h2>";
    
    echo "<h3>1. processUploadedEventImages() 수정</h3>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
    echo htmlspecialchars("// 기존: 무조건 모든 이미지 삭제
// DELETE FROM event_images WHERE event_id = ?

// 수정: 삭제 요청된 이미지만 삭제
if (!empty(\$_POST['remove_images'])) {
    \$removeIds = \$_POST['remove_images'];
    \$placeholders = str_repeat('?,', count(\$removeIds) - 1) . '?';
    DELETE FROM event_images WHERE id IN (\$placeholders)
}");
    echo "</pre>";
    
    echo "<h3>2. updateMultipleInstructors() 수정</h3>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
    echo htmlspecialchars("// 기존: 무조건 모든 강사 정보 삭제
// DELETE FROM lecture_instructors WHERE lecture_id = ?

// 수정: 새 강사 정보가 있을 때만 삭제
if (!empty(\$postData['instructor_names']) && is_array(\$postData['instructor_names'])) {
    DELETE FROM lecture_instructors WHERE lecture_id = ?
    // 새 강사 정보 저장
} else {
    // 기존 강사 정보 유지
}");
    echo "</pre>";
    
    echo "<h3>3. update() 메소드 수정</h3>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
    echo htmlspecialchars("// 기존: 파일 업로드가 있을 때만 처리
if (!empty(\$_FILES['event_images'])) {
    \$this->processUploadedEventImages(\$eventId, \$_FILES['event_images']);
}

// 수정: 파일 업로드 또는 삭제 요청이 있을 때 처리
if (!empty(\$_FILES['event_images']) || !empty(\$_POST['remove_images'])) {
    \$this->processUploadedEventImages(\$eventId, \$_FILES['event_images'] ?? []);
}");
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>