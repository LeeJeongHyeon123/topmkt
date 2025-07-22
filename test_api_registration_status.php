<?php
/**
 * 안계현 사용자 강의 167 신청 상태 API 테스트
 */

// 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

require_once 'src/config/config.php';
require_once 'src/config/database.php';
require_once 'src/controllers/RegistrationController.php';
require_once 'src/middlewares/AuthMiddleware.php';

// 세션 시작
session_start();

// 안계현 사용자로 로그인 설정
$_SESSION['user_id'] = 5;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['nickname'] = '안계현';

echo "=== 안계현 사용자 강의 167 신청 상태 API 테스트 ===\n\n";

try {
    $controller = new RegistrationController();
    
    // 출력 버퍼링 시작
    ob_start();
    
    // API 호출
    $controller->getRegistrationStatus(167);
    
    // 결과 출력
    $result = ob_get_clean();
    
    // HTML 헤더 제거 (Content-Type 등)
    $lines = explode("\n", $result);
    $jsonLine = '';
    foreach ($lines as $line) {
        if (trim($line) && substr(trim($line), 0, 1) === '{') {
            $jsonLine = trim($line);
            break;
        }
    }
    echo "API 응답 (원본):\n";
    echo $result . "\n\n";
    
    echo "JSON 라인: " . $jsonLine . "\n\n";
    
    // JSON 파싱해서 예쁘게 출력
    $data = json_decode($jsonLine ?: $result, true);
    if ($data) {
        echo "=== 파싱된 데이터 구조 ===\n";
        echo "status: " . ($data['status'] ?? 'null') . "\n";
        echo "message: " . ($data['message'] ?? 'null') . "\n\n";
        
        if (isset($data['data'])) {
            echo "=== data 객체 ===\n";
            if (isset($data['data']['registration'])) {
                $reg = $data['data']['registration'];
                echo "registration.status: " . ($reg['status'] ?? 'null') . "\n";
                echo "registration.admin_notes: " . ($reg['admin_notes'] ?? 'null') . "\n";
                echo "registration.id: " . ($reg['id'] ?? 'null') . "\n";
                echo "registration.created_at: " . ($reg['created_at'] ?? 'null') . "\n";
                echo "registration.processed_at: " . ($reg['processed_at'] ?? 'null') . "\n\n";
            }
            
            if (isset($data['data']['lecture_info'])) {
                echo "lecture_info.title: " . ($data['data']['lecture_info']['title'] ?? 'null') . "\n";
            }
        }
        
        echo "\n=== 전체 data 구조 ===\n";
        print_r($data['data'] ?? []);
    } else {
        echo "JSON 파싱 실패!\n";
    }
    
} catch (Exception $e) {
    echo "오류: " . $e->getMessage() . "\n";
}