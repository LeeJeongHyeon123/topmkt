<?php
// 임시저장된 강의 데이터 확인 스크립트

// 프로젝트 설정 로드
define('ROOT_PATH', '/workspace');
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';

try {
    $db = Database::getInstance();
    
    // 가장 최근 임시저장된 강의 조회
    $sql = "SELECT id, title, instructor_name, instructor_info, instructors_json, status, created_at, updated_at 
            FROM lectures 
            WHERE status = 'draft' 
            ORDER BY updated_at DESC, created_at DESC 
            LIMIT 1";
    
    $result = $db->fetch($sql);
    
    if ($result) {
        echo "=== 가장 최근 임시저장된 강의 ===\n";
        echo "ID: " . $result['id'] . "\n";
        echo "제목: " . $result['title'] . "\n";
        echo "상태: " . $result['status'] . "\n";
        echo "생성일: " . $result['created_at'] . "\n";
        echo "수정일: " . $result['updated_at'] . "\n";
        echo "\n";
        
        echo "=== 강사 정보 ===\n";
        echo "강사명 (instructor_name): " . ($result['instructor_name'] ?: '없음') . "\n";
        echo "강사 정보 (instructor_info): " . ($result['instructor_info'] ?: '없음') . "\n";
        echo "강사 JSON (instructors_json): " . ($result['instructors_json'] ?: '없음') . "\n";
        
        if ($result['instructors_json']) {
            echo "\n=== JSON 파싱 결과 ===\n";
            $instructors = json_decode($result['instructors_json'], true);
            if ($instructors && is_array($instructors)) {
                foreach ($instructors as $index => $instructor) {
                    echo "강사 " . ($index + 1) . ":\n";
                    echo "  - 이름: " . ($instructor['name'] ?? '없음') . "\n";
                    echo "  - 직책: " . ($instructor['title'] ?? '없음') . "\n";
                    echo "  - 소개: " . ($instructor['info'] ?? '없음') . "\n";
                    echo "\n";
                }
            } else {
                echo "JSON 파싱 실패 또는 빈 배열\n";
            }
        }
    } else {
        echo "임시저장된 강의가 없습니다.\n";
    }
    
} catch (Exception $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
}
?>