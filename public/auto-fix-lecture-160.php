<?php
/**
 * 자동 수정 스크립트 - 강의 160번 강사 이미지
 * 이 파일을 한 번 실행하면 강의 160번의 강사 이미지가 자동으로 수정됩니다.
 */

require_once '../src/config/database.php';

function fixLecture160InstructorImages() {
    try {
        // 실제 존재하는 강사 이미지 파일들
        $instructorImages = [
            '/assets/uploads/instructors/instructor_0_1751342657_file_68635e41b3571.jpg',
            '/assets/uploads/instructors/instructor_1_1751342657_file_68635e41b39b1.jpg'
        ];
        
        // 파일 존재 확인
        $validImages = [];
        foreach ($instructorImages as $index => $imagePath) {
            $filePath = '/workspace/public' . $imagePath;
            if (file_exists($filePath)) {
                $validImages[] = $imagePath;
                echo "✅ 발견: {$imagePath}\n";
            } else {
                echo "❌ 없음: {$imagePath}\n";
            }
        }
        
        if (empty($validImages)) {
            echo "오류: 유효한 강사 이미지를 찾을 수 없습니다.\n";
            return false;
        }
        
        // 강사 데이터 구성
        $instructorsData = [];
        foreach ($validImages as $index => $imagePath) {
            $instructorsData[] = [
                'name' => '강사 ' . ($index + 1),
                'info' => '전문적인 경험과 노하우를 바탕으로 실무에 바로 적용할 수 있는 내용을 전달합니다.',
                'title' => '전문강사',
                'image' => $imagePath
            ];
        }
        
        $instructorsJson = json_encode($instructorsData, JSON_UNESCAPED_UNICODE);
        echo "생성된 JSON: " . $instructorsJson . "\n";
        
        // Database 클래스 사용
        $db = Database::getInstance();
        $sql = "UPDATE lectures SET instructors_json = ? WHERE id = 160";
        $result = $db->execute($sql, [$instructorsJson]);
        
        if ($result) {
            echo "✅ 성공: 강의 160번 강사 이미지가 수정되었습니다!\n";
            
            // 확인
            $lecture = $db->fetch("SELECT instructors_json FROM lectures WHERE id = 160");
            echo "확인: " . $lecture['instructors_json'] . "\n";
            
            return true;
        } else {
            echo "❌ 실패: 데이터베이스 업데이트 실패\n";
            return false;
        }
        
    } catch (Exception $e) {
        echo "오류: " . $e->getMessage() . "\n";
        return false;
    }
}

// 웹에서 실행된 경우
if (isset($_SERVER['HTTP_HOST'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "=== 강의 160번 강사 이미지 자동 수정 ===\n";
    echo "시작 시간: " . date('Y-m-d H:i:s') . "\n\n";
    
    $success = fixLecture160InstructorImages();
    
    echo "\n=== 완료 ===\n";
    echo "결과: " . ($success ? "성공" : "실패") . "\n";
    echo "완료 시간: " . date('Y-m-d H:i:s') . "\n";
    
    if ($success) {
        echo "\n🎉 수정 완료! 아래 링크에서 확인하세요:\n";
        echo "https://www.topmktx.com/lectures/160\n";
    }
} 
// CLI에서 실행된 경우
else {
    fixLecture160InstructorImages();
}
?>