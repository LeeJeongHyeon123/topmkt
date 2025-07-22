<?php
/**
 * API 엔드포인트 - 강사 이미지 수정
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // 강의 160번의 강사 이미지 수정
    $lectureId = 160;
    
    // 발견된 실제 이미지 파일들
    $instructorImages = [
        '/assets/uploads/instructors/instructor_0_1751342657_file_68635e41b3571.jpg',
        '/assets/uploads/instructors/instructor_1_1751342657_file_68635e41b39b1.jpg'
    ];
    
    // 파일 존재 확인
    $validImages = [];
    foreach ($instructorImages as $index => $imagePath) {
        $filePath = '/workspace/public' . $imagePath;
        if (file_exists($filePath)) {
            $validImages[$index] = $imagePath;
        }
    }
    
    if (empty($validImages)) {
        throw new Exception('유효한 강사 이미지 파일을 찾을 수 없습니다.');
    }
    
    // 새로운 강사 데이터 구성
    $instructorsData = [];
    foreach ($validImages as $index => $imagePath) {
        $instructorsData[] = [
            'name' => '강사 ' . ($index + 1),
            'info' => '전문적인 경험과 노하우를 바탕으로 실무에 바로 적용할 수 있는 내용을 전달합니다.',
            'title' => '강사',
            'image' => $imagePath
        ];
    }
    
    $instructorsJson = json_encode($instructorsData, JSON_UNESCAPED_UNICODE);
    
    // 데이터베이스 직접 업데이트를 위한 파일 작성
    $sqlFile = '/workspace/fix_lecture_160.sql';
    $sql = "UPDATE lectures SET instructors_json = '" . addslashes($instructorsJson) . "' WHERE id = {$lectureId};\n";
    file_put_contents($sqlFile, $sql);
    
    // 성공 응답
    echo json_encode([
        'success' => true,
        'message' => '강사 이미지 수정 완료',
        'lecture_id' => $lectureId,
        'instructors_count' => count($instructorsData),
        'sql_file' => $sqlFile,
        'sql_content' => $sql,
        'instructors_json' => $instructorsJson,
        'valid_images' => $validImages
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>