<?php
/**
 * 현재 세션 사용자를 위한 JWT 토큰 생성
 */

// 프로젝트 경로 설정
define('PROJECT_ROOT', dirname(__DIR__));
define('SRC_PATH', PROJECT_ROOT . '/src');

// 세션 시작
session_start();

// 필요한 파일들 include
require_once SRC_PATH . '/helpers/JWTHelper.php';
require_once SRC_PATH . '/config/database.php';

echo "<h1>🔑 JWT 토큰 생성기</h1>";

// 현재 세션 확인
$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? null;

echo "<h2>현재 세션 정보</h2>";
echo "<p><strong>User ID:</strong> " . ($userId ?? 'N/A') . "</p>";
echo "<p><strong>User Role:</strong> " . ($userRole ?? 'N/A') . "</p>";

if (!$userId) {
    echo "<p style='color: red;'>❌ <strong>로그인된 사용자가 없습니다.</strong></p>";
    echo "<p><a href='/auth/login'>로그인 페이지로 이동</a></p>";
    exit;
}

try {
    // 데이터베이스에서 사용자 정보 조회
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, email, nickname, role, status FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo "<p style='color: red;'>❌ <strong>사용자 정보를 찾을 수 없습니다.</strong></p>";
        exit;
    }

    echo "<h2>데이터베이스 사용자 정보</h2>";
    echo "<p><strong>ID:</strong> " . $user['id'] . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
    echo "<p><strong>Nickname:</strong> " . htmlspecialchars($user['nickname']) . "</p>";
    echo "<p><strong>Role:</strong> " . $user['role'] . "</p>";
    echo "<p><strong>Status:</strong> " . $user['status'] . "</p>";

    // JWT 토큰 생성
    $payload = [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'user_role' => $user['role'],
        'nickname' => $user['nickname'],
        'status' => $user['status'],
        'iat' => time(),
        'exp' => time() + (24 * 60 * 60) // 24시간 만료
    ];

    echo "<h2>JWT 페이로드</h2>";
    echo "<pre>" . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

    $jwtToken = JWTHelper::encode($payload);

    if ($jwtToken) {
        echo "<h2>✅ JWT 토큰 생성 성공</h2>";
        echo "<p><strong>Token:</strong></p>";
        echo "<textarea style='width: 100%; height: 100px; word-wrap: break-word;'>" . $jwtToken . "</textarea>";

        // 쿠키 설정
        $cookieSet = setcookie(
            'jwt_token',
            $jwtToken,
            [
                'expires' => time() + (24 * 60 * 60),
                'path' => '/',
                'domain' => '',
                'secure' => true, // HTTPS에서만
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );

        if ($cookieSet) {
            echo "<p style='color: green;'>✅ <strong>JWT 토큰이 쿠키에 설정되었습니다.</strong></p>";
            echo "<p><strong>쿠키명:</strong> jwt_token</p>";
            echo "<p><strong>만료시간:</strong> 24시간</p>";
            echo "<p><strong>경로:</strong> /</p>";
            echo "<p><strong>보안설정:</strong> HttpOnly, Secure, SameSite=Strict</p>";

            echo "<h2>🎯 다음 단계</h2>";
            echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #0066cc;'>";
            echo "<p><strong>이제 다음 URL들을 테스트해보세요:</strong></p>";
            echo "<ul>";
            echo "<li><a href='/events/202/edit' target='_blank' style='color: orange;'>지난 행사 수정 (202) - 차단 메시지 확인</a></li>";
            echo "<li><a href='/events/205/edit' target='_blank' style='color: green;'>미래 행사 수정 (205) - 정상 접근</a></li>";
            echo "<li><a href='/lectures/3/edit' target='_blank' style='color: blue;'>지난 강의 수정 (3) - 차단 메시지 확인</a></li>";
            echo "</ul>";
            echo "<p style='color: red;'><strong>예상 결과:</strong> '지난 일정은 수정할 수 없습니다. 삭제만 가능합니다.' 메시지 표시</p>";
            echo "</div>";

        } else {
            echo "<p style='color: red;'>❌ <strong>쿠키 설정에 실패했습니다.</strong></p>";
            echo "<p>헤더가 이미 전송되었을 수 있습니다.</p>";
        }

    } else {
        echo "<p style='color: red;'>❌ <strong>JWT 토큰 생성에 실패했습니다.</strong></p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>오류:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><strong>생성 시간:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='javascript:history.back()'>뒤로 가기</a> | <a href='/'>홈으로</a></p>";
?>