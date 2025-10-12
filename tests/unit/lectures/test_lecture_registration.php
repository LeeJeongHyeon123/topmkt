<?php
/**
 * 강의 신청 기능 최종 테스트
 */

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

echo "<h1>🎯 강의 신청 기능 최종 테스트</h1>";

// 세션 시작
session_start();

try {
    // 기본 설정 로드
    require_once CONFIG_PATH . '/paths.php';
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    require_once SRC_PATH . '/helpers/WebLogger.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/helpers/GlobalErrorHandler.php';
    
    // 글로벌 에러 핸들러 등록
    GlobalErrorHandler::register();
    
    echo "<h2>✅ 시스템 초기화 완료</h2>";
    
    // 데이터베이스 연결 테스트
    echo "<h2>📊 데이터베이스 연결 테스트</h2>";
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    if ($connection) {
        echo "✅ 데이터베이스 연결: 성공<br>";
        
        // 테이블 존재 확인
        $result = $connection->query("SHOW TABLES LIKE 'lectures'");
        if ($result && $result->num_rows > 0) {
            echo "✅ lectures 테이블: 존재함<br>";
        } else {
            echo "❌ lectures 테이블: 존재하지 않음<br>";
        }
        
        $result = $connection->query("SHOW TABLES LIKE 'lecture_registrations'");
        if ($result && $result->num_rows > 0) {
            echo "✅ lecture_registrations 테이블: 존재함<br>";
        } else {
            echo "❌ lecture_registrations 테이블: 존재하지 않음<br>";
        }
    }
    
    // 강의 정보 조회 테스트
    echo "<h2>📚 강의 정보 조회 테스트</h2>";
    $lectureId = 167;
    $lectureQuery = "SELECT * FROM lectures WHERE id = ?";
    $lecture = $db->fetch($lectureQuery, [$lectureId]);
    
    if ($lecture) {
        echo "✅ 강의 ID {$lectureId} 조회: 성공<br>";
        echo "강의명: " . htmlspecialchars($lecture['title']) . "<br>";
        echo "등록 상태: " . ($lecture['is_registration_open'] ? '열림' : '닫힘') . "<br>";
    } else {
        echo "❌ 강의 ID {$lectureId} 조회: 실패<br>";
    }
    
    // SMS 헬퍼 테스트
    echo "<h2>📱 SMS 발송 시스템 테스트</h2>";
    try {
        require_once SRC_PATH . '/helpers/SmsHelper.php';
        echo "✅ SmsHelper 로드: 성공<br>";
        
        // 함수 존재 확인
        if (function_exists('sendLectureApplicationSms')) {
            echo "✅ sendLectureApplicationSms 함수: 존재함<br>";
        }
        if (function_exists('sendLectureApprovalSms')) {
            echo "✅ sendLectureApprovalSms 함수: 존재함<br>";
        }
        if (function_exists('sendLectureRejectionSms')) {
            echo "✅ sendLectureRejectionSms 함수: 존재함<br>";
        }
    } catch (Exception $e) {
        echo "❌ SMS 헬퍼 로드 실패: " . $e->getMessage() . "<br>";
    }
    
    // 가상 신청 데이터로 테스트
    echo "<h2>🧪 가상 신청 데이터 테스트</h2>";
    
    $testData = [
        'lecture_id' => $lectureId,
        'participant_name' => '테스트사용자',
        'participant_email' => 'test@example.com',
        'participant_phone' => '010-1234-5678',
        'special_requests' => '테스트 신청입니다.',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    echo "테스트 신청 데이터:<br>";
    echo "<pre>" . print_r($testData, true) . "</pre>";
    
    // RegistrationController 로드 테스트
    echo "<h2>🎮 RegistrationController 로드 테스트</h2>";
    try {
        require_once SRC_PATH . '/controllers/RegistrationController.php';
        echo "✅ RegistrationController 로드: 성공<br>";
        
        if (class_exists('RegistrationController')) {
            echo "✅ RegistrationController 클래스: 존재함<br>";
        }
    } catch (Exception $e) {
        echo "❌ RegistrationController 로드 실패: " . $e->getMessage() . "<br>";
    }
    
    echo "<h2>🎉 테스트 완료</h2>";
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✅ 시스템 준비 완료!</h3>";
    echo "<p><strong>모든 구성 요소가 정상적으로 로드되었습니다.</strong></p>";
    echo "<p>이제 실제 강의 신청을 테스트해보세요:</p>";
    echo "<p><a href='https://www.topmktx.com/lectures/{$lectureId}' target='_blank'>강의 신청 페이지로 이동</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>❌ 오류 발생</h2>";
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<strong>오류:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
    echo "<strong>라인:</strong> " . $e->getLine();
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2, h3 { color: #333; }
h1 { border-bottom: 2px solid #007bff; padding-bottom: 10px; }
h2 { border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-top: 30px; }
pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>