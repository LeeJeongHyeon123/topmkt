<?php
/**
 * 행사 수정 안전성 테스트
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>행사 수정 안전성 테스트</h1>";

// 테스트용 로그인 상태 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['is_logged_in'] = true;

try {
    $db = Database::getInstance();
    $eventId = 193;
    
    // 1. 현재 상태 확인
    echo "<h2>📊 현재 상태</h2>";
    $currentImages = $db->fetchAll("SELECT * FROM event_images WHERE event_id = ? ORDER BY id", [$eventId]);
    $currentInstructors = $db->fetchAll("SELECT * FROM lecture_instructors WHERE lecture_id = ? ORDER BY id", [$eventId]);
    
    echo "<p>현재 이미지 수: " . count($currentImages) . "개</p>";
    echo "<p>현재 강사 수: " . count($currentInstructors) . "명</p>";
    
    // 2. 수정된 조건 로직 테스트
    echo "<h2>🔧 수정된 조건 로직 테스트</h2>";
    
    // 다양한 시나리오 테스트
    $testScenarios = [
        "시나리오 1: 완전 빈 데이터" => [
            'instructor_names' => [],
            'instructor_infos' => [],
            'event_images' => ['name' => [''], 'error' => [UPLOAD_ERR_NO_FILE]],
            'remove_images' => []
        ],
        "시나리오 2: 빈 문자열 배열" => [
            'instructor_names' => [''],
            'instructor_infos' => [''],
            'event_images' => ['name' => [''], 'error' => [UPLOAD_ERR_NO_FILE]],
            'remove_images' => []
        ],
        "시나리오 3: null 값들" => [
            'instructor_names' => [null, null],
            'instructor_infos' => [null, null],
            'event_images' => ['name' => [''], 'error' => [UPLOAD_ERR_NO_FILE]],
            'remove_images' => []
        ],
        "시나리오 4: 실제 강사 데이터" => [
            'instructor_names' => ['홍길동', '김철수'],
            'instructor_infos' => ['강사1 소개', '강사2 소개'],
            'event_images' => ['name' => [''], 'error' => [UPLOAD_ERR_NO_FILE]],
            'remove_images' => []
        ],
        "시나리오 5: 이미지 삭제 요청" => [
            'instructor_names' => [],
            'instructor_infos' => [],
            'event_images' => ['name' => [''], 'error' => [UPLOAD_ERR_NO_FILE]],
            'remove_images' => ['61', '62']
        ]
    ];
    
    foreach ($testScenarios as $scenarioName => $data) {
        echo "<h3>" . $scenarioName . "</h3>";
        
        // 강사 정보 조건 테스트
        $hasValidInstructorData = isset($data['instructor_names']) && 
                                is_array($data['instructor_names']) && 
                                count($data['instructor_names']) > 0 && 
                                !empty(array_filter($data['instructor_names']));
        
        echo "<p><strong>강사 정보 처리:</strong> " . ($hasValidInstructorData ? "🔄 처리됨" : "⏸️ 건너뜀") . "</p>";
        
        // 이벤트 이미지 조건 테스트
        $files = $data['event_images'];
        $hasFiles = isset($files['name']) && is_array($files['name']) && 
                   count($files['name']) > 0 && 
                   !empty(array_filter($files['name'])) && 
                   !(count($files['name']) === 1 && empty($files['name'][0]));
                   
        $hasRemoveRequest = isset($data['remove_images']) && 
                           is_array($data['remove_images']) && 
                           count($data['remove_images']) > 0;
        
        echo "<p><strong>이벤트 이미지 처리:</strong> " . 
             ($hasFiles || $hasRemoveRequest ? "🔄 처리됨" : "⏸️ 건너뜀") . 
             " (파일: " . ($hasFiles ? "있음" : "없음") . ", 삭제 요청: " . ($hasRemoveRequest ? "있음" : "없음") . ")</p>";
        
        // 안전성 평가
        $isSafe = !$hasValidInstructorData && !$hasFiles && !$hasRemoveRequest;
        echo "<div style='background: " . ($isSafe ? "#f0fdf4; border: 1px solid #22c55e" : "#fef3c7; border: 1px solid #f59e0b") . "; border-radius: 6px; padding: 10px; margin: 10px 0;'>";
        echo "<strong>" . ($isSafe ? "✅ 안전" : "⚠️ 주의") . ":</strong> ";
        echo $isSafe ? "기존 데이터가 보존됩니다." : "일부 데이터가 처리될 수 있습니다.";
        echo "</div>";
        
        // 상세 정보
        if (isset($data['instructor_names'])) {
            echo "<p><small>강사 이름: " . json_encode($data['instructor_names']) . "</small></p>";
        }
        if (isset($data['remove_images'])) {
            echo "<p><small>삭제 요청: " . json_encode($data['remove_images']) . "</small></p>";
        }
        
        echo "<hr>";
    }
    
    // 3. 로그 모니터링 안내
    echo "<h2>📝 로그 모니터링</h2>";
    echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>실제 테스트 시 확인할 로그:</h3>";
    echo "<ul>";
    echo "<li><strong>강사 정보 처리:</strong> 'updateMultipleInstructors: 강사 정보 변경 없음, 기존 정보 유지' 메시지</li>";
    echo "<li><strong>이벤트 이미지 처리:</strong> 'processUploadedEventImages: 업로드할 파일도 삭제 요청도 없음' 메시지</li>";
    echo "<li><strong>안전한 수정:</strong> 두 메시지가 모두 나타나면 기존 데이터가 보존됨</li>";
    echo "</ul>";
    echo "</div>";
    
    // 4. 테스트 권장사항
    echo "<h2>🧪 테스트 권장사항</h2>";
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>안전한 테스트 방법:</h3>";
    echo "<ol>";
    echo "<li><strong>백업 확인:</strong> 데이터베이스와 이미지 파일 백업</li>";
    echo "<li><strong>단계별 테스트:</strong> 제목만 변경하여 수정 테스트</li>";
    echo "<li><strong>로그 확인:</strong> 수정 후 로그 파일에서 위의 메시지 확인</li>";
    echo "<li><strong>데이터 검증:</strong> 수정 전후 이미지 및 강사 수 비교</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>✅ 기대 결과:</h3>";
    echo "<ul>";
    echo "<li>제목, 설명, 날짜 등 기본 정보만 변경됨</li>";
    echo "<li>기존 이미지 " . count($currentImages) . "개 모두 보존됨</li>";
    echo "<li>기존 강사 " . count($currentInstructors) . "명 모두 보존됨</li>";
    echo "<li>로그에 안전 메시지 출력됨</li>";
    echo "</ul>";
    echo "</div>";
    
    // 5. 응급 복구 방법
    echo "<h2>🚨 응급 복구 방법</h2>";
    echo "<div style='background: #fee2e2; border: 1px solid #dc2626; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>만약 데이터가 삭제되었다면:</h3>";
    echo "<ul>";
    echo "<li><strong>즉시 중단:</strong> 추가 수정 작업 중단</li>";
    echo "<li><strong>백업 복원:</strong> 데이터베이스 백업에서 복원</li>";
    echo "<li><strong>로그 분석:</strong> 어떤 조건이 예상과 다르게 동작했는지 분석</li>";
    echo "<li><strong>코드 수정:</strong> 조건문 추가 강화</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>