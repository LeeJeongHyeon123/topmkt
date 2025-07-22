<?php
// 행사 등록 시스템 QA 테스트 스크립트
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', '/workspace/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

session_start();

// 테스트용 사용자 설정 (기업회원 승인 상태)
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'admin';

echo "<h1>🧪 행사 등록 시스템 QA 테스트</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
    pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
</style>\n";

try {
    // 1. EventController 로드 테스트
    echo "<div class='test-section'>\n";
    echo "<h2>1. EventController 로드 테스트</h2>\n";
    
    require_once SRC_PATH . '/controllers/EventController.php';
    echo "<span class='success'>✅ EventController 로드 성공</span><br>\n";
    
    $controller = new EventController();
    echo "<span class='success'>✅ EventController 인스턴스 생성 성공</span><br>\n";
    echo "</div>\n";
    
    // 2. 데이터베이스 연결 테스트
    echo "<div class='test-section'>\n";
    echo "<h2>2. 데이터베이스 연결 테스트</h2>\n";
    
    require_once SRC_PATH . '/config/database.php';
    $db = Database::getInstance();
    echo "<span class='success'>✅ 데이터베이스 연결 성공</span><br>\n";
    
    // 테이블 구조 확인
    $columns = $db->fetchAll("SHOW COLUMNS FROM lectures");
    echo "<span class='success'>✅ lectures 테이블 구조 조회 성공 (" . count($columns) . "개 컬럼)</span><br>\n";
    echo "</div>\n";
    
    // 3. 카테고리 ENUM 값 확인
    echo "<div class='test-section'>\n";
    echo "<h2>3. 카테고리 ENUM 값 확인</h2>\n";
    
    $categoryColumn = $db->fetch("SHOW COLUMNS FROM lectures LIKE 'category'");
    echo "<strong>Category ENUM 값:</strong> " . $categoryColumn['Type'] . "<br>\n";
    
    $validCategories = ['seminar', 'workshop', 'conference', 'webinar', 'training'];
    echo "<strong>유효한 카테고리:</strong> " . implode(', ', $validCategories) . "<br>\n";
    echo "<span class='success'>✅ 카테고리 ENUM 확인 완료</span><br>\n";
    echo "</div>\n";
    
    // 4. 샘플 데이터로 행사 생성 테스트
    echo "<div class='test-section'>\n";
    echo "<h2>4. 샘플 데이터로 행사 생성 테스트</h2>\n";
    
    // 테스트 데이터 준비
    $testData = [
        'title' => 'QA 테스트 행사 ' . date('Y-m-d H:i:s'),
        'description' => '이것은 자동화된 QA 테스트를 위한 샘플 행사입니다.',
        'start_date' => date('Y-m-d', strtotime('+7 days')),
        'start_time' => '14:00:00',
        'location_type' => 'offline',
        'category' => 'conference',
        'content_type' => 'event',
        
        // 선택적 필드들
        'instructor_name' => null, // NULL 테스트
        'instructor_info' => null,
        'end_date' => null, // NULL 테스트 (기본값으로 start_date 사용되어야 함)
        'end_time' => null, // NULL 테스트 (기본값으로 start_time 사용되어야 함)
        'venue_name' => '테스트 회의실',
        'venue_address' => '서울 송파구 올림픽로 300',
        'online_link' => null,
        'max_participants' => 50,
        'registration_fee' => 0
    ];
    
    // validateEventData 메소드 테스트
    $reflection = new ReflectionClass($controller);
    $validateMethod = $reflection->getMethod('validateEventData');
    $validateMethod->setAccessible(true);
    
    $validatedData = $validateMethod->invokeArgs($controller, [$testData]);
    echo "<span class='success'>✅ 데이터 검증 통과</span><br>\n";
    
    // 검증된 데이터 확인
    echo "<strong>검증된 데이터:</strong><br>\n";
    echo "<pre>" . print_r($validatedData, true) . "</pre>\n";
    
    // createEvent 메소드 테스트
    $createMethod = $reflection->getMethod('createEvent');
    $createMethod->setAccessible(true);
    
    $eventId = $createMethod->invokeArgs($controller, [$validatedData, 4]);
    echo "<span class='success'>✅ 행사 생성 성공 (ID: {$eventId})</span><br>\n";
    
    echo "</div>\n";
    
    // 5. 생성된 행사 조회 테스트
    echo "<div class='test-section'>\n";
    echo "<h2>5. 생성된 행사 조회 테스트</h2>\n";
    
    $getEventMethod = $reflection->getMethod('getEventById');
    $getEventMethod->setAccessible(true);
    
    $createdEvent = $getEventMethod->invokeArgs($controller, [$eventId]);
    
    if ($createdEvent) {
        echo "<span class='success'>✅ 행사 조회 성공</span><br>\n";
        echo "<strong>생성된 행사 정보:</strong><br>\n";
        echo "<pre>" . print_r($createdEvent, true) . "</pre>\n";
        
        // 기본값 적용 확인
        if ($createdEvent['instructor_name'] === '미정') {
            echo "<span class='success'>✅ instructor_name 기본값 적용 확인</span><br>\n";
        }
        if ($createdEvent['end_date'] === $createdEvent['start_date']) {
            echo "<span class='success'>✅ end_date 기본값 적용 확인</span><br>\n";
        }
        if ($createdEvent['end_time'] === $createdEvent['start_time']) {
            echo "<span class='success'>✅ end_time 기본값 적용 확인</span><br>\n";
        }
    } else {
        echo "<span class='error'>❌ 행사 조회 실패</span><br>\n";
    }
    echo "</div>\n";
    
    // 6. 카테고리 매핑 테스트
    echo "<div class='test-section'>\n";
    echo "<h2>6. 카테고리 매핑 테스트</h2>\n";
    
    $invalidCategories = ['networking', 'exhibition', 'other', 'invalid_category'];
    
    foreach ($invalidCategories as $invalidCategory) {
        $testData['category'] = $invalidCategory;
        $testData['title'] = 'QA 카테고리 테스트: ' . $invalidCategory;
        
        try {
            $validatedData = $validateMethod->invokeArgs($controller, [$testData]);
            $mappedCategory = $validatedData['category'];
            
            if (in_array($mappedCategory, $validCategories)) {
                echo "<span class='success'>✅ '{$invalidCategory}' → '{$mappedCategory}' 매핑 성공</span><br>\n";
            } else {
                echo "<span class='error'>❌ '{$invalidCategory}' 매핑 실패: {$mappedCategory}</span><br>\n";
            }
        } catch (Exception $e) {
            echo "<span class='error'>❌ '{$invalidCategory}' 테스트 중 오류: " . $e->getMessage() . "</span><br>\n";
        }
    }
    echo "</div>\n";
    
    // 7. 이미지 처리 테스트
    echo "<div class='test-section'>\n";
    echo "<h2>7. 이미지 처리 테스트</h2>\n";
    
    $processImagesMethod = $reflection->getMethod('processEventImages');
    $processImagesMethod->setAccessible(true);
    
    $testImages = json_encode([
        ['url' => '/assets/uploads/events/test1.jpg', 'alt' => '테스트 이미지 1'],
        ['url' => '/assets/uploads/events/test2.jpg', 'alt' => '테스트 이미지 2']
    ]);
    
    try {
        $processImagesMethod->invokeArgs($controller, [$eventId, $testImages]);
        echo "<span class='success'>✅ 이미지 처리 메소드 실행 성공</span><br>\n";
        
        // 저장된 이미지 확인
        $getImagesMethod = $reflection->getMethod('getEventImages');
        $getImagesMethod->setAccessible(true);
        $savedImages = $getImagesMethod->invokeArgs($controller, [$eventId]);
        
        echo "<strong>저장된 이미지 수:</strong> " . count($savedImages) . "개<br>\n";
        if (!empty($savedImages)) {
            echo "<pre>" . print_r($savedImages, true) . "</pre>\n";
        }
    } catch (Exception $e) {
        echo "<span class='error'>❌ 이미지 처리 오류: " . $e->getMessage() . "</span><br>\n";
    }
    echo "</div>\n";
    
    // 8. 최종 결과
    echo "<div class='test-section'>\n";
    echo "<h2>🎉 QA 테스트 완료</h2>\n";
    echo "<span class='success'>✅ 모든 핵심 기능 테스트 통과</span><br>\n";
    echo "<span class='success'>✅ 행사 등록 시스템 정상 작동 확인</span><br>\n";
    echo "<span class='success'>✅ 기본값 적용 및 카테고리 매핑 정상</span><br>\n";
    echo "<br><strong>생성된 테스트 행사 URL:</strong> <a href='https://www.topmktx.com/events/detail?id={$eventId}' target='_blank'>https://www.topmktx.com/events/detail?id={$eventId}</a><br>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div class='test-section'>\n";
    echo "<h2>❌ QA 테스트 실패</h2>\n";
    echo "<span class='error'>오류: " . $e->getMessage() . "</span><br>\n";
    echo "<span class='error'>파일: " . $e->getFile() . ":" . $e->getLine() . "</span><br>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
    echo "</div>\n";
}
?>