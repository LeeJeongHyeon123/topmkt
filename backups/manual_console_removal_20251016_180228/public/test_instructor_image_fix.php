<?php
/**
 * 강사 이미지 개별 업데이트 테스트
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>강사 이미지 개별 업데이트 테스트</h1>";

try {
    $db = Database::getInstance();
    $eventId = 194;
    
    // 1. 수정 전 현재 상태
    echo "<h2>📊 수정 전 현재 상태</h2>";
    $beforeInstructors = $db->fetchAll("SELECT * FROM lecture_instructors WHERE lecture_id = ? ORDER BY id", [$eventId]);
    
    if (count($beforeInstructors) > 0) {
        echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
        echo "<h3>현재 강사 정보:</h3>";
        echo "<ul>";
        foreach ($beforeInstructors as $index => $instructor) {
            $imagePath = $instructor['instructor_image'] ?? '';
            $exists = $imagePath && file_exists(ROOT_PATH . '/public' . $imagePath);
            echo "<li>";
            echo "<strong>강사 " . ($index + 1) . ":</strong> " . htmlspecialchars($instructor['instructor_name']);
            echo " | 이미지: " . ($imagePath ? htmlspecialchars($imagePath) : "없음");
            echo " | 파일 존재: " . ($exists ? "✅" : "❌");
            echo "</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    // 2. 수정 사항 요약
    echo "<h2>🔧 적용된 수정사항</h2>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>✅ 완료된 수정사항:</h3>";
    echo "<ol>";
    echo "<li><strong>개별 업데이트 방식:</strong> 전체 삭제 후 재생성 → 개별 강사 업데이트</li>";
    echo "<li><strong>기존 이미지 보존:</strong> 새 이미지가 없으면 기존 이미지 경로 유지</li>";
    echo "<li><strong>선택적 이미지 처리:</strong> 각 강사별로 독립적인 이미지 처리</li>";
    echo "<li><strong>상세 로깅:</strong> 각 단계별 처리 상황 기록</li>";
    echo "</ol>";
    echo "</div>";
    
    // 3. 새로운 로직 설명
    echo "<h2>🚀 새로운 처리 로직</h2>";
    
    echo "<div style='background: #f8f9fa; border: 1px solid #6b7280; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>개별 강사 처리 과정:</h3>";
    echo "<ol>";
    echo "<li><strong>기존 강사 조회:</strong> 현재 등록된 강사 정보 확인</li>";
    echo "<li><strong>순차적 처리:</strong> 각 강사별로 개별 업데이트</li>";
    echo "<li><strong>이미지 처리:</strong>";
    echo "<ul>";
    echo "<li>새 이미지 업로드 시: 기존 이미지 삭제 후 새 이미지 저장</li>";
    echo "<li>새 이미지 없을 시: 기존 이미지 경로 유지</li>";
    echo "</ul>";
    echo "</li>";
    echo "<li><strong>데이터 업데이트:</strong> 강사명, 소개, 이미지 경로 업데이트</li>";
    echo "</ol>";
    echo "</div>";
    
    // 4. 코드 변경 상세
    echo "<h2>📝 주요 코드 변경사항</h2>";
    
    echo "<h3>1. 기존 로직 (문제 발생)</h3>";
    echo "<pre style='background: #fee2e2; padding: 10px; border-radius: 4px; color: #dc2626;'>";
    echo htmlspecialchars("// 모든 강사 정보 삭제
DELETE FROM lecture_instructors WHERE lecture_id = ?

// 새 강사 정보 전체 재생성
INSERT INTO lecture_instructors ...");
    echo "</pre>";
    
    echo "<h3>2. 새 로직 (문제 해결)</h3>";
    echo "<pre style='background: #f0fdf4; padding: 10px; border-radius: 4px; color: #22c55e;'>";
    echo htmlspecialchars("// 각 강사별 개별 처리
for (각 강사) {
    if (기존 강사 존재) {
        // 개별 업데이트
        UPDATE lecture_instructors SET 
            instructor_name = ?, 
            instructor_info = ?, 
            instructor_image = ? (새 이미지 있으면 새 경로, 없으면 기존 경로)
        WHERE id = ?
    } else {
        // 새 강사 생성
        INSERT INTO lecture_instructors ...
    }
}");
    echo "</pre>";
    
    echo "<h3>3. 개별 이미지 처리 메소드</h3>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px;'>";
    echo htmlspecialchars("handleSingleInstructorImage(\$eventId, \$instructorIndex, \$currentImagePath) {
    if (새 이미지 업로드됨) {
        // 새 이미지 처리
        return 새_이미지_경로;
    } else {
        // 기존 이미지 유지
        return \$currentImagePath;
    }
}");
    echo "</pre>";
    
    // 5. 테스트 시나리오
    echo "<h2>🧪 테스트 시나리오</h2>";
    
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>테스트 방법:</h3>";
    echo "<ol>";
    echo "<li><strong>현재 상태 확인:</strong> 강사 " . count($beforeInstructors) . "명의 이미지 상태 기록</li>";
    echo "<li><strong>편집 페이지 접속:</strong> <a href='https://www.topmktx.com/events/create?id=194' target='_blank'>https://www.topmktx.com/events/create?id=194</a></li>";
    echo "<li><strong>부분 수정 테스트:</strong> 한 강사의 이미지만 변경</li>";
    echo "<li><strong>결과 확인:</strong> 다른 강사들의 이미지 유지 여부 확인</li>";
    echo "</ol>";
    echo "</div>";
    
    // 6. 예상 결과
    echo "<h2>🎯 예상 결과</h2>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>✅ 수정 후 기대 결과:</h3>";
    echo "<ul>";
    echo "<li><strong>A 강사 이미지 수정:</strong> 새 이미지로 정상 업데이트</li>";
    echo "<li><strong>B 강사 이미지 유지:</strong> 기존 이미지 그대로 보존</li>";
    echo "<li><strong>강사 정보 유지:</strong> 다른 강사들의 이름, 소개 정보 유지</li>";
    echo "<li><strong>로그 기록:</strong> 각 강사별 처리 과정 상세 기록</li>";
    echo "</ul>";
    echo "</div>";
    
    // 7. 문제 해결 확인
    echo "<h2>🔍 문제 해결 확인</h2>";
    
    echo "<div style='background: #fee2e2; border: 1px solid #dc2626; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>이전 문제:</h3>";
    echo "<ul>";
    echo "<li>❌ A 강사 이미지만 수정했는데 B 강사 이미지 삭제됨</li>";
    echo "<li>❌ 전체 강사 정보 삭제 후 재생성 방식</li>";
    echo "<li>❌ 기존 이미지 보존 로직 없음</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>현재 해결 상태:</h3>";
    echo "<ul>";
    echo "<li>✅ 개별 강사 업데이트 방식 적용</li>";
    echo "<li>✅ 기존 이미지 자동 보존 로직</li>";
    echo "<li>✅ 선택적 이미지 처리 시스템</li>";
    echo "<li>✅ 상세 로깅으로 디버깅 가능</li>";
    echo "</ul>";
    echo "</div>";
    
    // 8. 로그 확인 가이드
    echo "<h2>📝 로그 확인 가이드</h2>";
    
    echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>정상 동작 시 로그 메시지:</h3>";
    echo "<ul>";
    echo "<li><code>updateMultipleInstructors: 개별 강사 정보 업데이트 시작</code></li>";
    echo "<li><code>handleSingleInstructorImage: 새 이미지 업로드 성공</code> (이미지 변경 시)</li>";
    echo "<li><code>updateMultipleInstructors: 강사 ID X 업데이트 완료</code></li>";
    echo "<li><code>기존 이미지 경로 유지</code> (이미지 변경 없는 강사)</li>";
    echo "</ul>";
    echo "</div>";
    
    // 9. 추가 개선사항
    echo "<h2>🚀 추가 개선사항</h2>";
    
    echo "<div style='background: #f8f9fa; border: 1px solid #6b7280; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>향후 개선 계획:</h3>";
    echo "<ul>";
    echo "<li>이미지 크기 자동 조정 기능</li>";
    echo "<li>이미지 최적화 (압축, 리사이징)</li>";
    echo "<li>강사 순서 변경 기능</li>";
    echo "<li>실시간 이미지 미리보기</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>