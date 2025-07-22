<?php
// 132번 강의의 강사 이미지 문제 디버깅
echo "=== 132번 강의 강사 이미지 문제 디버깅 ===\n\n";

// 1. 강사 이미지 파일 존재 확인
echo "1. 강사 이미지 파일 존재 확인:\n";
$instructorDir = '/workspace/public/assets/uploads/instructors/';
if (is_dir($instructorDir)) {
    $files = scandir($instructorDir);
    $imageFiles = array_filter($files, function($file) {
        return !in_array($file, ['.', '..']) && 
               preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
    });
    
    echo "전체 강사 이미지 파일 수: " . count($imageFiles) . "\n";
    foreach ($imageFiles as $file) {
        $filePath = $instructorDir . $file;
        $size = filesize($filePath);
        echo "- {$file} (크기: {$size} bytes)\n";
    }
} else {
    echo "강사 이미지 디렉토리가 존재하지 않습니다.\n";
}

echo "\n2. 강의 상세 뷰 파일에서 강사 이미지 처리 로직 확인:\n";
$detailViewPath = '/workspace/src/views/lectures/detail.php';
if (file_exists($detailViewPath)) {
    echo "- detail.php 파일 존재: YES\n";
    
    // 강사 이미지 관련 코드 추출
    $content = file_get_contents($detailViewPath);
    
    // instructors_json 관련 코드 찾기
    preg_match_all('/instructors_json.*?;/s', $content, $matches);
    echo "- instructors_json 관련 코드 라인 수: " . count($matches[0]) . "\n";
    
    // 이미지 처리 관련 코드 찾기
    if (strpos($content, 'instructor-avatar') !== false) {
        echo "- instructor-avatar 클래스 사용: YES\n";
    }
    
    if (strpos($content, 'imagePath') !== false) {
        echo "- imagePath 변수 사용: YES\n";
    }
} else {
    echo "- detail.php 파일이 존재하지 않습니다.\n";
}

echo "\n3. LectureController에서 강사 이미지 업로드 로직 확인:\n";
$controllerPath = '/workspace/src/controllers/LectureController.php';
if (file_exists($controllerPath)) {
    echo "- LectureController.php 파일 존재: YES\n";
    
    $content = file_get_contents($controllerPath);
    
    // 강사 이미지 업로드 함수 확인
    if (strpos($content, 'handleInstructorImageUploads') !== false) {
        echo "- handleInstructorImageUploads 함수 존재: YES\n";
    }
    
    // instructors_json 처리 확인
    if (strpos($content, 'instructors_json') !== false) {
        echo "- instructors_json 처리 코드 존재: YES\n";
    }
    
    // 이미지 업로드 디렉토리 확인
    if (strpos($content, '/assets/uploads/instructors/') !== false) {
        echo "- 강사 이미지 업로드 경로 설정: YES\n";
    }
} else {
    echo "- LectureController.php 파일이 존재하지 않습니다.\n";
}

echo "\n4. 강의 등록/수정 폼에서 강사 이미지 필드 확인:\n";
$createViewPath = '/workspace/src/views/lectures/create.php';
if (file_exists($createViewPath)) {
    echo "- create.php 파일 존재: YES\n";
    
    $content = file_get_contents($createViewPath);
    
    // 파일 업로드 필드 확인
    if (strpos($content, 'instructors') !== false && strpos($content, 'file') !== false) {
        echo "- 강사 이미지 업로드 필드 존재: YES\n";
    }
    
    // multiple instructor 지원 확인
    if (strpos($content, 'instructor-item') !== false) {
        echo "- 다중 강사 지원: YES\n";
    }
} else {
    echo "- create.php 파일이 존재하지 않습니다.\n";
}

echo "\n5. 문제점 분석 및 해결 방안:\n";
echo "===========================================\n";

echo "\n💡 문제 가능성:\n";
echo "1. 132번 강의의 instructors_json 데이터가 비어있거나 잘못된 형식\n";
echo "2. 강사 이미지 파일이 실제로 uploads/instructors/ 폴더에 없음\n";
echo "3. 강사 이미지 파일 경로가 잘못 저장됨\n";
echo "4. JSON 파싱 오류\n";
echo "5. 강의 등록 시 강사 이미지 업로드가 제대로 되지 않음\n";

echo "\n🔧 해결 방안:\n";
echo "1. 132번 강의의 실제 데이터 확인 (instructors_json 필드)\n";
echo "2. 강사 이미지 파일 존재 여부 및 경로 확인\n";
echo "3. 강의 등록/수정 시 강사 이미지 업로드 과정 검증\n";
echo "4. JSON 데이터 형식 및 파싱 로직 검토\n";
echo "5. 상세 페이지에서 강사 이미지 표시 로직 점검\n";

echo "\n✅ 다음 단계:\n";
echo "1. 실제 데이터베이스에서 132번 강의 데이터 조회\n";
echo "2. 해당 강의의 강사 이미지 파일 확인\n";
echo "3. 필요시 강사 이미지 다시 업로드 또는 JSON 데이터 수정\n";

echo "\n=== 디버깅 완료 ===\n";
?>