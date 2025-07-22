<?php
/**
 * 강사 이미지 수정 문제 디버깅
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>강사 이미지 수정 문제 디버깅</h1>";

try {
    $db = Database::getInstance();
    $eventId = 194;
    
    // 1. 현재 강사 정보 확인
    echo "<h2>📋 현재 강사 정보 상태</h2>";
    $instructors = $db->fetchAll("SELECT * FROM lecture_instructors WHERE lecture_id = ? ORDER BY id", [$eventId]);
    
    if (count($instructors) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>강사명</th><th>강사 소개</th><th>이미지 경로</th><th>파일 존재</th><th>파일 크기</th></tr>";
        
        foreach ($instructors as $instructor) {
            $imagePath = $instructor['instructor_image'] ?? '';
            $fullPath = $imagePath ? ROOT_PATH . '/public' . $imagePath : '';
            $exists = $imagePath && file_exists($fullPath);
            $size = $exists ? filesize($fullPath) : 0;
            
            echo "<tr>";
            echo "<td>" . $instructor['id'] . "</td>";
            echo "<td>" . htmlspecialchars($instructor['instructor_name']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($instructor['instructor_info'], 0, 50)) . "...</td>";
            echo "<td>" . ($imagePath ? htmlspecialchars($imagePath) : "<span style='color: #999;'>없음</span>") . "</td>";
            echo "<td>" . ($exists ? "✅" : "❌") . "</td>";
            echo "<td>" . ($exists ? number_format($size) . " bytes" : "N/A") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>강사 정보가 없습니다.</p>";
    }
    
    // 2. 문제 분석
    echo "<h2>🔍 문제 분석</h2>";
    
    echo "<div style='background: #fee2e2; border: 1px solid #dc2626; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>❌ 현재 문제:</h3>";
    echo "<ul>";
    echo "<li>A 강사 이미지와 B 강사 이미지가 모두 등록되어 있음</li>";
    echo "<li>A 강사 이미지만 수정하고 저장</li>";
    echo "<li>결과: A 강사 이미지는 저장되고 B 강사 이미지는 삭제됨</li>";
    echo "</ul>";
    echo "</div>";
    
    // 3. 현재 강사 업데이트 로직 분석
    echo "<h2>🔧 현재 강사 업데이트 로직 분석</h2>";
    
    $controllerPath = SRC_PATH . '/controllers/EventController.php';
    if (file_exists($controllerPath)) {
        $content = file_get_contents($controllerPath);
        
        // updateMultipleInstructors 메소드 찾기
        if (preg_match('/function updateMultipleInstructors\([^}]+\}[^}]+\}/s', $content, $match)) {
            echo "<h3>updateMultipleInstructors 메소드 (처음 800자):</h3>";
            echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; max-height: 300px; overflow-y: auto;'>";
            echo htmlspecialchars(substr($match[0], 0, 800));
            echo "...</pre>";
        }
        
        // saveMultipleInstructors 메소드 찾기
        if (preg_match('/function saveMultipleInstructors\([^}]+\}[^}]+\}/s', $content, $saveMatch)) {
            echo "<h3>saveMultipleInstructors 메소드 (처음 600자):</h3>";
            echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; max-height: 300px; overflow-y: auto;'>";
            echo htmlspecialchars(substr($saveMatch[0], 0, 600));
            echo "...</pre>";
        }
    }
    
    // 4. 예상 원인 분석
    echo "<h2>🧐 예상 원인 분석</h2>";
    
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>⚠️ 예상 원인:</h3>";
    echo "<ol>";
    echo "<li><strong>전체 삭제 후 재생성:</strong> updateMultipleInstructors에서 모든 강사 정보 삭제 후 새로 생성</li>";
    echo "<li><strong>불완전한 데이터 전송:</strong> 폼에서 B 강사 이미지 정보가 전송되지 않음</li>";
    echo "<li><strong>이미지 처리 분리:</strong> 강사 정보와 이미지 처리가 별도로 되어 기존 이미지 유지 실패</li>";
    echo "<li><strong>파일 업로드 조건:</strong> 새 이미지가 없으면 기존 이미지 정보 사라짐</li>";
    echo "</ol>";
    echo "</div>";
    
    // 5. 강사 이미지 처리 시뮬레이션
    echo "<h2>🎭 강사 이미지 처리 시뮬레이션</h2>";
    
    // 시뮬레이션 시나리오
    $scenarios = [
        "시나리오 1: A 강사 이미지만 수정" => [
            'instructor_names' => ['A 강사', 'B 강사'],
            'instructor_infos' => ['A 강사 소개', 'B 강사 소개'],
            'instructor_images' => [
                'name' => ['new_a_image.jpg', ''],  // A만 새 이미지
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE]
            ]
        ],
        "시나리오 2: 강사 정보만 수정" => [
            'instructor_names' => ['A 강사', 'B 강사'],
            'instructor_infos' => ['A 강사 수정된 소개', 'B 강사 소개'],
            'instructor_images' => [
                'name' => ['', ''],  // 이미지 변경 없음
                'error' => [UPLOAD_ERR_NO_FILE, UPLOAD_ERR_NO_FILE]
            ]
        ]
    ];
    
    foreach ($scenarios as $scenarioName => $data) {
        echo "<h3>" . $scenarioName . "</h3>";
        
        echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 10px; margin: 10px 0;'>";
        echo "<strong>전송 데이터:</strong>";
        echo "<ul>";
        echo "<li>강사 이름: " . json_encode($data['instructor_names']) . "</li>";
        echo "<li>강사 소개: " . json_encode($data['instructor_infos']) . "</li>";
        echo "<li>이미지 파일: " . json_encode($data['instructor_images']['name']) . "</li>";
        echo "</ul>";
        echo "</div>";
        
        // 현재 로직 시뮬레이션
        $hasValidInstructorData = isset($data['instructor_names']) && 
                                is_array($data['instructor_names']) && 
                                count($data['instructor_names']) > 0 && 
                                !empty(array_filter($data['instructor_names']));
        
        echo "<div style='background: " . ($hasValidInstructorData ? "#fee2e2; border: 1px solid #dc2626" : "#f0fdf4; border: 1px solid #22c55e") . "; border-radius: 6px; padding: 10px; margin: 10px 0;'>";
        echo "<strong>현재 로직 결과:</strong> " . ($hasValidInstructorData ? "🔄 전체 삭제 후 재생성" : "⏸️ 기존 정보 유지");
        echo "</div>";
        
        if ($hasValidInstructorData) {
            echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 10px; margin: 10px 0;'>";
            echo "<strong>⚠️ 문제:</strong> 기존 강사 정보(이미지 포함) 모두 삭제 후 새 데이터로만 재생성";
            echo "</div>";
        }
        
        echo "<hr>";
    }
    
    // 6. 해결 방안 제시
    echo "<h2>💡 해결 방안</h2>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>✅ 권장 해결 방법:</h3>";
    echo "<ol>";
    echo "<li><strong>개별 강사 업데이트:</strong> 전체 삭제 대신 개별 강사 정보 업데이트</li>";
    echo "<li><strong>기존 이미지 유지:</strong> 새 이미지가 없으면 기존 이미지 경로 유지</li>";
    echo "<li><strong>이미지 처리 분리:</strong> 강사 정보와 이미지 처리를 분리하여 선택적 업데이트</li>";
    echo "<li><strong>폼 데이터 보완:</strong> 기존 이미지 정보도 폼에서 전송하도록 개선</li>";
    echo "</ol>";
    echo "</div>";
    
    // 7. 구체적 수정 방안
    echo "<h2>🔨 구체적 수정 방안</h2>";
    
    echo "<div style='background: #f8f9fa; border: 1px solid #6b7280; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>수정할 코드:</h3>";
    echo "<pre>" . htmlspecialchars("
// 현재: 전체 삭제 후 재생성
DELETE FROM lecture_instructors WHERE lecture_id = ?
// 새 데이터만 INSERT

// 개선: 개별 업데이트
foreach (\$instructors as \$index => \$instructor) {
    if (기존 강사 존재) {
        UPDATE lecture_instructors SET 
            instructor_name = ?, 
            instructor_info = ?, 
            instructor_image = ? (새 이미지가 있을 때만)
        WHERE id = ?
    } else {
        INSERT INTO lecture_instructors ...
    }
}
") . "</pre>";
    echo "</div>";
    
    // 8. 테스트 권장사항
    echo "<h2>🧪 테스트 권장사항</h2>";
    
    echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>테스트 단계:</h3>";
    echo "<ol>";
    echo "<li><strong>현재 상태 확인:</strong> 강사 " . count($instructors) . "명의 이미지 상태 기록</li>";
    echo "<li><strong>수정 테스트:</strong> 한 강사 이미지만 수정</li>";
    echo "<li><strong>결과 확인:</strong> 다른 강사 이미지 유지 여부 확인</li>";
    echo "<li><strong>로그 분석:</strong> 어떤 로직이 실행되었는지 확인</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>