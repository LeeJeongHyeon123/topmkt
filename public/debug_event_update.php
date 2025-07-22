<?php
/**
 * 행사 수정 디버그 테스트
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>행사 수정 디버그 테스트</h1>";

// 테스트용 로그인 상태 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['is_logged_in'] = true;

try {
    $db = Database::getInstance();
    $eventId = 193;
    
    // 1. 수정 전 상태 기록
    echo "<h2>📊 수정 전 상태</h2>";
    $beforeImages = $db->fetchAll("SELECT * FROM event_images WHERE event_id = ? ORDER BY id", [$eventId]);
    $beforeInstructors = $db->fetchAll("SELECT * FROM lecture_instructors WHERE lecture_id = ? ORDER BY id", [$eventId]);
    
    echo "<p>수정 전 이미지 수: " . count($beforeImages) . "개</p>";
    echo "<p>수정 전 강사 수: " . count($beforeInstructors) . "명</p>";
    
    // 2. 수정 요청 시뮬레이션 (실제 수정은 하지 않고 로직만 테스트)
    echo "<h2>🔍 수정 로직 시뮬레이션</h2>";
    
    // 가상의 POST 데이터 생성 (실제 수정 시 받는 데이터)
    $simulatedPost = [
        'title' => '수정된 행사 제목',
        'description' => '수정된 설명',
        'start_date' => '2025-07-15',
        'end_date' => '2025-07-19',
        'location_type' => 'online',
        'online_link' => 'https://example.com/meeting',
        'max_participants' => 150,
        'registration_fee' => 5000,
        'instructor_names' => [], // 빈 배열 - 강사 정보 변경 없음
        'instructor_infos' => []
    ];
    
    $simulatedFiles = [
        'event_images' => [
            'name' => [''], // 빈 이름 - 새 이미지 업로드 없음
            'error' => [UPLOAD_ERR_NO_FILE]
        ],
        'instructor_images' => [
            'name' => [''], // 빈 이름 - 새 강사 이미지 업로드 없음
            'error' => [UPLOAD_ERR_NO_FILE]
        ]
    ];
    
    // 3. 각 단계별 로직 테스트
    echo "<h3>Step 1: 이벤트 이미지 처리 로직</h3>";
    
    // 이벤트 이미지 처리 조건 확인
    $hasEventFiles = !empty($simulatedFiles['event_images']['name']) && 
                     !(count($simulatedFiles['event_images']['name']) === 1 && 
                       empty($simulatedFiles['event_images']['name'][0]));
    $hasRemoveRequest = !empty($simulatedPost['remove_images']);
    
    echo "<p>새 이미지 파일 있음: " . ($hasEventFiles ? "✅ 있음" : "❌ 없음") . "</p>";
    echo "<p>이미지 삭제 요청 있음: " . ($hasRemoveRequest ? "✅ 있음" : "❌ 없음") . "</p>";
    
    $shouldProcessEventImages = $hasEventFiles || $hasRemoveRequest;
    echo "<p><strong>이벤트 이미지 처리 여부: " . ($shouldProcessEventImages ? "🔄 처리함" : "⏸️ 처리 안함") . "</strong></p>";
    
    if ($shouldProcessEventImages) {
        echo "<div style='background: #fee2e2; border: 1px solid #dc2626; border-radius: 6px; padding: 10px; margin: 10px 0;'>";
        echo "<strong>⚠️ 경고:</strong> 이벤트 이미지 처리가 실행됩니다!";
        echo "</div>";
    } else {
        echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 10px; margin: 10px 0;'>";
        echo "<strong>✅ 안전:</strong> 이벤트 이미지 처리가 실행되지 않습니다.";
        echo "</div>";
    }
    
    echo "<h3>Step 2: 강사 정보 처리 로직</h3>";
    
    // 강사 정보 처리 조건 확인
    $hasInstructorData = !empty($simulatedPost['instructor_names']) && 
                         is_array($simulatedPost['instructor_names']);
    $hasInstructorFiles = !empty($simulatedFiles['instructor_images']['name']) && 
                          !(count($simulatedFiles['instructor_images']['name']) === 1 && 
                            empty($simulatedFiles['instructor_images']['name'][0]));
    
    echo "<p>새 강사 정보 있음: " . ($hasInstructorData ? "✅ 있음" : "❌ 없음") . "</p>";
    echo "<p>새 강사 이미지 파일 있음: " . ($hasInstructorFiles ? "✅ 있음" : "❌ 없음") . "</p>";
    
    $shouldProcessInstructors = $hasInstructorData;
    $shouldProcessInstructorImages = $hasInstructorFiles;
    
    echo "<p><strong>강사 정보 처리 여부: " . ($shouldProcessInstructors ? "🔄 처리함" : "⏸️ 처리 안함") . "</strong></p>";
    echo "<p><strong>강사 이미지 처리 여부: " . ($shouldProcessInstructorImages ? "🔄 처리함" : "⏸️ 처리 안함") . "</strong></p>";
    
    if ($shouldProcessInstructors) {
        echo "<div style='background: #fee2e2; border: 1px solid #dc2626; border-radius: 6px; padding: 10px; margin: 10px 0;'>";
        echo "<strong>⚠️ 경고:</strong> 강사 정보 처리가 실행됩니다! 기존 강사 정보가 삭제될 수 있습니다.";
        echo "</div>";
    } else {
        echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 10px; margin: 10px 0;'>";
        echo "<strong>✅ 안전:</strong> 강사 정보 처리가 실행되지 않습니다.";
        echo "</div>";
    }
    
    // 4. 실제 현재 코드 동작 분석
    echo "<h2>🔧 현재 코드 동작 분석</h2>";
    
    // EventController 파일 읽기
    $eventControllerPath = SRC_PATH . '/controllers/EventController.php';
    if (file_exists($eventControllerPath)) {
        $controllerCode = file_get_contents($eventControllerPath);
        
        // 중요한 부분 추출
        echo "<h3>update() 메소드 확인</h3>";
        
        // 이벤트 이미지 처리 조건 추출
        preg_match('/if\s*\([^)]*event_images[^)]*\)\s*{[^}]*processUploadedEventImages/', $controllerCode, $eventImageCondition);
        if ($eventImageCondition) {
            echo "<div style='background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; padding: 10px; margin: 10px 0;'>";
            echo "<strong>이벤트 이미지 처리 조건:</strong>";
            echo "<pre style='margin: 5px 0;'>" . htmlspecialchars($eventImageCondition[0]) . "</pre>";
            echo "</div>";
        }
        
        // 강사 이미지 처리 조건 추출
        preg_match('/if\s*\([^)]*instructor_images[^)]*\)\s*{[^}]*processInstructorImages/', $controllerCode, $instructorImageCondition);
        if ($instructorImageCondition) {
            echo "<div style='background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; padding: 10px; margin: 10px 0;'>";
            echo "<strong>강사 이미지 처리 조건:</strong>";
            echo "<pre style='margin: 5px 0;'>" . htmlspecialchars($instructorImageCondition[0]) . "</pre>";
            echo "</div>";
        }
        
        // updateMultipleInstructors 메소드 확인
        preg_match('/updateMultipleInstructors[^}]*}/', $controllerCode, $updateInstructorsMethod);
        if ($updateInstructorsMethod) {
            echo "<div style='background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; padding: 10px; margin: 10px 0;'>";
            echo "<strong>updateMultipleInstructors 메소드:</strong>";
            echo "<pre style='margin: 5px 0; max-height: 200px; overflow-y: auto;'>" . htmlspecialchars(substr($updateInstructorsMethod[0], 0, 500)) . "...</pre>";
            echo "</div>";
        }
    }
    
    // 5. 권장사항
    echo "<h2>💡 권장사항</h2>";
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>문제 가능성:</h3>";
    echo "<ul>";
    echo "<li>강사 정보 처리 조건이 너무 넓어서 빈 배열도 처리할 수 있음</li>";
    echo "<li>파일 업로드 체크 로직이 예상과 다르게 동작할 수 있음</li>";
    echo "<li>폼 제출 시 빈 필드들이 예상과 다른 형태로 전송될 수 있음</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>테스트 방법:</h3>";
    echo "<ul>";
    echo "<li>실제 수정 폼에서 어떤 데이터가 전송되는지 확인</li>";
    echo "<li>각 조건문이 예상대로 동작하는지 로그 확인</li>";
    echo "<li>단계별로 어떤 메소드가 호출되는지 추적</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>