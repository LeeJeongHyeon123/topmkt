<?php
// 132번 강의의 강사 이미지 업데이트 스크립트
require_once '../src/config/config.php';
require_once '../src/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== 132번 강의 강사 이미지 업데이트 ===\n\n";

try {
    $db = Database::getInstance();
    
    // 1. 현재 132번 강의 정보 조회
    echo "1. 현재 132번 강의 정보 조회:\n";
    $stmt = $db->prepare("SELECT id, title, instructor_name, instructor_info, instructors_json FROM lectures WHERE id = ?");
    $stmt->execute([132]);
    $lecture = $stmt->fetch();
    
    if (!$lecture) {
        echo "❌ 132번 강의를 찾을 수 없습니다.\n";
        exit;
    }
    
    echo "✅ 강의 찾음:\n";
    echo "- ID: " . $lecture['id'] . "\n";
    echo "- 제목: " . $lecture['title'] . "\n";
    echo "- 강사명: " . ($lecture['instructor_name'] ?? 'NULL') . "\n";
    echo "- 현재 JSON: " . ($lecture['instructors_json'] ?? 'NULL') . "\n\n";
    
    // 2. 기본 강사 정보 준비
    $instructorName = trim($lecture['instructor_name'] ?? '마케팅 전문가');
    $instructorInfo = trim($lecture['instructor_info'] ?? '다년간의 마케팅 경험과 실무 노하우를 바탕으로 실전에서 바로 적용할 수 있는 전략을 제공합니다.');
    
    // 강사명에 쉼표가 있으면 첫 번째 강사만 사용
    if (strpos($instructorName, ',') !== false) {
        $instructorName = trim(explode(',', $instructorName)[0]);
    }
    
    // 기본 강사 이미지 선택 (132번 강의에는 instructor-kim.jpg 사용)
    $defaultImage = '/assets/uploads/instructors/instructor-kim.jpg';
    
    // 파일 존재 확인
    $imagePath = $_SERVER['DOCUMENT_ROOT'] . $defaultImage;
    if (!file_exists($imagePath)) {
        echo "⚠️ 기본 이미지 파일이 없습니다: $imagePath\n";
        echo "사용 가능한 이미지들:\n";
        
        $instructorDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/uploads/instructors/';
        if (is_dir($instructorDir)) {
            $files = scandir($instructorDir);
            $imageFiles = array_filter($files, function($file) {
                return !in_array($file, ['.', '..']) && 
                       preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
            });
            
            foreach ($imageFiles as $file) {
                if (strpos($file, 'instructor-') === 0) {
                    echo "- /assets/uploads/instructors/$file\n";
                }
            }
            
            // 첫 번째 기본 이미지 사용
            $defaultImages = [
                'instructor-kim.jpg',
                'instructor-lee.jpg', 
                'instructor-park.jpg',
                'instructor-1.jpg',
                'instructor-2.jpg',
                'instructor-3.jpg'
            ];
            
            foreach ($defaultImages as $imgFile) {
                if (file_exists($instructorDir . $imgFile)) {
                    $defaultImage = '/assets/uploads/instructors/' . $imgFile;
                    echo "✅ 대체 이미지 사용: $defaultImage\n";
                    break;
                }
            }
        }
    } else {
        echo "✅ 기본 이미지 파일 확인: $imagePath (" . filesize($imagePath) . " bytes)\n";
    }
    
    // 3. JSON 데이터 생성
    echo "\n2. 강사 JSON 데이터 생성:\n";
    $instructorsData = [
        [
            'name' => $instructorName,
            'title' => '마케팅 컨설턴트',
            'info' => $instructorInfo,
            'image' => $defaultImage
        ]
    ];
    
    $instructorsJson = json_encode($instructorsData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    echo "생성된 JSON:\n" . $instructorsJson . "\n\n";
    
    // 4. 데이터베이스 업데이트
    echo "3. 데이터베이스 업데이트:\n";
    
    $updateStmt = $db->prepare("UPDATE lectures SET instructors_json = ? WHERE id = ?");
    $result = $updateStmt->execute([json_encode($instructorsData, JSON_UNESCAPED_UNICODE), 132]);
    
    if ($result) {
        echo "✅ 업데이트 성공!\n";
        
        // 5. 업데이트 결과 확인
        echo "\n4. 업데이트 결과 확인:\n";
        $stmt = $db->prepare("SELECT id, title, instructors_json FROM lectures WHERE id = ?");
        $stmt->execute([132]);
        $updatedLecture = $stmt->fetch();
        
        echo "업데이트된 JSON: " . $updatedLecture['instructors_json'] . "\n";
        
        // JSON 파싱 테스트
        $parsedData = json_decode($updatedLecture['instructors_json'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "✅ JSON 파싱 성공\n";
            echo "강사명: " . $parsedData[0]['name'] . "\n";
            echo "이미지: " . $parsedData[0]['image'] . "\n";
        } else {
            echo "❌ JSON 파싱 실패: " . json_last_error_msg() . "\n";
        }
        
    } else {
        echo "❌ 업데이트 실패\n";
    }
    
    echo "\n=== 작업 완료 ===\n";
    echo "132번 강의 페이지를 새로고침하여 변경사항을 확인하세요.\n";
    echo "디버그 정보: ?debug 파라미터를 URL에 추가하여 상세 정보를 확인할 수 있습니다.\n";
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}
?>