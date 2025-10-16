<?php
/**
 * 강의 160번 강사 이미지 문제 수정 스크립트
 */

require_once '../src/config/database.php';

try {
    $db = Database::getInstance();
    
    // 강의 160번 데이터 조회
    $lecture = $db->fetch("SELECT * FROM lectures WHERE id = 160");
    
    if (!$lecture) {
        echo "강의 160번을 찾을 수 없습니다.\n";
        exit;
    }
    
    echo "=== 강의 160번 강사 이미지 수정 시작 ===\n";
    echo "현재 instructors_json: " . $lecture['instructors_json'] . "\n";
    
    // 현재 강사 데이터 파싱
    $instructors = json_decode($lecture['instructors_json'], true);
    if (!$instructors) {
        echo "강사 데이터를 파싱할 수 없습니다.\n";
        exit;
    }
    
    // 업로드된 강사 이미지 파일 찾기
    $instructorImages = [
        0 => '/assets/uploads/instructors/instructor_0_1751342657_file_68635e41b3571.jpg',
        1 => '/assets/uploads/instructors/instructor_1_1751342657_file_68635e41b39b1.jpg'
    ];
    
    // 강사 데이터에 이미지 경로 추가
    foreach ($instructors as $index => &$instructor) {
        if (isset($instructorImages[$index])) {
            $instructor['image'] = $instructorImages[$index];
            echo "강사 {$index}에 이미지 추가: " . $instructorImages[$index] . "\n";
            
            // 파일 존재 확인
            $filePath = '/workspace/public' . $instructorImages[$index];
            if (file_exists($filePath)) {
                echo "  ✅ 파일 존재 확인: " . $filePath . "\n";
            } else {
                echo "  ❌ 파일 없음: " . $filePath . "\n";
            }
        }
    }
    unset($instructor);
    
    // 업데이트된 JSON 생성
    $updatedInstructorsJson = json_encode($instructors, JSON_UNESCAPED_UNICODE);
    echo "\n업데이트될 instructors_json: " . $updatedInstructorsJson . "\n";
    
    // 데이터베이스 업데이트
    $sql = "UPDATE lectures SET instructors_json = ? WHERE id = 160";
    $result = $db->execute($sql, [$updatedInstructorsJson]);
    
    if ($result) {
        echo "\n✅ 강의 160번 강사 이미지 업데이트 완료!\n";
        echo "업데이트된 강사 수: " . count($instructors) . "명\n";
        
        // 확인을 위해 다시 조회
        $updatedLecture = $db->fetch("SELECT instructors_json FROM lectures WHERE id = 160");
        echo "확인 - 업데이트된 데이터: " . $updatedLecture['instructors_json'] . "\n";
    } else {
        echo "\n❌ 데이터베이스 업데이트 실패\n";
    }
    
} catch (Exception $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
}
?>