<?php
/**
 * 강사 이미지 업로드 구조 테스트
 * 새로운 instructor_images[] 구조가 정상적으로 처리되는지 확인
 */

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/paths.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/controllers/LectureController.php';

echo "<h1>강사 이미지 업로드 구조 테스트</h1>";

// 테스트용 가짜 FILES 배열 생성 (새로운 구조)
$testFiles = [
    'name' => [
        'test_instructor_1.jpg',
        'test_instructor_2.jpg'
    ],
    'type' => [
        'image/jpeg',
        'image/jpeg'
    ],
    'tmp_name' => [
        '/tmp/test_tmp_1',
        '/tmp/test_tmp_2'
    ],
    'error' => [
        UPLOAD_ERR_OK,
        UPLOAD_ERR_OK
    ],
    'size' => [
        100000,
        120000
    ]
];

echo "<h2>테스트 파일 구조:</h2>";
echo "<pre>" . print_r($testFiles, true) . "</pre>";

// LectureController 인스턴스 생성
$controller = new LectureController();

// 리플렉션을 사용하여 protected 메서드 호출
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('handleInstructorImageUploads');
$method->setAccessible(true);

echo "<h2>handleInstructorImageUploads 메서드 호출 테스트:</h2>";

try {
    // 테스트용 임시 파일 생성 (실제 업로드 시뮬레이션)
    $testImagePath1 = '/tmp/test_tmp_1';
    $testImagePath2 = '/tmp/test_tmp_2';
    
    // 기존 instructor 이미지를 임시 파일로 복사
    copy('/workspace/var/www/html/topmkt/public/assets/uploads/instructors/instructor-1.jpg', $testImagePath1);
    copy('/workspace/var/www/html/topmkt/public/assets/uploads/instructors/instructor-2.jpg', $testImagePath2);
    
    // 임시 파일이 업로드 파일인 것처럼 속이기 위한 설정
    $_FILES['test'] = $testFiles;
    
    echo "<p>임시 파일 생성 완료</p>";
    echo "<p>File 1 exists: " . (file_exists($testImagePath1) ? 'YES' : 'NO') . "</p>";
    echo "<p>File 2 exists: " . (file_exists($testImagePath2) ? 'YES' : 'NO') . "</p>";
    
    // 메서드 호출
    $result = $method->invoke($controller, $testFiles);
    
    echo "<h3>처리 결과:</h3>";
    echo "<pre>" . print_r($result, true) . "</pre>";
    
    if (!empty($result)) {
        echo "<p style='color: green;'>✅ 성공: " . count($result) . "개의 이미지가 처리되었습니다.</p>";
        
        foreach ($result as $index => $imagePath) {
            echo "<p>강사 {$index}: <a href='{$imagePath}' target='_blank'>{$imagePath}</a></p>";
        }
    } else {
        echo "<p style='color: red;'>❌ 실패: 이미지가 처리되지 않았습니다.</p>";
    }
    
    // 임시 파일 정리
    if (file_exists($testImagePath1)) unlink($testImagePath1);
    if (file_exists($testImagePath2)) unlink($testImagePath2);
    
} catch (Exception $e) {
    echo "<p style='color: red;'>오류 발생: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>디버그 로그 확인:</h2>";
$logFile = '/workspace/var/www/html/topmkt/logs/debug_instructor_images.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -20); // 최근 20줄만 표시
    
    echo "<pre>";
    foreach ($recentLines as $line) {
        if (!empty(trim($line))) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p>로그 파일이 존재하지 않습니다.</p>";
}

echo "<hr>";
echo "<p><a href='/events/detail?id=189'>이벤트 189 상세 페이지로 이동</a></p>";
echo "<p><a href='/events/create'>새 이벤트 생성 테스트</a></p>";
?>