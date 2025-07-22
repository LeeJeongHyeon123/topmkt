<?php
/**
 * 강사 이미지 문제 수정 스크립트 (웹 기반)
 */

// 기본 구성
define('BASE_PATH', dirname(__DIR__));
define('SRC_PATH', BASE_PATH . '/src');

// 직접 데이터베이스 접근 (mysqli 없이)
$lectureId = 160;

echo "<h1>강의 {$lectureId}번 강사 이미지 수정</h1>";

// 현재 instructors_json 확인
echo "<h2>1. 현재 상태 분석</h2>";

// 강사 이미지 파일 찾기
$instructorDir = '/workspace/public/assets/uploads/instructors/';
$imageFiles = [
    0 => 'instructor_0_1751342657_file_68635e41b3571.jpg',
    1 => 'instructor_1_1751342657_file_68635e41b39b1.jpg'
];

echo "<h3>발견된 강사 이미지 파일:</h3>";
foreach ($imageFiles as $index => $fileName) {
    $filePath = $instructorDir . $fileName;
    $webPath = '/assets/uploads/instructors/' . $fileName;
    
    if (file_exists($filePath)) {
        $size = filesize($filePath);
        echo "<p>✅ 강사 {$index}: {$fileName} ({$size} bytes)</p>";
        echo "<img src='{$webPath}' style='max-width: 150px; max-height: 150px; border: 1px solid #ccc;'><br>";
    } else {
        echo "<p>❌ 강사 {$index}: {$fileName} - 파일 없음</p>";
    }
}

// SQL 파일 생성 (mysqli가 없으므로 SQL 출력)
echo "<h2>2. 수정 SQL 생성</h2>";

$instructorsData = [
    [
        'name' => '강사 1',
        'info' => '전문적인 경험과 노하우를 바탕으로 실무에 바로 적용할 수 있는 내용을 전달합니다.',
        'title' => '강사',
        'image' => '/assets/uploads/instructors/instructor_0_1751342657_file_68635e41b3571.jpg'
    ],
    [
        'name' => '강사 2', 
        'info' => '전문적인 경험과 노하우를 바탕으로 실무에 바로 적용할 수 있는 내용을 전달합니다.',
        'title' => '강사',
        'image' => '/assets/uploads/instructors/instructor_1_1751342657_file_68635e41b3571.jpg'
    ]
];

$instructorsJson = json_encode($instructorsData, JSON_UNESCAPED_UNICODE);

echo "<h3>업데이트할 JSON 데이터:</h3>";
echo "<pre>" . htmlspecialchars($instructorsJson) . "</pre>";

$sql = "UPDATE lectures SET instructors_json = '" . addslashes($instructorsJson) . "' WHERE id = {$lectureId};";

echo "<h3>실행할 SQL:</h3>";
echo "<textarea style='width: 100%; height: 100px;'>" . htmlspecialchars($sql) . "</textarea>";

echo "<h2>3. 수동 적용 방법</h2>";
echo "<p>1. 위의 SQL을 복사하여 데이터베이스에서 직접 실행하거나</p>";
echo "<p>2. <a href='/lectures/160' target='_blank'>강의 160번 페이지</a>를 확인하여 강사 이미지가 표시되는지 확인</p>";

// 개발자 도구용 JavaScript 생성
echo "<h2>4. JavaScript 기반 업데이트 (개발자 도구용)</h2>";
echo "<pre>";
echo "// 개발자 도구에서 실행할 수 있는 코드\n";
echo "fetch('/api/update-lecture-instructors', {\n";
echo "  method: 'POST',\n";
echo "  headers: { 'Content-Type': 'application/json' },\n";
echo "  body: JSON.stringify({\n";
echo "    lecture_id: {$lectureId},\n";
echo "    instructors_json: " . json_encode($instructorsJson) . "\n";
echo "  })\n";
echo "}).then(r => r.json()).then(console.log);\n";
echo "</pre>";

echo "<h2>5. 디버그 정보</h2>";
echo "<p><strong>강의 ID:</strong> {$lectureId}</p>";
echo "<p><strong>수정 시간:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>이미지 개수:</strong> " . count($imageFiles) . "개</p>";

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #333; }
h2 { color: #666; border-bottom: 1px solid #ddd; }
pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
textarea { font-family: monospace; }
</style>