<?php
/**
 * 쿠키 상태 확인 페이지
 */

echo "<h1>🍪 쿠키 상태 확인</h1>";

echo "<h2>모든 쿠키 목록</h2>";
if (empty($_COOKIE)) {
    echo "<p style='color: red;'>❌ <strong>설정된 쿠키가 없습니다.</strong></p>";
} else {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>쿠키명</th><th>값</th><th>길이</th></tr>";
    foreach ($_COOKIE as $name => $value) {
        $shortValue = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($name) . "</strong></td>";
        echo "<td>" . htmlspecialchars($shortValue) . "</td>";
        echo "<td>" . strlen($value) . " chars</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>JWT 관련 쿠키 확인</h2>";
$jwtCookies = ['access_token', 'auth_token', 'jwt_token', 'refresh_token'];
foreach ($jwtCookies as $cookieName) {
    $value = $_COOKIE[$cookieName] ?? null;
    if ($value) {
        echo "<p>✅ <strong>$cookieName:</strong> 존재 (" . strlen($value) . " chars)</p>";
        echo "<p><textarea style='width: 100%; height: 60px; font-family: monospace; font-size: 12px;'>" . htmlspecialchars($value) . "</textarea></p>";
    } else {
        echo "<p>❌ <strong>$cookieName:</strong> 없음</p>";
    }
}

echo "<h2>세션 정보</h2>";
session_start();
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>User ID:</strong> " . ($_SESSION['user_id'] ?? 'N/A') . "</p>";
echo "<p><strong>Auth Method:</strong> " . ($_SESSION['auth_method'] ?? 'N/A') . "</p>";
echo "<p><strong>Last Activity:</strong> " . ($_SESSION['last_activity'] ?? 'N/A') . "</p>";

if (isset($_SESSION['last_activity'])) {
    $lastActivity = date('Y-m-d H:i:s', $_SESSION['last_activity']);
    echo "<p><strong>Last Activity (readable):</strong> $lastActivity</p>";
}

echo "<h2>쿠키 설정 테스트</h2>";
echo "<form method='post'>";
echo "<button type='submit' name='test_cookie'>테스트 쿠키 설정</button>";
echo "</form>";

if (isset($_POST['test_cookie'])) {
    $testValue = 'test_' . time();
    $result = setcookie('test_cookie', $testValue, time() + 3600, '/', '', isset($_SERVER['HTTPS']), true);
    echo "<p><strong>쿠키 설정 결과:</strong> " . ($result ? '✅ 성공' : '❌ 실패') . "</p>";
    echo "<p><strong>HTTPS 상태:</strong> " . (isset($_SERVER['HTTPS']) ? '✅ HTTPS' : '❌ HTTP') . "</p>";
    echo "<p><strong>헤더 전송 여부:</strong> " . (headers_sent() ? '❌ 이미 전송됨' : '✅ 아직 전송 안됨') . "</p>";
    echo "<p style='color: orange;'>페이지를 새로고침하여 쿠키 설정 확인</p>";
}

echo "<h2>수동 JWT 토큰 생성</h2>";
echo "<form method='post'>";
echo "<button type='submit' name='generate_jwt'>JWT 토큰 수동 생성 및 설정</button>";
echo "</form>";

if (isset($_POST['generate_jwt'])) {
    // 프로젝트 경로 설정
    define('PROJECT_ROOT', dirname(__DIR__));
    define('SRC_PATH', PROJECT_ROOT . '/src');

    try {
        require_once SRC_PATH . '/helpers/JWTHelper.php';
        require_once SRC_PATH . '/config/database.php';

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            echo "<p style='color: red;'>❌ 로그인된 사용자가 없습니다.</p>";
        } else {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, email, nickname, role, status FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                $tokens = JWTHelper::createTokenPair($user);

                // 쿠키 설정
                $accessResult = setcookie(
                    'access_token',
                    $tokens['access_token'],
                    time() + 3600,
                    '/',
                    '',
                    isset($_SERVER['HTTPS']),
                    true
                );

                $jwtResult = setcookie(
                    'jwt_token',
                    $tokens['access_token'],
                    time() + 3600,
                    '/',
                    '',
                    isset($_SERVER['HTTPS']),
                    true
                );

                echo "<p>✅ <strong>JWT 토큰 생성 완료</strong></p>";
                echo "<p><strong>access_token 쿠키:</strong> " . ($accessResult ? '✅ 설정됨' : '❌ 실패') . "</p>";
                echo "<p><strong>jwt_token 쿠키:</strong> " . ($jwtResult ? '✅ 설정됨' : '❌ 실패') . "</p>";
                echo "<p style='color: green;'><strong>페이지를 새로고침하여 확인하세요!</strong></p>";

                echo "<h3>테스트 링크</h3>";
                echo "<p><a href='/events/202/edit' target='_blank'>지난 행사 수정 테스트 (202)</a></p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ 오류: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

echo "<hr>";
echo "<p><strong>확인 시간:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>