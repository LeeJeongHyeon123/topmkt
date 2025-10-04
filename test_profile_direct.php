<?php
/**
 * 프로필 페이지 직접 테스트 (로그인 우회)
 */

define('BASE_PATH', '/var/www/html/topmkt');
define('SRC_PATH', BASE_PATH . '/src');

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/controllers/UserController.php';

// 세션 시작
session_start();

echo "🔍 프로필 페이지 직접 테스트\n";
echo "=================================\n\n";

try {
    // 1. 우리집탄이 사용자 정보 확인
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, nickname, status FROM users WHERE id = 4");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "❌ 우리집탄이 사용자(ID: 4)를 찾을 수 없습니다.\n";
        exit;
    }

    echo "✅ 사용자 정보 확인:\n";
    echo "   ID: {$user['id']}\n";
    echo "   닉네임: {$user['nickname']}\n";
    echo "   상태: {$user['status']}\n\n";

    // 2. 가짜 세션 설정 (로그인 우회)
    $_SESSION['user_id'] = 4;
    $_SESSION['user_role'] = 'ROLE_USER';
    $_SESSION['nickname'] = '우리집탄이';

    echo "🔧 가짜 세션 설정 완료\n\n";

    // 3. AuthMiddleware 로드 후 테스트
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

    $isLoggedIn = AuthMiddleware::isLoggedIn();
    $currentUserId = AuthMiddleware::getCurrentUserId();

    echo "🔐 인증 상태 확인:\n";
    echo "   로그인 상태: " . ($isLoggedIn ? "✅ 로그인됨" : "❌ 로그인 안됨") . "\n";
    echo "   현재 사용자 ID: $currentUserId\n\n";

    if ($isLoggedIn) {
        // 4. UserController 직접 실행
        echo "🚀 UserController::showMyProfile() 실행...\n";

        $controller = new UserController();

        // 출력 버퍼링 시작
        ob_start();

        try {
            $controller->showMyProfile();
            $output = ob_get_contents();

            if (headers_sent($filename, $linenum)) {
                echo "📤 헤더가 전송됨 (파일: $filename, 라인: $linenum)\n";
            }

            echo "✅ showMyProfile() 실행 성공\n";
            echo "📄 출력 길이: " . strlen($output) . " bytes\n";

            // 리다이렉트 확인
            $headers = headers_list();
            foreach ($headers as $header) {
                if (stripos($header, 'Location:') === 0) {
                    echo "🔀 리다이렉트: $header\n";
                }
            }

        } catch (Exception $e) {
            echo "❌ showMyProfile() 실행 오류: " . $e->getMessage() . "\n";
            echo "📍 오류 위치: " . $e->getFile() . ":" . $e->getLine() . "\n";
        } finally {
            ob_end_clean();
        }

    } else {
        echo "❌ 로그인 상태가 아니므로 테스트 불가\n";
    }

} catch (Exception $e) {
    echo "❌ 테스트 실패: " . $e->getMessage() . "\n";
    echo "📍 오류 위치: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n🏁 테스트 완료\n";
?>