<?php
/**
 * UserController showMyProfile 메서드 직접 테스트
 */

define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

// 필요한 파일들 include
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/controllers/UserController.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

echo "🔍 UserController::showMyProfile() 직접 테스트\n";
echo "=================================\n\n";

// 세션 시작
session_start();

// 가짜 세션 설정 (우리집탄이 계정)
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_CORPORATE';
$_SESSION['nickname'] = '우리집탄이';

echo "1. 세션 설정 완료:\n";
echo "   user_id: " . $_SESSION['user_id'] . "\n";
echo "   user_role: " . $_SESSION['user_role'] . "\n";
echo "   nickname: " . $_SESSION['nickname'] . "\n\n";

// 인증 상태 확인
$isLoggedIn = AuthMiddleware::isLoggedIn();
$currentUserId = AuthMiddleware::getCurrentUserId();

echo "2. 인증 상태 확인:\n";
echo "   로그인 상태: " . ($isLoggedIn ? "✅" : "❌") . "\n";
echo "   현재 사용자 ID: $currentUserId\n\n";

if (!$isLoggedIn) {
    echo "❌ 로그인 상태가 아니므로 테스트 종료\n";
    exit;
}

try {
    echo "3. UserController 생성 및 실행:\n";
    $controller = new UserController();
    echo "   ✅ UserController 인스턴스 생성 성공\n";

    // 출력 버퍼링 시작하여 실제 HTML 출력 캡처
    ob_start();

    echo "   🚀 showMyProfile() 메서드 실행...\n";
    $startTime = microtime(true);

    $controller->showMyProfile();

    $endTime = microtime(true);
    $duration = ($endTime - $startTime) * 1000;

    $output = ob_get_contents();
    ob_end_clean();

    echo "   ✅ showMyProfile() 실행 완료\n";
    echo "   ⏱️ 실행 시간: " . round($duration, 2) . "ms\n";
    echo "   📄 출력 크기: " . strlen($output) . " bytes\n\n";

    // 오류 메시지가 포함되어 있는지 확인
    if (strpos($output, '프로필을 불러오는 중 오류가 발생했습니다') !== false) {
        echo "❌ 출력에 오류 메시지 포함됨\n";

        // 오류 메시지 주변 내용 추출
        $lines = explode("\n", $output);
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, '프로필을 불러오는 중 오류가 발생했습니다') !== false) {
                echo "   오류 라인 번호: " . ($lineNum + 1) . "\n";
                echo "   오류 메시지: " . trim($line) . "\n";
                break;
            }
        }
    } else {
        echo "✅ 정상적인 프로필 페이지 출력됨\n";

        // 성공적인 경우 간단한 내용 확인
        if (strpos($output, '우리집탄이') !== false) {
            echo "   ✅ 닉네임 '우리집탄이' 포함됨\n";
        }
        if (strpos($output, 'profile-container') !== false) {
            echo "   ✅ 프로필 컨테이너 요소 포함됨\n";
        }
    }

    // 헤더 상태 확인
    $headers = headers_list();
    if (!empty($headers)) {
        echo "\n4. HTTP 헤더 상태:\n";
        foreach ($headers as $header) {
            if (stripos($header, 'Location:') === 0) {
                echo "   🔀 리다이렉트: $header\n";
            } elseif (stripos($header, 'HTTP/') === 0) {
                echo "   📡 상태: $header\n";
            }
        }
    }

} catch (Exception $e) {
    echo "❌ showMyProfile() 실행 오류:\n";
    echo "   메시지: " . $e->getMessage() . "\n";
    echo "   파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   스택 추적:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n🏁 테스트 완료\n";
?>