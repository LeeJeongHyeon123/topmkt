<?php
require_once 'src/config/database.php';

try {
    $db = Database::getInstance();
    
    // 132번 강의 데이터 조회
    $lecture = $db->fetch("SELECT id, title, instructors_json FROM lectures WHERE id = ?", [132]);
    
    if ($lecture) {
        echo "=== 132번 강의 정보 ===\n";
        echo "ID: " . $lecture['id'] . "\n";
        echo "제목: " . ($lecture['title'] ?? 'NULL') . "\n";
        echo "강사 JSON: " . ($lecture['instructors_json'] ?? 'NULL') . "\n";
        
        // JSON 데이터 파싱
        if (!empty($lecture['instructors_json'])) {
            $instructors = json_decode($lecture['instructors_json'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "\n=== 강사 정보 파싱 결과 ===\n";
                print_r($instructors);
                
                // 각 강사의 이미지 파일 존재 여부 확인
                if (is_array($instructors)) {
                    foreach ($instructors as $index => $instructor) {
                        echo "\n--- 강사 " . ($index + 1) . " ---\n";
                        echo "이름: " . ($instructor['name'] ?? 'NULL') . "\n";
                        echo "이미지: " . ($instructor['image'] ?? 'NULL') . "\n";
                        
                        if (!empty($instructor['image'])) {
                            $imagePath = '/workspace/public/assets/uploads/instructors/' . $instructor['image'];
                            $exists = file_exists($imagePath);
                            echo "이미지 파일 존재: " . ($exists ? 'YES' : 'NO') . "\n";
                            if ($exists) {
                                echo "파일 크기: " . filesize($imagePath) . " bytes\n";
                            }
                        }
                    }
                }
            } else {
                echo "JSON 파싱 오류: " . json_last_error_msg() . "\n";
            }
        }
    } else {
        echo "132번 강의를 찾을 수 없습니다.\n";
        
        // 비슷한 ID의 강의들 확인
        $nearbyLectures = $db->fetchAll("SELECT id, title FROM lectures WHERE id BETWEEN 130 AND 135 ORDER BY id");
        if ($nearbyLectures) {
            echo "\n=== 130-135번 사이의 강의들 ===\n";
            foreach ($nearbyLectures as $lecture) {
                echo "ID: " . $lecture['id'] . " - " . $lecture['title'] . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
}
?>