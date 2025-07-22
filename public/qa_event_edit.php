<?php
/**
 * 행사 수정 모드 QA 테스트
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/controllers/EventController.php';

echo "<h1>행사 수정 모드 QA 테스트</h1>";

// 테스트용 로그인 상태 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['is_logged_in'] = true;

echo "<p>✅ 테스트 로그인 설정: 사용자 ID=4, 역할=ROLE_USER</p>";

// GET 파라미터 설정
$_GET['id'] = '190';

try {
    // 1. 데이터베이스에서 행사 190 정보 확인
    $db = Database::getInstance();
    $event = $db->fetch("SELECT * FROM lectures WHERE id = 190 AND content_type = 'event'");
    
    if (!$event) {
        echo "<p style='color: red;'>❌ 행사 190이 존재하지 않습니다.</p>";
        exit;
    }
    
    echo "<h2>📊 행사 190 기본 정보</h2>";
    echo "<ul>";
    echo "<li>제목: " . htmlspecialchars($event['title']) . "</li>";
    echo "<li>시작일: " . $event['start_date'] . "</li>";
    echo "<li>종료일: " . $event['end_date'] . "</li>";
    echo "<li>위치 타입: " . $event['location_type'] . "</li>";
    echo "<li>작성자: " . $event['user_id'] . "</li>";
    echo "<li>상태: " . $event['status'] . "</li>";
    echo "</ul>";
    
    // 2. 행사 이미지 확인
    $images = $db->fetchAll("SELECT * FROM event_images WHERE event_id = 190");
    echo "<h2>🖼️ 행사 이미지 (" . count($images) . "개)</h2>";
    if (count($images) > 0) {
        echo "<ul>";
        foreach ($images as $image) {
            echo "<li>" . htmlspecialchars($image['image_path']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>이미지가 없습니다.</p>";
    }
    
    // 3. 강사 정보 확인
    $instructors = $db->fetchAll("SELECT * FROM lecture_instructors WHERE lecture_id = 190");
    echo "<h2>👨‍🏫 강사 정보 (" . count($instructors) . "개)</h2>";
    if (count($instructors) > 0) {
        echo "<ul>";
        foreach ($instructors as $instructor) {
            echo "<li>" . htmlspecialchars($instructor['instructor_name']) . " - " . htmlspecialchars($instructor['instructor_info']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>강사 정보가 없습니다.</p>";
    }
    
    // 4. EventController 테스트
    echo "<h2>🎮 EventController 테스트</h2>";
    $controller = new EventController();
    
    // 출력 캡처
    ob_start();
    $controller->create();
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "<p>✅ 컨트롤러 실행 성공</p>";
    echo "<p>출력 길이: " . number_format(strlen($output)) . " 바이트</p>";
    
    // 5. 출력 내용 분석
    echo "<h2>📝 출력 내용 분석</h2>";
    $checks = [
        '행사 수정' => strpos($output, '행사 수정') !== false,
        'value=' => strpos($output, 'value=') !== false,
        'Quill.js' => strpos($output, 'quill') !== false,
        'loadEditData' => strpos($output, 'loadEditData') !== false,
        'toggleLocationFields' => strpos($output, 'toggleLocationFields') !== false,
        'addInstructor' => strpos($output, 'addInstructor') !== false,
        'JavaScript 오류' => strpos($output, 'ReferenceError') !== false || strpos($output, 'TypeError') !== false
    ];
    
    echo "<ul>";
    foreach ($checks as $check => $result) {
        $status = $result ? "✅" : "❌";
        if ($check === 'JavaScript 오류') {
            $status = $result ? "❌" : "✅";
        }
        echo "<li>$status $check</li>";
    }
    echo "</ul>";
    
    // 6. 중요 폼 필드 확인
    echo "<h2>📋 중요 폼 필드 확인</h2>";
    $fields = ['title', 'start_date', 'end_date', 'location_type', 'venue_name', 'max_participants'];
    echo "<ul>";
    foreach ($fields as $field) {
        $found = strpos($output, "name=\"$field\"") !== false;
        $status = $found ? "✅" : "❌";
        echo "<li>$status $field 필드</li>";
    }
    echo "</ul>";
    
    echo "<h2>🎯 QA 결과 요약</h2>";
    $hasErrors = strpos($output, 'ReferenceError') !== false || strpos($output, 'TypeError') !== false;
    $hasTitle = strpos($output, '행사 수정') !== false;
    $hasFields = strpos($output, 'value=') !== false;
    
    if (!$hasErrors && $hasTitle && $hasFields) {
        echo "<p style='color: green; font-size: 1.2em;'>🎉 <strong>QA 통과!</strong> 행사 수정 모드가 정상적으로 작동합니다.</p>";
    } else {
        echo "<p style='color: red; font-size: 1.2em;'>❌ <strong>QA 실패!</strong> 문제가 있습니다.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>